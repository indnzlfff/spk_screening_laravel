<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Categorical Naive Bayes used by the school for an initial screening map.
 * The result is not a diagnosis and does not decide student admission.
 */
class NaiveBayesScreeningService
{
    public const CLASSES = [
        'Sesuai harapan',
        'Memerlukan perhatian lebih lanjut',
    ];

    public const POSITIVE_CLASS = 'Memerlukan perhatian lebih lanjut';

    public const ASPECTS = [
        'nilai_agama_moral' => 'Nilai agama dan moral',
        'motorik' => 'Fisik-motorik',
        'kognitif' => 'Kognitif',
        'bahasa' => 'Bahasa',
        'sosial_emosional' => 'Sosial-emosional',
    ];

    public const FEATURES = [
        'usia_kategori' => 'Kategori usia saat pengamatan',
        'nilai_agama_moral' => 'Nilai agama dan moral',
        'motorik' => 'Fisik-motorik',
        'kognitif' => 'Kognitif',
        'bahasa' => 'Bahasa',
        'sosial_emosional' => 'Sosial-emosional',
    ];

    private const CATEGORIES = [
        'usia_kategori' => ['A', 'B', 'C'],
        'nilai_agama_moral' => ['1', '2', '3', '4'],
        'motorik' => ['1', '2', '3', '4'],
        'kognitif' => ['1', '2', '3', '4'],
        'bahasa' => ['1', '2', '3', '4'],
        'sosial_emosional' => ['1', '2', '3', '4'],
    ];

    /** @return array<int, array<string, mixed>> */
    public function trainingData(): array
    {
        return DB::table('data_latih_naive_bayes')
            ->whereBetween('nilai_agama_moral', [1, 4])
            ->whereBetween('motorik', [1, 4])
            ->whereBetween('kognitif', [1, 4])
            ->whereBetween('bahasa', [1, 4])
            ->whereBetween('sosial_emosional', [1, 4])
            ->orderByDesc('id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /** @param array<int, array<string, mixed>> $rows @return array<int, string> */
    public function configurationErrors(array $rows): array
    {
        $counts = array_fill_keys(self::CLASSES, 0);
        foreach ($rows as $row) {
            if (isset($counts[$row['kelas'] ?? ''])) {
                $counts[$row['kelas']]++;
            }
        }

        $errors = [];
        foreach ($counts as $class => $count) {
            if ($count < 1) {
                $errors[] = 'Data latih berlabel “'.$class.'” belum tersedia.';
            }
        }

        foreach (self::ASPECTS as $feature => $label) {
            foreach (self::CLASSES as $class) {
                $hasCategory = collect($rows)->contains(function (array $row) use ($feature, $class): bool {
                    return ($row['kelas'] ?? null) === $class
                        && in_array((string) ($row[$feature] ?? ''), self::CATEGORIES[$feature], true);
                });

                if (! $hasCategory) {
                    $errors[] = 'Data aspek '.$label.' untuk kelas “'.$class.'” belum tersedia.';
                }
            }
        }

        return $errors;
    }

    /** Usia tepat lima tahun tetap kategori B; kategori C mulai setelahnya. */
    public static function ageCategory(string $birthDate, string $referenceDate): string
    {
        $birth = new \DateTimeImmutable($birthDate);
        $reference = new \DateTimeImmutable($referenceDate);

        if ($birth > $reference) {
            throw new RuntimeException('Tanggal lahir calon siswa tidak valid.');
        }

        $addCalendarMonths = static function (\DateTimeImmutable $date, int $months): \DateTimeImmutable {
            $targetMonth = $date->modify('first day of this month')->modify('+'.$months.' months');
            $day = min((int) $date->format('d'), (int) $targetMonth->format('t'));

            return $targetMonth->setDate(
                (int) $targetMonth->format('Y'),
                (int) $targetMonth->format('m'),
                $day,
            );
        };

        if ($reference > $addCalendarMonths($birth, 60)) {
            return 'C';
        }

        return $reference < $addCalendarMonths($birth, 54) ? 'A' : 'B';
    }

    /** Nilai 0 belum teramati: pemanggil hanya mengirim nilai 1–4 di sini. */
    public static function aspectCategory(array $observedValues): ?string
    {
        if ($observedValues === []) {
            return null;
        }

        return (string) max(1, min(4, (int) round(array_sum($observedValues) / count($observedValues))));
    }

    /** @param array<int, array<string, mixed>> $training @param array<string, string|int|null> $features */
    public static function predict(array $training, array $features): array
    {
        $classRows = array_fill_keys(self::CLASSES, []);
        foreach ($training as $row) {
            if (isset($classRows[$row['kelas'] ?? ''])) {
                $classRows[$row['kelas']][] = $row;
            }
        }

        foreach (self::CLASSES as $class) {
            if ($classRows[$class] === []) {
                throw new RuntimeException('Kedua kelas harus memiliki data latih.');
            }
        }

        $usableFeatures = [];
        foreach (self::FEATURES as $feature => $label) {
            if (! array_key_exists($feature, $features) || $features[$feature] === null || $features[$feature] === '') {
                continue;
            }

            if (! in_array((string) $features[$feature], self::CATEGORIES[$feature], true)) {
                throw new RuntimeException('Nilai fitur '.$label.' berada di luar kategori yang diizinkan.');
            }

            foreach (self::CLASSES as $class) {
                $hasCategory = collect($classRows[$class])->contains(
                    fn (array $row): bool => in_array((string) ($row[$feature] ?? ''), self::CATEGORIES[$feature], true),
                );

                if (! $hasCategory) {
                    throw new RuntimeException('Data latih aspek '.$label.' belum tersedia pada kelas “'.$class.'”.');
                }
            }

            $usableFeatures[] = $feature;
        }

        if ($usableFeatures === []) {
            throw new RuntimeException('Tidak ada fitur screening yang dapat dihitung.');
        }

        $total = count($training);
        $logPosterior = [];
        $likelihoods = [];

        foreach (self::CLASSES as $class) {
            $classSet = $classRows[$class];
            $count = count($classSet);
            $logPosterior[$class] = log(($count + 1) / ($total + count(self::CLASSES)));
            $likelihoods[$class] = [];

            foreach ($usableFeatures as $feature) {
                $categories = self::CATEGORIES[$feature];
                $observed = array_values(array_filter(
                    $classSet,
                    fn (array $row): bool => in_array((string) ($row[$feature] ?? ''), $categories, true),
                ));
                $matches = count(array_filter(
                    $observed,
                    fn (array $row): bool => (string) $row[$feature] === (string) $features[$feature],
                ));
                $probability = ($matches + 1) / (count($observed) + count($categories));
                $likelihoods[$class][$feature] = $probability;
                $logPosterior[$class] += log($probability);
            }
        }

        $maximum = max($logPosterior);
        $scaled = [];
        foreach (self::CLASSES as $class) {
            $scaled[$class] = exp($logPosterior[$class] - $maximum);
        }
        $sum = array_sum($scaled);
        $probabilities = [];
        foreach (self::CLASSES as $class) {
            $probabilities[$class] = $scaled[$class] / $sum;
        }

        $predictedClass = $probabilities[self::CLASSES[0]] >= $probabilities[self::CLASSES[1]]
            ? self::CLASSES[0]
            : self::CLASSES[1];

        return [
            'class' => $predictedClass,
            'probabilities' => $probabilities,
            'likelihoods' => $likelihoods,
        ];
    }

    /** Reproducible stratified 80/20 holdout evaluation. */
    public static function evaluate(array $rows): array
    {
        $byClass = array_fill_keys(self::CLASSES, []);
        foreach ($rows as $row) {
            if (isset($byClass[$row['kelas'] ?? ''])) {
                $byClass[$row['kelas']][] = $row;
            }
        }

        foreach ($byClass as $class => $items) {
            if (count($items) < 2) {
                throw new RuntimeException('Evaluasi memerlukan sedikitnya dua catatan pada setiap kelas agar data latih dan data uji sama-sama terisi.');
            }
        }

        mt_srand(20261018);
        $training = [];
        $testing = [];
        foreach ($byClass as $items) {
            for ($index = count($items) - 1; $index > 0; $index--) {
                $swap = mt_rand(0, $index);
                [$items[$index], $items[$swap]] = [$items[$swap], $items[$index]];
            }

            $testCount = max(1, (int) floor(count($items) * 0.2));
            if ($testCount >= count($items)) {
                $testCount = count($items) - 1;
            }
            $testing = array_merge($testing, array_slice($items, 0, $testCount));
            $training = array_merge($training, array_slice($items, $testCount));
        }

        $matrix = array_fill_keys(self::CLASSES, array_fill_keys(self::CLASSES, 0));
        foreach ($testing as $row) {
            $features = $row;
            $matrix[$row['kelas']][self::predict($training, $features)['class']]++;
        }

        $positive = self::POSITIVE_CLASS;
        $negative = self::CLASSES[0];
        $truePositive = $matrix[$positive][$positive];
        $falseNegative = $matrix[$positive][$negative];
        $falsePositive = $matrix[$negative][$positive];
        $trueNegative = $matrix[$negative][$negative];
        $precision = ($truePositive + $falsePositive) > 0 ? $truePositive / ($truePositive + $falsePositive) : 0.0;
        $recall = ($truePositive + $falseNegative) > 0 ? $truePositive / ($truePositive + $falseNegative) : 0.0;

        return [
            'matrix' => $matrix,
            'train_count' => count($training),
            'test_count' => count($testing),
            'positive_class' => $positive,
            'accuracy' => ($truePositive + $trueNegative) / max(1, $truePositive + $trueNegative + $falsePositive + $falseNegative),
            'precision' => $precision,
            'recall' => $recall,
            'f1' => ($precision + $recall) > 0 ? 2 * $precision * $recall / ($precision + $recall) : 0.0,
        ];
    }

    /** Calculate and persist one screening; caller should wrap batch actions in a transaction. */
    public function recordForApplicant(int $applicantId, int $userId): int
    {
        $result = $this->calculateForApplicant($applicantId);
        $screeningId = DB::table('screening')->insertGetId([
            'nomor_screening' => 'SCR-'.now()->format('ymd').'-'.random_int(100000, 999999),
            'pendaftar_id' => $applicantId,
            'metode' => 'Naive Bayes',
            'kelas_prediksi' => $result['class'],
            'probabilitas_sesuai_harapan' => number_format($result['probabilities'][self::CLASSES[0]], 10, '.', ''),
            'probabilitas_perhatian_lanjutan' => number_format($result['probabilities'][self::CLASSES[1]], 10, '.', ''),
            'rekomendasi' => $result['class'],
            'catatan' => 'Hasil klasifikasi Categorical Naive Bayes hanya merupakan informasi awal untuk tindak lanjut sekolah, bukan diagnosis dan bukan penentu penerimaan.',
            'dihitung_oleh' => $userId,
        ]);

        DB::table('screening_validations')->insert([
            'screening_id' => $screeningId,
            'status' => 'dikonfirmasi',
            'catatan_admin' => $this->automaticParentNote($result),
            'validated_by' => $userId,
            'validated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($result['features'] as $code => $value) {
            DB::table('detail_screening_naive_bayes')->insert([
                'screening_id' => $screeningId,
                'kode_fitur' => $code,
                'nama_fitur' => self::FEATURES[$code] ?? $code,
                'nilai_fitur' => $value,
                'peluang_sesuai_harapan' => $result['likelihoods'][self::CLASSES[0]][$code] ?? null,
                'peluang_perhatian_lanjutan' => $result['likelihoods'][self::CLASSES[1]][$code] ?? null,
            ]);
        }

        return (int) $screeningId;
    }

    /** Create a plain-language parent note directly from the stored prediction. */
    private function automaticParentNote(array $result): string
    {
        $class = (string) $result['class'];
        $confidence = number_format((float) ($result['probabilities'][$class] ?? 0) * 100, 2, ',', '.');
        $followUp = $class === self::CLASSES[0]
            ? 'Lanjutkan stimulasi dan pemantauan perkembangan anak sesuai kebutuhannya.'
            : 'Sekolah mengajak orang tua berdiskusi untuk merencanakan pendampingan dan pemantauan lanjutan.';

        return 'Pemetaan awal berdasarkan pola data latih sekolah: '.$class.' (peluang '.$confidence.'%). '
            .$followUp.' Hasil ini bukan diagnosis medis dan tidak menentukan penerimaan siswa.';
    }

    /** @return array<string, mixed> */
    public function calculateForApplicant(int $applicantId): array
    {
        $training = $this->trainingData();
        $errors = $this->configurationErrors($training);
        if ($errors !== []) {
            throw new RuntimeException(implode(' ', $errors).' Tambahkan data latih berlabel yang sudah ditetapkan atau divalidasi sekolah.');
        }

        $person = DB::table('pendaftar')->where('id', $applicantId)->first();
        if (! $person) {
            throw new RuntimeException('Data pendaftar tidak ditemukan.');
        }

        $criteria = DB::table('kriteria')
            ->where('aktif', 1)
            ->whereIn('kode', ['C1', 'C2', 'C3', 'C4', 'C5'])
            ->orderBy('kode')
            ->get()
            ->keyBy('kode');

        if ($criteria->count() !== 5) {
            throw new RuntimeException('Lima aspek perkembangan pada proposal belum disiapkan di pengaturan kriteria.');
        }

        $questions = DB::table('indikator as i')
            ->join('kriteria as k', 'k.id', '=', 'i.kriteria_id')
            ->where('i.aktif', 1)
            ->where('k.aktif', 1)
            ->whereIn('k.kode', ['C1', 'C2', 'C3', 'C4', 'C5'])
            ->orderBy('k.kode')
            ->orderBy('i.kode')
            ->select(
                'i.id as indikator_id',
                'i.kode as indikator_kode',
                'i.pertanyaan',
                'k.kode as kode_kriteria',
            )
            ->get();

        $perAspect = $questions->groupBy('kode_kriteria');
        foreach (['C1', 'C2', 'C3', 'C4', 'C5'] as $code) {
            if (($perAspect->get($code)?->count() ?? 0) !== 4) {
                throw new RuntimeException('Pastikan setiap aspek memiliki tepat 4 pertanyaan aktif (20 pertanyaan seluruhnya).');
            }
        }

        $answers = DB::table('jawaban')
            ->where('pendaftar_id', $applicantId)
            ->pluck('nilai', 'indikator_id');
        $codeToFeature = [
            'C1' => 'nilai_agama_moral',
            'C2' => 'motorik',
            'C3' => 'kognitif',
            'C4' => 'bahasa',
            'C5' => 'sosial_emosional',
        ];

        $aspectValues = [];
        foreach ($perAspect as $code => $items) {
            $observed = [];
            foreach ($items as $question) {
                if (! $answers->has($question->indikator_id)) {
                    throw new RuntimeException('Kuesioner belum lengkap. Pilih nilai 0–4 pada seluruh 20 pertanyaan; 0 berarti belum teramati.');
                }
                $value = (float) $answers[$question->indikator_id];
                if ($value < 0 || $value > 4 || floor($value) !== $value) {
                    throw new RuntimeException('Jawaban kuesioner harus berupa nilai 0 sampai 4.');
                }
                if ($value > 0) {
                    $observed[] = $value;
                }
            }
            $aspectValues[$codeToFeature[$code]] = self::aspectCategory($observed);
        }

        $questionnaireDate = DB::table('jawaban as j')
            ->join('indikator as i', 'i.id', '=', 'j.indikator_id')
            ->where('j.pendaftar_id', $applicantId)
            ->where('i.aktif', 1)
            ->min('j.created_at');

        $referenceDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) ($questionnaireDate ?: $person->created_at));
        if (! $referenceDate) {
            $referenceDate = new \DateTimeImmutable((string) ($questionnaireDate ?: $person->created_at));
        }

        $features = [
            'usia_kategori' => self::ageCategory((string) $person->tanggal_lahir, $referenceDate->format('Y-m-d')),
            ...$aspectValues,
        ];
        $prediction = self::predict($training, $features);

        return [
            'person' => (array) $person,
            'features' => $features,
            'class' => $prediction['class'],
            'probabilities' => $prediction['probabilities'],
            'likelihoods' => $prediction['likelihoods'],
        ];
    }
}

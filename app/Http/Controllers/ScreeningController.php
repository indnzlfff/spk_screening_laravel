<?php

namespace App\Http\Controllers;

use App\Services\NaiveBayesScreeningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ScreeningController extends Controller
{
    public function index(NaiveBayesScreeningService $model): View
    {
        $activeQuestions = (int) DB::table('indikator as i')
            ->join('kriteria as k', 'k.id', '=', 'i.kriteria_id')
            ->where('i.aktif', 1)->where('k.aktif', 1)
            ->whereIn('k.kode', ['C1', 'C2', 'C3', 'C4', 'C5'])->count();

        $answerCounts = DB::table('jawaban as j')
            ->join('indikator as i', 'i.id', '=', 'j.indikator_id')
            ->join('kriteria as k', 'k.id', '=', 'i.kriteria_id')
            ->where('i.aktif', 1)->where('k.aktif', 1)
            ->whereIn('k.kode', ['C1', 'C2', 'C3', 'C4', 'C5'])
            ->select('j.pendaftar_id')->selectRaw('COUNT(*) AS total_jawaban')
            ->groupBy('j.pendaftar_id');

        $applicants = DB::table('pendaftar as p')
            ->leftJoinSub($answerCounts, 'answers', 'answers.pendaftar_id', '=', 'p.id')
            ->select('p.id', 'p.nomor_pendaftaran', 'p.nama_anak', 'p.created_at')
            ->selectRaw('COALESCE(answers.total_jawaban, 0) AS jumlah_jawaban')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')->from('screening as existing')
                    ->whereColumn('existing.pendaftar_id', 'p.id')
                    ->where('existing.metode', 'Naive Bayes');
            })
            ->orderBy('p.nama_anak')->orderBy('p.id')->get();

        $training = $model->trainingData();

        return view('admin.screening.index', [
            'activeQuestions' => $activeQuestions,
            'trainingErrors' => $model->configurationErrors($training),
            'applicants' => $applicants,
        ]);
    }

    public function store(Request $request, NaiveBayesScreeningService $model): RedirectResponse
    {
        $validated = $request->validate([
            'pendaftar_ids' => ['required', 'array', 'min:1'],
            'pendaftar_ids.*' => ['required', 'integer', 'distinct', 'exists:pendaftar,id'],
        ]);

        $activeQuestions = (int) DB::table('indikator as i')
            ->join('kriteria as k', 'k.id', '=', 'i.kriteria_id')
            ->where('i.aktif', 1)->where('k.aktif', 1)
            ->whereIn('k.kode', ['C1', 'C2', 'C3', 'C4', 'C5'])->count();

        if ($activeQuestions !== 20) {
            return back()->withErrors(['screening' => 'Screening membutuhkan tepat 20 pertanyaan aktif, empat untuk setiap aspek.']);
        }

        $trainingErrors = $model->configurationErrors($model->trainingData());
        if ($trainingErrors !== []) {
            return back()->withErrors(['screening' => implode(' ', $trainingErrors).' Lengkapi data latih yang labelnya telah ditetapkan atau divalidasi sekolah.']);
        }

        try {
            $selectedIds = collect($validated['pendaftar_ids'])->map(fn ($id): int => (int) $id)->sort()->values();
            $ids = DB::transaction(function () use ($selectedIds, $model): array {
                $saved = [];
                foreach ($selectedIds as $applicantId) {
                    $applicant = DB::table('pendaftar')->where('id', $applicantId)->lockForUpdate()->first();
                    if (! $applicant) {
                        throw new RuntimeException('Data pendaftar tidak ditemukan. Muat ulang antrean lalu coba kembali.');
                    }

                    $alreadyScreened = DB::table('screening')
                        ->where('pendaftar_id', $applicantId)
                        ->where('metode', 'Naive Bayes')
                        ->exists();
                    if ($alreadyScreened) {
                        throw new RuntimeException('Pendaftar ini sudah pernah dihitung. Hasilnya tersedia di Riwayat hasil.');
                    }

                    $completeAnswers = DB::table('jawaban as j')
                        ->join('indikator as i', 'i.id', '=', 'j.indikator_id')
                        ->join('kriteria as k', 'k.id', '=', 'i.kriteria_id')
                        ->where('j.pendaftar_id', $applicantId)
                        ->where('i.aktif', 1)->where('k.aktif', 1)
                        ->whereIn('k.kode', ['C1', 'C2', 'C3', 'C4', 'C5'])->count();

                    if ($completeAnswers !== 20) {
                        throw new RuntimeException('Ada calon siswa yang kuesionernya belum lengkap. Periksa antrean lalu coba kembali.');
                    }

                    $saved[] = $model->recordForApplicant((int) $applicantId, (int) session('school_user_id'));
                }

                return $saved;
            });
        } catch (RuntimeException $exception) {
            return back()->withErrors(['screening' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['screening' => 'Hasil screening belum tersimpan. Periksa koneksi database lalu coba kembali.']);
        }

        return count($ids) === 1
            ? redirect()->route('admin.screening.result', $ids[0])->with('status', 'Screening berhasil dihitung dan disimpan.')
            : redirect()->route('admin.screening.history')->with('status', count($ids).' hasil screening berhasil disimpan.');
    }

    public function result(int $id): View
    {
        $result = DB::table('screening as s')
            ->join('pendaftar as p', 'p.id', '=', 's.pendaftar_id')
            ->where('s.id', $id)->where('s.metode', 'Naive Bayes')
            ->select('s.*', 'p.nomor_pendaftaran', 'p.nama_anak', 'p.tanggal_lahir')
            ->first();

        abort_if(! $result, 404);

        $details = DB::table('detail_screening_naive_bayes')
            ->where('screening_id', $id)
            ->orderByRaw("FIELD(kode_fitur, 'usia_kategori','nilai_agama_moral','motorik','kognitif','bahasa','sosial_emosional')")
            ->get();

        $validation = DB::table('screening_validations')->where('screening_id', $id)->first();

        return view('admin.screening.result', compact('result', 'details', 'validation'));
    }

    public function history(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $query = DB::table('screening as s')
            ->join('pendaftar as p', 'p.id', '=', 's.pendaftar_id')
            ->where('s.metode', 'Naive Bayes')
            ->leftJoin('screening_validations as v', 'v.screening_id', '=', 's.id')
            ->select('s.id', 's.nomor_screening', 's.kelas_prediksi', 's.created_at', 'p.nomor_pendaftaran', 'p.nama_anak', 'v.status as status_validasi')
            ->orderByDesc('s.created_at');

        if ($search !== '') {
            $query->where(function ($filter) use ($search): void {
                $filter->where('p.nama_anak', 'like', '%'.$search.'%')
                    ->orWhere('p.nomor_pendaftaran', 'like', '%'.$search.'%')
                    ->orWhere('s.nomor_screening', 'like', '%'.$search.'%');
            });
        }

        return view('admin.screening.history', ['rows' => $query->paginate(15)->withQueryString(), 'search' => $search]);
    }
}

<?php

namespace Tests\Unit;

use App\Services\NaiveBayesScreeningService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NaiveBayesScreeningServiceTest extends TestCase
{
    #[Test]
    public function age_is_classified_at_the_agreed_boundaries(): void
    {
        self::assertSame('A', NaiveBayesScreeningService::ageCategory('2021-01-02', '2025-07-01'));
        self::assertSame('B', NaiveBayesScreeningService::ageCategory('2021-01-01', '2025-07-01'));
        self::assertSame('B', NaiveBayesScreeningService::ageCategory('2020-01-01', '2025-01-01'));
        self::assertSame('C', NaiveBayesScreeningService::ageCategory('2020-01-01', '2025-01-02'));
    }

    #[Test]
    public function unobserved_answers_are_excluded_from_the_aspect_average(): void
    {
        self::assertSame('4', NaiveBayesScreeningService::aspectCategory([3, 4]));
        self::assertNull(NaiveBayesScreeningService::aspectCategory([]));
    }

    #[Test]
    public function categorical_model_returns_a_valid_class_and_normalized_probabilities(): void
    {
        $training = [
            ['kelas' => 'Sesuai harapan', 'usia_kategori' => 'B', 'nilai_agama_moral' => 4, 'motorik' => 4, 'kognitif' => 4, 'bahasa' => 4, 'sosial_emosional' => 4],
            ['kelas' => 'Sesuai harapan', 'usia_kategori' => 'B', 'nilai_agama_moral' => 3, 'motorik' => 3, 'kognitif' => 3, 'bahasa' => 3, 'sosial_emosional' => 3],
            ['kelas' => 'Memerlukan perhatian lebih lanjut', 'usia_kategori' => 'B', 'nilai_agama_moral' => 1, 'motorik' => 1, 'kognitif' => 1, 'bahasa' => 1, 'sosial_emosional' => 1],
            ['kelas' => 'Memerlukan perhatian lebih lanjut', 'usia_kategori' => 'A', 'nilai_agama_moral' => 1, 'motorik' => 1, 'kognitif' => 1, 'bahasa' => 1, 'sosial_emosional' => 1],
        ];
        $features = ['usia_kategori' => 'B', 'nilai_agama_moral' => '4', 'motorik' => '4', 'kognitif' => '4', 'bahasa' => '4', 'sosial_emosional' => '4'];

        $result = NaiveBayesScreeningService::predict($training, $features);

        self::assertSame('Sesuai harapan', $result['class']);
        self::assertEqualsWithDelta(1.0, array_sum($result['probabilities']), 1e-10);
    }

    #[Test]
    public function model_refuses_to_predict_without_both_school_labels(): void
    {
        $this->expectException(\RuntimeException::class);
        NaiveBayesScreeningService::predict(
            [['kelas' => 'Sesuai harapan']],
            ['usia_kategori' => 'B'],
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\NaiveBayesScreeningService as Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class TrainingDataController extends Controller
{
    public function index(Model $model): View
    {
        $rows = DB::table('data_latih_naive_bayes')->orderByDesc('created_at')->orderByDesc('id')->paginate(20);
        $usableCount = count($model->trainingData());
        $classCounts = collect($model->trainingData())->countBy('kelas');

        return view('admin.training.index', [
            'rows' => $rows,
            'usableCount' => $usableCount,
            'classCounts' => $classCounts,
            'aspects' => Model::ASPECTS,
            'classes' => Model::CLASSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_data' => ['nullable', 'string', 'regex:/^[A-Za-z0-9-]{3,40}$/', 'max:40'],
            'usia_kategori' => ['required', 'in:A,B,C'],
            'kelas' => ['required', 'in:'.implode(',', Model::CLASSES)],
            'nilai_agama_moral' => ['required', 'integer', 'between:1,4'],
            'motorik' => ['required', 'integer', 'between:1,4'],
            'kognitif' => ['required', 'integer', 'between:1,4'],
            'bahasa' => ['required', 'integer', 'between:1,4'],
            'sosial_emosional' => ['required', 'integer', 'between:1,4'],
            'sumber' => ['nullable', 'string', 'max:160'],
        ], [
            'kelas.in' => 'Pilih label yang telah ditetapkan atau divalidasi sekolah.',
            '*.between' => 'Setiap aspek harus berupa kategori 1 sampai 4.',
        ]);

        $code = strtoupper(trim((string) ($validated['kode_data'] ?? '')));
        if ($code === '') {
            $code = 'LATIH-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
        }

        try {
            DB::table('data_latih_naive_bayes')->insert([
                'kode_data' => $code,
                'usia_bulan' => null,
                'usia_kategori' => $validated['usia_kategori'],
                'nilai_agama_moral' => $validated['nilai_agama_moral'],
                'motorik' => $validated['motorik'],
                'kognitif' => $validated['kognitif'],
                'bahasa' => $validated['bahasa'],
                'sosial_emosional' => $validated['sosial_emosional'],
                'kelas' => $validated['kelas'],
                'sumber' => $validated['sumber'] ?? null,
                'dicatat_oleh' => (int) session('school_user_id'),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            return back()->withInput()->withErrors(['kode_data' => 'Catatan belum tersimpan. Pastikan kode data belum digunakan dan periksa database.']);
        }

        return redirect()->route('admin.training.index')->with('status', 'Catatan data latih berhasil disimpan.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $deleted = DB::table('data_latih_naive_bayes')->where('id', $id)->delete();

        return redirect()->route('admin.training.index')->with('status', $deleted ? 'Catatan data latih dihapus.' : 'Catatan tidak ditemukan.');
    }
}

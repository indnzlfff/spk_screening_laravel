<?php

namespace App\Http\Controllers;

use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApplicantController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $answerCounts = DB::table('jawaban')->select('pendaftar_id')->selectRaw('COUNT(*) AS total_jawaban')->groupBy('pendaftar_id');
        $latestScreening = DB::table('screening')->where('metode', 'Naive Bayes')
            ->selectRaw('MAX(id) AS id, pendaftar_id')->groupBy('pendaftar_id');

        $query = DB::table('pendaftar as p')
            ->leftJoinSub($answerCounts, 'a', 'a.pendaftar_id', '=', 'p.id')
            ->leftJoinSub($latestScreening, 'latest', 'latest.pendaftar_id', '=', 'p.id')
            ->leftJoin('screening as s', 's.id', '=', 'latest.id')
            ->select('p.id', 'p.nomor_pendaftaran', 'p.nama_anak', 'p.status_pendaftaran', 'p.created_at')
            ->selectRaw('COALESCE(a.total_jawaban, 0) AS jumlah_jawaban')
            ->addSelect('s.kelas_prediksi');

        if ($search !== '') {
            $query->where(function ($filter) use ($search): void {
                $filter->where('p.nama_anak', 'like', '%'.$search.'%')
                    ->orWhere('p.nomor_pendaftaran', 'like', '%'.$search.'%');
            });
        }

        return view('admin.applicants.index', ['rows' => $query->orderByDesc('p.created_at')->paginate(20)->withQueryString(), 'search' => $search]);
    }

    public function show(int $id): View
    {
        $applicant = DB::table('pendaftar')->where('id', $id)->first();
        abort_if(! $applicant, 404);

        $answers = DB::table('jawaban as j')
            ->join('indikator as i', 'i.id', '=', 'j.indikator_id')
            ->join('kriteria as k', 'k.id', '=', 'i.kriteria_id')
            ->where('j.pendaftar_id', $id)
            ->orderBy('k.kode')->orderBy('i.kode')
            ->select('k.nama as aspek', 'i.kode', 'i.pertanyaan', 'j.nilai')
            ->get();

        $screenings = DB::table('screening as s')
            ->leftJoin('screening_validations as v', 'v.screening_id', '=', 's.id')
            ->where('s.pendaftar_id', $id)->where('s.metode', 'Naive Bayes')
            ->select('s.id', 's.nomor_screening', 's.kelas_prediksi', 's.created_at', 'v.status as status_validasi')
            ->orderByDesc('s.created_at')->get();

        return view('admin.applicants.show', compact('applicant', 'answers', 'screenings'));
    }

    public function updateStatus(Request $request, int $id, BillingService $billing): RedirectResponse
    {
        $validated = $request->validate([
            'status_pendaftaran' => ['required', 'in:baru,diproses,diterima,ditolak'],
        ]);

        $updated = DB::table('pendaftar')->where('id', $id)->update([
            'status_pendaftaran' => $validated['status_pendaftaran'],
            'updated_at' => now(),
        ]);
        abort_if($updated === 0 && ! DB::table('pendaftar')->where('id', $id)->exists(), 404);

        $invoiceId = $validated['status_pendaftaran'] === 'diterima'
            ? $billing->ensureRegistrationInvoice($id, (int) session('school_user_id'))
            : null;

        $message = 'Status penerimaan diperbarui secara manual. Hasil screening tidak mengubah keputusan ini.';
        if ($validated['status_pendaftaran'] === 'diterima') {
            $message = $invoiceId
                ? 'Pendaftar diterima. Tagihan administrasi dibuat sesuai pengaturan biaya sekolah.'
                : 'Pendaftar diterima. Tagihan belum dibuat karena pengaturan biaya admin belum valid.';
        }

        return redirect()->route('admin.applicants.show', $id)->with('status', $message);
    }
}

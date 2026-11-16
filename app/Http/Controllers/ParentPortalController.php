<?php

namespace App\Http\Controllers;

use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ParentPortalController extends Controller
{
    public function dashboard(BillingService $billing): View
    {
        $registrations = DB::table('pendaftar')
            ->where('akun_orang_tua_id', (int) session('parent_account_id'))
            ->orderByDesc('created_at')->orderByDesc('id')->get();

        $screenings = $registrations->isEmpty() ? collect() : DB::table('screening as s')
            ->leftJoin('screening_validations as v', 'v.screening_id', '=', 's.id')
            ->whereIn('s.pendaftar_id', $registrations->pluck('id'))
            ->where('s.metode', 'Naive Bayes')
            ->select('s.id', 's.pendaftar_id', 's.nomor_screening', 's.kelas_prediksi', 's.probabilitas_sesuai_harapan', 's.probabilitas_perhatian_lanjutan', 's.created_at', 'v.status as status_validasi', 'v.catatan_admin')
            ->orderByDesc('s.id')->get()->unique('pendaftar_id')->keyBy('pendaftar_id');

        foreach ($registrations as $registration) {
            $registration->screening = $screenings->get($registration->id);
            $registration->invoice = $registration->status_pendaftaran === 'diterima'
                ? $billing->ensureRegistrationInvoice((int) $registration->id)
                : null;
            $registration->invoiceSummary = $registration->invoice
                ? $billing->invoiceSummary((int) $registration->id)
                : null;
        }

        return view('parent.dashboard', compact('registrations'));
    }

    public function printRegistration(int $id): View
    {
        $registration = DB::table('pendaftar as p')
            ->leftJoin('akun_orang_tua as parent', 'parent.id', '=', 'p.akun_orang_tua_id')
            ->where('p.id', $id)
            ->where('p.akun_orang_tua_id', (int) session('parent_account_id'))
            ->select('p.*', 'parent.nama as nama_akun_orang_tua', 'parent.email as email_orang_tua')
            ->first();
        abort_if(! $registration, 404);

        $answers = DB::table('jawaban as j')
            ->join('indikator as i', 'i.id', '=', 'j.indikator_id')
            ->join('kriteria as k', 'k.id', '=', 'i.kriteria_id')
            ->where('j.pendaftar_id', $id)
            ->orderBy('k.kode')->orderBy('i.kode')
            ->select('k.kode as kode_aspek', 'k.nama as aspek', 'i.kode', 'i.pertanyaan', 'j.nilai')
            ->get();

        $screening = DB::table('screening as s')
            ->leftJoin('screening_validations as v', 'v.screening_id', '=', 's.id')
            ->where('s.pendaftar_id', $id)->where('s.metode', 'Naive Bayes')
            ->orderByDesc('s.id')
            ->select('s.*', 'v.status as status_validasi', 'v.catatan_admin', 'v.validated_at')
            ->first();

        $details = $screening && $screening->status_validasi === 'dikonfirmasi'
            ? DB::table('detail_screening_naive_bayes')->where('screening_id', $screening->id)
                ->orderByRaw("FIELD(kode_fitur, 'usia_kategori','nilai_agama_moral','motorik','kognitif','bahasa','sosial_emosional')")
                ->get()
            : collect();

        return view('parent.print-registration', compact('registration', 'answers', 'screening', 'details'));
    }

    public function invoice(int $id, BillingService $billing): View
    {
        $applicant = DB::table('pendaftar')
            ->where('id', $id)
            ->where('akun_orang_tua_id', (int) session('parent_account_id'))
            ->first();
        abort_if(! $applicant, 404);

        $invoice = null;
        $payments = collect();
        if ($applicant->status_pendaftaran === 'diterima') {
            $billing->ensureRegistrationInvoice($id);
            $invoice = $billing->invoiceSummary($id);
            if ($invoice) {
                $payments = DB::table('pembayaran')->where('tagihan_id', $invoice->id)->orderByDesc('id')->get();
            }
        }

        return view('parent.invoice', [
            'applicant' => $applicant,
            'invoice' => $invoice,
            'payments' => $payments,
            'bankName' => $billing->setting('payment_bank_name') ?? '',
            'accountName' => $billing->setting('payment_account_name') ?? '',
            'accountNumber' => $billing->setting('payment_account_number') ?? '',
        ]);
    }

    public function reportPayment(Request $request, int $id, BillingService $billing): RedirectResponse
    {
        $applicant = DB::table('pendaftar')
            ->where('id', $id)
            ->where('akun_orang_tua_id', (int) session('parent_account_id'))
            ->first();
        abort_if(! $applicant, 404);

        if ($applicant->status_pendaftaran !== 'diterima') {
            return back()->withErrors(['payment' => 'Tagihan tersedia setelah status pendaftaran diterima oleh sekolah.']);
        }

        $billing->ensureRegistrationInvoice($id);
        $invoice = $billing->invoiceSummary($id);
        if (! $invoice) {
            return back()->withErrors(['payment' => 'Tagihan belum tersedia. Hubungi admin sekolah untuk memeriksa pengaturan biaya.']);
        }

        $rules = [
            'metode' => ['required', 'in:transfer,tunai'],
            'jumlah' => ['required', 'string', 'max:20', 'regex:/^(?:Rp\s*)?[0-9.]+$/i'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
        if ($request->input('metode') === 'transfer') {
            $rules['bukti_transfer'] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'];
        } else {
            $rules['tanggal_rencana'] = ['required', 'date', 'after_or_equal:today'];
        }
        $validated = $request->validate($rules);

        $digits = preg_replace('/\D+/', '', $validated['jumlah']);
        if ($digits === '' || strlen($digits) > 10 || (float) $digits <= 0) {
            return back()->withInput()->withErrors(['jumlah' => 'Masukkan jumlah pembayaran Rupiah yang valid dan lebih dari nol.']);
        }
        $amount = (float) $digits;
        if ($amount > (float) $invoice->dapat_dilaporkan) {
            return back()->withInput()->withErrors(['jumlah' => 'Jumlah melebihi sisa tagihan yang belum dibayar atau belum menunggu verifikasi.']);
        }

        $proofPath = null;
        if ($validated['metode'] === 'transfer') {
            $proofPath = $request->file('bukti_transfer')->store('pembayaran', 'local');
            if (! $proofPath) {
                return back()->withInput()->withErrors(['bukti_transfer' => 'Bukti transfer belum berhasil disimpan. Coba kembali.']);
            }
        }

        try {
            DB::table('pembayaran')->insert([
                'tagihan_id' => $invoice->id,
                'metode' => $validated['metode'],
                'jumlah' => number_format($amount, 2, '.', ''),
                'bukti_path' => $proofPath,
                'tanggal_rencana' => $validated['metode'] === 'tunai' ? $validated['tanggal_rencana'] : null,
                'status' => 'menunggu',
                'catatan' => $validated['metode'] === 'tunai'
                    ? 'Rencana pembayaran tunai; menunggu pembayaran diterima sekolah.'
                    : (($validated['catatan'] ?? '') ?: null),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            if ($proofPath) {
                Storage::disk('local')->delete($proofPath);
            }
            report($exception);

            return back()->withInput()->withErrors(['payment' => 'Laporan pembayaran belum berhasil disimpan. Coba kembali.']);
        }

        return redirect()->route('parent.invoice', $id)->with('status', 'Laporan pembayaran telah dikirim dan menunggu verifikasi sekolah.');
    }
}

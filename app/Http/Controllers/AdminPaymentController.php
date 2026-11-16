<?php

namespace App\Http\Controllers;

use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminPaymentController extends Controller
{
    public function index(BillingService $billing): View
    {
        $invoiceRows = $billing->allInvoiceRows();
        $pending = DB::table('pembayaran as b')
            ->join('tagihan as t', 't.id', '=', 'b.tagihan_id')
            ->join('pendaftar as p', 'p.id', '=', 't.pendaftar_id')
            ->where('b.status', 'menunggu')
            ->select('b.id', 'b.tagihan_id', 'b.metode', 'b.jumlah', 'b.bukti_path', 'b.tanggal_rencana', 'b.created_at', 't.kode_tagihan', 'p.nama_anak', 'p.nomor_pendaftaran')
            ->orderBy('b.created_at')->orderBy('b.id')->get();

        $cashOptions = array_values(array_filter($invoiceRows, static fn ($row): bool =>
            (float) $row->jumlah - (float) $row->dibayar - (float) $row->menunggu > 0
        ));

        return view('admin.payments.index', [
            'feeAmount' => $billing->setting('registration_fee_amount') ?? '',
            'dueDate' => $billing->setting('registration_fee_due_date') ?? '',
            'bankName' => $billing->setting('payment_bank_name') ?? '',
            'accountName' => $billing->setting('payment_account_name') ?? '',
            'accountNumber' => $billing->setting('payment_account_number') ?? '',
            'invoiceRows' => $invoiceRows,
            'pending' => $pending,
            'cashOptions' => $cashOptions,
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_fee_amount' => ['required', 'integer', 'min:1', 'max:9999999999'],
            'registration_fee_due_date' => ['nullable', 'date_format:Y-m-d'],
            'payment_bank_name' => ['required', 'string', 'max:80'],
            'payment_account_name' => ['required', 'string', 'max:120'],
            'payment_account_number' => ['required', 'string', 'max:40'],
        ]);

        $settings = [
            'registration_fee_amount' => [$validated['registration_fee_amount'], 'Biaya pendaftaran saat ini; perubahan hanya berlaku untuk tagihan baru.'],
            'registration_fee_due_date' => [$validated['registration_fee_due_date'] ?: null, 'Jatuh tempo tagihan pendaftaran. Kosong berarti sebelum murid mulai masuk sekolah.'],
            'payment_bank_name' => [trim($validated['payment_bank_name']), 'Bank tujuan transfer yang dikonfigurasi admin.'],
            'payment_account_name' => [trim($validated['payment_account_name']), 'Nama pemilik rekening tujuan transfer.'],
            'payment_account_number' => [trim($validated['payment_account_number']), 'Nomor rekening tujuan transfer.'],
        ];

        foreach ($settings as $name => [$value, $description]) {
            DB::table('settings')->updateOrInsert(
                ['nama_setting' => $name],
                ['nilai' => $value, 'keterangan' => $description, 'updated_at' => now()],
            );
        }

        return back()->with('status', 'Pengaturan biaya dan rekening berhasil disimpan. Tagihan lama tetap memakai nominal saat diterbitkan.');
    }

    public function createMissing(BillingService $billing): RedirectResponse
    {
        $created = DB::transaction(function () use ($billing): int {
            $created = 0;
            $accepted = DB::table('pendaftar')->where('status_pendaftaran', 'diterima')->orderBy('id')->pluck('id');
            foreach ($accepted as $id) {
                $before = DB::table('tagihan')->where('pendaftar_id', $id)->where('jenis', 'pendaftaran')->exists();
                if (! $before && $billing->ensureRegistrationInvoice((int) $id, (int) session('school_user_id'))) {
                    $created++;
                }
            }

            return $created;
        });

        return back()->with('status', $created.' tagihan baru dibuat untuk pendaftar yang telah diterima.');
    }

    public function process(Request $request, int $id, string $decision, BillingService $billing): RedirectResponse
    {
        abort_unless(in_array($decision, ['approve', 'reject'], true), 404);
        $validated = $request->validate(['catatan' => ['nullable', 'string', 'max:500']]);

        try {
            DB::transaction(function () use ($id, $decision, $validated, $billing): void {
                $payment = DB::table('pembayaran as b')
                    ->join('tagihan as t', 't.id', '=', 'b.tagihan_id')
                    ->where('b.id', $id)
                    ->select('b.*', 't.pendaftar_id')
                    ->lockForUpdate()->first();

                if (! $payment || $payment->status !== 'menunggu') {
                    throw new RuntimeException('Pembayaran tidak ditemukan atau sudah diproses.');
                }

                DB::table('tagihan')->where('id', $payment->tagihan_id)->lockForUpdate()->first();
                if ($decision === 'approve') {
                    $summary = $billing->invoiceSummary((int) $payment->pendaftar_id);
                    if (! $summary || (float) $payment->jumlah > (float) $summary->sisa_tagihan) {
                        throw new RuntimeException('Jumlah pembayaran melebihi sisa tagihan yang belum dibayar.');
                    }
                }

                $note = trim((string) ($validated['catatan'] ?? ''));
                if ($note === '') {
                    $note = $decision === 'approve'
                        ? ($payment->metode === 'tunai' ? 'Pembayaran tunai telah diterima sekolah.' : null)
                        : ($payment->metode === 'tunai' ? 'Rencana pembayaran tunai dibatalkan oleh sekolah.' : 'Bukti atau nominal perlu diperbaiki.');
                }

                DB::table('pembayaran')->where('id', $id)->where('status', 'menunggu')->update([
                    'status' => $decision === 'approve' ? 'disetujui' : 'ditolak',
                    'diverifikasi_oleh' => (int) session('school_user_id'),
                    'diverifikasi_pada' => now(),
                    'catatan' => $note,
                ]);
            });
        } catch (RuntimeException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['payment' => 'Pembayaran belum berhasil diproses. Periksa data lalu coba kembali.']);
        }

        return back()->with('status', $decision === 'approve' ? 'Pembayaran berhasil disetujui.' : 'Pembayaran berhasil ditolak.');
    }

    public function recordCash(Request $request, BillingService $billing): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'integer', 'exists:tagihan,id'],
            'jumlah' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($validated, $billing): void {
                $invoice = DB::table('tagihan')->where('id', $validated['invoice_id'])->lockForUpdate()->first();
                if (! $invoice || $invoice->jenis !== 'pendaftaran') {
                    throw new RuntimeException('Tagihan tidak ditemukan.');
                }

                $summary = $billing->invoiceSummary((int) $invoice->pendaftar_id);
                if (! $summary || (float) $validated['jumlah'] > (float) $summary->dapat_dilaporkan) {
                    throw new RuntimeException('Jumlah tunai melebihi sisa tagihan yang tersedia.');
                }

                DB::table('pembayaran')->insert([
                    'tagihan_id' => $invoice->id,
                    'metode' => 'tunai',
                    'jumlah' => number_format((float) $validated['jumlah'], 2, '.', ''),
                    'status' => 'disetujui',
                    'catatan' => trim((string) ($validated['catatan'] ?? '')) ?: 'Diterima langsung oleh pihak sekolah.',
                    'diverifikasi_oleh' => (int) session('school_user_id'),
                    'diverifikasi_pada' => now(),
                    'created_at' => now(),
                ]);
            });
        } catch (RuntimeException $exception) {
            return back()->withErrors(['cash' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['cash' => 'Pembayaran tunai belum berhasil dicatat. Coba kembali.']);
        }

        return back()->with('status', 'Pembayaran tunai telah dicatat sebagai diterima.');
    }

    public function proof(int $id)
    {
        $payment = DB::table('pembayaran')->where('id', $id)->first();
        abort_if(! $payment || ! $payment->bukti_path || ! Storage::disk('local')->exists($payment->bukti_path), 404);

        return Storage::disk('local')->download($payment->bukti_path, 'bukti-pembayaran-'.$payment->id);
    }
}

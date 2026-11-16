<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BillingService
{
    public function setting(string $name): ?string
    {
        $value = DB::table('settings')->where('nama_setting', $name)->value('nilai');

        return $value === null ? null : (string) $value;
    }

    public function ensureRegistrationInvoice(int $applicantId, ?int $userId = null): ?int
    {
        $person = DB::table('pendaftar')->where('id', $applicantId)->first();
        if (! $person || $person->status_pendaftaran !== 'diterima') {
            return null;
        }

        $existing = DB::table('tagihan')
            ->where('pendaftar_id', $applicantId)
            ->where('jenis', 'pendaftaran')
            ->value('id');
        if ($existing) {
            return (int) $existing;
        }

        $fee = $this->setting('registration_fee_amount');
        if (! is_numeric($fee) || (float) $fee <= 0) {
            return null;
        }

        $dueDate = $this->setting('registration_fee_due_date');
        if ($dueDate !== null && $dueDate !== '') {
            $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $dueDate);
            if (! $parsedDate || $parsedDate->format('Y-m-d') !== $dueDate) {
                $dueDate = null;
            }
        } else {
            $dueDate = null;
        }

        DB::table('tagihan')->insertOrIgnore([
            'pendaftar_id' => $applicantId,
            'kode_tagihan' => 'INV-'.$person->nomor_pendaftaran,
            'jenis' => 'pendaftaran',
            'keterangan' => 'Biaya pendaftaran murid baru',
            'jumlah' => number_format((float) $fee, 2, '.', ''),
            'jatuh_tempo' => $dueDate,
            'dibuat_oleh' => $userId,
            'created_at' => now(),
        ]);

        $id = DB::table('tagihan')
            ->where('pendaftar_id', $applicantId)
            ->where('jenis', 'pendaftaran')
            ->value('id');

        return $id ? (int) $id : null;
    }

    public function invoiceSummary(int $applicantId): ?object
    {
        $invoice = DB::table('tagihan as t')
            ->leftJoin('pembayaran as b', 'b.tagihan_id', '=', 't.id')
            ->where('t.pendaftar_id', $applicantId)
            ->where('t.jenis', 'pendaftaran')
            ->groupBy('t.id', 't.kode_tagihan', 't.keterangan', 't.jumlah', 't.jatuh_tempo', 't.created_at')
            ->select('t.id', 't.kode_tagihan', 't.keterangan', 't.jumlah', 't.jatuh_tempo', 't.created_at')
            ->selectRaw("COALESCE(SUM(CASE WHEN b.status = 'disetujui' THEN b.jumlah ELSE 0 END), 0) AS sudah_dibayar")
            ->selectRaw("COALESCE(SUM(CASE WHEN b.status = 'menunggu' THEN b.jumlah ELSE 0 END), 0) AS menunggu_verifikasi")
            ->first();

        if (! $invoice) {
            return null;
        }

        $invoice->sisa_tagihan = max(0, (float) $invoice->jumlah - (float) $invoice->sudah_dibayar);
        $invoice->dapat_dilaporkan = max(0, $invoice->sisa_tagihan - (float) $invoice->menunggu_verifikasi);

        return $invoice;
    }

    public function allInvoiceRows(): array
    {
        return DB::table('tagihan as t')
            ->join('pendaftar as p', 'p.id', '=', 't.pendaftar_id')
            ->leftJoin('pembayaran as b', 'b.tagihan_id', '=', 't.id')
            ->groupBy('t.id', 't.kode_tagihan', 't.jumlah', 't.jatuh_tempo', 't.created_at', 'p.nomor_pendaftaran', 'p.nama_anak')
            ->select('t.id', 't.kode_tagihan', 't.jumlah', 't.jatuh_tempo', 't.created_at', 'p.nomor_pendaftaran', 'p.nama_anak')
            ->selectRaw("COALESCE(SUM(CASE WHEN b.status = 'disetujui' THEN b.jumlah ELSE 0 END), 0) AS dibayar")
            ->selectRaw("COALESCE(SUM(CASE WHEN b.status = 'menunggu' THEN b.jumlah ELSE 0 END), 0) AS menunggu")
            ->orderByDesc('t.created_at')->orderByDesc('t.id')
            ->get()->all();
    }
}

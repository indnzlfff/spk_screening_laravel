@extends('layouts.app')

@section('title', 'Tagihan Pendaftaran')

@section('content')
<div class="page-head"><div><h1>Tagihan pendaftaran</h1><p>{{ $applicant->nama_anak }} · {{ $applicant->nomor_pendaftaran }}</p></div><a class="btn" href="{{ route('parent.dashboard') }}">Kembali ke akun</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if($applicant->status_pendaftaran !== 'diterima')
    <div class="notice">Tagihan ditampilkan setelah pihak sekolah menetapkan status pendaftaran diterima.</div>
@elseif(!$invoice)
    <div class="alert alert-warning">Tagihan belum tersedia. Hubungi admin sekolah untuk memeriksa pengaturan biaya.</div>
@else
    <section class="panel">
        <div class="page-head"><div><span class="muted small">Kode tagihan</span><h2>{{ $invoice->kode_tagihan }}</h2><p>{{ $invoice->keterangan }}</p></div><span class="badge">{{ $invoice->sisa_tagihan <= 0 ? 'Lunas' : ((float)$invoice->sudah_dibayar > 0 ? 'Belum lunas' : 'Belum ada pembayaran') }}</span></div>
        <div class="metrics" style="grid-template-columns:repeat(3,minmax(0,1fr))"><div class="metric"><span>Total tagihan</span><strong>Rp{{ number_format((float)$invoice->jumlah, 0, ',', '.') }}</strong></div><div class="metric"><span>Disetujui sekolah</span><strong>Rp{{ number_format((float)$invoice->sudah_dibayar, 0, ',', '.') }}</strong></div><div class="metric"><span>Sisa tagihan</span><strong>Rp{{ number_format((float)$invoice->sisa_tagihan, 0, ',', '.') }}</strong></div></div>
        <p class="small muted">Pembayaran yang menunggu verifikasi belum mengurangi sisa tagihan. Jatuh tempo: {{ $invoice->jatuh_tempo ? \Illuminate\Support\Carbon::parse($invoice->jatuh_tempo)->format('d-m-Y') : 'sebelum murid mulai masuk sekolah' }}.</p>
    </section>
    @if((float)$invoice->dapat_dilaporkan > 0)
        <section class="panel"><h2>Kirim pembayaran</h2><p class="muted">Pembayaran dapat dilakukan dengan transfer atau tunai di sekolah. Batas yang dapat dilaporkan saat ini Rp{{ number_format((float)$invoice->dapat_dilaporkan, 0, ',', '.') }}.</p>
            <div class="notice"><strong>{{ $bankName }} · {{ $accountName }}</strong><br>Nomor rekening: <strong>{{ $accountNumber }}</strong><br><span class="small">Pastikan kembali detail rekening kepada pihak sekolah sebelum transfer.</span></div>
            <form method="post" action="{{ route('parent.payment.report', $applicant->id) }}" enctype="multipart/form-data" class="form-grid">
                @csrf
                <div class="field"><label for="metode">Metode pembayaran</label><select id="metode" name="metode" required><option value="transfer">Transfer bank</option><option value="tunai">Tunai di sekolah</option></select></div>
                <div class="field"><label for="jumlah">Jumlah (Rupiah)</label><input id="jumlah" name="jumlah" type="text" inputmode="numeric" placeholder="Contoh: 500000" required></div>
                <div class="field" id="proof-field"><label for="bukti_transfer">Bukti transfer (PDF/JPG/PNG, maks. 3 MB)</label><input id="bukti_transfer" name="bukti_transfer" type="file" accept=".pdf,.jpg,.jpeg,.png"></div>
                <div class="field" id="cash-date-field" hidden><label for="tanggal_rencana">Tanggal rencana pembayaran tunai</label><input id="tanggal_rencana" name="tanggal_rencana" type="date" min="{{ now()->toDateString() }}"></div>
                <div class="field full"><label for="catatan">Catatan (opsional)</label><input id="catatan" name="catatan" maxlength="500"></div>
                <div class="actions full"><button class="btn btn-primary" type="submit">Kirim laporan pembayaran</button></div>
            </form>
        </section>
    @endif
    <section class="panel"><h2>Riwayat pembayaran</h2><div class="table-wrap"><table class="table"><thead><tr><th>Tanggal</th><th>Metode</th><th>Jumlah</th><th>Status</th><th>Catatan sekolah</th></tr></thead><tbody>
        @forelse($payments as $payment)
            <tr><td>{{ \Illuminate\Support\Carbon::parse($payment->created_at)->format('d-m-Y H:i') }}</td><td>{{ $payment->metode === 'tunai' ? 'Tunai' : 'Transfer' }}</td><td>Rp{{ number_format((float)$payment->jumlah, 0, ',', '.') }}</td><td>{{ $payment->metode === 'tunai' && $payment->status === 'menunggu' ? 'Rencana tunai menunggu' : ucfirst($payment->status) }}@if($payment->tanggal_rencana)<div class="small muted">Rencana {{ \Illuminate\Support\Carbon::parse($payment->tanggal_rencana)->format('d-m-Y') }}</div>@endif</td><td>{{ $payment->catatan ?: '—' }}</td></tr>
        @empty
            <tr><td colspan="5" class="empty">Belum ada pembayaran yang dilaporkan.</td></tr>
        @endforelse
    </tbody></table></div></section>
    <script>
    (() => {
        const method = document.getElementById('metode');
        const proof = document.getElementById('proof-field');
        const proofInput = document.getElementById('bukti_transfer');
        const cash = document.getElementById('cash-date-field');
        const cashDate = document.getElementById('tanggal_rencana');
        const update = () => {
            const isTransfer = method.value === 'transfer';
            proof.hidden = !isTransfer; proofInput.required = isTransfer;
            cash.hidden = isTransfer; cashDate.required = !isTransfer;
        };
        method.addEventListener('change', update); update();
    })();
    </script>
@endif
@endsection

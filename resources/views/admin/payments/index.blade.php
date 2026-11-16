@extends('layouts.app')

@section('title', 'Tagihan dan Pembayaran')

@section('content')
<div class="page-head"><div><h1>Tagihan dan pembayaran</h1><p>Atur biaya pendaftaran, tinjau pembayaran, dan catat penerimaan tunai.</p></div></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="notice">Tagihan hanya dibuat untuk calon siswa yang status pendaftarannya telah diterima. Pembayaran transfer dan rencana tunai mengurangi sisa yang dapat dilaporkan setelah diverifikasi sesuai kebijakan sekolah.</div>

<section class="panel"><h2>Pengaturan biaya dan rekening</h2><p class="small muted">Perubahan biaya hanya berlaku untuk tagihan baru. Tagihan yang sudah terbit tetap memakai nominal saat dibuat.</p>
    <form method="post" action="{{ route('admin.payments.settings') }}" class="form-grid">@csrf
        <div class="field"><label for="registration_fee_amount">Biaya pendaftaran (Rupiah)</label><input id="registration_fee_amount" type="number" min="1" step="1" name="registration_fee_amount" value="{{ old('registration_fee_amount', $feeAmount) }}" required></div>
        <div class="field"><label for="registration_fee_due_date">Tanggal jatuh tempo (opsional)</label><input id="registration_fee_due_date" type="date" name="registration_fee_due_date" value="{{ old('registration_fee_due_date', $dueDate) }}"></div>
        <div class="field"><label for="payment_bank_name">Nama bank</label><input id="payment_bank_name" name="payment_bank_name" maxlength="80" value="{{ old('payment_bank_name', $bankName) }}" required></div>
        <div class="field"><label for="payment_account_name">Nama pemilik rekening</label><input id="payment_account_name" name="payment_account_name" maxlength="120" value="{{ old('payment_account_name', $accountName) }}" required></div>
        <div class="field"><label for="payment_account_number">Nomor rekening</label><input id="payment_account_number" name="payment_account_number" maxlength="40" value="{{ old('payment_account_number', $accountNumber) }}" required></div>
        <div class="actions" style="align-items:end"><button class="btn btn-primary" type="submit">Simpan pengaturan</button></div>
    </form>
</section>

<section class="panel"><div class="page-head"><div><h2>Daftar tagihan</h2><p>{{ count($invoiceRows) }} tagihan tercatat.</p></div><form method="post" action="{{ route('admin.payments.create-missing') }}" onsubmit="return confirm('Buat tagihan untuk semua pendaftar diterima yang belum memilikinya?')">@csrf<button class="btn" type="submit">Buat tagihan yang belum terbit</button></form></div>
    <div class="table-wrap"><table class="table"><thead><tr><th>Kode</th><th>Nama anak</th><th>Total</th><th>Dibayar</th><th>Menunggu</th><th>Sisa</th><th>Jatuh tempo</th></tr></thead><tbody>
        @forelse($invoiceRows as $row)
            <tr><td>{{ $row->kode_tagihan }}</td><td>{{ $row->nama_anak }}<div class="small muted">{{ $row->nomor_pendaftaran }}</div></td><td>Rp{{ number_format((float)$row->jumlah,0,',','.') }}</td><td>Rp{{ number_format((float)$row->dibayar,0,',','.') }}</td><td>Rp{{ number_format((float)$row->menunggu,0,',','.') }}</td><td><strong>Rp{{ number_format(max(0,(float)$row->jumlah-(float)$row->dibayar),0,',','.') }}</strong></td><td>{{ $row->jatuh_tempo ? \Illuminate\Support\Carbon::parse($row->jatuh_tempo)->format('d-m-Y') : 'Sebelum masuk sekolah' }}</td></tr>
        @empty<tr><td colspan="7" class="empty">Belum ada tagihan.</td></tr>@endforelse
    </tbody></table></div>
</section>

<section class="panel"><h2>Menunggu verifikasi ({{ $pending->count() }})</h2><div class="table-wrap"><table class="table"><thead><tr><th>Waktu</th><th>Calon siswa</th><th>Metode dan jumlah</th><th>Bukti atau jadwal</th><th>Tindakan</th></tr></thead><tbody>
    @forelse($pending as $payment)
        <tr><td>{{ \Illuminate\Support\Carbon::parse($payment->created_at)->format('d-m-Y H:i') }}</td><td>{{ $payment->nama_anak }}<div class="small muted">{{ $payment->nomor_pendaftaran }} · {{ $payment->kode_tagihan }}</div></td><td>{{ $payment->metode === 'tunai' ? 'Tunai' : 'Transfer' }}<div><strong>Rp{{ number_format((float)$payment->jumlah,0,',','.') }}</strong></div></td><td>@if($payment->bukti_path)<a class="btn" href="{{ route('admin.payments.proof', $payment->id) }}">Unduh bukti</a>@elseif($payment->tanggal_rencana)Rencana {{ \Illuminate\Support\Carbon::parse($payment->tanggal_rencana)->format('d-m-Y') }}@else—@endif</td><td>
            <form method="post" action="{{ route('admin.payments.approve', $payment->id) }}" class="actions" style="margin-bottom:7px">@csrf<input class="search-input" name="catatan" maxlength="500" placeholder="Catatan opsional"><button class="btn btn-primary" type="submit" onclick="return confirm('Setujui setelah memeriksa transfer atau menerima uang tunai?')">Setujui</button></form>
            <form method="post" action="{{ route('admin.payments.reject', $payment->id) }}" class="actions">@csrf<input class="search-input" name="catatan" maxlength="500" placeholder="Alasan penolakan"><button class="btn btn-danger" type="submit">Tolak</button></form>
        </td></tr>
    @empty<tr><td colspan="5" class="empty">Tidak ada pembayaran yang menunggu verifikasi.</td></tr>@endforelse
</tbody></table></div>
</section>

<section class="panel"><h2>Catat pembayaran tunai</h2><p class="muted">Gunakan setelah uang benar-benar diterima oleh sekolah.</p>
    <form method="post" action="{{ route('admin.payments.cash') }}" class="form-grid">@csrf
        <div class="field full"><label for="invoice_id">Tagihan</label><select id="invoice_id" name="invoice_id" required><option value="">Pilih tagihan</option>@foreach($cashOptions as $row)<option value="{{ $row->id }}">{{ $row->nama_anak }} · {{ $row->kode_tagihan }} (sisa dapat dibayar Rp{{ number_format(max(0,(float)$row->jumlah-(float)$row->dibayar-(float)$row->menunggu),0,',','.') }})</option>@endforeach</select></div>
        <div class="field"><label for="jumlah">Jumlah tunai (Rupiah)</label><input id="jumlah" name="jumlah" type="number" min="1" step="1" required></div>
        <div class="field"><label for="catatan">Catatan (opsional)</label><input id="catatan" name="catatan" maxlength="500"></div>
        <div class="actions full"><button class="btn btn-primary" type="submit" onclick="return confirm('Pastikan uang tunai sudah diterima sekolah sebelum mencatatnya.')">Catat pembayaran diterima</button></div>
    </form>
</section>
@endsection

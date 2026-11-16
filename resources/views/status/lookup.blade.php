@extends('layouts.app')

@section('title', 'Cek Status Pendaftaran')

@section('content')
<div class="page-head"><div><h1>Cek status pendaftaran</h1><p>Masukkan nomor pendaftaran dan nomor HP yang digunakan saat mendaftar.</p></div></div>
@if(session('nomor_pendaftaran_baru'))<div class="alert alert-success">Pendaftaran berhasil dikirim. Simpan nomor pendaftaran <strong>{{ session('nomor_pendaftaran_baru') }}</strong> untuk mengecek status.</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<section class="panel"><form method="post" action="{{ route('status.find') }}" class="form-grid">@csrf
    <div class="field"><label for="nomor_pendaftaran">Nomor pendaftaran</label><input id="nomor_pendaftaran" name="nomor_pendaftaran" value="{{ old('nomor_pendaftaran', session('nomor_pendaftaran_baru')) }}" required></div>
    <div class="field"><label for="no_hp">Nomor HP orang tua/wali</label><input id="no_hp" name="no_hp" value="{{ old('no_hp') }}" required></div>
    <div class="field full"><button class="btn btn-primary" type="submit">Tampilkan status</button></div>
</form></section>
@if($applicant)
<section class="panel"><h2>Informasi pendaftaran</h2><div class="form-grid"><div><span class="muted">Nama anak</span><br><strong>{{ $applicant->nama_anak }}</strong></div><div><span class="muted">Nomor pendaftaran</span><br><strong>{{ $applicant->nomor_pendaftaran }}</strong></div><div><span class="muted">Status pendaftaran</span><br><strong>{{ ucfirst($applicant->status_pendaftaran) }}</strong></div><div><span class="muted">Tanggal daftar</span><br><strong>{{ \Illuminate\Support\Carbon::parse($applicant->created_at)->format('d-m-Y') }}</strong></div></div></section>
<section class="panel"><h2>Hasil screening yang dikonfirmasi</h2>
    @if($screens->isEmpty())<p class="muted">Belum ada hasil screening yang disampaikan sekolah.</p>@else
        <div class="table-wrap"><table class="table"><thead><tr><th>Tanggal</th><th>Nomor hasil</th><th>Hasil</th><th>Catatan sekolah</th></tr></thead><tbody>@foreach($screens as $screen)<tr><td>{{ \Illuminate\Support\Carbon::parse($screen->created_at)->format('d-m-Y H:i') }}</td><td>{{ $screen->nomor_screening }}</td><td>{{ $screen->kelas_prediksi }}</td><td>{{ $screen->catatan_admin ?: 'Silakan hubungi pihak sekolah untuk informasi lebih lanjut.' }}</td></tr>@endforeach</tbody></table></div>
    @endif
</section>
@endif
@endsection

@extends('layouts.app')

@section('title', 'Akun Orang Tua')

@section('content')
<div class="page-head parent-dashboard-head"><div><p class="eyebrow"><span class="eyebrow-dot"></span>Portal keluarga sekolah</p><h1>Akun orang tua</h1><p>Halo, {{ session('parent_account_name') }}. Pantau SPMB, status penerimaan, hasil screening, dan pembayaran di sini.</p></div><div class="actions"><a class="btn btn-primary" href="{{ route('registration.create') }}">Mulai SPMB <span aria-hidden="true">→</span></a><form method="post" action="{{ route('parent.logout') }}">@csrf<button class="btn" type="submit">Keluar</button></form></div></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($registrations->isEmpty())
    <section class="panel empty"><h2>Belum ada pendaftaran anak</h2><p>Setelah mengisi data calon siswa dan kuesioner, informasi pendaftaran akan muncul di akun ini.</p><a class="btn btn-primary" href="{{ route('registration.create') }}">Daftarkan anak</a></section>
@else
    @foreach($registrations as $registration)
        @php($labels = ['baru' => 'Menunggu tinjauan sekolah', 'diproses' => 'Sedang diproses sekolah', 'diterima' => 'Diterima', 'ditolak' => 'Tidak diterima'])
        <section class="panel parent-registration-card">
            <div class="page-head parent-registration-top"><div><span class="badge">{{ $registration->nomor_pendaftaran }}</span><h2>{{ $registration->nama_anak }}</h2><p>Terdaftar {{ \Illuminate\Support\Carbon::parse($registration->created_at)->format('d-m-Y') }}</p></div><div class="parent-status-block"><strong class="parent-admission-status">{{ $labels[$registration->status_pendaftaran] ?? ucfirst($registration->status_pendaftaran) }}</strong>@if($registration->status_pendaftaran === 'diterima')<span class="status-detail">Sekolah telah mengonfirmasi penerimaan.</span>@endif</div></div>

            <div class="parent-screening-result">
                <h3>Hasil screening</h3>
                @if($registration->screening && $registration->screening->status_validasi === 'dikonfirmasi')
                    <p class="screening-confirmed"><strong>{{ $registration->screening->kelas_prediksi }}</strong><span>Dikonfirmasi otomatis · {{ \Illuminate\Support\Carbon::parse($registration->screening->created_at)->format('d-m-Y') }}</span></p>
                    <div class="metrics parent-screening-probabilities" style="grid-template-columns:repeat(2,minmax(0,1fr))"><div class="metric"><span>Sesuai harapan</span><strong>{{ number_format((float)$registration->screening->probabilitas_sesuai_harapan * 100, 2, ',', '.') }}%</strong></div><div class="metric"><span>Perlu perhatian lebih lanjut</span><strong>{{ number_format((float)$registration->screening->probabilitas_perhatian_lanjutan * 100, 2, ',', '.') }}%</strong></div></div>
                    @if($registration->screening->catatan_admin)<p class="small">Catatan hasil screening: {{ $registration->screening->catatan_admin }}</p>@endif
                    @if($registration->status_pendaftaran === 'baru' || $registration->status_pendaftaran === 'diproses')<p class="small muted">Hasil screening sudah dikonfirmasi otomatis. Keputusan penerimaan tetap ditetapkan sekolah dan statusnya ditampilkan di atas.</p>@endif
                    <p class="small muted">Hasil ini merupakan pemetaan awal perkembangan anak, bukan diagnosis medis atau keputusan otomatis penerimaan.</p>
                @elseif($registration->screening)
                    <p>Hasil screening sedang menunggu konfirmasi sekolah.</p>
                @else
                    <p>Hasil screening belum tersedia. Sekolah akan memperbarui informasinya di sini.</p>
                @endif
            </div>

            <div class="actions parent-registration-actions">
                <a class="btn" href="{{ route('parent.registration.print', $registration->id) }}">Cetak status, data & hasil (2 lembar)</a>
                @if($registration->status_pendaftaran === 'diterima')
                    <a class="btn btn-primary" href="{{ route('parent.invoice', $registration->id) }}">Informasi pembayaran</a>
                @endif
            </div>
            @if($registration->invoiceSummary)<p class="small muted parent-invoice-summary">Sisa tagihan: <strong>Rp{{ number_format((float)$registration->invoiceSummary->sisa_tagihan, 0, ',', '.') }}</strong></p>@elseif($registration->status_pendaftaran === 'diterima')<p class="small muted parent-invoice-summary">Tagihan administrasi belum tersedia. Silakan hubungi admin sekolah.</p>@endif
        </section>
    @endforeach
@endif
@endsection

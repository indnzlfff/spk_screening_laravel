@extends('layouts.app')

@section('title', 'Beranda Admin')

@section('content')
<div class="page-head">
    <div><h1>Beranda</h1><p>Selamat datang, {{ session('school_user_name') }}. Berikut ringkasan pendaftaran dan screening.</p></div>
    <a class="btn btn-primary" href="{{ route('admin.applicants.index') }}">Lihat data pendaftar</a>
</div>
<div class="metrics">
    <div class="metric"><span>Total pendaftar</span><strong>{{ number_format($totalApplicants) }}</strong></div>
    <div class="metric"><span>Kuesioner lengkap</span><strong>{{ number_format($completeQuestionnaires) }}</strong></div>
    <div class="metric"><span>Hasil screening tersimpan</span><strong>{{ number_format($screeningCount) }}</strong></div>
    <div class="metric"><span>Pertanyaan aktif</span><strong>{{ $activeQuestions }}<small style="font-size:.85rem;font-weight:500"> / 20</small></strong></div>
</div>
<section class="panel">
    <h2>Alur kerja utama</h2>
    <div class="workflow">
        <a class="step" href="{{ route('admin.applicants.index') }}"><b>Pendaftaran</b><small>Periksa data calon siswa dan kuesioner</small></a>
        <a class="step" href="{{ route('admin.screening.index') }}"><b>Screening</b><small>Periksa kelengkapan dan proses Naïve Bayes</small></a>
        <a class="step" href="{{ route('admin.screening.history') }}"><b>Hasil</b><small>Lihat serta telusuri riwayat hasil</small></a>
    </div>
</section>
<section class="panel">
    <div class="page-head"><h2 style="margin:0">Pendaftar terbaru</h2><a href="{{ route('admin.screening.index') }}">Lihat antrean screening →</a></div>
    <div class="table-wrap"><table class="table"><thead><tr><th>No. pendaftaran</th><th>Nama anak</th><th>Tanggal daftar</th><th>Status pendaftaran</th><th>Hasil screening</th></tr></thead><tbody>
    @forelse($applicants as $applicant)
        <tr><td>{{ $applicant->nomor_pendaftaran }}</td><td>{{ $applicant->nama_anak }}</td><td>{{ \Illuminate\Support\Carbon::parse($applicant->created_at)->format('d-m-Y') }}</td><td>{{ ucfirst($applicant->status_pendaftaran) }}</td><td>{{ $applicant->kelas_prediksi ?? 'Belum ada hasil' }}</td></tr>
    @empty
        <tr><td colspan="5" class="empty">Belum ada data pendaftar.</td></tr>
    @endforelse
    </tbody></table></div>
</section>
<p class="small muted">Hasil klasifikasi menjadi bahan pertimbangan awal pendampingan. Hasil tidak menggantikan keputusan penerimaan pihak sekolah.</p>
@endsection

@extends('layouts.app')

@section('title', 'Cetak Formulir dan Screening')

@push('styles')
<style>
    @page { size: A4; margin: 12mm; }
    .print-document { max-width: 210mm; margin: 24px auto; color: #29445a; }
    .print-page { background: #fff; border: 1px solid #e3ebef; border-radius: 16px; box-shadow: 0 12px 34px rgba(41,68,90,.08); margin: 0 0 22px; padding: 20px; }
    .print-school-head { align-items: center; border-bottom: 2px solid #a9ddf5; display: flex; gap: 14px; margin-bottom: 18px; padding-bottom: 12px; }
    .print-school-head img { height: 54px; object-fit: contain; width: 54px; }
    .print-school-head h1 { font-size: 19px; margin: 0; }
    .print-school-head p { font-size: 11px; margin: 3px 0 0; }
    .print-page h2 { font-size: 20px; margin: 0 0 12px; }
    .print-meta { color: #62778a; font-size: 11px; margin-bottom: 16px; }
    .print-grid { display: grid; gap: 8px 16px; grid-template-columns: 1fr 1fr; }
    .print-field { border-bottom: 1px solid #e7edf0; min-height: 37px; padding: 4px 0 7px; }
    .print-field.full { grid-column: 1 / -1; }
    .print-field span { color: #718598; display: block; font-size: 10px; margin-bottom: 2px; }
    .print-field strong { font-size: 12px; font-weight: 700; overflow-wrap: anywhere; }
    .print-callout { background: #f1f8fb; border-left: 3px solid #a9ddf5; border-radius: 8px; font-size: 11px; line-height: 1.45; margin: 10px 0 14px; padding: 9px 11px; }
    .print-result { background: #fff7dc; border-radius: 10px; margin: 8px 0 12px; padding: 10px 12px; }
    .print-result strong { display: block; font-size: 15px; margin-bottom: 4px; }
    .print-result p { font-size: 10px; margin: 3px 0; }
    .print-table { border-collapse: collapse; font-size: 9px; width: 100%; }
    .print-table th, .print-table td { border: 1px solid #dfe7eb; padding: 4px 5px; text-align: left; vertical-align: top; }
    .print-table th { background: #eef7fa; }
    .print-table tr { break-inside: avoid; page-break-inside: avoid; }
    .print-section-title { font-size: 12px; margin: 12px 0 6px; }
    @media print {
        body { background: #fff !important; }
        .site-header, footer, .skip-link, .lightbox, .print-toolbar { display: none !important; }
        .main.public-main { margin: 0 !important; max-width: none !important; padding: 0 !important; }
        .print-document { margin: 0; max-width: none; }
        .print-page { border: 0; border-radius: 0; box-shadow: none; margin: 0; padding: 0; }
        .print-page:first-child { break-after: page; page-break-after: always; }
        .print-page:last-child { break-after: auto; page-break-after: auto; }
    }
</style>
@endpush

@section('content')
<div class="actions print-toolbar"><button class="btn btn-primary" type="button" onclick="window.print()">Cetak / Simpan PDF</button><a class="btn" href="{{ route('parent.dashboard') }}">Kembali ke akun</a></div>
@php($admissionLabels = ['baru' => 'Menunggu tinjauan sekolah', 'diproses' => 'Sedang diproses sekolah', 'diterima' => 'Diterima', 'ditolak' => 'Tidak diterima'])
<div class="print-document">
    <section class="print-page">
        <header class="print-school-head"><img src="{{ asset('assets/school/logo-sekolah.png') }}" alt="Logo TK Pembina Hidayatul Mubtadiin"><div><h1>TK Pembina Hidayatul Mubtadiin</h1><p>Formulir data calon siswa · Tahun ajaran {{ now()->format('Y') }}/{{ now()->addYear()->format('Y') }}</p></div></header>
        <h2>Data diri anak dan orang tua / wali</h2>
        <div class="print-result"><strong>Status penerimaan: {{ $admissionLabels[$registration->status_pendaftaran] ?? ucfirst($registration->status_pendaftaran) }}</strong><p>Nomor pendaftaran: {{ $registration->nomor_pendaftaran }} · Didaftarkan {{ \Illuminate\Support\Carbon::parse($registration->created_at)->format('d-m-Y') }}</p><p>Dokumen ini dapat dicetak sebagai bukti data dan status pendaftaran untuk diserahkan kepada sekolah.</p></div>
        <div class="print-grid">
            <div class="print-field"><span>Nama lengkap anak</span><strong>{{ $registration->nama_anak }}</strong></div>
            <div class="print-field"><span>NIK anak</span><strong>{{ $registration->nik ?: '—' }}</strong></div>
            <div class="print-field"><span>Tempat lahir</span><strong>{{ $registration->tempat_lahir }}</strong></div>
            <div class="print-field"><span>Tanggal lahir</span><strong>{{ \Illuminate\Support\Carbon::parse($registration->tanggal_lahir)->format('d-m-Y') }}</strong></div>
            <div class="print-field"><span>Jenis kelamin</span><strong>{{ $registration->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</strong></div>
            <div class="print-field"><span>Asal PAUD</span><strong>{{ $registration->asal_paud ?: '—' }}</strong></div>
            <div class="print-field"><span>Provinsi</span><strong>{{ $registration->provinsi ?: '—' }}</strong></div>
            <div class="print-field"><span>Kabupaten / kota</span><strong>{{ $registration->kabupaten_kota ?: '—' }}</strong></div>
            <div class="print-field"><span>Kecamatan</span><strong>{{ $registration->kecamatan ?: '—' }}</strong></div>
            <div class="print-field"><span>Kelurahan / desa</span><strong>{{ $registration->kelurahan_desa ?: '—' }}</strong></div>
            <div class="print-field"><span>Kode pos</span><strong>{{ $registration->kode_pos ?: '—' }}</strong></div>
            <div class="print-field"><span>Nomor HP orang tua / wali</span><strong>{{ $registration->no_hp }}</strong></div>
            <div class="print-field"><span>Nama akun orang tua</span><strong>{{ $registration->nama_akun_orang_tua ?: '—' }}</strong></div>
            <div class="print-field"><span>Email akun orang tua</span><strong>{{ $registration->email_orang_tua ?: '—' }}</strong></div>
            <div class="print-field full"><span>Alamat anak</span><strong>{{ $registration->alamat_anak }}</strong></div>
            <div class="print-field"><span>Nama ayah</span><strong>{{ $registration->nama_ayah ?: '—' }}</strong></div>
            <div class="print-field"><span>Pekerjaan ayah</span><strong>{{ $registration->pekerjaan_ayah ?: '—' }}</strong></div>
            <div class="print-field"><span>Nama ibu</span><strong>{{ $registration->nama_ibu ?: '—' }}</strong></div>
            <div class="print-field"><span>Pekerjaan ibu</span><strong>{{ $registration->pekerjaan_ibu ?: '—' }}</strong></div>
            <div class="print-field"><span>Nama wali (jika ada)</span><strong>{{ $registration->nama_wali ?: '—' }}</strong></div>
            <div class="print-field full"><span>Alamat wali (jika berbeda)</span><strong>{{ $registration->alamat_wali ?: 'Sama dengan alamat anak / tidak diisi' }}</strong></div>
        </div>
    </section>

    <section class="print-page">
        <header class="print-school-head"><img src="{{ asset('assets/school/logo-sekolah.png') }}" alt="Logo TK Pembina Hidayatul Mubtadiin"><div><h1>TK Pembina Hidayatul Mubtadiin</h1><p>Hasil screening dan kuesioner perkembangan</p></div></header>
        <h2>Hasil keseluruhan screening</h2>
        <p class="print-meta">Pendaftar: <strong>{{ $registration->nama_anak }}</strong> · {{ $registration->nomor_pendaftaran }}</p>
        @if($screening && $screening->status_validasi === 'dikonfirmasi')
            <div class="print-result"><strong>{{ $screening->kelas_prediksi }}</strong><p>Nomor hasil: {{ $screening->nomor_screening }} · Dikonfirmasi otomatis {{ $screening->validated_at ? \Illuminate\Support\Carbon::parse($screening->validated_at)->format('d-m-Y H:i') : '' }}</p><p>Peluang Sesuai harapan: {{ number_format((float)$screening->probabilitas_sesuai_harapan * 100, 2, ',', '.') }}% · Peluang Memerlukan perhatian lebih lanjut: {{ number_format((float)$screening->probabilitas_perhatian_lanjutan * 100, 2, ',', '.') }}%</p><p>{{ $screening->catatan_admin }}</p></div>
            <h3 class="print-section-title">Ringkasan fitur yang dihitung</h3>
            <table class="print-table"><thead><tr><th>Fitur</th><th>Nilai</th><th>Peluang kelas sesuai harapan</th><th>Peluang kelas perhatian</th></tr></thead><tbody>
                @foreach($details as $detail)<tr><td>{{ $detail->nama_fitur }}</td><td>{{ $detail->nilai_fitur ?? '—' }}</td><td>{{ $detail->peluang_sesuai_harapan === null ? '—' : number_format((float)$detail->peluang_sesuai_harapan, 6, ',', '.') }}</td><td>{{ $detail->peluang_perhatian_lanjutan === null ? '—' : number_format((float)$detail->peluang_perhatian_lanjutan, 6, ',', '.') }}</td></tr>@endforeach
            </tbody></table>
        @elseif($screening)
            <div class="print-callout">Hasil screening sedang menunggu konfirmasi sekolah. Kesimpulan dan peluang belum ditampilkan.</div>
        @else
            <div class="print-callout">Hasil screening belum tersedia.</div>
        @endif
        <div class="print-callout">Hasil merupakan pemetaan awal untuk membantu sekolah meninjau kebutuhan pendampingan; bukan diagnosis medis dan bukan keputusan otomatis penerimaan.</div>
        <h3 class="print-section-title">Jawaban kuesioner ({{ $answers->count() }} pertanyaan)</h3>
        <table class="print-table"><thead><tr><th>No.</th><th>Aspek</th><th>Pertanyaan</th><th>Jawaban</th></tr></thead><tbody>
            @forelse($answers as $index => $answer)<tr><td>{{ $index + 1 }}</td><td>{{ $answer->aspek }}</td><td>{{ $answer->pertanyaan }}</td><td>{{ (float)$answer->nilai === 0.0 ? '0 — Belum teramati' : number_format((float)$answer->nilai, 0) }}</td></tr>@empty<tr><td colspan="4">Belum ada jawaban kuesioner.</td></tr>@endforelse
        </tbody></table>
    </section>
</div>
@endsection

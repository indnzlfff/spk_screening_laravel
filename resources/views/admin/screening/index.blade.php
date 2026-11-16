@extends('layouts.app')

@section('title', 'Antrean Screening')

@section('content')
<div class="page-head"><div><h1>Antrean screening</h1><p>Pilih pendaftar dengan 20 jawaban lengkap untuk menghitung pemetaan awal Naïve Bayes.</p></div><a class="btn" href="{{ route('admin.screening.history') }}">Riwayat hasil</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="notice">Hasil screening membantu sekolah meninjau kebutuhan pendampingan. Hasil bukan diagnosis dan tidak menentukan penerimaan siswa.</div>
@if($activeQuestions !== 20)<div class="alert alert-warning">Pertanyaan aktif saat ini {{ $activeQuestions }}. Proses membutuhkan 20 pertanyaan, empat untuk setiap aspek.</div>@endif
<form method="post" action="{{ route('admin.screening.store') }}">
    @csrf
    <section class="panel">
        <div class="actions" style="justify-content:space-between;margin-bottom:12px"><label><input type="checkbox" id="select-all" {{ $activeQuestions !== 20 || $trainingErrors ? 'disabled' : '' }}> Pilih semua yang siap dihitung</label><button class="btn btn-primary" type="submit" {{ $activeQuestions !== 20 || $trainingErrors ? 'disabled' : '' }}>Hitung screening terpilih</button></div>
        <div class="table-wrap"><table class="table"><thead><tr><th>Pilih</th><th>Nomor pendaftaran</th><th>Nama anak</th><th>Jawaban</th><th>Status</th></tr></thead><tbody>
        @forelse($applicants as $applicant)
            @php($complete = (int) $applicant->jumlah_jawaban === 20 && $activeQuestions === 20 && ! $trainingErrors)
            <tr><td><input class="applicant-check" type="checkbox" name="pendaftar_ids[]" value="{{ $applicant->id }}" {{ $complete ? '' : 'disabled' }}></td><td>{{ $applicant->nomor_pendaftaran }}</td><td><strong>{{ $applicant->nama_anak }}</strong></td><td>{{ $applicant->jumlah_jawaban }} / 20</td><td><span class="badge">{{ $complete ? 'Siap dihitung' : ((int) $applicant->jumlah_jawaban === 20 ? 'Periksa data latih' : 'Menunggu jawaban') }}</span></td></tr>
        @empty
            <tr><td colspan="5" class="empty">Tidak ada pendaftar baru yang menunggu screening.</td></tr>
        @endforelse
        </tbody></table></div>
    </section>
</form>
<script>
const all=document.getElementById('select-all');if(all){const boxes=[...document.querySelectorAll('.applicant-check:not(:disabled)')];all.addEventListener('change',()=>boxes.forEach(box=>box.checked=all.checked));}
</script>
@endsection

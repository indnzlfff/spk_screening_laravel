@extends('layouts.app')

@section('title', $isSpmb ? 'Mulai SPMB' : 'Portal Akun')

@section('content')
<section class="registration-intro parent-access-intro">
    <p class="eyebrow"><span class="eyebrow-dot"></span>{{ $isSpmb ? 'Penerimaan murid baru' : 'Portal keluarga sekolah' }}</p>
    <h1>{{ $isSpmb ? 'Mulai SPMB dengan akun orang tua' : 'Pilih akses akun Anda' }}</h1>
    <p>{{ $isSpmb ? 'Buat akun atau masuk terlebih dahulu. Setelah itu Anda dapat mengisi data anak, mengunggah dokumen, dan menyelesaikan kuesioner.' : 'Gunakan akun orang tua untuk SPMB dan informasi pendaftaran, atau masuk sebagai admin sekolah.' }}</p>
</section>

@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

@if($isSpmb)
    <section class="parent-access-grid" aria-label="Pilihan akun orang tua untuk SPMB">
        <article class="panel parent-access-card">
            <span class="access-number">01</span>
            <h2>Orang tua baru</h2>
            <p>Buat akun orang tua untuk mengisi formulir calon siswa, kuesioner, dan memantau status pendaftaran.</p>
            <a class="btn btn-primary" href="{{ route('parent.register') }}">Buat akun &amp; lanjutkan <span aria-hidden="true">→</span></a>
            <a class="google-signin" href="{{ route('parent.google.redirect') }}" aria-label="Daftar atau masuk menggunakan Google">
                <svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.91c-.58 2.96-2.25 5.47-4.73 7.16l7.64 5.93c4.46-4.12 7.16-10.2 7.16-17.56Z"/><path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.75-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.9.94 7.59 2.56 10.78l7.97-6.19Z"/><path fill="#34A853" d="M24 48c6.47 0 11.91-2.13 15.88-5.79l-7.64-5.93c-2.12 1.42-4.83 2.27-8.24 2.27-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/></svg>
                Lanjutkan dengan Google
            </a>
        </article>
        <article class="panel parent-access-card">
            <span class="access-number access-number-blue">02</span>
            <h2>Sudah memiliki akun?</h2>
            <p>Masuk menggunakan email atau nomor HP dan kata sandi untuk melanjutkan pendaftaran atau melihat status anak.</p>
            <a class="btn" href="{{ route('parent.login') }}">Masuk akun orang tua</a>
        </article>
    </section>
@else
    <section class="parent-access-grid" aria-label="Pilihan jenis akun">
        <article class="panel parent-access-card">
            <span class="access-number">01</span>
            <h2>Admin</h2>
            <p>Masuk ke panel sekolah untuk mengelola data pendaftar, screening, dan administrasi.</p>
            <a class="btn" href="{{ route('admin.login') }}">Masuk sebagai Admin</a>
        </article>
        <article class="panel parent-access-card">
            <span class="access-number access-number-blue">02</span>
            <h2>Orang Tua</h2>
            <p>Masuk menggunakan email atau nomor HP dan kata sandi untuk melihat status pendaftaran, hasil screening, dan cetak bukti.</p>
            <a class="btn btn-primary" href="{{ route('parent.login') }}">Masuk sebagai Orang Tua</a>
        </article>
    </section>
@endif

@if($isSpmb)
    <section class="panel parent-flow-note">
        <h2>Alur SPMB</h2>
        <ol><li>Buat akun orang tua atau masuk.</li><li>Isi data calon siswa dan unggah akta kelahiran serta kartu keluarga.</li><li>Lengkapi seluruh pertanyaan kuesioner, lalu kirim pendaftaran.</li><li>Pantau status dan hasil screening melalui dashboard orang tua. Jika dinyatakan diterima oleh sekolah, informasi tagihan administrasi tersedia di akun.</li></ol>
        <p class="small muted">Hasil screening merupakan pemetaan awal perkembangan anak; keputusan penerimaan ditetapkan oleh sekolah.</p>
    </section>
@endif
@endsection

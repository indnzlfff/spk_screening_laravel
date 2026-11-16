@extends('layouts.app')

@section('title', 'Buat Akun Orang Tua')

@section('content')
<section class="login-box panel parent-auth-box">
    <p class="eyebrow"><span class="eyebrow-dot"></span>Portal orang tua</p>
    <h1>Buat akun orang tua</h1>
    <p class="muted">Akun ini digunakan untuk mengisi SPMB, melihat status pendaftaran, dan mengakses informasi pembayaran.</p>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('parent.register.submit') }}" class="parent-auth-form">
        @csrf
        <div class="field"><label for="nama">Nama orang tua/wali</label><input id="nama" name="nama" value="{{ old('nama') }}" maxlength="150" autocomplete="name" data-capitalize="words" autocapitalize="words" required></div>
        <div class="field"><label for="email">Email aktif</label><input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="191" autocomplete="email" required></div>
        <div class="field"><label for="no_hp">Nomor HP aktif</label><input id="no_hp" name="no_hp" type="tel" value="{{ old('no_hp') }}" inputmode="tel" autocomplete="tel" required></div>
        <div class="field"><label for="password">Kata sandi (minimal 8 karakter)</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required></div>
        <div class="field"><label for="password_confirmation">Ulangi kata sandi</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required></div>
        <button class="btn btn-primary" type="submit">Buat akun &amp; lanjutkan <span aria-hidden="true">→</span></button>
    </form>
    <div class="auth-divider"><span>atau</span></div>
    <a class="google-signin" href="{{ route('parent.google.redirect') }}"><span class="google-g" aria-hidden="true">G</span>Daftar atau masuk dengan Google</a>
    <p class="small muted parent-auth-switch">Sudah memiliki akun? <a href="{{ route('parent.login') }}">Masuk di sini</a>.</p>
</section>
@endsection

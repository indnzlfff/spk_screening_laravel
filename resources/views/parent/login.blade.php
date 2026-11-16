@extends('layouts.app')

@section('title', 'Masuk Akun Orang Tua')

@section('content')
<section class="login-box panel parent-auth-box">
    <p class="eyebrow"><span class="eyebrow-dot"></span>Portal orang tua</p>
    <h1>Masuk akun orang tua</h1>
    <p class="muted">Gunakan email atau nomor HP dan kata sandi akun Anda.</p>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('parent.login.submit') }}" class="parent-auth-form">
        @csrf
        <div class="field"><label for="email_or_hp">Email atau nomor HP</label><input id="email_or_hp" name="email_or_hp" type="text" value="{{ old('email_or_hp') }}" autocomplete="username" required></div>
        <div class="field"><label for="password">Kata sandi</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <button class="btn btn-primary" type="submit">Masuk &amp; lanjutkan</button>
    </form>
    <div class="auth-divider"><span>atau</span></div>
    <a class="google-signin" href="{{ route('parent.google.redirect') }}"><span class="google-g" aria-hidden="true">G</span>Masuk dengan Google</a>
    <p class="small muted parent-auth-switch">Belum memiliki akun? <a href="{{ route('parent.register') }}">Buat akun orang tua</a>.</p>
</section>
@endsection

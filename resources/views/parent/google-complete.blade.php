@extends('layouts.app')

@section('title', 'Lengkapi Akun Orang Tua')

@section('content')
<section class="login-box panel parent-auth-box">
    <p class="eyebrow"><span class="eyebrow-dot"></span>Verifikasi akun Google</p>
    <h1>Lengkapi akun orang tua</h1>
    <p class="muted">Google mengonfirmasi akun <strong>{{ $profile['email'] }}</strong>. Tambahkan nomor HP aktif untuk kebutuhan komunikasi pendaftaran.</p>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('parent.google.complete.submit') }}" class="parent-auth-form">
        @csrf
        <div class="field"><label for="no_hp">Nomor HP aktif</label><input id="no_hp" name="no_hp" type="tel" value="{{ old('no_hp') }}" inputmode="tel" autocomplete="tel" required></div>
        <button class="btn btn-primary" type="submit">Buat akun &amp; lanjutkan <span aria-hidden="true">→</span></button>
    </form>
</section>
@endsection

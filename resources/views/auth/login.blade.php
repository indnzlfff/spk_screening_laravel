@extends('layouts.app')

@section('title', 'Masuk Petugas')

@section('content')
<section class="panel login-box">
    <h1 style="font-size:1.7rem;margin:0 0 6px">Masuk petugas sekolah</h1>
    <p class="muted">Gunakan akun admin atau guru yang sudah terdaftar.</p>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('admin.login.submit') }}" class="form-grid">
        @csrf
        <div class="field full"><label for="username">Nama pengguna</label><input id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus></div>
        <div class="field full"><label for="password">Kata sandi</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <div class="field full"><button class="btn btn-primary" type="submit">Masuk</button></div>
    </form>
</section>
@endsection

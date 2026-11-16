<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FFF9F0">
    <meta name="description" content="TK Pembina Hidayatul Mubtadiin, lembaga PAUD Terakreditasi A dan Tersertifikasi ISO 9001:2008 di Kota Pasuruan.">
    <title>@yield('title', 'Beranda') · TK Pembina Hidayatul Mubtadiin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900;1000&family=Nunito+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        (() => {
            document.documentElement.classList.add('js');
            try {
                const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (window.location.pathname === '/' && !reduced && !sessionStorage.getItem('tkpm-intro-seen')) {
                    document.documentElement.classList.add('show-site-intro');
                }
            } catch (_) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
    <noscript><style>.site-intro{display:none!important}</style></noscript>
    @stack('styles')
</head>
<body>
<a class="skip-link" href="#main-content">Lewati ke isi halaman</a>
@include('layouts.partials.intro')
@include('layouts.partials.navigation')
@if(request()->routeIs('admin.*') && session()->has('school_user_id'))
    <div class="shell">
        <aside class="sidebar" aria-label="Menu admin sekolah">
            <div class="sidebar-heading"><span class="brand-mark brand-mark-logo" aria-hidden="true"><img src="{{ asset('assets/school/logo-sekolah.png') }}" alt=""></span><span><strong>Panel Sekolah</strong><small>Pengelolaan pendaftaran</small></span></div>
            <nav aria-label="Menu panel sekolah">
                <div class="side-label">Utama</div>
                <a class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Beranda</a>
                <a class="side-link {{ request()->routeIs('admin.applicants.*') ? 'active' : '' }}" href="{{ route('admin.applicants.index') }}">Data pendaftar</a>
                <div class="side-label">Screening</div>
                <a class="side-link {{ request()->routeIs('admin.screening.index') ? 'active' : '' }}" href="{{ route('admin.screening.index') }}">Antrean screening</a>
                <a class="side-link {{ request()->routeIs('admin.training.*') ? 'active' : '' }}" href="{{ route('admin.training.index') }}">Data latih</a>
                <a class="side-link {{ request()->routeIs('admin.evaluation') ? 'active' : '' }}" href="{{ route('admin.evaluation') }}">Evaluasi model</a>
                <a class="side-link {{ request()->routeIs('admin.screening.history','admin.screening.result') ? 'active' : '' }}" href="{{ route('admin.screening.history') }}">Riwayat hasil</a>
                @if(session('school_user_role') === 'admin')
                    <div class="side-label">Administrasi</div>
                    <a class="side-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}">Tagihan dan pembayaran</a>
                @endif
            </nav>
            <form method="post" action="{{ route('admin.logout') }}" style="margin:24px 10px 0">
                @csrf
                <button class="side-link" type="submit" style="width:100%;border:0;background:#fff1ee;text-align:left;cursor:pointer">Keluar dari akun Admin</button>
            </form>
        </aside>
        <main class="main" id="main-content">@yield('content')</main>
    </div>
@else
    <main class="main public-main" id="main-content">@yield('content')</main>
@endif
@include('layouts.partials.footer')
<div class="lightbox" id="site-lightbox" role="dialog" aria-modal="true" aria-label="Pratinjau gambar" hidden>
    <button class="lightbox-close" type="button" aria-label="Tutup gambar" data-lightbox-close>×</button>
    <figure><img src="" alt="" data-lightbox-image><figcaption data-lightbox-caption></figcaption></figure>
</div>
<script src="{{ asset('assets/js/site.js') }}?v={{ filemtime(public_path('assets/js/site.js')) }}" defer></script>
@stack('scripts')
</body>
</html>

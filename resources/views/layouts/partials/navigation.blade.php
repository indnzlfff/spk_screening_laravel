<header class="site-header">
    @php
        $isSchoolUser = session()->has('school_user_id');
        $isParentUser = session()->has('parent_account_id');
    @endphp
    <div class="topbar-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="TK Pembina Hidayatul Mubtadiin, beranda">
            <span class="brand-mark brand-mark-logo" aria-hidden="true"><img src="{{ asset('assets/school/logo-sekolah.png') }}" alt=""></span>
            <span class="brand-copy"><strong>TK Pembina Hidayatul Mubtadiin</strong><small>Terakreditasi A · ISO 9001:2008</small></span>
        </a>
        <button class="nav-toggle" type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="primary-navigation" data-nav-toggle>
            <span></span><span></span><span></span>
        </button>
        <nav class="top-links" id="primary-navigation" aria-label="Navigasi utama" data-primary-navigation>
            <a class="nav-link {{ request()->routeIs('home') ? 'is-current' : '' }}" href="{{ route('home') }}">Beranda</a>
            <a class="nav-link {{ request()->routeIs('school.about','school.profile') ? 'is-current' : '' }}" href="{{ route('school.about') }}#profil-guru">Profil</a>
            <a class="nav-link {{ request()->routeIs('activities.*') ? 'is-current' : '' }}" href="{{ route('activities.index') }}">Dokumentasi</a>
            <a class="nav-primary nav-spmb {{ request()->routeIs('spmb.*','registration.*') ? 'is-current' : '' }}" href="{{ route('spmb.entry') }}">SPMB <span aria-hidden="true">↗</span></a>
            <details class="account-menu">
                <summary class="nav-link" aria-label="Buka pilihan akun">Akun</summary>
                <div class="account-menu-panel">
                    <a href="{{ $isSchoolUser ? route('admin.dashboard') : route('admin.login') }}">Admin</a>
                    <a href="{{ $isParentUser ? route('parent.dashboard') : route('parent.login') }}">Orang Tua</a>
                </div>
            </details>
        </nav>
    </div>
</header>

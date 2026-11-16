@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<section class="home-hero">
    <div class="home-copy reveal is-visible">
        <p class="eyebrow"><span class="eyebrow-dot"></span>TK Pembina Hidayatul Mubtadiin · Kota Pasuruan</p>
        <h1>Tempat Kecil untuk <em>Mimpi yang Besar!</em></h1>
        <p class="lead">Menemani setiap langkah kecil anak dalam tumbuh, belajar, bermain, dan mengenal dunia dengan penuh keceriaan.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="{{ route('spmb.entry') }}">Mulai SPMB <span aria-hidden="true">→</span></a>
            <a class="btn" href="{{ route('school.about') }}">Kenali Sekolah Kami</a>
        </div>
        <div class="hero-credentials" aria-label="Identitas mutu sekolah">
            <span class="credential-pill"><b>A</b> Akreditasi A</span>
            <span class="credential-pill"><b>✓</b> ISO 9001:2008</span>
            <span class="credential-pill"><b>2000</b> Berdiri sejak</span>
        </div>
    </div>
    <div class="hero-art reveal reveal-delay-1">
        <span class="hero-sticker hero-sticker-one">Belajar sambil bermain</span>
        <img src="{{ asset('assets/illustrations/hero-classroom.svg') }}" alt="Ilustrasi suasana anak-anak belajar, membaca, dan bermain bersama di kelas" fetchpriority="high">
        <span class="hero-sticker hero-sticker-two">Tumbuh dengan gembira</span>
    </div>
</section>

<section class="school-welcome">
    <div class="welcome-visual reveal">
        <img class="welcome-photo" src="{{ asset('assets/school/guru-dan-staf.jpg') }}" alt="Guru dan staf TK Pembina Hidayatul Mubtadiin" loading="lazy">
        <span class="welcome-badge">Hangat · Islami · Menyenangkan</span>
    </div>
    <div class="welcome-copy reveal reveal-delay-1">
        <p class="eyebrow"><span class="eyebrow-dot"></span>Selamat datang</p>
        <h2>Ruang aman untuk tumbuh dan menemukan hal baru.</h2>
        <p>TK Pembina Hidayatul Mubtadiin telah mendampingi keluarga di Kota Pasuruan sejak 15 Juli 2000. Dalam naungan Yayasan Pendidikan Hidayatul Mubtadiin (YAHIMU), anak belajar melalui pengalaman yang dekat dengan kehidupan, nilai karakter Islami, dan rasa ingin tahu.</p>
        <a class="btn" href="{{ route('school.about') }}">Cerita tentang sekolah <span aria-hidden="true">→</span></a>
    </div>
</section>

<section class="section-block">
    <div class="section-heading reveal">
        <p class="eyebrow"><span class="eyebrow-dot"></span>Yang membuat kami istimewa</p>
        <h2>Hari-hari kecil yang penuh arti</h2>
        <p>Kami mendampingi anak dengan perhatian, ruang untuk mencoba, serta pengalaman belajar yang menyenangkan.</p>
    </div>
    <div class="reason-grid">
        <article class="reason-card reveal">
            <span class="reason-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9.5V21h13V9.5M9 21v-7h6v7"/></svg></span>
            <h3>Lingkungan yang nyaman</h3><p>Lingkungan belajar yang akrab membantu anak merasa aman, diterima, dan siap bereksplorasi.</p>
        </article>
        <article class="reason-card reveal reveal-delay-1">
            <span class="reason-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/><path d="m19 16 .8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8L19 16Z"/></svg></span>
            <h3>Belajar kreatif</h3><p>Kegiatan bermain, berkarya, dan mencoba hal baru memberi ruang bagi imajinasi dan rasa ingin tahu.</p>
        </article>
        <article class="reason-card reveal reveal-delay-2">
            <span class="reason-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s-8-4.4-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6.6-8 11-8 11Z"/><path d="m8.5 12 2.2 2.2 4.8-5"/></svg></span>
            <h3>Karakter Islami</h3><p>Pembiasaan ibadah, doa harian, dan akhlakul karimah hadir dalam keseharian anak.</p>
        </article>
        <article class="reason-card reveal reveal-delay-3">
            <span class="reason-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20v-1.6A4.4 4.4 0 0 1 8 14h2a4.4 4.4 0 0 1 4.5 4.4V20"/><path d="M16 5.2a3.1 3.1 0 0 1 0 5.8M17 14h.5a3.7 3.7 0 0 1 3.5 3.7V20"/></svg></span>
            <h3>Guru yang mendampingi</h3><p>Tenaga pendidik profesional mendukung setiap anak dengan perhatian dan komunikasi hangat.</p>
        </article>
    </div>
</section>

<section class="learning-section">
    <div class="section-heading reveal">
        <p class="eyebrow"><span class="eyebrow-dot"></span>Belajar dari banyak cara</p>
        <h2>Aktivitas kecil, pengalaman besar</h2>
        <p>Anak mengenal diri dan dunia melalui kegiatan yang aktif, beragam, dan sesuai tahap tumbuh kembangnya.</p>
    </div>
    <div class="learning-grid">
        <article class="learning-card reveal"><img src="{{ asset('assets/illustrations/activity-imtaq.svg') }}" alt="Ilustrasi pembiasaan doa dan nilai akhlak" loading="lazy"><div class="learning-card-copy"><span>Iman &amp; akhlak</span><h3>IMTAQ Day</h3><p>Pembiasaan ibadah, hafalan doa harian, dan nilai akhlakul karimah.</p></div></article>
        <article class="learning-card reveal reveal-delay-1"><img src="{{ asset('assets/illustrations/activity-nature.svg') }}" alt="Ilustrasi anak mengenal alam dan menanam padi" loading="lazy"><div class="learning-card-copy"><span>Eksplorasi alam</span><h3>Outing Class</h3><p>Belajar di alam terbuka melalui kegiatan pertanian, menanam padi, dan edukasi lingkungan.</p></div></article>
        <article class="learning-card reveal reveal-delay-2"><img src="{{ asset('assets/illustrations/activity-creativity.svg') }}" alt="Ilustrasi kegiatan seni dan kreativitas anak" loading="lazy"><div class="learning-card-copy"><span>Bakat &amp; ekspresi</span><h3>Seni dan olahraga</h3><p>Mengembangkan bakat melalui seni tari, pentas seni, serta olahraga renang.</p></div></article>
        <article class="learning-card reveal reveal-delay-3"><img src="{{ asset('assets/illustrations/activity-blocks.svg') }}" alt="Ilustrasi anak membangun dan belajar bersama" loading="lazy"><div class="learning-card-copy"><span>Belajar bersama</span><h3>Lingkungan terpadu</h3><p>Terintegrasi dalam satu kompleks bersama KB dan SD Hidayatul Mubtadiin.</p></div></article>
    </div>
</section>

<section class="teacher-preview section-block">
    <div class="section-heading reveal">
        <p class="eyebrow"><span class="eyebrow-dot"></span>Orang-orang di balik keceriaan</p>
        <h2>Berkenalan dengan pendidik kami</h2>
        <p>Para guru dan tenaga pendidikan yang menemani proses belajar anak setiap hari.</p>
    </div>
    <div class="teacher-grid">
        <article class="teacher-card reveal"><img src="{{ asset('assets/school/kepala-sekolah-sri-mintarti.jpg') }}" alt="Sri Mintarti, kepala sekolah" loading="lazy"><div class="teacher-card-copy"><h3>Sri Mintarti</h3><p>Kepala Sekolah</p></div></article>
        <article class="teacher-card reveal reveal-delay-1"><img src="{{ asset('assets/school/guru-hariyanti.jpg') }}" alt="Hariyanti, guru sentra persiapan" loading="lazy"><div class="teacher-card-copy"><h3>Hariyanti, S. Pd</h3><p>Guru Sentra Persiapan · Wali Kelas</p></div></article>
        <article class="teacher-card reveal reveal-delay-2"><img src="{{ asset('assets/school/guru-ova-sulistyowati.jpg') }}" alt="Ova Sulistyowati, guru sentra kreativitas" loading="lazy"><div class="teacher-card-copy"><h3>Ova Sulistyowati, S. Pd</h3><p>Guru Sentra Kreativitas · Wali Kelas</p></div></article>
        <article class="teacher-card reveal reveal-delay-3"><img src="{{ asset('assets/school/guru-khalimah.jpg') }}" alt="Khalimah, guru sentra alam dan sains" loading="lazy"><div class="teacher-card-copy"><h3>Khalimah, S. Pd</h3><p>Guru Sentra Alam dan Sains</p></div></article>
    </div>
    <div class="section-link"><a class="btn" href="{{ route('school.profile') }}">Lihat semua profil pendidik <span aria-hidden="true">→</span></a></div>
</section>

<section class="gallery-section">
    <div class="section-heading reveal">
        <p class="eyebrow"><span class="eyebrow-dot"></span>Cuplikan kebersamaan</p>
        <h2>Belajar, berkarya, dan tumbuh bersama</h2>
        <p>Sentuh gambar untuk melihat lebih dekat suasana dan inspirasi kegiatan di sekolah.</p>
    </div>
    <div class="gallery-grid">
        <button class="gallery-tile reveal" type="button" data-lightbox-src="{{ asset('assets/school/guru-dan-staf.jpg') }}" data-lightbox-alt="Guru dan staf TK Pembina Hidayatul Mubtadiin" data-lightbox-caption="Guru dan staf TK Pembina Hidayatul Mubtadiin"><img src="{{ asset('assets/school/guru-dan-staf.jpg') }}" alt="Guru dan staf sekolah" loading="lazy"><span class="gallery-caption">Kebersamaan keluarga sekolah</span></button>
        <button class="gallery-tile reveal reveal-delay-1" type="button" data-lightbox-src="{{ asset('assets/illustrations/activity-imtaq.svg') }}" data-lightbox-alt="Ilustrasi kegiatan IMTAQ Day" data-lightbox-caption="IMTAQ Day · Doa harian dan akhlakul karimah"><img src="{{ asset('assets/illustrations/activity-imtaq.svg') }}" alt="Kegiatan IMTAQ Day" loading="lazy"><span class="gallery-caption">IMTAQ Day</span></button>
        <button class="gallery-tile reveal reveal-delay-2" type="button" data-lightbox-src="{{ asset('assets/illustrations/activity-nature.svg') }}" data-lightbox-alt="Ilustrasi outing class dan edukasi alam" data-lightbox-caption="Outing Class · Eksplorasi alam"><img src="{{ asset('assets/illustrations/activity-nature.svg') }}" alt="Outing class dan edukasi alam" loading="lazy"><span class="gallery-caption">Eksplorasi alam</span></button>
        <button class="gallery-tile reveal reveal-delay-1" type="button" data-lightbox-src="{{ asset('assets/illustrations/activity-creativity.svg') }}" data-lightbox-alt="Ilustrasi seni tari dan kreativitas" data-lightbox-caption="Ekspresi seni dan kreativitas"><img src="{{ asset('assets/illustrations/activity-creativity.svg') }}" alt="Seni dan kreativitas" loading="lazy"><span class="gallery-caption">Seni dan kreativitas</span></button>
        <button class="gallery-tile reveal reveal-delay-2" type="button" data-lightbox-src="{{ asset('assets/illustrations/activity-blocks.svg') }}" data-lightbox-alt="Ilustrasi belajar dan bermain bersama" data-lightbox-caption="Belajar dan bermain bersama"><img src="{{ asset('assets/illustrations/activity-blocks.svg') }}" alt="Belajar dan bermain bersama" loading="lazy"><span class="gallery-caption">Belajar bersama</span></button>
    </div>
</section>

<section class="admission-card reveal">
    <div>
        <p class="eyebrow"><span class="eyebrow-dot"></span>Langkah pertama dimulai di sini</p>
        <h2>Siap memulai petualangan belajar anak?</h2>
        <p>Kenali sekolah lebih dekat, lengkapi pendaftaran, lalu pantau informasi melalui akun orang tua. Tim sekolah siap mendampingi.</p>
        <p class="admission-note">Screening merupakan pemetaan awal perkembangan anak, bukan diagnosis medis atau keputusan otomatis penerimaan siswa.</p>
    </div>
    <div class="admission-actions"><a class="btn btn-primary" href="{{ route('spmb.entry') }}">Mulai SPMB <span aria-hidden="true">→</span></a><a class="btn" href="{{ route('account.access') }}">Portal akun orang tua</a></div>
</section>
@endsection

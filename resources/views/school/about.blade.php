@extends('layouts.app')

@section('title', 'Profil Sekolah')

@section('content')
<section class="staff-page-head reveal">
    <div>
        <p class="eyebrow"><span class="eyebrow-dot"></span>Profil sekolah</p>
        <h1>TK Pembina Hidayatul Mubtadiin</h1>
        <p>Lembaga Pendidikan Anak Usia Dini Terakreditasi A &amp; Tersertifikasi ISO 9001:2008 yang memadukan nilai karakter islami, kreativitas, dan eksplorasi alam sejak dini di Kota Pasuruan.</p>
    </div>
    <div class="staff-school-logo"><img src="{{ asset('assets/school/logo-sekolah.png') }}" alt="Logo TK Pembina Hidayatul Mubtadiin"></div>
</section>

<section class="panel reveal">
    <div class="section-intro"><h2>Tentang Kami</h2></div>
    <p class="school-about-copy">Didirikan pada 15 Juli 2000 di bawah naungan Yayasan Pendidikan Hidayatul Mubtadiin (YAHIMU), TK Pembina Hidayatul Mubtadiin berkomitmen memberikan layanan pendidikan prasekolah terbaik di Kota Pasuruan. Dengan dukungan tenaga pendidik profesional di bawah kepemimpinan Ibu Sri Mintarti serta lingkungan belajar yang terintegrasi dengan KB dan SD Hidayatul Mubtadiin, kami mendampingi tumbuh kembang anak secara holistik melalui pendekatan bermain sambil belajar yang bernuansa Islami.</p>
</section>

<section class="panel reveal">
    <div class="section-intro"><h2>Identitas dan legalitas</h2><p>Informasi resmi TK Pembina Hidayatul Mubtadiin.</p></div>
    <dl class="identity-grid">
        <div class="identity-item"><dt>Nama Instansi</dt><dd>TK Pembina Hidayatul Mubtadiin</dd></div>
        <div class="identity-item"><dt>Yayasan</dt><dd>Yayasan Pendidikan Hidayatul Mubtadiin (YAHIMU)</dd></div>
        <div class="identity-item"><dt>NPSN</dt><dd>20559183</dd></div>
        <div class="identity-item"><dt>Status</dt><dd>Swasta</dd></div>
        <div class="identity-item"><dt>Tanggal Berdiri</dt><dd>15 Juli 2000<br><span class="muted small">SK Pendirian: 01/YAHIMU/VII/2000</span></dd></div>
        <div class="identity-item"><dt>Akreditasi</dt><dd>A<br><span class="muted small">SK No. 158/BAN PAUD DAN PNF/AKR/2018</span></dd></div>
        <div class="identity-item"><dt>Sertifikasi Mutu</dt><dd>ISO 9001:2008</dd></div>
        <div class="identity-item"><dt>Kepala Sekolah</dt><dd>Sri Mintarti</dd></div>
    </dl>
</section>

<section class="panel reveal">
    <div class="section-intro"><h2>Program dan kegiatan</h2><p>Kegiatan belajar untuk menumbuhkan iman, kreativitas, kemandirian, dan rasa ingin tahu anak.</p></div>
    <div class="program-grid">
        <article class="program-card"><span class="program-number">01</span><div><h3>IMTAQ Day</h3><p>Pembiasaan ibadah, hafalan doa harian, dan nilai akhlakul karimah.</p></div></article>
        <article class="program-card"><span class="program-number">02</span><div><h3>Outing Class &amp; Edukasi Alam</h3><p>Belajar langsung di alam terbuka, seperti pertanian dan menanam padi, serta edukasi lingkungan.</p></div></article>
        <article class="program-card"><span class="program-number">03</span><div><h3>Pengembangan Bakat &amp; Ekstrakurikuler</h3><p>Seni tari, pentas seni, dan olahraga renang.</p></div></article>
        <article class="program-card"><span class="program-number">04</span><div><h3>Lingkungan Pendidikan Terpadu</h3><p>Terintegrasi dalam satu kompleks dengan KB dan SD Hidayatul Mubtadiin.</p></div></article>
    </div>
</section>

<section class="panel reveal profile-staff-preview" id="profil-guru" aria-labelledby="profile-staff-title">
    <div class="section-intro"><h2 id="profile-staff-title">Profil &amp; Guru</h2><p>Kenali para pendidik yang mendampingi anak belajar dan tumbuh setiap hari.</p></div>
    <div class="staff-grid">
        <article class="staff-card"><div class="staff-photo"><img src="{{ asset('assets/school/kepala-sekolah-sri-mintarti.jpg') }}" alt="Sri Mintarti, Kepala Sekolah" loading="lazy"></div><div class="staff-card-copy"><h3>Sri Mintarti</h3><p>Kepala Sekolah</p></div></article>
        <article class="staff-card"><div class="staff-photo"><img src="{{ asset('assets/school/guru-hariyanti.jpg') }}" alt="Hariyanti, guru sentra persiapan" loading="lazy"></div><div class="staff-card-copy"><h3>Hariyanti, S. Pd</h3><p>Guru Sentra Persiapan · Wali Kelas</p></div></article>
        <article class="staff-card"><div class="staff-photo"><img src="{{ asset('assets/school/guru-ova-sulistyowati.jpg') }}" alt="Ova Sulistyowati, guru sentra kreativitas" loading="lazy"></div><div class="staff-card-copy"><h3>Ova Sulistyowati, S. Pd</h3><p>Guru Sentra Kreativitas · Wali Kelas</p></div></article>
        <article class="staff-card"><div class="staff-photo"><img src="{{ asset('assets/school/guru-khalimah.jpg') }}" alt="Khalimah, guru sentra alam dan sains" loading="lazy"></div><div class="staff-card-copy"><h3>Khalimah, S. Pd</h3><p>Guru Sentra Alam dan Sains</p></div></article>
    </div>
    <div class="school-about-actions"><a class="btn" href="{{ route('school.profile') }}">Lihat semua profil pendidik <span aria-hidden="true">→</span></a></div>
</section>

<section class="panel reveal profile-staff-preview" id="profil-guru" aria-labelledby="profile-staff-title">
    <div class="section-intro"><h2 id="profile-staff-title">Profil pendidik</h2><p>Kenali para pendidik yang mendampingi anak belajar dan tumbuh setiap hari.</p></div>
    <div class="staff-grid">
        <article class="staff-card"><div class="staff-photo"><img src="{{ asset('assets/school/kepala-sekolah-sri-mintarti.jpg') }}" alt="Sri Mintarti, Kepala Sekolah" loading="lazy"></div><div class="staff-card-copy"><h3>Sri Mintarti</h3><p>Kepala Sekolah</p></div></article>
        <article class="staff-card"><div class="staff-photo"><img src="{{ asset('assets/school/guru-hariyanti.jpg') }}" alt="Hariyanti, guru sentra persiapan" loading="lazy"></div><div class="staff-card-copy"><h3>Hariyanti, S. Pd</h3><p>Guru Sentra Persiapan · Wali Kelas</p></div></article>
        <article class="staff-card"><div class="staff-photo"><img src="{{ asset('assets/school/guru-ova-sulistyowati.jpg') }}" alt="Ova Sulistyowati, guru sentra kreativitas" loading="lazy"></div><div class="staff-card-copy"><h3>Ova Sulistyowati, S. Pd</h3><p>Guru Sentra Kreativitas · Wali Kelas</p></div></article>
        <article class="staff-card"><div class="staff-photo"><img src="{{ asset('assets/school/guru-khalimah.jpg') }}" alt="Khalimah, guru sentra alam dan sains" loading="lazy"></div><div class="staff-card-copy"><h3>Khalimah, S. Pd</h3><p>Guru Sentra Alam dan Sains</p></div></article>
    </div>
    <div class="school-about-actions"><a class="btn" href="{{ route('school.profile') }}">Lihat semua profil pendidik <span aria-hidden="true">→</span></a></div>
</section>

<section class="panel reveal">
    <div class="section-intro"><h2>Kontak dan lokasi</h2><p>Hubungi atau kunjungi TK Pembina Hidayatul Mubtadiin.</p></div>
    <dl class="contact-grid">
        <div class="contact-card"><dt>Alamat</dt><dd>Jl. Teuku Umar No. 35, RT. 05 / RW. 11, Kel. Sebani, Kec. Gadingrejo, Kota Pasuruan, Jawa Timur 67139</dd></div>
        <div class="contact-card"><dt>Telepon</dt><dd><a href="tel:0895349199975">0895349199975</a></dd></div>
        <div class="contact-card"><dt>Email</dt><dd><a href="mailto:tkpembinahidayatulmubtadiin@gmail.com">tkpembinahidayatulmubtadiin@gmail.com</a></dd></div>
        <div class="contact-card"><dt>Instagram</dt><dd><a href="https://www.instagram.com/tk_p_hidayatulmubtadiin?dlrf=ZWpjcmowaDZkMGU0" target="_blank" rel="noopener noreferrer">@tk_p_hidayatulmubtadiin <span aria-hidden="true">↗</span></a></dd></div>
    </dl>
    <div class="school-about-actions">
        <a class="btn btn-primary" href="{{ route('school.profile') }}">Lihat profil guru <span aria-hidden="true">→</span></a>
        <a class="btn" href="{{ route('spmb.entry') }}">Informasi SPMB</a>
    </div>
</section>
@endsection

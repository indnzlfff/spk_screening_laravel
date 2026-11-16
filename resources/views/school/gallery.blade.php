@extends('layouts.app')

@section('title', 'Galeri Kegiatan')

@section('content')
<section class="staff-page-head reveal">
    <div>
        <p class="eyebrow"><span class="eyebrow-dot"></span>Galeri kegiatan</p>
        <h1>Belajar, berkarya, dan tumbuh bersama</h1>
        <p>Cuplikan kebersamaan di sekolah serta ilustrasi kegiatan yang menemani anak mengenal iman, alam, seni, dan teman-teman.</p>
    </div>
    <div class="staff-school-logo"><img src="{{ asset('assets/school/logo-sekolah.png') }}" alt="Logo TK Pembina Hidayatul Mubtadiin"></div>
</section>

<section class="panel reveal" aria-labelledby="gallery-title">
    <div class="section-intro"><h2 id="gallery-title">Kegiatan di TK Pembina Hidayatul Mubtadiin</h2><p>Pilih gambar untuk melihatnya lebih dekat.</p></div>
    <div class="gallery-grid">
        <button class="gallery-tile reveal" type="button" data-lightbox-src="{{ asset('assets/school/guru-dan-staf.jpg') }}" data-lightbox-alt="Guru dan staf TK Pembina Hidayatul Mubtadiin" data-lightbox-caption="Kebersamaan guru dan staf sekolah">
            <img src="{{ asset('assets/school/guru-dan-staf.jpg') }}" alt="Guru dan staf sekolah" loading="lazy"><span class="gallery-caption">Kebersamaan keluarga sekolah</span>
        </button>
        <button class="gallery-tile reveal reveal-delay-1" type="button" data-lightbox-src="{{ asset('assets/illustrations/activity-imtaq.svg') }}" data-lightbox-alt="Ilustrasi pembiasaan doa dan nilai akhlak" data-lightbox-caption="IMTAQ Day · Doa harian dan akhlakul karimah">
            <img src="{{ asset('assets/illustrations/activity-imtaq.svg') }}" alt="Ilustrasi kegiatan IMTAQ Day" loading="lazy"><span class="gallery-caption">IMTAQ Day</span>
        </button>
        <button class="gallery-tile reveal reveal-delay-2" type="button" data-lightbox-src="{{ asset('assets/illustrations/activity-nature.svg') }}" data-lightbox-alt="Ilustrasi outing class dan edukasi alam" data-lightbox-caption="Outing Class · Eksplorasi alam">
            <img src="{{ asset('assets/illustrations/activity-nature.svg') }}" alt="Ilustrasi outing class dan edukasi alam" loading="lazy"><span class="gallery-caption">Eksplorasi alam</span>
        </button>
        <button class="gallery-tile reveal reveal-delay-1" type="button" data-lightbox-src="{{ asset('assets/illustrations/activity-creativity.svg') }}" data-lightbox-alt="Ilustrasi seni tari dan kreativitas anak" data-lightbox-caption="Seni tari, pentas seni, dan kreativitas">
            <img src="{{ asset('assets/illustrations/activity-creativity.svg') }}" alt="Ilustrasi seni dan kreativitas" loading="lazy"><span class="gallery-caption">Seni dan kreativitas</span>
        </button>
        <button class="gallery-tile reveal reveal-delay-2" type="button" data-lightbox-src="{{ asset('assets/illustrations/activity-blocks.svg') }}" data-lightbox-alt="Ilustrasi anak belajar dan bermain bersama" data-lightbox-caption="Belajar dan bermain bersama">
            <img src="{{ asset('assets/illustrations/activity-blocks.svg') }}" alt="Ilustrasi belajar dan bermain bersama" loading="lazy"><span class="gallery-caption">Belajar bersama</span>
        </button>
    </div>
</section>

<section class="admission-card reveal">
    <div><p class="eyebrow">Ingin berkenalan lebih dekat?</p><h2>Tempat tumbuh, belajar, dan bermain</h2><p>Lihat profil sekolah dan para pendidik kami.</p></div>
    <a class="btn btn-primary" href="{{ route('school.about') }}#profil-guru">Profil &amp; Guru <span aria-hidden="true">→</span></a>
</section>
@endsection

@extends('layouts.app')

@section('title', 'Profil Guru')

@php
$staffGroups = [
    [
        'title' => 'Kepala Sekolah',
        'people' => [
            ['name' => 'Sri Mintarti, S. Pd', 'role' => 'Kepala Sekolah', 'photo' => 'kepala-sekolah-sri-mintarti.jpg'],
        ],
    ],
    [
        'title' => 'Sentra Persiapan',
        'people' => [
            ['name' => 'Hariyanti, S. Pd', 'role' => 'Guru Sentra Persiapan · Wali Kelas', 'photo' => 'guru-hariyanti.jpg'],
            ['name' => 'Laila Dwi Puspita, S. Pd', 'role' => 'Guru Sentra Persiapan · Wali Kelas', 'photo' => 'guru-laila-dwi-puspita.jpg'],
        ],
    ],
    [
        'title' => 'Sentra Kreativitas',
        'people' => [
            ['name' => 'Ova Sulistyowati, S. Pd', 'role' => 'Guru Sentra Kreativitas · Wali Kelas', 'photo' => 'guru-ova-sulistyowati.jpg'],
            ['name' => 'Devi Yunianti, S. Pd', 'role' => 'Guru Sentra Kreativitas · Wali Kelas', 'photo' => 'guru-devi-yunianti.jpg'],
        ],
    ],
    [
        'title' => 'Sentra Alam dan Sains',
        'people' => [
            ['name' => 'Khalimah, S. Pd', 'role' => 'Guru Sentra Alam dan Sains · Wali Kelas', 'photo' => 'guru-khalimah.jpg'],
            ['name' => 'Tenang Wahyuniati, Ama.Pd.TK', 'role' => 'Guru Sentra Alam dan Sains · Wali Kelas', 'photo' => 'guru-tenang-wahyuniati.jpg'],
        ],
    ],
    [
        'title' => 'Sentra Imtaq',
        'people' => [
            ['name' => 'Siti Aminah', 'role' => 'Guru Sentra Imtaq · Wali Kelas', 'photo' => 'guru-siti-aminah.jpg'],
            ['name' => 'Imelda Primadona Trianti, S. Pd', 'role' => 'Guru Sentra Imtaq · Wali Kelas', 'photo' => 'guru-imelda-primadona-trianti.jpg'],
        ],
    ],
    [
        'title' => 'Sentra Balok',
        'people' => [
            ['name' => 'Maulidatul Ahiroh', 'role' => 'Guru Sentra Balok · Wali Kelas', 'photo' => 'guru-maulidatul-ahiroh.jpg'],
        ],
    ],
    [
        'title' => 'Tata Usaha',
        'people' => [
            ['name' => 'Indana Zulfa', 'role' => 'Tenaga Tata Usaha', 'photo' => 'staf-indana-zulfa.jpg'],
        ],
    ],
];
@endphp

@section('content')
<section class="staff-page-head reveal">
    <div>
        <p class="eyebrow"><span class="eyebrow-dot"></span>Profil sekolah</p>
        <h1>Kenali pendidik dan staf kami</h1>
        <p>Para pendidik dan tenaga kependidikan TK Pembina Hidayatul Mubtadiin mendampingi anak melalui kegiatan belajar di berbagai sentra.</p>
    </div>
    <div class="staff-school-logo"><img src="{{ asset('assets/school/logo-sekolah.png') }}" alt="Logo TK Pembina Hidayatul Mubtadiin"></div>
</section>

@foreach($staffGroups as $group)
    <section class="staff-section reveal" aria-labelledby="staff-group-{{ $loop->index }}">
        <div class="staff-section-head">
            <h2 id="staff-group-{{ $loop->index }}">{{ $group['title'] }}</h2>
            <span class="staff-count">{{ count($group['people']) }} profil</span>
        </div>
        <div class="staff-grid">
            @foreach($group['people'] as $person)
                <article class="staff-card">
                    <div class="staff-photo"><img src="{{ asset('assets/school/' . $person['photo']) }}" alt="Foto {{ $person['name'] }}" loading="lazy"></div>
                    <div class="staff-card-copy">
                        <h3>{{ $person['name'] }}</h3>
                        <p>{{ $person['role'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endforeach
@endsection

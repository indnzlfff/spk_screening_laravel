@extends('layouts.app')

@section('title', 'Pendaftaran Calon Siswa')

@section('content')
@php($occupationOptions = ['Mengurus rumah tangga', 'Tidak bekerja', 'PNS / ASN', 'TNI', 'Polri', 'Pegawai BUMN / BUMD', 'Karyawan swasta', 'Wiraswasta / pengusaha', 'Petani / pekebun', 'Nelayan', 'Buruh harian', 'Guru / dosen', 'Tenaga kesehatan', 'Pedagang', 'Sopir / pengemudi', 'Pekerja jasa / profesional', 'Pekerja lepas', 'Pekerja migran Indonesia', 'Pensiunan', 'Pelajar / mahasiswa', 'Lainnya'])
@php($answerLabels = [0 => 'Belum teramati (diabaikan)', 1 => 'Belum tampak mampu / bantuan penuh', 2 => 'Belum konsisten / banyak bantuan', 3 => 'Mampu dengan sedikit bantuan', 4 => 'Mandiri dan konsisten'])
<section class="registration-intro">
    <p class="eyebrow"><span class="eyebrow-dot"></span>Calon keluarga TK Pembina Hidayatul Mubtadiin</p>
    <h1>Pendaftaran calon siswa</h1>
    <p>Lengkapi data diri anak dan kuesioner. Setelah dikirim, status pendaftaran dapat dipantau melalui akun orang tua.</p>
</section>
<div class="parent-account-context"><span class="account-context-icon" aria-hidden="true">✓</span><span>Anda masuk sebagai <strong>{{ $parentAccount->nama }}</strong><small>{{ $parentAccount->email ?: $parentAccount->no_hp }}</small></span><a href="{{ route('parent.dashboard') }}">Kembali ke akun</a></div>
<div class="registration-progress" role="group" aria-label="Tahapan pendaftaran" data-wizard-progress>
    <div class="progress-step is-active" data-wizard-indicator="1" aria-current="step"><span>01</span>Data diri</div>
    <div class="progress-step" data-wizard-indicator="2"><span>02</span>Kuesioner</div>
</div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<noscript><style>[data-region-selects]{display:none!important}</style></noscript>
@php($initialWizardStep = collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'jawaban.')) ? 2 : 1)
<form class="registration-form registration-wizard" method="post" action="{{ route('registration.store') }}" enctype="multipart/form-data" data-registration-wizard data-initial-step="{{ $initialWizardStep }}">
    @csrf
    <div class="registration-wizard-page is-current" data-wizard-step="1">
    <section class="panel registration-section reveal" id="registration-student"><h2 tabindex="-1">Data calon siswa</h2><div class="form-grid">
        <div class="field"><label for="nama_anak">Nama lengkap anak</label><input id="nama_anak" name="nama_anak" value="{{ old('nama_anak') }}" maxlength="150" data-capitalize="words" autocapitalize="words" required></div>
        <div class="field"><label for="nik">NIK anak</label><input id="nik" name="nik" value="{{ old('nik') }}" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required></div>
        <div class="field"><label for="tempat_lahir">Tempat lahir</label><input id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir') }}" data-capitalize="words" autocapitalize="words" required></div>
        <div class="field"><label for="tanggal_lahir">Tanggal lahir</label><input id="tanggal_lahir" name="tanggal_lahir" type="date" value="{{ old('tanggal_lahir') }}" required></div>
        <div class="field"><label for="jenis_kelamin">Jenis kelamin</label><select id="jenis_kelamin" name="jenis_kelamin" required><option value="">Pilih</option><option value="L" @selected(old('jenis_kelamin')==='L')>Laki-laki</option><option value="P" @selected(old('jenis_kelamin')==='P')>Perempuan</option></select></div>
        <div class="field full region-fields" data-region-form
             data-api-base="https://www.emsifa.com/api-wilayah-indonesia/v2"
             data-old-province="{{ old('provinsi') }}"
             data-old-regency="{{ old('kabupaten_kota') }}"
             data-old-district="{{ old('kecamatan') }}"
             data-old-village="{{ old('kelurahan_desa') }}"
             data-old-postal="{{ old('kode_pos') }}">
            <div class="region-grid" data-region-selects>
                <div class="field"><label for="provinsi">Provinsi</label><select id="provinsi" name="provinsi" data-region-level="province" required disabled><option value="">Memuat daftar provinsi…</option></select></div>
                <div class="field"><label for="kabupaten_kota">Kabupaten/kota</label><select id="kabupaten_kota" name="kabupaten_kota" data-region-level="regency" required disabled><option value="">Pilih provinsi terlebih dahulu</option></select></div>
                <div class="field"><label for="kecamatan">Kecamatan</label><select id="kecamatan" name="kecamatan" data-region-level="district" required disabled><option value="">Pilih kabupaten/kota terlebih dahulu</option></select></div>
                <div class="field"><label for="kelurahan_desa">Kelurahan/desa</label><select id="kelurahan_desa" name="kelurahan_desa" data-region-level="village" required disabled><option value="">Pilih kecamatan terlebih dahulu</option></select></div>
                <div class="field"><label for="kode_pos">Kode pos</label><select id="kode_pos" name="kode_pos" data-region-level="postal" required disabled><option value="">Terisi setelah memilih kelurahan/desa</option></select></div>
            </div>
            <div class="region-grid region-fallback" data-region-fallback hidden>
                <div class="field"><label for="provinsi_manual">Provinsi</label><input id="provinsi_manual" data-fallback-name="provinsi" data-capitalize="words" autocapitalize="words" value="{{ old('provinsi') }}" disabled></div>
                <div class="field"><label for="kabupaten_kota_manual">Kabupaten/kota</label><input id="kabupaten_kota_manual" data-fallback-name="kabupaten_kota" data-capitalize="words" autocapitalize="words" value="{{ old('kabupaten_kota') }}" disabled></div>
                <div class="field"><label for="kecamatan_manual">Kecamatan</label><input id="kecamatan_manual" data-fallback-name="kecamatan" data-capitalize="words" autocapitalize="words" value="{{ old('kecamatan') }}" disabled></div>
                <div class="field"><label for="kelurahan_desa_manual">Kelurahan/desa</label><input id="kelurahan_desa_manual" data-fallback-name="kelurahan_desa" data-capitalize="words" autocapitalize="words" value="{{ old('kelurahan_desa') }}" disabled></div>
                <div class="field"><label for="kode_pos_manual">Kode pos</label><input id="kode_pos_manual" data-fallback-name="kode_pos" value="{{ old('kode_pos') }}" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" disabled></div>
            </div>
            <noscript>
                <div class="region-grid">
                    <div class="field"><label for="provinsi_nojs">Provinsi</label><input id="provinsi_nojs" name="provinsi" value="{{ old('provinsi') }}" data-capitalize="words" autocapitalize="words" required></div>
                    <div class="field"><label for="kabupaten_kota_nojs">Kabupaten/kota</label><input id="kabupaten_kota_nojs" name="kabupaten_kota" value="{{ old('kabupaten_kota') }}" data-capitalize="words" autocapitalize="words" required></div>
                    <div class="field"><label for="kecamatan_nojs">Kecamatan</label><input id="kecamatan_nojs" name="kecamatan" value="{{ old('kecamatan') }}" data-capitalize="words" autocapitalize="words" required></div>
                    <div class="field"><label for="kelurahan_desa_nojs">Kelurahan/desa</label><input id="kelurahan_desa_nojs" name="kelurahan_desa" value="{{ old('kelurahan_desa') }}" data-capitalize="words" autocapitalize="words" required></div>
                    <div class="field"><label for="kode_pos_nojs">Kode pos</label><input id="kode_pos_nojs" name="kode_pos" value="{{ old('kode_pos') }}" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" required></div>
                </div>
            </noscript>
        </div>
        <div class="field full"><label for="alamat_anak">Alamat lengkap anak</label><textarea id="alamat_anak" name="alamat_anak" data-capitalize="sentence" autocapitalize="sentences" required>{{ old('alamat_anak') }}</textarea></div>
    </div></section>
    <section class="panel registration-section reveal" id="registration-parent"><h2>Data orang tua</h2><div class="form-grid">
        <div class="field"><label for="nama_ayah">Nama ayah</label><input id="nama_ayah" name="nama_ayah" value="{{ old('nama_ayah') }}" data-capitalize="words" autocapitalize="words"></div>
        <div class="field"><label for="pekerjaan_ayah">Pekerjaan ayah</label><select id="pekerjaan_ayah" name="pekerjaan_ayah"><option value="">Pilih pekerjaan</option>@if(old('pekerjaan_ayah') && !in_array(old('pekerjaan_ayah'), $occupationOptions, true))<option value="{{ old('pekerjaan_ayah') }}" selected>{{ old('pekerjaan_ayah') }} · pilihan sebelumnya</option>@endif @foreach($occupationOptions as $occupation)<option value="{{ $occupation }}" @selected(old('pekerjaan_ayah') === $occupation)>{{ $occupation }}</option>@endforeach</select></div>
        <div class="field"><label for="nama_ibu">Nama ibu</label><input id="nama_ibu" name="nama_ibu" value="{{ old('nama_ibu') }}" data-capitalize="words" autocapitalize="words"></div>
        <div class="field"><label for="pekerjaan_ibu">Pekerjaan ibu</label><select id="pekerjaan_ibu" name="pekerjaan_ibu"><option value="">Pilih pekerjaan</option>@if(old('pekerjaan_ibu') && !in_array(old('pekerjaan_ibu'), $occupationOptions, true))<option value="{{ old('pekerjaan_ibu') }}" selected>{{ old('pekerjaan_ibu') }} · pilihan sebelumnya</option>@endif @foreach($occupationOptions as $occupation)<option value="{{ $occupation }}" @selected(old('pekerjaan_ibu') === $occupation)>{{ $occupation }}</option>@endforeach</select></div>
        <div class="field"><label for="nama_wali">Nama wali (jika ada)</label><input id="nama_wali" name="nama_wali" value="{{ old('nama_wali') }}" data-capitalize="words" autocapitalize="words"></div>
    </div></section>
    <section class="panel registration-section reveal" id="registration-documents"><h2>Dokumen pendaftaran</h2><div class="form-grid">
        <div class="field"><label for="berkas_akta">Akta kelahiran</label><input id="berkas_akta" type="file" name="berkas_akta" accept=".pdf,.jpg,.jpeg,.png" required></div>
        <div class="field"><label for="berkas_kk">Kartu keluarga</label><input id="berkas_kk" type="file" name="berkas_kk" accept=".pdf,.jpg,.jpeg,.png" required></div>
    </div></section>
    <div class="wizard-actions wizard-actions-next"><button class="btn btn-primary" type="button" data-wizard-next>Lanjutkan ke kuesioner <span aria-hidden="true">→</span></button></div>
    </div>
    <div class="registration-wizard-page" data-wizard-step="2">
    <section class="panel registration-section reveal" id="registration-screening"><h2 tabindex="-1">Kuesioner screening kesiapan</h2>
        @foreach($questions->groupBy('aspek') as $aspect => $items)
            @php($aspectCode = explode('-', $items->first()->kode)[1] ?? '')
            <h3 class="form-question-heading">{{ $aspectCode }} — {{ strtoupper($aspect) }}</h3>
            <div class="card-list">
                @foreach($items as $question)
                    @php($questionNumber = $loop->iteration)
                    <article class="question">
                        <p class="question-prompt" id="question-{{ $question->id }}"><span class="question-number">{{ $questionNumber }}.</span>{{ $question->pertanyaan }}</p>
                        <div class="question-options" role="radiogroup" aria-labelledby="question-{{ $question->id }}">
                            @for ($value = (int) $question->nilai_min; $value <= (int) $question->nilai_max; $value++)
                                <label class="answer-option" for="answer-{{ $question->id }}-{{ $value }}">
                                    <input type="radio" id="answer-{{ $question->id }}-{{ $value }}" name="jawaban[{{ $question->id }}]" value="{{ $value }}" required @checked((string) old('jawaban.'.$question->id) === (string) $value)>
                                    <span class="answer-value">{{ $value }}</span>
                                    <span class="answer-label">{{ $answerLabels[$value] ?? 'Nilai pengamatan' }}</span>
                                </label>
                                @endfor
                        </div>
                    </article>
                @endforeach
            </div>
        @endforeach
    </section>
    <div class="wizard-actions wizard-actions-submit">
        <button class="btn" type="button" data-wizard-back><span aria-hidden="true">←</span> Kembali ke data diri</button>
        <button class="btn btn-primary" type="submit" {{ !$registrationOpen || $questions->count() !== 20 ? 'disabled' : '' }}>Daftar sekarang <span aria-hidden="true">→</span></button>
    </div>
    </div>
</form>
@endsection

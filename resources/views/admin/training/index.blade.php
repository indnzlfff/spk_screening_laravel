@extends('layouts.app')

@section('title', 'Data Latih Naïve Bayes')

@section('content')
<div class="page-head"><div><h1>Data latih Naïve Bayes</h1><p>Catatan anonim berlabel yang telah ditetapkan atau divalidasi oleh sekolah.</p></div><a class="btn" href="{{ route('admin.evaluation') }}">Lihat evaluasi</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="notice">Gunakan catatan nyata sesuai kriteria yang disepakati sekolah. Jangan memasukkan nama atau NIK anak. Hasil klasifikasi hanya menjadi bahan pertimbangan awal pendampingan.</div>
<div class="metrics" style="grid-template-columns:repeat(3,minmax(0,1fr))"><div class="metric"><span>Catatan siap dipakai</span><strong>{{ $usableCount }}</strong></div><div class="metric"><span>Sesuai harapan</span><strong>{{ $classCounts->get('Sesuai harapan', 0) }}</strong></div><div class="metric"><span>Perlu perhatian lebih lanjut</span><strong>{{ $classCounts->get('Memerlukan perhatian lebih lanjut', 0) }}</strong></div></div>
@if(session('school_user_role')==='admin')
<section class="panel"><h2>Tambah catatan anonim</h2>
    <form method="post" action="{{ route('admin.training.store') }}" class="form-grid">@csrf
        <div class="field"><label for="kode_data">Kode anonim (opsional)</label><input id="kode_data" name="kode_data" maxlength="40" value="{{ old('kode_data') }}" placeholder="Dibuat otomatis jika kosong"></div>
        <div class="field"><label for="usia_kategori">Kategori usia saat pengamatan</label><select id="usia_kategori" name="usia_kategori" required><option value="">Pilih kategori</option><option value="A" @selected(old('usia_kategori')==='A')>A — di bawah 4 tahun 6 bulan</option><option value="B" @selected(old('usia_kategori')==='B')>B — 4 tahun 6 bulan sampai 5 tahun</option><option value="C" @selected(old('usia_kategori')==='C')>C — lebih dari 5 tahun</option></select></div>
        @foreach($aspects as $field => $label)<div class="field"><label for="{{ $field }}">{{ $label }}</label><select id="{{ $field }}" name="{{ $field }}" required><option value="">Pilih nilai 1–4</option>@for($value=1;$value<=4;$value++)<option value="{{ $value }}" @selected((string)old($field)===(string)$value)>{{ $value }}</option>@endfor</select></div>@endforeach
        <div class="field"><label for="kelas">Label dari sekolah</label><select id="kelas" name="kelas" required><option value="">Pilih label</option>@foreach($classes as $class)<option value="{{ $class }}" @selected(old('kelas')===$class)>{{ $class }}</option>@endforeach</select></div>
        <div class="field full"><label for="sumber">Catatan sumber (opsional)</label><input id="sumber" name="sumber" maxlength="160" value="{{ old('sumber') }}" placeholder="Keterangan sumber tanpa identitas anak"></div>
        <div class="field full"><button class="btn btn-primary" type="submit">Simpan data latih</button></div>
    </form>
</section>
@else
<div class="alert alert-info">Data latih dapat dilihat oleh petugas. Penambahan dan penghapusan catatan hanya dapat dilakukan oleh admin sekolah.</div>
@endif
<section class="panel"><h2>Catatan tersimpan</h2><div class="table-wrap"><table class="table"><thead><tr><th>Kode</th><th>Usia</th>@foreach($aspects as $label)<th>{{ $label }}</th>@endforeach<th>Label</th><th>Sumber</th>@if(session('school_user_role')==='admin')<th>Aksi</th>@endif</tr></thead><tbody>
@forelse($rows as $row)<tr><td>{{ $row->kode_data }}</td><td>{{ $row->usia_kategori }}</td>@foreach(array_keys($aspects) as $field)<td>{{ $row->{$field} }}</td>@endforeach<td>{{ $row->kelas }}</td><td>{{ $row->sumber ?: '—' }}</td>@if(session('school_user_role')==='admin')<td><form method="post" action="{{ route('admin.training.destroy', $row->id) }}" onsubmit="return confirm('Hapus catatan data latih ini?')">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Hapus</button></form></td>@endif</tr>@empty<tr><td colspan="9" class="empty">Belum ada catatan data latih.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $rows->links() }}</div></section>
@endsection

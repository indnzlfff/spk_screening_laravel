@extends('layouts.app')

@section('title', 'Data Pendaftar')

@section('content')
<div class="page-head"><div><h1>Data pendaftar</h1><p>Kelola status penerimaan secara terpisah dari hasil screening.</p></div><a class="btn" href="{{ route('admin.screening.index') }}">Antrean screening</a></div>
<section class="panel">
    <form method="get" class="actions" style="margin-bottom:14px"><input class="search-input" style="max-width:500px" name="q" value="{{ $search }}" placeholder="Cari nama atau nomor pendaftaran"><button class="btn btn-primary">Cari</button>@if($search)<a class="btn" href="{{ route('admin.applicants.index') }}">Reset</a>@endif</form>
    <div class="table-wrap"><table class="table"><thead><tr><th>No. pendaftaran</th><th>Nama anak</th><th>Tanggal daftar</th><th>Jawaban</th><th>Status pendaftaran</th><th>Hasil screening</th><th></th></tr></thead><tbody>
    @forelse($rows as $row)<tr><td>{{ $row->nomor_pendaftaran }}</td><td><strong>{{ $row->nama_anak }}</strong></td><td>{{ \Illuminate\Support\Carbon::parse($row->created_at)->format('d-m-Y') }}</td><td>{{ $row->jumlah_jawaban }} / 20</td><td>{{ ucfirst($row->status_pendaftaran) }}</td><td>{{ $row->kelas_prediksi ?? 'Belum dihitung' }}</td><td><a class="btn" href="{{ route('admin.applicants.show', $row->id) }}">Detail</a></td></tr>@empty<tr><td colspan="7" class="empty">Belum ada pendaftar.</td></tr>@endforelse
    </tbody></table></div><div class="pagination">{{ $rows->links() }}</div>
</section>
@endsection

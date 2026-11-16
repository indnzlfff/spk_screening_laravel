@extends('layouts.app')

@section('title', 'Riwayat Screening')

@section('content')
<div class="page-head"><div><h1>Riwayat screening</h1><p>Telusuri hasil yang sudah dihitung dan tersimpan.</p></div><a class="btn btn-primary" href="{{ route('admin.screening.index') }}">Buka antrean screening</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<section class="panel">
    <form method="get" class="actions" style="margin-bottom:14px"><input class="search-input" style="max-width:500px" name="q" value="{{ $search }}" placeholder="Cari nama, nomor pendaftaran, atau nomor hasil"><button class="btn btn-primary">Cari</button>@if($search)<a class="btn" href="{{ route('admin.screening.history') }}">Reset</a>@endif</form>
    <div class="table-wrap"><table class="table"><thead><tr><th>No. hasil</th><th>No. pendaftaran</th><th>Nama anak</th><th>Tanggal</th><th>Hasil</th><th></th></tr></thead><tbody>
    @forelse($rows as $row)<tr><td>{{ $row->nomor_screening }}</td><td>{{ $row->nomor_pendaftaran }}</td><td>{{ $row->nama_anak }}</td><td>{{ \Illuminate\Support\Carbon::parse($row->created_at)->format('d-m-Y H:i') }}</td><td>{{ $row->kelas_prediksi }}</td><td><span class="badge">{{ $row->status_validasi === 'dikonfirmasi' ? 'Terkonfirmasi otomatis' : 'Menunggu validasi' }}</span> <a class="btn" href="{{ route('admin.screening.result', $row->id) }}">Lihat hasil</a></td></tr>@empty<tr><td colspan="6" class="empty">Belum ada hasil screening.</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination">{{ $rows->links() }}</div>
</section>
@endsection

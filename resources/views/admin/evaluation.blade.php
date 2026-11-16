@extends('layouts.app')

@section('title', 'Evaluasi Model')

@section('content')
<div class="page-head"><div><h1>Evaluasi model</h1><p>Pengukuran dilakukan dengan pemisahan data berlabel yang konsisten dan berstrata.</p></div><a class="btn" href="{{ route('admin.training.index') }}">Kelola data latih</a></div>
<div class="notice">Angka evaluasi mencerminkan data latih berlabel yang tersedia. Interpretasikan sesuai jumlah serta mutu data; hasil tidak otomatis mewakili seluruh calon siswa.</div>
@if($message)<div class="alert alert-warning">{{ $message }}</div>@endif
@if($result)
<section class="panel"><h2>Ringkasan pembagian data</h2><p>Data latih evaluasi: <strong>{{ $result['train_count'] }}</strong> · Data uji: <strong>{{ $result['test_count'] }}</strong></p></section>
<section class="panel"><h2>Confusion matrix</h2><div class="table-wrap"><table class="table"><thead><tr><th>Aktual ↓ / Prediksi →</th>@foreach(\App\Services\NaiveBayesScreeningService::CLASSES as $class)<th>{{ $class }}</th>@endforeach</tr></thead><tbody>@foreach(\App\Services\NaiveBayesScreeningService::CLASSES as $actual)<tr><th>{{ $actual }}</th>@foreach(\App\Services\NaiveBayesScreeningService::CLASSES as $predicted)<td>{{ $result['matrix'][$actual][$predicted] }}</td>@endforeach</tr>@endforeach</tbody></table></div></section>
<div class="metrics"><div class="metric"><span>Akurasi</span><strong>{{ number_format($result['accuracy']*100, 2, ',', '.') }}%</strong></div><div class="metric"><span>Precision</span><strong>{{ number_format($result['precision']*100, 2, ',', '.') }}%</strong></div><div class="metric"><span>Recall</span><strong>{{ number_format($result['recall']*100, 2, ',', '.') }}%</strong></div><div class="metric"><span>F1-score</span><strong>{{ number_format($result['f1']*100, 2, ',', '.') }}%</strong></div></div>
@endif
@endsection

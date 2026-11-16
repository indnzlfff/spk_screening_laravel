<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $activeQuestions = (int) DB::table('indikator as i')
            ->join('kriteria as k', 'k.id', '=', 'i.kriteria_id')
            ->where('i.aktif', 1)
            ->where('k.aktif', 1)
            ->whereIn('k.kode', ['C1', 'C2', 'C3', 'C4', 'C5'])
            ->count();

        $latestScreening = DB::table('screening')
            ->selectRaw('MAX(id) AS id, pendaftar_id')
            ->where('metode', 'Naive Bayes')
            ->groupBy('pendaftar_id');

        $applicants = DB::table('pendaftar as p')
            ->leftJoinSub($latestScreening, 'latest', 'latest.pendaftar_id', '=', 'p.id')
            ->leftJoin('screening as s', 's.id', '=', 'latest.id')
            ->select('p.id', 'p.nomor_pendaftaran', 'p.nama_anak', 'p.status_pendaftaran', 'p.created_at', 's.id as screening_id', 's.kelas_prediksi')
            ->orderByDesc('p.created_at')
            ->limit(8)
            ->get();

        $totalApplicants = (int) DB::table('pendaftar')->count();
        $completeQuestionnaires = $activeQuestions === 0 ? 0 : (int) DB::table('pendaftar as p')
            ->whereRaw('(SELECT COUNT(*) FROM jawaban j JOIN indikator i ON i.id=j.indikator_id JOIN kriteria k ON k.id=i.kriteria_id WHERE j.pendaftar_id=p.id AND i.aktif=1 AND k.aktif=1 AND k.kode IN ("C1","C2","C3","C4","C5")) = ?', [$activeQuestions])
            ->count();

        $screeningCount = (int) DB::table('screening')->where('metode', 'Naive Bayes')->count();
        $classCounts = DB::table('screening')
            ->where('metode', 'Naive Bayes')
            ->selectRaw('kelas_prediksi, COUNT(*) AS total')
            ->groupBy('kelas_prediksi')
            ->pluck('total', 'kelas_prediksi');

        return view('admin.dashboard', [
            'activeQuestions' => $activeQuestions,
            'totalApplicants' => $totalApplicants,
            'completeQuestionnaires' => $completeQuestionnaires,
            'screeningCount' => $screeningCount,
            'classCounts' => $classCounts,
            'applicants' => $applicants,
        ]);
    }
}

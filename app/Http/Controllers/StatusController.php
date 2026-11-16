<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatusController extends Controller
{
    public function show(): View
    {
        return view('status.lookup', ['applicant' => null, 'screens' => collect()]);
    }

    public function lookup(Request $request): View
    {
        $validated = $request->validate([
            'nomor_pendaftaran' => ['required', 'string', 'max:30'],
            'no_hp' => ['required', 'string', 'max:20'],
        ]);

        $applicant = DB::table('pendaftar')
            ->where('nomor_pendaftaran', trim($validated['nomor_pendaftaran']))
            ->where('no_hp', trim($validated['no_hp']))
            ->first();

        if (! $applicant) {
            return redirect()->route('status.lookup')
                ->withErrors(['lookup' => 'Data tidak ditemukan. Periksa nomor pendaftaran dan nomor HP.'])
                ->withInput();
        }

        $screens = DB::table('screening')
            ->join('screening_validations as v', 'v.screening_id', '=', 'screening.id')
            ->where('pendaftar_id', $applicant->id)
            ->where('metode', 'Naive Bayes')
            ->where('v.status', 'dikonfirmasi')
            ->select('screening.nomor_screening', 'screening.kelas_prediksi', 'screening.created_at', 'v.catatan_admin')
            ->orderByDesc('screening.created_at')->get();

        return view('status.lookup', compact('applicant', 'screens'));
    }
}

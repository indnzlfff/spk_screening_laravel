<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class RegistrationController extends Controller
{
    public function create(): View
    {
        $parentAccount = DB::table('akun_orang_tua')->where('id', (int) session('parent_account_id'))->where('aktif', 1)->first();
        abort_if(! $parentAccount, 403);

        $questions = DB::table('indikator as i')
            ->join('kriteria as k', 'k.id', '=', 'i.kriteria_id')
            ->where('i.aktif', 1)->where('k.aktif', 1)
            ->orderBy('k.kode')->orderBy('i.kode')
            ->select('i.id', 'i.kode', 'i.pertanyaan', 'i.nilai_min', 'i.nilai_max', 'i.petunjuk_skala', 'k.nama as aspek')
            ->get();

        $open = DB::table('settings')->where('nama_setting', 'spmb_pendaftaran_aktif')->value('nilai') ?? '1';

        return view('registration.create', [
            'questions' => $questions,
            'registrationOpen' => $open === '1',
            'parentAccount' => $parentAccount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $parentAccount = DB::table('akun_orang_tua')->where('id', (int) session('parent_account_id'))->where('aktif', 1)->first();
        abort_if(! $parentAccount, 403);

        $questions = DB::table('indikator as i')
            ->join('kriteria as k', 'k.id', '=', 'i.kriteria_id')
            ->where('i.aktif', 1)->where('k.aktif', 1)
            ->orderBy('k.kode')->orderBy('i.kode')
            ->select('i.id', 'i.kode', 'i.nilai_min', 'i.nilai_max')
            ->get();

        if ($questions->count() !== 20) {
            return back()->withInput()->withErrors(['form' => 'Pendaftaran belum dapat dikirim karena kuesioner aktif belum berisi tepat 20 pertanyaan. Hubungi pihak sekolah.']);
        }

        $setting = DB::table('settings')->where('nama_setting', 'spmb_pendaftaran_aktif')->value('nilai') ?? '1';
        if ($setting !== '1') {
            return back()->withInput()->withErrors(['form' => 'Pendaftaran sedang ditutup oleh pihak sekolah.']);
        }

        $answerRules = [];
        foreach ($questions as $question) {
            $answerRules['jawaban.'.$question->id] = ['required', 'integer', 'between:'.(int) $question->nilai_min.','.(int) $question->nilai_max];
        }

        $validated = $request->validate(array_merge([
            'nama_anak' => ['required', 'string', 'max:150'],
            'nik' => ['required', 'digits:16'],
            'asal_paud' => ['nullable', 'string', 'max:150'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kabupaten_kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kelurahan_desa' => ['required', 'string', 'max:100'],
            'kode_pos' => ['required', 'digits:5'],
            'alamat_anak' => ['required', 'string', 'max:2000'],
            'nama_ayah' => ['nullable', 'string', 'max:150'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:100'],
            'nama_ibu' => ['nullable', 'string', 'max:150'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:100'],
            'nama_wali' => ['nullable', 'string', 'max:150'],
            'alamat_wali' => ['nullable', 'string', 'max:2000'],
            'berkas_akta' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'berkas_kk' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ], $answerRules));

        if (trim((string) ($validated['nama_ayah'] ?? '')) === '' && trim((string) ($validated['nama_ibu'] ?? '')) === '' && trim((string) ($validated['nama_wali'] ?? '')) === '') {
            return back()->withInput()->withErrors(['nama_wali' => 'Isi minimal satu nama orang tua atau wali.']);
        }

        $storedDocuments = [];

        try {
            $registrationNumber = DB::transaction(function () use ($validated, $questions, $parentAccount, $request, &$storedDocuments): string {
                DB::table('settings')->insertOrIgnore([
                    'nama_setting' => 'spmb_kuota',
                    'nilai' => '75',
                    'keterangan' => 'Batas pendaftaran murid; maksimum 75.',
                ]);

                $quota = (int) (DB::table('settings')->where('nama_setting', 'spmb_kuota')->lockForUpdate()->value('nilai') ?? 75);
                $quota = min(75, max(1, $quota));
                $active = DB::table('settings')->where('nama_setting', 'spmb_pendaftaran_aktif')->value('nilai') ?? '1';
                $registered = DB::table('pendaftar')->count();

                if ($active !== '1' || $registered >= $quota) {
                    throw new RuntimeException('Pendaftaran sedang ditutup atau kuota sekolah telah terpenuhi.');
                }

                foreach (['berkas_akta' => 'akta', 'berkas_kk' => 'kk'] as $field => $type) {
                    $file = $request->file($field);
                    $filename = bin2hex(random_bytes(24)).'.'.$file->extension();
                    $path = $file->storeAs('pendaftar', $filename, 'local');
                    if ($path === false) {
                        throw new RuntimeException('Berkas '.$type.' belum berhasil disimpan. Coba kembali.');
                    }
                    $storedDocuments[$type] = $path;
                }

                $number = null;
                for ($attempt = 0; $attempt < 10 && $number === null; $attempt++) {
                    $candidate = 'TKPHM'.random_int(100000, 999999);
                    if (! DB::table('pendaftar')->where('nomor_pendaftaran', $candidate)->exists()) {
                        $number = $candidate;
                    }
                }
                if ($number === null) {
                    throw new RuntimeException('Nomor pendaftaran belum dapat dibuat. Silakan kirim ulang formulir.');
                }

                $applicantId = DB::table('pendaftar')->insertGetId([
                    'nomor_pendaftaran' => $number,
                    'nama_anak' => trim($validated['nama_anak']),
                    'nik' => $validated['nik'],
                    'dokumen_akta' => $storedDocuments['akta'],
                    'dokumen_kk' => $storedDocuments['kk'],
                    'asal_paud' => ($validated['asal_paud'] ?? '') ?: null,
                    'tempat_lahir' => trim($validated['tempat_lahir']),
                    'tanggal_lahir' => $validated['tanggal_lahir'],
                    'jenis_kelamin' => $validated['jenis_kelamin'],
                    'provinsi' => trim($validated['provinsi']),
                    'kabupaten_kota' => trim($validated['kabupaten_kota']),
                    'kecamatan' => trim($validated['kecamatan']),
                    'kelurahan_desa' => trim($validated['kelurahan_desa']),
                    'kode_pos' => $validated['kode_pos'],
                    'alamat_anak' => trim($validated['alamat_anak']),
                    'nama_ayah' => ($validated['nama_ayah'] ?? '') ?: null,
                    'pekerjaan_ayah' => ($validated['pekerjaan_ayah'] ?? '') ?: null,
                    'nama_ibu' => ($validated['nama_ibu'] ?? '') ?: null,
                    'pekerjaan_ibu' => ($validated['pekerjaan_ibu'] ?? '') ?: null,
                    'nama_wali' => ($validated['nama_wali'] ?? '') ?: null,
                    'no_hp' => $parentAccount->no_hp,
                    'akun_orang_tua_id' => (int) $parentAccount->id,
                    'alamat_wali' => ($validated['alamat_wali'] ?? '') ?: null,
                ]);

                $answers = [];
                foreach ($questions as $question) {
                    $answers[] = [
                        'pendaftar_id' => $applicantId,
                        'indikator_id' => $question->id,
                        'nilai' => $validated['jawaban'][$question->id],
                    ];
                }
                DB::table('jawaban')->insert($answers);

                return $number;
            });
        } catch (RuntimeException $exception) {
            foreach (array_filter($storedDocuments, 'is_string') as $path) {
                Storage::disk('local')->delete($path);
            }
            return back()->withInput()->withErrors(['form' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            foreach (array_filter($storedDocuments, 'is_string') as $path) {
                Storage::disk('local')->delete($path);
            }
            report($exception);
            return back()->withInput()->withErrors(['form' => 'Pendaftaran belum tersimpan. Periksa kembali data dan koneksi database.']);
        }

        return redirect()->route('parent.dashboard')
            ->with('status', 'Pendaftaran berhasil dikirim dengan nomor '.$registrationNumber.'. Status dan hasil screening dapat dipantau di akun orang tua.');
    }
}

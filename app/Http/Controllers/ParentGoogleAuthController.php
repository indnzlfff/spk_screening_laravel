<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ParentGoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = (string) config('services.google.client_id');
        $clientSecret = (string) config('services.google.client_secret');
        if ($clientId === '' || $clientSecret === '') {
            return redirect()->route('account.access')->withErrors([
                'google' => 'Login Google belum aktif. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada file .env terlebih dahulu.',
            ]);
        }

        $state = Str::random(64);
        $request->session()->put('parent_google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('parent_google_oauth_state', '');
        $receivedState = (string) $request->query('state', '');
        if ($expectedState === '' || $receivedState === '' || ! hash_equals($expectedState, $receivedState)) {
            return redirect()->route('account.access')->withErrors(['google' => 'Sesi login Google kedaluwarsa. Silakan coba kembali.']);
        }

        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('account.access')->withErrors(['google' => 'Login Google dibatalkan atau belum berhasil.']);
        }

        try {
            $tokenResponse = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'code' => $request->query('code'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri(),
            ]);

            if (! $tokenResponse->successful() || ! $tokenResponse->json('access_token')) {
                return redirect()->route('account.access')->withErrors(['google' => 'Google tidak dapat memverifikasi login ini. Periksa konfigurasi OAuth lalu coba kembali.']);
            }

            $profileResponse = Http::withToken((string) $tokenResponse->json('access_token'))
                ->timeout(15)->get('https://openidconnect.googleapis.com/v1/userinfo');
            if (! $profileResponse->successful()) {
                return redirect()->route('account.access')->withErrors(['google' => 'Profil Google tidak dapat dibaca. Silakan coba kembali.']);
            }

            $profile = $profileResponse->json();
        } catch (Throwable) {
            return redirect()->route('account.access')->withErrors(['google' => 'Layanan Google tidak dapat dihubungi. Periksa koneksi internet server lalu coba kembali.']);
        }

        $googleId = trim((string) ($profile['sub'] ?? ''));
        $email = mb_strtolower(trim((string) ($profile['email'] ?? '')));
        $name = trim((string) ($profile['name'] ?? ''));
        $emailVerified = filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($googleId === '' || $email === '' || ! $emailVerified) {
            return redirect()->route('account.access')->withErrors(['google' => 'Gunakan akun Google dengan alamat email terverifikasi.']);
        }

        $account = DB::table('akun_orang_tua')->where('google_id', $googleId)->first();
        if ($account) {
            if ((int) $account->aktif !== 1) {
                return redirect()->route('account.access')->withErrors(['google' => 'Akun orang tua ini sedang dinonaktifkan. Hubungi sekolah.']);
            }

            $emailOwner = DB::table('akun_orang_tua')->where('email', $email)->where('id', '<>', $account->id)->exists();
            if ($emailOwner) {
                return redirect()->route('account.access')->withErrors(['google' => 'Email Google ini sudah digunakan akun lain. Hubungi sekolah untuk menggabungkan akun.']);
            }
            if ($account->email !== $email) {
                DB::table('akun_orang_tua')->where('id', $account->id)->update([
                    'email' => $email,
                    'updated_at' => now(),
                ]);
                $account->email = $email;
            }

            $this->authenticateParent($request, $account);

            return redirect()->intended(route('parent.dashboard'));
        }

        if (DB::table('akun_orang_tua')->where('email', $email)->exists()) {
            return redirect()->route('account.access')->withErrors(['google' => 'Email ini sudah memiliki akun orang tua. Masuk dengan email atau nomor HP dan kata sandi akun tersebut.']);
        }

        $request->session()->put('google_parent_profile', [
            'google_id' => $googleId,
            'email' => $email,
            'name' => $name !== '' ? $name : str($email)->before('@')->toString(),
        ]);

        return redirect()->route('parent.google.complete');
    }

    public function completeForm(Request $request): View|RedirectResponse
    {
        $profile = $request->session()->get('google_parent_profile');
        if (! is_array($profile) || empty($profile['google_id']) || empty($profile['email'])) {
            return redirect()->route('account.access')->withErrors(['google' => 'Mulai proses daftar atau masuk menggunakan Google terlebih dahulu.']);
        }

        return view('parent.google-complete', ['profile' => $profile]);
    }

    public function complete(Request $request): RedirectResponse
    {
        $profile = $request->session()->get('google_parent_profile');
        if (! is_array($profile) || empty($profile['google_id']) || empty($profile['email'])) {
            return redirect()->route('account.access')->withErrors(['google' => 'Sesi pendaftaran Google kedaluwarsa. Silakan mulai kembali.']);
        }

        $validated = $request->validate([
            'no_hp' => ['required', 'regex:/^[0-9+().\s-]{8,20}$/'],
        ]);
        $phone = $this->normalizePhone($validated['no_hp']);
        if (strlen($phone) < 8 || strlen($phone) > 15) {
            return back()->withInput()->withErrors(['no_hp' => 'Nomor HP harus terdiri dari 8 sampai 15 angka.']);
        }

        if (DB::table('akun_orang_tua')->where('no_hp', $phone)->exists()) {
            return back()->withInput()->withErrors(['no_hp' => 'Nomor HP ini sudah terhubung dengan akun lain. Masuk menggunakan akun tersebut.']);
        }
        if (DB::table('akun_orang_tua')->where('email', $profile['email'])->orWhere('google_id', $profile['google_id'])->exists()) {
            return redirect()->route('account.access')->withErrors(['google' => 'Akun dengan email Google ini sudah terdaftar. Silakan masuk.']);
        }

        $accountId = DB::table('akun_orang_tua')->insertGetId([
            'nama' => mb_substr(trim((string) $profile['name']), 0, 150),
            'no_hp' => $phone,
            'email' => $profile['email'],
            'google_id' => $profile['google_id'],
            'password_hash' => Hash::make(Str::random(72)),
            'aktif' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $account = DB::table('akun_orang_tua')->where('id', $accountId)->first();
        $this->authenticateParent($request, $account);

        return redirect()->intended(route('parent.dashboard'))
            ->with('status', 'Akun orang tua berhasil dibuat dengan Google. Silakan lanjutkan pengisian formulir SPMB.');
    }

    private function authenticateParent(Request $request, object $account): void
    {
        $request->session()->regenerate();
        $request->session()->forget('google_parent_profile');
        $request->session()->put([
            'parent_account_id' => (int) $account->id,
            'parent_account_name' => $account->nama,
        ]);
    }

    private function normalizePhone(string $value): string
    {
        $phone = preg_replace('/\D+/', '', $value) ?? '';
        if (str_starts_with($phone, '0')) {
            return '62'.substr($phone, 1);
        }
        if (str_starts_with($phone, '8')) {
            return '62'.$phone;
        }

        return $phone;
    }

    private function redirectUri(): string
    {
        return (string) config('services.google.redirect') ?: route('parent.google.callback');
    }
}

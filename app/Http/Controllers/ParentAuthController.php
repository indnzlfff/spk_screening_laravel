<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ParentAuthController extends Controller
{
    public function access(): View|RedirectResponse
    {
        if (session()->has('parent_account_id')) {
            return redirect()->route('parent.dashboard');
        }

        return view('parent.access', ['isSpmb' => false]);
    }

    public function spmb(): View|RedirectResponse
    {
        if (session()->has('parent_account_id')) {
            return redirect()->route('registration.create');
        }

        session()->put('url.intended', route('registration.create'));

        return view('parent.access', ['isSpmb' => true]);
    }

    public function show(): View|RedirectResponse
    {
        if (session()->has('parent_account_id')) {
            return redirect()->route('parent.dashboard');
        }

        return view('parent.login');
    }

    public function registerForm(): View|RedirectResponse
    {
        if (session()->has('parent_account_id')) {
            return redirect()->route('parent.dashboard');
        }

        return view('parent.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:191'],
            'no_hp' => ['required', 'regex:/^[0-9+().\s-]{8,20}$/'],
            'password' => ['required', 'string', 'min:8', 'max:200', 'confirmed'],
        ]);

        $phone = $this->normalizePhone($validated['no_hp']);
        if (strlen($phone) < 8 || strlen($phone) > 15) {
            return back()->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['no_hp' => 'Nomor HP harus terdiri dari 8 sampai 15 angka.']);
        }

        $email = mb_strtolower(trim($validated['email']));
        if (DB::table('akun_orang_tua')->where('email', $email)->exists()) {
            return back()->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['email' => 'Email ini sudah terhubung dengan akun orang tua. Silakan masuk.']);
        }

        if (DB::table('akun_orang_tua')->where('no_hp', $phone)->exists()) {
            return back()->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['no_hp' => 'Nomor HP ini sudah memiliki akun. Silakan masuk menggunakan akun tersebut.']);
        }

        $accountId = DB::table('akun_orang_tua')->insertGetId([
            'nama' => trim($validated['nama']),
            'no_hp' => $phone,
            'email' => $email,
            'google_id' => null,
            'password_hash' => Hash::make($validated['password']),
            'aktif' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $account = DB::table('akun_orang_tua')->where('id', $accountId)->first();
        $this->authenticateParent($request, $account);

        return redirect()->intended(route('parent.dashboard'))
            ->with('status', 'Akun orang tua berhasil dibuat. Silakan lanjutkan pengisian formulir SPMB.');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email_or_hp' => ['required', 'string', 'max:191'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        $identifier = trim($validated['email_or_hp']);
        if (str_contains($identifier, '@')) {
            $account = DB::table('akun_orang_tua')->where('email', mb_strtolower($identifier))->first();
        } else {
            $phone = $this->normalizePhone($identifier);
            $account = DB::table('akun_orang_tua')->where('no_hp', $phone)->first();
        }

        if (! $account || (int) $account->aktif !== 1 || ! Hash::check($validated['password'], $account->password_hash)) {
            return back()->withInput($request->only('email_or_hp'))
                ->withErrors(['login' => 'Email/nomor HP atau kata sandi tidak cocok. Periksa kembali, lalu coba lagi.']);
        }

        $this->authenticateParent($request, $account);

        return redirect()->intended(route('parent.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'parent_account_id', 'parent_account_name', 'google_parent_profile', 'parent_google_oauth_state',
        ]);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Akun orang tua berhasil keluar.');
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
}

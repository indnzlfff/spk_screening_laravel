<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (session()->has('school_user_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:60'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        $user = DB::table('users')
            ->where('username', trim($credentials['username']))
            ->where('aktif', 1)
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password_hash)) {
            return back()->withErrors(['username' => 'Nama pengguna atau kata sandi tidak sesuai.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();
        $request->session()->put([
            'school_user_id' => (int) $user->id,
            'school_user_name' => $user->nama,
            'school_user_role' => $user->role,
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['school_user_id', 'school_user_name', 'school_user_role']);
        $request->session()->regenerate();

        return redirect()->route('admin.login')->with('status', 'Anda telah keluar dari panel sekolah.');
    }
}

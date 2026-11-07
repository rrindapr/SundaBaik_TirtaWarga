<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public static function dashboardUrl(?object $user): ?string
    {
        return match ($user?->role) {
            'bpbd'  => route('bpbd.dashboard'),
            'rt'    => route('rt.dashboard'),
            default => null,
        };
    }

    //login lgsg ke dashbord
    public function show()
    {
        $url = self::dashboardUrl(Auth::user());

        if (Auth::check() && $url) {
            return redirect($url);
        }

        return view('auth.login');
    }

    /** Proses login. */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(
            [
                'email'    => ['required', 'email'],
                'password' => ['required', 'string'],
            ],
            [
                'email.required'    => 'Email wajib diisi.',
                'email.email'       => 'Format tidak valid.',
                'password.required' => 'Password wajib diisi.',
            ]
        );

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $url = self::dashboardUrl($request->user());

        if (! $url) {
            $this->signOut($request);

            return back()
                ->withErrors(['email' => 'Akun ini belum memiliki role. Hubungi admin TirtaWarga.'])
                ->onlyInput('email');
        }

        return redirect($url);
    }

    /** Proses logout. */
    public function logout(Request $request): RedirectResponse
    {
        $this->signOut($request);

        return redirect()->route('login')->with('status', 'Anda telah logout.');
    }

    private function signOut(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}

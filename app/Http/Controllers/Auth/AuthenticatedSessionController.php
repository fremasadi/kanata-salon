<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
{
    // Jalankan autentikasi bawaan Breeze
    $request->authenticate();

    // Regenerasi session untuk keamanan
    $request->session()->regenerate();

    // Ambil user yang sedang login
    $user = $request->user();

    // 🔀 Arahkan berdasarkan role
    if ($user->role === 'admin' || $user->role === 'pegawai') {
        return redirect()->intended('/dashboard');
    }

    return redirect()->intended('/');
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

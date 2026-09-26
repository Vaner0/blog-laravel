<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class WebAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('blog.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $donneesConnexion = $request->validate([
            'email' => ['required', 'email'],
            'mot_de_passe' => ['required', 'string'],
        ]);

        if (!Auth::attempt([
            'email' => $donneesConnexion['email'],
            'password' => $donneesConnexion['mot_de_passe'],
        ])) {
            return back()
                ->withErrors(['email' => 'Ces identifiants ne correspondent pas à un compte.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('blog.home'));
    }

    public function showRegister(): View
    {
        return view('blog.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $donneesInscription = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $donneesInscription['nom'],
            'email' => $donneesInscription['email'],
            'password' => Hash::make($donneesInscription['mot_de_passe']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('blog.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('blog.home');
    }
}

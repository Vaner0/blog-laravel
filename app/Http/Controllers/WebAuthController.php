<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class WebAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('blog.auth.login');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Utilisateur Google',
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
                'password' => Hash::make(bin2hex(random_bytes(32))),
            ],
        );

        if ($user->google_id !== $googleUser->getId()) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $user->email_verified_at ?: now(),
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('blog.home'));
    }

    public function login(Request $request): RedirectResponse
    {
        $donneesConnexion = $request->validate([
            'email' => ['required', 'email'],
            'mot_de_passe' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
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

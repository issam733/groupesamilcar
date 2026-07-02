<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Journal;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }
        return view('auth.login');
    }

    /**
     * Handle login POST
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,enseignant,parent,eleve',
        ], [
            'email.required'    => 'L\'adresse email est obligatoire.',
            'email.email'       => 'L\'adresse email n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Check role matches
            if ($user->role !== $request->role) {
                Auth::logout();
                return back()
                    ->withInput($request->only('email', 'role'))
                    ->withErrors(['email' => 'Ce compte n\'a pas accès à l\'espace sélectionné.']);
            }

            // Check account active
            if (!$user->actif) {
                Auth::logout();
                return back()
                    ->withInput($request->only('email', 'role'))
                    ->withErrors(['email' => 'Votre compte a été désactivé. Contactez l\'administration.']);
            }

            // Log action
            Journal::log('Connexion', "s'est connecté en tant que {$user->role}", $user->id);

            $request->session()->regenerate();

            return $this->redirectByRole($user->role);
        }

        return back()
            ->withInput($request->only('email', 'role'))
            ->withErrors(['email' => 'Email ou mot de passe incorrect.']);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Journal::log('Déconnexion', 's\'est déconnecté', Auth::id());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous avez été déconnecté.');
    }

    /**
     * Redirect based on role
     */
    private function redirectByRole(string $role)
    {
        return match($role) {
            'admin'      => redirect()->route('admin.dashboard'),
            'enseignant' => redirect()->route('enseignant.dashboard'),
            'parent'     => redirect()->route('parent.dashboard'),
            'eleve'      => redirect()->route('eleve.dashboard'),
            default      => redirect()->route('login'),
        };
    }
}

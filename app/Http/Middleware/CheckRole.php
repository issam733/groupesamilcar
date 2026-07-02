<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Usage : Route::middleware('role:admin') ou 'role:admin,enseignant'
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!in_array($user->role, $roles)) {
            return match($user->role) {
                'admin'      => redirect()->route('admin.dashboard'),
                'enseignant' => redirect()->route('enseignant.dashboard'),
                'parent'     => redirect()->route('parent.dashboard'),
                'eleve'      => redirect()->route('eleve.dashboard'),
                default      => redirect()->route('login'),
            };
        }

        if (!$user->actif) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Votre compte a été désactivé.']);
        }

        return $next($request);
    }
}

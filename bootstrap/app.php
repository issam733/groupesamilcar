<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ─── IMPORTANT : enregistrement du middleware de rôle ───
        // C'est cette ligne qui permet d'utiliser 'role:admin', 'role:parent', etc.
        // dans les routes (web.php) pour protéger l'accès par rôle.
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        // ─── Où envoyer un utilisateur DÉJÀ connecté qui visite une page
        //     "invité" (/, /login) ? Sans cette cible, Laravel retombe sur "/",
        //     elle-même protégée par 'guest' → boucle ERR_TOO_MANY_REDIRECTS.
        //     On le redirige vers son espace selon son rôle.
        $middleware->redirectUsersTo(function (Request $request) {
            $user = Auth::user();

            return match ($user?->role) {
                'admin'      => route('admin.dashboard'),
                'enseignant' => route('enseignant.dashboard'),
                'parent'     => route('parent.dashboard'),
                'eleve'      => route('eleve.dashboard'),
                default      => '/login',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Eleve;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Crée un compte de connexion (table users) pour chaque élève qui n'en a pas
 * encore (user_id vide), puis affiche les identifiants générés.
 *
 *   php artisan eleves:comptes
 *
 * Mot de passe par défaut : 'Amilcar2026!' (modifiable avec --password=...)
 */
class CreerComptesEleves extends Command
{
    protected $signature = 'eleves:comptes {--password=Amilcar2026! : Mot de passe par défaut attribué aux comptes créés}';

    protected $description = "Crée les comptes de connexion manquants pour les élèves existants";

    public function handle(): int
    {
        $motDePasse = (string) $this->option('password');

        $eleves = Eleve::whereNull('user_id')->orderBy('nom')->get();

        if ($eleves->isEmpty()) {
            $this->info('Tous les élèves possèdent déjà un compte de connexion. Rien à faire.');
            return self::SUCCESS;
        }

        $this->info("Création des comptes pour {$eleves->count()} élève(s)…");

        $lignes  = [];
        $ignores = 0;

        foreach ($eleves as $eleve) {
            $email = $eleve->email ?: (strtolower($eleve->matricule) . '@eleve.gsa');

            // Si l'email est déjà pris dans users, on bascule sur l'email matricule.
            if (User::where('email', $email)->exists()) {
                $email = strtolower($eleve->matricule) . '@eleve.gsa';
            }
            if (User::where('email', $email)->exists()) {
                $this->warn("  • {$eleve->matricule} ({$eleve->prenom} {$eleve->nom}) : email déjà utilisé, ignoré.");
                $ignores++;
                continue;
            }

            $user = User::create([
                'nom'      => $eleve->nom,
                'prenom'   => $eleve->prenom,
                'email'    => $email,
                'password' => Hash::make($motDePasse),
                'role'     => 'eleve',
                'actif'    => true,
            ]);

            $eleve->update(['user_id' => $user->id]);

            $lignes[] = ["{$eleve->prenom} {$eleve->nom}", $eleve->matricule, $email, $motDePasse];
        }

        if ($lignes) {
            $this->newLine();
            $this->table(['Élève', 'Matricule', 'Email (identifiant)', 'Mot de passe'], $lignes);
        }

        $this->newLine();
        $this->info(count($lignes) . " compte(s) créé(s)." . ($ignores ? " {$ignores} ignoré(s)." : ''));
        $this->line("Les élèves se connectent en choisissant l'espace « Élève », avec l'email ci-dessus.");

        return self::SUCCESS;
    }
}

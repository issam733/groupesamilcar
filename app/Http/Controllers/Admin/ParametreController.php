<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiSetting;
use App\Models\User;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\AIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Exception;

class ParametreController extends Controller
{
    /* ─── INDEX : page Paramètres ───────────────────────────── */
    public function index()
    {
        $iaSettings = AiSetting::current();
        $admins = User::admins()->orderBy('nom')->get();

        $cles = [
            'groq'      => AiSetting::masquer($iaSettings->groq_api_key),
            'anthropic' => AiSetting::masquer($iaSettings->anthropic_api_key),
        ];

        return view('admin.parametres.index', compact('iaSettings', 'admins', 'cles'));
    }

    /* ─── MAJ des réglages IA (provider + clés) ─────────────── */
    public function updateIA(Request $request)
    {
        $data = $request->validate([
            'provider'          => 'required|in:groq,anthropic',
            'groq_api_key'      => 'nullable|string|max:255',
            'anthropic_api_key' => 'nullable|string|max:255',
            'groq_model'        => 'nullable|string|max:100',
            'anthropic_model'   => 'nullable|string|max:100',
        ]);

        $iaSettings = AiSetting::current();

        $update = [
            'provider'   => $data['provider'],
            'updated_by' => Auth::id(),
        ];

        // On ne remplace une clé que si un nouveau champ non vide a été saisi,
        // pour ne jamais effacer une clé existante en laissant le champ vide.
        if (!empty($data['groq_api_key'])) {
            $update['groq_api_key'] = $data['groq_api_key'];
        }
        if (!empty($data['anthropic_api_key'])) {
            $update['anthropic_api_key'] = $data['anthropic_api_key'];
        }
        if (!empty($data['groq_model'])) {
            $update['groq_model'] = $data['groq_model'];
        }
        if (!empty($data['anthropic_model'])) {
            $update['anthropic_model'] = $data['anthropic_model'];
        }

        $iaSettings->update($update);

        Journal::log('modification', "a mis à jour la configuration IA (fournisseur actif : {$data['provider']})");

        return redirect()->route('admin.parametres')
            ->with('success', 'Configuration IA mise à jour. Fournisseur actif : ' . ucfirst($data['provider']) . '.');
    }

    /* ─── Test de connexion à l'API du fournisseur actif ────── */
    public function testerIA(Request $request)
    {
        try {
            $service = new AIService();
            $service->testerConnexion();

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie avec ' . ucfirst($service->providerActuel()) . '.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /* ─── Créer un nouvel administrateur ────────────────────── */
    public function storeAdmin(Request $request)
    {
        $data = $request->validate([
            'nom'      => 'required|string|max:100',
            'prenom'   => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_admin_actuel' => 'required|string',
        ], [
            'email.unique'        => 'Cet email est déjà utilisé par un autre compte.',
            'password.min'        => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'  => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        // Confirmation par le mot de passe de l'admin connecté avant de créer un accès admin
        if (!Hash::check($data['password_admin_actuel'], Auth::user()->password)) {
            return redirect()->route('admin.parametres')
                ->withErrors(['password_admin_actuel' => 'Mot de passe incorrect. Création annulée.'])
                ->withInput();
        }

        $admin = User::create([
            'nom'      => $data['nom'],
            'prenom'   => $data['prenom'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'admin',
            'actif'    => true,
        ]);

        Journal::log('creation', "a créé un nouvel administrateur : {$admin->prenom} {$admin->nom} ({$admin->email})");

        return redirect()->route('admin.parametres')
            ->with('success', "L'administrateur {$admin->prenom} {$admin->nom} a été créé.");
    }

    /* ─── Désactiver / réactiver un administrateur ──────────── */
    public function toggleAdmin(User $admin)
    {
        if ($admin->id === Auth::id()) {
            return redirect()->route('admin.parametres')
                ->withErrors(['admin' => 'Vous ne pouvez pas désactiver votre propre compte.']);
        }

        $admin->update(['actif' => !$admin->actif]);

        $action = $admin->actif ? 'réactivé' : 'désactivé';
        Journal::log('modification', "a {$action} l'administrateur {$admin->prenom} {$admin->nom}");

        return redirect()->route('admin.parametres')
            ->with('success', "L'administrateur {$admin->prenom} {$admin->nom} a été {$action}.");
    }

    /* ─── RÉPARATION PONCTUELLE : resynchronise la table migrations ─────────
       Nécessaire car la table `migrations` de production ne correspondait pas
       aux tables réellement présentes en base (import initial hors Artisan).
       Sans ceci, `php artisan migrate` retente de recréer `users` etc. et
       échoue silencieusement au démarrage du conteneur (le script Docker
       avale l'erreur avec `|| true`), empêchant toute nouvelle migration
       de s'appliquer. À supprimer une fois utilisée. ────────────────────── */
    public function repererMigrations(Request $request)
    {
        if ($request->query('confirmer') !== '1') {
            return response(
                "Action sensible : ajoutez ?confirmer=1 à l'URL pour l'exécuter volontairement.",
                400
            )->header('Content-Type', 'text/plain; charset=utf-8');
        }

        $dejaAppliquees = [
            '0001_01_01_000000_create_users_table',
            '0001_01_01_000001_create_cache_table',
            '0001_01_01_000002_create_jobs_table',
            '2026_06_26_093000_change_examens_statut_to_string',
            '2026_06_26_100000_create_examen_copies_table',
            '2026_06_26_110000_add_rapport_to_examen_copies',
            '2026_06_26_120000_add_rapport_classe_to_examens',
            '2026_06_26_130000_create_notifications_table',
            '2026_06_27_111314_create_points_eleves_table',
            '2026_06_30_140000_create_messagerie_tables',
            '2026_07_01_090000_create_emplois_du_temps_table',
            '2026_07_01_100000_create_absences_table',
            '2026_07_01_110000_create_cahier_textes_table',
            '2026_07_02_090000_add_fichier_to_cahier_textes',
        ];

        $batch = (int) (DB::table('migrations')->max('batch') ?? 0) + 1;
        $inserees = 0;

        foreach ($dejaAppliquees as $migration) {
            $existe = DB::table('migrations')->where('migration', $migration)->exists();
            if (!$existe) {
                DB::table('migrations')->insert(['migration' => $migration, 'batch' => $batch]);
                $inserees++;
            }
        }

        Artisan::call('migrate', ['--force' => true]);
        $sortie = Artisan::output();

        Journal::log('modification', "a resynchronisé la table migrations ({$inserees} entrée(s) ajoutée(s)) puis exécuté migrate");

        return response(
            "Entrées de migrations resynchronisées : {$inserees}\n\n--- Résultat de 'php artisan migrate --force' ---\n\n{$sortie}"
        )->header('Content-Type', 'text/plain; charset=utf-8');
    }
}

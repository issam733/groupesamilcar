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
}

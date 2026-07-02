<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParentEleve;
use App\Models\User;
use App\Models\Journal;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    /* ─── INDEX ─────────────────────────────────────────────── */
    public function index(Request $request)
    {
        $query = ParentEleve::with(['eleves.classe'])->where('actif', true);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom',      'like', "%$search%")
                  ->orWhere('prenom', 'like', "%$search%")
                  ->orWhere('email',  'like', "%$search%")
                  ->orWhere('telephone', 'like', "%$search%");
            });
        }

        $parents = $query->orderBy('nom')->paginate(20)->withQueryString();

        $stats = [
            'total'        => ParentEleve::where('actif', true)->count(),
            'avec_compte'  => ParentEleve::where('actif', true)->whereNotNull('user_id')->count(),
            'sans_compte'  => ParentEleve::where('actif', true)->whereNull('user_id')->count(),
            'new_mois'     => ParentEleve::where('actif', true)->whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.parents.index', compact('parents', 'stats'));
    }

    /* ─── CREATE ────────────────────────────────────────────── */
    public function create()
    {
        return view('admin.parents.create');
    }

    /* ─── STORE ─────────────────────────────────────────────── */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'telephone'  => 'nullable|string|max:20',
            'email'      => 'nullable|email|unique:parents,email',
            'profession' => 'nullable|string|max:100',
            'creer_compte'    => 'nullable|boolean',
            'email_connexion' => 'nullable|email|unique:users,email',
            'password'        => 'nullable|min:8|confirmed',
        ], [
            'nom.required'    => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.unique'    => 'Cet email est déjà utilisé.',
        ]);

        $parentData = [
            'nom'        => $data['nom'],
            'prenom'     => $data['prenom'],
            'telephone'  => $data['telephone'] ?? null,
            'email'      => $data['email'] ?? null,
            'profession' => $data['profession'] ?? null,
            'actif'      => true,
        ];

        $parent = ParentEleve::create($parentData);

        // Créer compte utilisateur si demandé
        if ($request->boolean('creer_compte') && $request->filled('email_connexion')) {
            $user = User::create([
                'nom'      => $data['nom'],
                'prenom'   => $data['prenom'],
                'email'    => $request->email_connexion,
                'password' => Hash::make($request->password ?? 'Amilcar2026!'),
                'role'     => 'parent',
                'actif'    => true,
            ]);
            $parent->update(['user_id' => $user->id]);
        }

        Journal::log('creation', "a ajouté le parent {$parent->prenom} {$parent->nom}");

        return redirect()->route('admin.parents.index')
            ->with('success', "Le parent {$parent->prenom} {$parent->nom} a été ajouté.");
    }

    /* ─── SHOW ──────────────────────────────────────────────── */
    public function show(ParentEleve $parent)
    {
        $parent->load(['eleves.classe', 'user']);
        return view('admin.parents.show', compact('parent'));
    }

    /* ─── EDIT ──────────────────────────────────────────────── */
    public function edit(ParentEleve $parent)
    {
        return view('admin.parents.edit', compact('parent'));
    }

    /* ─── UPDATE ────────────────────────────────────────────── */
    public function update(Request $request, ParentEleve $parent)
    {
        $data = $request->validate([
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'telephone'  => 'nullable|string|max:20',
            'email'      => "nullable|email|unique:parents,email,{$parent->id}",
            'profession' => 'nullable|string|max:100',
        ]);

        $parent->update($data);
        Journal::log('modification', "a modifié la fiche de {$parent->prenom} {$parent->nom}");

        return redirect()->route('admin.parents.show', $parent)
            ->with('success', 'Fiche parent mise à jour.');
    }

    /* ─── DESTROY ───────────────────────────────────────────── */
    public function destroy(ParentEleve $parent)
    {
        $parent->update(['actif' => false]);
        if ($parent->user_id) {
            User::find($parent->user_id)?->update(['actif' => false]);
        }
        Journal::log('suppression', "a désactivé le parent {$parent->prenom} {$parent->nom}");

        return redirect()->route('admin.parents.index')
            ->with('success', "Le parent {$parent->prenom} {$parent->nom} a été désactivé.");
    }
}

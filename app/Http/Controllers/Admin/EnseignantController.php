<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enseignant;
use App\Models\Classe;
use App\Models\User;
use App\Models\Journal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class EnseignantController extends Controller
{
    /* ─── INDEX ─────────────────────────────────────────────── */
    public function index(Request $request)
    {
        $query = Enseignant::with(['classes'])->where('actif', true);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom',     'like', "%$search%")
                  ->orWhere('prenom','like', "%$search%")
                  ->orWhere('matiere','like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($matiere = $request->get('matiere')) {
            $query->where('matiere', 'like', "%$matiere%");
        }

        $enseignants = $query->orderBy('nom')->paginate(20)->withQueryString();

        $stats = [
            'total'    => Enseignant::where('actif', true)->count(),
            'actifs'   => Enseignant::where('actif', true)->count(),
            'inactifs' => Enseignant::where('actif', false)->count(),
            'classes'  => Classe::where('active', true)->count(),
        ];

        // Liste des matières uniques pour le filtre
        $matieres = Enseignant::where('actif', true)
            ->whereNotNull('matiere')
            ->distinct()
            ->pluck('matiere')
            ->sort()
            ->values();

        return view('admin.enseignants.index', compact('enseignants', 'stats', 'matieres'));
    }

    /* ─── CREATE ────────────────────────────────────────────── */
    public function create()
    {
        $classes = Classe::where('active', true)->orderBy('nom')->get();
        return view('admin.enseignants.create', compact('classes'));
    }

    /* ─── STORE ─────────────────────────────────────────────── */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'matiere'   => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'email'     => 'nullable|email|unique:enseignants,email',
            'diplome'   => 'nullable|string|max:255',
            'photo'     => 'nullable|image|max:2048',
            // Compte utilisateur optionnel
            'creer_compte'    => 'nullable|boolean',
            'email_connexion' => 'nullable|email|unique:users,email',
            'password'        => 'nullable|min:8|confirmed',
        ], [
            'nom.required'    => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.unique'    => 'Cet email est déjà utilisé par un autre enseignant.',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('enseignants/photos', 'public');
        }

        $data['actif'] = true;
        unset($data['creer_compte'], $data['email_connexion'], $data['password'], $data['password_confirmation']);

        $enseignant = Enseignant::create($data);

        // Créer un compte utilisateur si demandé
        if ($request->boolean('creer_compte') && $request->filled('email_connexion')) {
            $user = User::create([
                'nom'      => $data['nom'],
                'prenom'   => $data['prenom'],
                'email'    => $request->email_connexion,
                'password' => Hash::make($request->password ?? 'Amilcar2026!'),
                'role'     => 'enseignant',
                'actif'    => true,
            ]);
            $enseignant->update(['user_id' => $user->id]);
        }

        Journal::log('creation', "a ajouté l'enseignant {$enseignant->prenom} {$enseignant->nom}");

        return redirect()->route('admin.enseignants.index')
            ->with('success', "L'enseignant {$enseignant->prenom} {$enseignant->nom} a été ajouté.");
    }

    /* ─── SHOW ──────────────────────────────────────────────── */
    public function show(Enseignant $enseignant)
    {
        $enseignant->load(['classes.eleves', 'matieres.classe', 'examens']);

        $stats = [
            'classes'  => $enseignant->classes()->count(),
            'eleves'   => $enseignant->classes()->withCount('eleves')->get()->sum('eleves_count'),
            'examens'  => $enseignant->examens()->count(),
            'matieres' => $enseignant->matieres()->count(),
        ];

        return view('admin.enseignants.show', compact('enseignant', 'stats'));
    }

    /* ─── EDIT ──────────────────────────────────────────────── */
    public function edit(Enseignant $enseignant)
    {
        $classes = Classe::where('active', true)->orderBy('nom')->get();
        return view('admin.enseignants.edit', compact('enseignant', 'classes'));
    }

    /* ─── UPDATE ────────────────────────────────────────────── */
    public function update(Request $request, Enseignant $enseignant)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'matiere'   => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'email'     => "nullable|email|unique:enseignants,email,{$enseignant->id}",
            'diplome'   => 'nullable|string|max:255',
            'photo'     => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($enseignant->photo) Storage::disk('public')->delete($enseignant->photo);
            $data['photo'] = $request->file('photo')->store('enseignants/photos', 'public');
        }

        $enseignant->update($data);
        Journal::log('modification', "a modifié la fiche de {$enseignant->prenom} {$enseignant->nom}");

        return redirect()->route('admin.enseignants.show', $enseignant)
            ->with('success', 'Fiche enseignant mise à jour.');
    }

    /* ─── DÉSACTIVER ────────────────────────────────────────── */
    public function destroy(Enseignant $enseignant)
    {
        $enseignant->update(['actif' => false]);
        // Désactiver aussi le compte user lié
        if ($enseignant->user_id) {
            User::find($enseignant->user_id)?->update(['actif' => false]);
        }
        Journal::log('suppression', "a désactivé l'enseignant {$enseignant->prenom} {$enseignant->nom}");

        return redirect()->route('admin.enseignants.index')
            ->with('success', "L'enseignant {$enseignant->prenom} {$enseignant->nom} a été désactivé.");
    }

    /* ─── RÉACTIVER ─────────────────────────────────────────── */
    public function reactiver(Enseignant $enseignant)
    {
        $enseignant->update(['actif' => true]);
        Journal::log('modification', "a réactivé l'enseignant {$enseignant->prenom} {$enseignant->nom}");

        return back()->with('success', "L'enseignant a été réactivé.");
    }
}

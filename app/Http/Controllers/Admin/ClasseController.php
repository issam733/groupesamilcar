<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Journal;

class ClasseController extends Controller
{
    /* ─── INDEX ─────────────────────────────────────────────── */
    public function index(Request $request)
    {
        $query = Classe::with(['enseignant', 'eleves', 'matieres'])
            ->where('active', true);

        if ($niveau = $request->get('niveau')) {
            $query->where('niveau', $niveau);
        }

        if ($search = $request->get('search')) {
            $query->where('nom', 'like', "%$search%");
        }

        $classes = $query->orderByRaw("FIELD(niveau,'Préparatoire','Primaire','Collège','Lycée')")
                         ->orderBy('nom')
                         ->get();

        $stats = [
            'total'         => Classe::where('active', true)->count(),
            'prep'          => Classe::where('active', true)->where('niveau', 'Préparatoire')->count(),
            'primaire'      => Classe::where('active', true)->where('niveau', 'Primaire')->count(),
            'college'       => Classe::where('active', true)->where('niveau', 'Collège')->count(),
            'lycee'         => Classe::where('active', true)->where('niveau', 'Lycée')->count(),
            'total_eleves'  => \App\Models\Eleve::where('actif', true)->count(),
        ];

        $enseignants = Enseignant::where('actif', true)->orderBy('nom')->get();

        return view('admin.classes.index', compact('classes', 'stats', 'enseignants'));
    }

    /* ─── CREATE ────────────────────────────────────────────── */
    public function create()
    {
        $enseignants = Enseignant::where('actif', true)->orderBy('nom')->get();
        return view('admin.classes.create', compact('enseignants'));
    }

    /* ─── STORE ─────────────────────────────────────────────── */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'            => 'required|string|max:100',
            'niveau'         => 'required|in:Préparatoire,Primaire,Collège,Lycée',
            'enseignant_id'  => 'nullable|exists:enseignants,id',
            'effectif_max'   => 'required|integer|min:5|max:50',
            'annee_scolaire' => 'required|string|max:20',
            // Matières dynamiques
            'matieres'       => 'nullable|array',
            'matieres.*.nom'           => 'required|string|max:100',
            'matieres.*.coefficient'   => 'required|numeric|min:0.5|max:10',
            'matieres.*.heures_semaine'=> 'required|integer|min:1|max:10',
            'matieres.*.enseignant_id' => 'nullable|exists:enseignants,id',
        ]);

        $matieres = $data['matieres'] ?? [];
        unset($data['matieres']);
        $data['active'] = true;

        $classe = Classe::create($data);

        // Créer les matières associées
        foreach ($matieres as $m) {
            if (!empty($m['nom'])) {
                Matiere::create([
                    'classe_id'      => $classe->id,
                    'nom'            => $m['nom'],
                    'coefficient'    => $m['coefficient'] ?? 1,
                    'heures_semaine' => $m['heures_semaine'] ?? 2,
                    'enseignant_id'  => $m['enseignant_id'] ?? null,
                ]);
            }
        }

        Journal::log('creation', "a créé la classe {$classe->nom} ({$classe->niveau}) avec " . count($matieres) . " matière(s)");

        return redirect()->route('admin.classes.index')
            ->with('success', "La classe {$classe->nom} a été créée avec " . count($matieres) . " matière(s).");
    }

    /* ─── SHOW ──────────────────────────────────────────────── */
    public function show(Classe $classe)
    {
        $classe->load(['enseignant', 'eleves', 'matieres.enseignant', 'emplois.matiere']);
        return view('admin.classes.show', compact('classe'));
    }

    /* ─── EDIT ──────────────────────────────────────────────── */
    public function edit(Classe $classe)
    {
        $classe->load(['matieres.enseignant']);
        $enseignants = Enseignant::where('actif', true)->orderBy('nom')->get();
        return view('admin.classes.edit', compact('classe', 'enseignants'));
    }

    /* ─── UPDATE ────────────────────────────────────────────── */
    public function update(Request $request, Classe $classe)
    {
        $data = $request->validate([
            'nom'            => 'required|string|max:100',
            'niveau'         => 'required|in:Préparatoire,Primaire,Collège,Lycée',
            'enseignant_id'  => 'nullable|exists:enseignants,id',
            'effectif_max'   => 'required|integer|min:5|max:50',
            'annee_scolaire' => 'required|string|max:20',
            'matieres'       => 'nullable|array',
            'matieres.*.id'            => 'nullable|exists:matieres,id',
            'matieres.*.nom'           => 'required|string|max:100',
            'matieres.*.coefficient'   => 'required|numeric|min:0.5|max:10',
            'matieres.*.heures_semaine'=> 'required|integer|min:1|max:10',
            'matieres.*.enseignant_id' => 'nullable|exists:enseignants,id',
        ]);

        $matieres = $data['matieres'] ?? [];
        unset($data['matieres']);

        $classe->update($data);

        // Sync matières : supprimer les anciennes, recréer
        $classe->matieres()->delete();
        foreach ($matieres as $m) {
            if (!empty($m['nom'])) {
                Matiere::create([
                    'classe_id'      => $classe->id,
                    'nom'            => $m['nom'],
                    'coefficient'    => $m['coefficient'] ?? 1,
                    'heures_semaine' => $m['heures_semaine'] ?? 2,
                    'enseignant_id'  => $m['enseignant_id'] ?? null,
                ]);
            }
        }

        Journal::log('modification', "a modifié la classe {$classe->nom}");

        return redirect()->route('admin.classes.show', $classe)
            ->with('success', "La classe {$classe->nom} a été mise à jour.");
    }

    /* ─── DESTROY ───────────────────────────────────────────── */
    public function destroy(Classe $classe)
    {
        // Vérifier qu'il n'y a pas d'élèves actifs
        if ($classe->eleves()->count() > 0) {
            return back()->with('error', "Impossible de supprimer : la classe contient {$classe->eleves()->count()} élève(s). Transférez-les d'abord.");
        }

        $classe->update(['active' => false]);
        Journal::log('suppression', "a désactivé la classe {$classe->nom}");

        return redirect()->route('admin.classes.index')
            ->with('success', "La classe {$classe->nom} a été désactivée.");
    }

    /* ─── AJAX : ajouter une matière inline ─────────────────── */
    public function ajouterMatiere(Request $request, Classe $classe)
    {
        $data = $request->validate([
            'nom'            => 'required|string|max:100',
            'coefficient'    => 'required|numeric|min:0.5|max:10',
            'heures_semaine' => 'required|integer|min:1|max:10',
            'enseignant_id'  => 'nullable|exists:enseignants,id',
        ]);

        $matiere = Matiere::create(array_merge($data, ['classe_id' => $classe->id]));
        Journal::log('creation', "a ajouté la matière {$matiere->nom} à la classe {$classe->nom}");

        return response()->json(['success' => true, 'matiere' => $matiere->load('enseignant')]);
    }

    /* ─── AJAX : supprimer une matière ─────────────────────── */
    public function supprimerMatiere(Classe $classe, Matiere $matiere)
    {
        $nom = $matiere->nom;
        $matiere->delete();
        Journal::log('suppression', "a supprimé la matière {$nom} de la classe {$classe->nom}");

        return response()->json(['success' => true]);
    }
}

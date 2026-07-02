<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Journal;

class NoteController extends Controller
{
    /* ─── INDEX : choix de la matière + trimestre ──────────── */
    public function index(Request $request)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens) abort(403);

        // Toutes les matières enseignées par cet enseignant, avec leur classe
        $matieres = Matiere::where('enseignant_id', $ens->id)
            ->with('classe')
            ->get()
            ->filter(fn($m) => $m->classe !== null);

        $trimestre = (int) $request->get('trimestre', 1);

        return view('enseignant.notes.index', compact('ens', 'matieres', 'trimestre'));
    }

    /* ─── SAISIE : grille élèves × types pour une matière ──── */
    public function saisie(Classe $classe, Matiere $matiere, int $trimestre)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens) abort(403);

        // Sécurité : la matière doit appartenir à cet enseignant ET à cette classe
        if ($matiere->enseignant_id !== $ens->id || $matiere->classe_id !== $classe->id) {
            abort(403, "Vous n'enseignez pas cette matière dans cette classe.");
        }

        $classe->load(['eleves' => fn($q) => $q->where('actif', true)->orderBy('nom')]);

        // Notes existantes : index par "eleve_id_type"
        $notesExistantes = Note::where('classe_id', $classe->id)
            ->where('matiere_id', $matiere->id)
            ->where('trimestre', $trimestre)
            ->get()
            ->keyBy(fn($n) => "{$n->eleve_id}_{$n->type}");

        $types = Note::types(); // ['devoir'=>'Devoir','controle'=>'Contrôle','examen'=>'Examen']

        return view('enseignant.notes.saisie', compact('ens', 'classe', 'matiere', 'trimestre', 'notesExistantes', 'types'));
    }

    /* ─── SAUVEGARDER (AJAX) ───────────────────────────────── */
    public function sauvegarder(Request $request)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens) abort(403);

        $data = $request->validate([
            'classe_id'        => 'required|exists:classes,id',
            'matiere_id'       => 'required|exists:matieres,id',
            'trimestre'        => 'required|integer|min:1|max:3',
            'type'             => 'required|in:devoir,controle,examen',
            'notes'            => 'required|array',
            'notes.*.eleve_id' => 'required|exists:eleves,id',
            'notes.*.valeur'   => 'nullable|numeric|min:0|max:20',
        ]);

        // Sécurité : on ne sauvegarde que dans une matière appartenant à l'enseignant
        $matiere = Matiere::where('id', $data['matiere_id'])
            ->where('enseignant_id', $ens->id)
            ->where('classe_id', $data['classe_id'])
            ->first();

        if (!$matiere) {
            return response()->json([
                'success' => false,
                'message' => "Vous n'êtes pas autorisé à saisir des notes pour cette matière.",
            ], 403);
        }

        $count = 0;
        foreach ($data['notes'] as $n) {
            if ($n['valeur'] === null || $n['valeur'] === '') continue;

            Note::updateOrCreate(
                [
                    'eleve_id'   => $n['eleve_id'],
                    'matiere_id' => $data['matiere_id'],
                    'trimestre'  => $data['trimestre'],
                    'type'       => $data['type'],
                ],
                [
                    'classe_id' => $data['classe_id'],
                    'valeur'    => $n['valeur'],
                    'saisi_par' => auth()->id(),
                ]
            );
            $count++;
        }

        Journal::log('creation', "a saisi {$count} note(s) en {$matiere->nom}");

        return response()->json(['success' => true, 'count' => $count]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Journal;

class NoteController extends Controller
{
    /* ─── INDEX : sélection classe/trimestre ───────────────── */
    public function index(Request $request)
    {
        $classes   = Classe::where('active', true)
            ->orderByRaw("FIELD(niveau,'Préparatoire','Primaire','Collège','Lycée')")
            ->orderBy('nom')
            ->get();

        $trimestre = $request->get('trimestre', 1);

        return view('admin.notes.index', compact('classes', 'trimestre'));
    }

    /* ─── SAISIE : grille de saisie pour une classe/trimestre ─ */
    public function saisie(Classe $classe, int $trimestre)
    {
        $classe->load(['matieres', 'eleves' => fn($q) => $q->orderBy('nom')]);

        // Notes existantes pour ce trimestre, indexées par eleve_id + matiere_id + type
        $notesExistantes = Note::where('classe_id', $classe->id)
            ->where('trimestre', $trimestre)
            ->get()
            ->groupBy(fn($n) => "{$n->eleve_id}_{$n->matiere_id}_{$n->type}");

        $types = Note::types();

        return view('admin.notes.saisie', compact('classe', 'trimestre', 'notesExistantes', 'types'));
    }

    /* ─── SAUVEGARDER : enregistrement en masse (AJAX) ──────── */
    public function sauvegarder(Request $request)
    {
        $data = $request->validate([
            'classe_id'  => 'required|exists:classes,id',
            'matiere_id' => 'required|exists:matieres,id',
            'trimestre'  => 'required|integer|min:1|max:3',
            'type'       => 'required|in:devoir,controle,examen',
            'notes'      => 'required|array',
            'notes.*.eleve_id' => 'required|exists:eleves,id',
            'notes.*.valeur'   => 'nullable|numeric|min:0|max:20',
        ]);

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
                    'classe_id'  => $data['classe_id'],
                    'valeur'     => $n['valeur'],
                    'saisi_par'  => auth()->id(),
                ]
            );
            $count++;
        }

        Journal::log('creation', "a saisi {$count} note(s)");

        return response()->json(['success' => true, 'count' => $count]);
    }

    /* ─── BULLETINS : liste pour sélection ──────────────────── */
    public function bulletins(Request $request)
    {
        $classes   = Classe::where('active', true)->orderBy('nom')->get();
        $classeId  = $request->get('classe_id');
        $trimestre = $request->get('trimestre', 1);

        $eleves = collect();
        if ($classeId) {
            $eleves = Eleve::where('classe_id', $classeId)
                ->where('actif', true)
                ->orderBy('nom')
                ->get()
                ->map(function ($e) use ($trimestre) {
                    $e->moyenne_calc = $e->moyenneTrimestre((int) $trimestre);
                    return $e;
                });
        }

        return view('admin.notes.bulletins', compact('classes', 'classeId', 'trimestre', 'eleves'));
    }

    /* ─── BULLETIN PDF (vue imprimable) ─────────────────────── */
    public function bulletinPdf(Eleve $eleve, int $trim)
    {
        $eleve->load(['classe.matieres', 'notes' => fn($q) => $q->where('trimestre', $trim)->with('matiere')]);

        $lignes = $eleve->classe->matieres->map(function ($matiere) use ($eleve, $trim) {
            $notesMatiere = $eleve->notes->where('matiere_id', $matiere->id);
            $moyenneMatiere = $notesMatiere->count()
                ? round($notesMatiere->avg('valeur'), 2)
                : null;

            return [
                'matiere'     => $matiere->nom,
                'coefficient' => $matiere->coefficient,
                'moyenne'     => $moyenneMatiere,
                'points'      => $moyenneMatiere !== null ? round($moyenneMatiere * $matiere->coefficient, 2) : null,
            ];
        });

        $totalCoef   = $eleve->classe->matieres->sum('coefficient');
        $totalPoints = $lignes->sum('points');
        $moyenneGenerale = $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : null;

        // Classement dans la classe pour ce trimestre
        $classement = null;
        if ($moyenneGenerale !== null) {
            $moyennesClasse = Eleve::where('classe_id', $eleve->classe_id)
                ->where('actif', true)
                ->get()
                ->map(fn($e) => $e->moyenneTrimestre($trim))
                ->filter()
                ->sort()
                ->reverse()
                ->values();

            $classement = $moyennesClasse->search(fn($m) => round($m, 2) === $moyenneGenerale);
            $classement = $classement !== false ? $classement + 1 : null;
        }

        $totalEleves = Eleve::where('classe_id', $eleve->classe_id)->where('actif', true)->count();

        Journal::log('export', "a généré le bulletin de {$eleve->prenom} {$eleve->nom} (T{$trim})");

        return view('admin.notes.bulletin-pdf', compact(
            'eleve', 'lignes', 'moyenneGenerale', 'trim', 'classement', 'totalEleves'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Absence;
use App\Models\Enseignant;
use App\Http\Controllers\Enseignant\DashboardController as EnseignantDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AbsenceController extends Controller
{
    private function layout(): string
    {
        return Auth::user()->role === 'admin' ? 'admin.layouts.app' : 'enseignant.layouts.app';
    }

    /** Classes que l'utilisateur courant a le droit de gérer. */
    private function classesAutorisees(): Collection
    {
        $u = Auth::user();

        if ($u->role === 'admin') {
            return Classe::where('active', true)->orderBy('nom')->get();
        }

        // Enseignant : ses classes (principal + via matière), comme pour les notes
        $ens = Enseignant::where('user_id', $u->id)->first();
        if (!$ens) return collect();

        $ids = EnseignantDashboard::classeIds($ens);
        return Classe::whereIn('id', $ids)->where('active', true)->orderBy('nom')->get();
    }

    private function peutAcceder($classeId): bool
    {
        return $this->classesAutorisees()->contains('id', (int) $classeId);
    }

    /* ─── APPEL : sélection classe + date, puis feuille d'appel ─── */
    public function index(Request $request)
    {
        $classes  = $this->classesAutorisees();
        $classeId = $request->query('classe_id');
        $date     = $request->query('date', now()->toDateString());

        $classeSel    = null;
        $eleves       = collect();
        $absencesJour = collect();

        if ($classeId && $this->peutAcceder($classeId)) {
            $classeSel = Classe::find($classeId);
            $eleves = $classeSel->eleves()->orderBy('nom')->orderBy('prenom')->get();
            $absencesJour = Absence::where('date', $date)
                ->whereIn('eleve_id', $eleves->pluck('id'))
                ->get()->keyBy('eleve_id');
        }

        return view('absences.index', [
            'layout'       => $this->layout(),
            'classes'      => $classes,
            'classeSel'    => $classeSel,
            'classeId'     => $classeId,
            'date'         => $date,
            'eleves'       => $eleves,
            'absencesJour' => $absencesJour,
        ]);
    }

    /* ─── ENREGISTRER l'appel d'une classe pour une date ─── */
    public function enregistrer(Request $request)
    {
        $data = $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'date'      => 'required|date',
            'absents'   => 'nullable|array',
            'justifie'  => 'nullable|array',
            'motif'     => 'nullable|array',
        ]);

        if (!$this->peutAcceder($data['classe_id'])) abort(403);

        $classe  = Classe::findOrFail($data['classe_id']);
        $eleves  = $classe->eleves()->pluck('id');
        $absents = array_map('intval', array_keys($request->input('absents', [])));

        $nbAbsents = 0;
        foreach ($eleves as $eid) {
            $estAbsent = in_array((int) $eid, $absents, true);
            $existante = Absence::where('eleve_id', $eid)->where('date', $data['date'])->first();

            if ($estAbsent) {
                $justifie = !empty($request->input("justifie.$eid"));
                $motif    = $request->input("motif.$eid");

                if ($existante) {
                    $existante->update(['justifie' => $justifie, 'motif' => $motif]);
                } else {
                    // La création déclenche l'observer -> email au parent
                    Absence::create([
                        'eleve_id'  => $eid,
                        'date'      => $data['date'],
                        'justifie'  => $justifie,
                        'motif'     => $motif,
                        'saisi_par' => Auth::id(),
                    ]);
                }
                $nbAbsents++;
            } elseif ($existante) {
                // L'élève n'est plus marqué absent : on retire l'absence de ce jour
                $existante->delete();
            }
        }

        return redirect()
            ->route('absences.index', ['classe_id' => $data['classe_id'], 'date' => $data['date']])
            ->with('success', "Appel enregistré — {$nbAbsents} absent(s) pour le " . Carbon::parse($data['date'])->format('d/m/Y') . ".");
    }

    /* ─── HISTORIQUE des absences ─── */
    public function historique(Request $request)
    {
        $classes   = $this->classesAutorisees();
        $classeIds = $classes->pluck('id');

        $query = Absence::whereHas('eleve', fn($q) => $q->whereIn('classe_id', $classeIds))
            ->with(['eleve.classe', 'saiseur'])
            ->orderByDesc('date')
            ->orderBy('eleve_id');

        // Filtres optionnels
        if ($request->filled('classe_id') && $this->peutAcceder($request->classe_id)) {
            $query->whereHas('eleve', fn($q) => $q->where('classe_id', $request->classe_id));
        }
        if ($request->filled('statut')) {
            $query->where('justifie', $request->statut === 'justifie');
        }

        $absences = $query->limit(300)->get();

        return view('absences.historique', [
            'layout'    => $this->layout(),
            'classes'   => $classes,
            'absences'  => $absences,
            'classeId'  => $request->classe_id,
            'statut'    => $request->statut,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enseignant;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Examen;
use App\Models\Annonce;
use App\Models\EmploiDuTemps;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Récupère le profil enseignant lié à l'utilisateur connecté.
     * Méthode réutilisée par tous les contrôleurs de l'espace enseignant.
     */
    public static function enseignantActuel(): ?Enseignant
    {
        return Enseignant::where('user_id', Auth::id())->first();
    }

    /**
     * IDs des classes où l'enseignant intervient :
     * - classes dont il est le professeur principal (classes.enseignant_id)
     * - classes où il enseigne au moins une matière (matieres.enseignant_id)
     */
    public static function classeIds(Enseignant $ens): array
    {
        $principal = Classe::where('enseignant_id', $ens->id)->pluck('id');
        $viaMatiere = Matiere::where('enseignant_id', $ens->id)->pluck('classe_id');

        return $principal->merge($viaMatiere)->unique()->filter()->values()->all();
    }

    /* ─── ACCUEIL : vue d'ensemble ─────────────────────────── */
    public function index()
    {
        $ens = self::enseignantActuel();
        if (!$ens) {
            abort(403, "Aucun profil enseignant n'est associé à ce compte.");
        }

        $classeIds = self::classeIds($ens);
        $matieres  = Matiere::where('enseignant_id', $ens->id)->with('classe')->get();

        $stats = [
            'classes'  => count($classeIds),
            'matieres' => $matieres->count(),
            'eleves'   => \App\Models\Eleve::whereIn('classe_id', $classeIds)->where('actif', true)->count(),
            'examens'  => Examen::where('enseignant_id', $ens->id)->count(),
        ];

        $annonces = Annonce::where('publie', true)
            ->whereIn('cible', ['all', 'enseignants'])
            ->latest()
            ->take(5)
            ->get();

        $coursAujourdhui = $this->coursAujourdhui($ens);

        return view('enseignant.dashboard.index', compact('ens', 'stats', 'matieres', 'annonces', 'coursAujourdhui'));
    }

    /* ─── MES CLASSES & MATIÈRES ───────────────────────────── */
    public function classes()
    {
        $ens = self::enseignantActuel();
        if (!$ens) abort(403);

        $matieres = Matiere::where('enseignant_id', $ens->id)
            ->with(['classe' => fn($q) => $q->withCount(['eleves' => fn($e) => $e->where('actif', true)])])
            ->get()
            ->groupBy(fn($m) => $m->classe->nom ?? 'Sans classe');

        return view('enseignant.classes.index', compact('ens', 'matieres'));
    }

    /* ─── MON EMPLOI DU TEMPS ──────────────────────────────── */
    public function emploi()
    {
        $ens = self::enseignantActuel();
        if (!$ens) abort(403);

        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        $creneaux = EmploiDuTemps::where('enseignant_id', $ens->id)
            ->with(['classe', 'matiere'])
            ->get();

        // Grille : [jour][heure_debut] = créneau
        $grille = [];
        $heures = [];
        foreach ($creneaux as $c) {
            $h = substr($c->heure_debut, 0, 5);
            $grille[$c->jour][$h] = $c;
            $heures[$h] = $h;
        }
        ksort($heures);

        return view('enseignant.emploi.index', compact('ens', 'jours', 'grille', 'heures', 'creneaux'));
    }

    /* ─── ANNONCES DE L'ÉCOLE ──────────────────────────────── */
    public function annonces()
    {
        $ens = self::enseignantActuel();
        if (!$ens) abort(403);

        $annonces = Annonce::where('publie', true)
            ->whereIn('cible', ['all', 'enseignants'])
            ->with('auteur')
            ->latest()
            ->paginate(15);

        return view('enseignant.annonces.index', compact('ens', 'annonces'));
    }

    /* ─── Liste des cours du jour pour l'accueil ───────────── */
    private function coursAujourdhui(Enseignant $ens): \Illuminate\Support\Collection
    {
        $joursMap = [0 => 'Dimanche', 1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'];
        $aujourdhui = $joursMap[now()->dayOfWeek] ?? 'Lundi';

        return EmploiDuTemps::where('enseignant_id', $ens->id)
            ->where('jour', $aujourdhui)
            ->with(['classe', 'matiere'])
            ->orderBy('heure_debut')
            ->get();
    }
}

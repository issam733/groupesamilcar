<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParentEleve;
use App\Models\Eleve;
use App\Models\Attestation;
use App\Models\Copie;
use App\Models\Annonce;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Récupère le profil parent lié à l'utilisateur connecté
     */
    private function parentActuel(): ?ParentEleve
    {
        return ParentEleve::where('user_id', Auth::id())->first();
    }

    /* ─── DASHBOARD : vue d'ensemble de tous les enfants ────── */
    public function index()
    {
        $parent = $this->parentActuel();

        if (!$parent) {
            abort(403, 'Aucun profil parent associé à ce compte.');
        }

        $enfants = $parent->eleves()->with('classe')->where('actif', true)->get()
            ->map(function ($eleve) {
                $eleve->moyenne_t1 = $eleve->moyenneTrimestre(1);
                $eleve->moyenne_t2 = $eleve->moyenneTrimestre(2);
                $eleve->moyenne_t3 = $eleve->moyenneTrimestre(3);
                $eleve->absences_count = $eleve->absences()->count();
                $eleve->absences_non_justifiees = $eleve->absencesNonJustifiees();

                // Devoirs à venir (cahier de texte de la classe de l'enfant)
                $eleve->devoirs_a_venir = \App\Models\CahierTexte::where('classe_id', $eleve->classe_id)
                    ->whereNotNull('devoirs')->where('devoirs', '!=', '')
                    ->whereNotNull('date_remise')
                    ->whereDate('date_remise', '>=', now()->toDateString())
                    ->with('matiere')
                    ->orderBy('date_remise')
                    ->limit(5)->get();

                return $eleve;
            });

        $annonces = Annonce::where('publie', true)
            ->whereIn('cible', ['all', 'parents'])
            ->latest()
            ->take(5)
            ->get();

        return view('parent.dashboard.index', compact('parent', 'enfants', 'annonces'));
    }

    /* ─── DÉTAIL D'UN ENFANT ─────────────────────────────────── */
    public function enfant(Eleve $eleve)
    {
        $parent = $this->parentActuel();

        // Sécurité : vérifier que cet élève appartient bien à ce parent
        if (!$parent || $eleve->parent_id !== $parent->id) {
            abort(403, 'Vous n\'avez pas accès à ce dossier.');
        }

        $eleve->load(['classe.matieres', 'notes.matiere', 'absences', 'attestations']);

        $moyennes = [
            1 => $eleve->moyenneTrimestre(1),
            2 => $eleve->moyenneTrimestre(2),
            3 => $eleve->moyenneTrimestre(3),
        ];

        // Détail des notes par matière (trimestre actuel = dernier avec des notes)
        $trimestreActuel = collect($moyennes)->filter()->keys()->last() ?? 1;

        $notesParMatiere = $eleve->classe->matieres->map(function ($matiere) use ($eleve, $trimestreActuel) {
            $notes = $eleve->notes->where('matiere_id', $matiere->id)->where('trimestre', $trimestreActuel);
            return [
                'matiere'     => $matiere->nom,
                'coefficient' => $matiere->coefficient,
                'notes'       => $notes->values(),
                'moyenne'     => $notes->count() ? round($notes->avg('valeur'), 2) : null,
            ];
        });

        $absencesRecentes = $eleve->absences->sortByDesc('date')->take(10);

        return view('parent.enfant.show', compact(
            'eleve', 'moyennes', 'trimestreActuel', 'notesParMatiere', 'absencesRecentes'
        ));
    }
    public function annonces()
    {
        $annonces = \App\Models\Annonce::where('publie', true)
            ->whereIn('cible', ['all', 'parent', 'parents'])
            ->latest()
            ->get();

        return view('parent.annonces', compact('annonces'));
    }

    /* ─── ATTESTATIONS DE L'ENFANT (téléchargement) ─────────── */
    public function attestations(Eleve $eleve)
    {
        $parent = $this->parentActuel();
        if (!$parent || $eleve->parent_id !== $parent->id) {
            abort(403);
        }

        $attestations = $eleve->attestations()->latest()->get();

        return view('parent.enfant.attestations', compact('eleve', 'attestations'));
    }

    /* ─── RAPPORTS D'EXAMEN (transmis par les enseignants) ──── */
    public function rapports(Eleve $eleve)
    {
        $parent = $this->parentActuel();
        if (!$parent || $eleve->parent_id !== $parent->id) {
            abort(403);
        }

        // Uniquement les copies dont le rapport a été transmis au parent
        $copies = Copie::where('eleve_id', $eleve->id)
            ->whereNotNull('rapport')
            ->where('rapport_envoye_parent', true)
            ->with('examen.matiere')
            ->latest()
            ->get();

        return view('parent.enfant.rapports', compact('eleve', 'copies'));
    }

    /* ─── NOTIFICATIONS (in-app) ───────────────────────────── */
    public function notifications()
    {
        $parent = $this->parentActuel();
        if (!$parent) abort(403);

        $user = Auth::user();

        // On récupère la liste avant de marquer comme lues (pour styliser les non-lues)
        $notifications = $user->notifications()->latest()->take(50)->get();

        // Puis on marque tout comme lu à l'ouverture de la page
        $user->unreadNotifications->markAsRead();

        return view('parent.notifications', compact('parent', 'notifications'));
    }

    /* ─── EMPLOI DU TEMPS DE L'ENFANT ────────────────────────── */
    public function emploi(Eleve $eleve)
    {
        $parent = $this->parentActuel();
        if (!$parent || $eleve->parent_id !== $parent->id) {
            abort(403);
        }

        $eleve->load('classe.emplois.matiere', 'classe.emplois.enseignant');
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        $grille = [];
        foreach ($eleve->classe->emplois as $creneau) {
            $grille[$creneau->jour][substr($creneau->heure_debut, 0, 5)] = $creneau;
        }

        return view('parent.enfant.emploi', compact('eleve', 'jours', 'grille'));
    }
}

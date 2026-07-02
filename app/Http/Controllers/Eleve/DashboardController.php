<?php

namespace App\Http\Controllers\Eleve;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Eleve;
use App\Models\Annonce;
use App\Models\Ressource;
use App\Models\Examen;
use App\Models\Copie;
use Illuminate\Support\Facades\Auth;
use App\Models\PointEleve;
class DashboardController extends Controller
{
    /**
     * Récupère le profil élève lié à l'utilisateur connecté
     */
    private function ajouterPoints(Eleve $eleve, int $points, string $motif, bool $uneFoisParJour = true): void
    {
        $dejaAttribue = $eleve->pointsEleves()
            ->where('motif', $motif)
            ->whereDate('date_action', today())
            ->exists();

        if ($uneFoisParJour && $dejaAttribue) {
            return;
        }

        $eleve->pointsEleves()->create([
            'points' => $points,
            'motif' => $motif,
            'date_action' => today(),
        ]);
    }
    private function eleveActuel(): ?Eleve
    {
        return Eleve::where('user_id', Auth::id())->first();
    }

    /* ─── DASHBOARD : vue d'ensemble ─────────────────────────── */
    public function index()
    {
        $eleve = $this->eleveActuel();

        if (!$eleve) {
            abort(403, 'Aucun profil élève associé à ce compte.');
        }

        $this->ajouterPoints($eleve, 5, 'Connexion quotidienne');
        $eleve->load('classe');

        $moyennes = [
            1 => $eleve->moyenneTrimestre(1),
            2 => $eleve->moyenneTrimestre(2),
            3 => $eleve->moyenneTrimestre(3),
        ];

        $annonces = Annonce::where('publie', true)
            ->whereIn('cible', ['all', 'eleves'])
            ->latest()
            ->take(5)
            ->get();

        $prochainCours = $this->prochainCours($eleve);

        // Devoirs à venir (cahier de texte de sa classe, à remettre aujourd'hui ou après)
        $devoirs = \App\Models\CahierTexte::where('classe_id', $eleve->classe_id)
            ->whereNotNull('devoirs')->where('devoirs', '!=', '')
            ->whereNotNull('date_remise')
            ->whereDate('date_remise', '>=', now()->toDateString())
            ->with('matiere')
            ->orderBy('date_remise')
            ->limit(5)->get();

        $stats = [
            'absences'     => $eleve->absences()->count(),
            'ressources'   => $eleve->classe ? Ressource::where('classe_id', $eleve->classe_id)->count() : 0,
            'moyenne_actuelle' => collect($moyennes)->filter()->last(),
        ];

        return view('eleve.dashboard.index', compact('eleve', 'moyennes', 'annonces', 'prochainCours', 'stats', 'devoirs'));
    }

    /* ─── MES COURS (emploi du temps + bibliothèque) ────────── */
    public function cours()
    {
        $eleve = $this->eleveActuel();

        if (!$eleve || !$eleve->classe_id) {
            abort(403);
        }

        $this->ajouterPoints($eleve, 2, 'Consultation d’un cours');

        $eleve->load('classe.emplois.matiere', 'classe.emplois.enseignant', 'classe.matieres.enseignant');
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        $grille = [];
        foreach ($eleve->classe->emplois as $creneau) {
            $grille[$creneau->jour][substr($creneau->heure_debut, 0, 5)] = $creneau;
        }

        return view('eleve.dashboard.cours', compact('eleve', 'jours', 'grille'));
    }

    /* ─── MES RÉSULTATS (notes détaillées) ──────────────────── */
    public function resultats(Request $request)
    {
        $eleve = $this->eleveActuel();

        if (!$eleve) {
            abort(403);
        }

        $this->ajouterPoints($eleve, 1, 'Consultation des résultats');

        $trimestre = (int) $request->get('trimestre', 1);
        $eleve->load('classe.matieres', 'notes.matiere');

        $notesParMatiere = $eleve->classe
            ? $eleve->classe->matieres->map(function ($matiere) use ($eleve, $trimestre) {
                $notes = $eleve->notes->where('matiere_id', $matiere->id)->where('trimestre', $trimestre);
                return [
                    'matiere'     => $matiere->nom,
                    'coefficient' => $matiere->coefficient,
                    'notes'       => $notes->values(),
                    'moyenne'     => $notes->count() ? round($notes->avg('valeur'), 2) : null,
                ];
            })
            : collect();

        $moyenneGenerale = $eleve->moyenneTrimestre($trimestre);

        return view('eleve.dashboard.resultats', compact('eleve', 'trimestre', 'notesParMatiere', 'moyenneGenerale'));
    }

    /* ─── BIBLIOTHÈQUE (lecture seule) ───────────────────────── */
    public function bibliotheque()
    {
        $eleve = $this->eleveActuel();

        if (!$eleve) {
            abort(403);
        }

        $this->ajouterPoints($eleve, 2, 'Consultation de la bibliothèque');

        $ressources = $eleve->classe_id
            ? Ressource::with('matiere')->where('classe_id', $eleve->classe_id)->latest()->get()
            : collect();

        $parMatiere = $ressources->groupBy(fn($r) => $r->matiere->nom ?? 'Général');

        return view('eleve.dashboard.bibliotheque', compact('eleve', 'parMatiere'));
    }

    /* ─── MES EXAMENS (envoyés par les enseignants) ─────────── */
    public function examens()
    {
        $eleve = $this->eleveActuel();
        if (!$eleve) abort(403);

        $examens = $eleve->classe_id
            ? Examen::with(['matiere', 'enseignant'])
                ->where('classe_id', $eleve->classe_id)
                ->where('statut', 'envoye')
                ->latest()
                ->get()
            : collect();

        return view('eleve.examens.index', compact('eleve', 'examens'));
    }

    /* ─── CONSULTER UN EXAMEN (sujet seul, sans corrigé) ────── */
    public function examenShow(Examen $examen)
    {
        $eleve = $this->eleveActuel();
        if (!$eleve) abort(403);

        // Sécurité : uniquement un examen envoyé à SA classe
        if ($examen->statut !== 'envoye' || $examen->classe_id !== $eleve->classe_id) {
            abort(403, "Cet examen n'est pas disponible pour votre classe.");
        }

        $examen->load(['matiere', 'enseignant']);
        $contenu = json_decode($examen->contenu, true) ?? [];

        // Copie déjà soumise par cet élève (le cas échéant)
        $copie = Copie::where('examen_id', $examen->id)
            ->where('eleve_id', $eleve->id)
            ->first();

        return view('eleve.examens.show', compact('eleve', 'examen', 'contenu', 'copie'));
    }

    /* ─── SOUMETTRE ses réponses à un examen ────────────────── */
    public function examenSoumettre(Request $request, Examen $examen)
    {

        $eleve = $this->eleveActuel();
        if (!$eleve) abort(403);

        if ($examen->statut !== 'envoye' || $examen->classe_id !== $eleve->classe_id) {
            abort(403, "Cet examen n'est pas disponible pour votre classe.");
        }

        // Empêcher une double soumission
        if (Copie::where('examen_id', $examen->id)->where('eleve_id', $eleve->id)->exists()) {
            return redirect()->route('eleve.examens.show', $examen)
                ->with('success', "Vous avez déjà soumis cet examen.");
        }

        $contenu  = json_decode($examen->contenu, true) ?? [];
        $qcm      = $contenu['qcm'] ?? [];
        $ouvertes = $contenu['questions_ouvertes'] ?? [];

        $repQcm = (array) $request->input('qcm', []);       // [numero => index choisi]
        $repOuv = (array) $request->input('ouvertes', []);  // [numero => texte]

        // Correction automatique du QCM
        $score = 0.0;
        $bareme = 0.0;
        foreach ($qcm as $i => $q) {
            $num = $q['numero'] ?? ($i + 1);
            $pts = (float) ($q['points'] ?? 1);
            $bareme += $pts;
            if (isset($repQcm[$num]) && (int) $repQcm[$num] === (int) ($q['bonne_reponse'] ?? -1)) {
                $score += $pts;
            }
        }

        // S'il n'y a pas de questions ouvertes, la copie est déjà entièrement corrigée
        $sansOuvertes = count($ouvertes) === 0;

        Copie::create([

            'examen_id'   => $examen->id,
            'eleve_id'    => $eleve->id,
            'reponses'    => ['qcm' => $repQcm, 'ouvertes' => $repOuv],
            'score_qcm'   => $score,
            'bareme_qcm'  => $bareme,
            'note_finale' => $sansOuvertes ? $score : null,
            'statut'      => $sansOuvertes ? 'corrige' : 'soumis',
        ]);
        $this->ajouterPoints($eleve, 15, 'Soumission d’un examen', false);
        return redirect()->route('eleve.examens.show', $examen)
            ->with('success', "Vos réponses ont été envoyées à votre enseignant.");
    }

    /* ─── Trouve le prochain cours selon le jour/heure actuels ─ */
    private function prochainCours(Eleve $eleve): ?array
    {
        if (!$eleve->classe_id) return null;

        $joursMap = [1 => 'Dimanche', 2 => 'Lundi', 3 => 'Mardi', 4 => 'Mercredi', 5 => 'Jeudi', 6 => 'Vendredi', 7 => 'Samedi'];
        $aujourdhui = $joursMap[now()->dayOfWeek + 1] ?? 'Lundi';
        $heureActuelle = now()->format('H:i');

        $creneau = \App\Models\EmploiDuTemps::where('classe_id', $eleve->classe_id)
            ->where('jour', $aujourdhui)
            ->where('heure_debut', '>=', $heureActuelle)
            ->orderBy('heure_debut')
            ->with(['matiere', 'enseignant'])
            ->first();

        if (!$creneau) return null;

        return [
            'matiere'    => $creneau->matiere->nom ?? '—',
            'enseignant' => $creneau->enseignant ? $creneau->enseignant->prenom . ' ' . $creneau->enseignant->nom : '—',
            'heure'      => substr($creneau->heure_debut, 0, 5) . ' - ' . substr($creneau->heure_fin, 0, 5),
        ];
    }
    public function profil()

{
    $eleve = $this->eleveActuel();

    if (!$eleve) {
        abort(403, 'Aucun profil élève associé à ce compte.');
    }

    $eleve->load(['classe', 'parent']);

    $totalPoints = $eleve->totalPoints();

    $classement = Eleve::where('actif', true)
        ->withSum('pointsEleves as total_points', 'points')
        ->get()
        ->sortByDesc('total_points')
        ->values()
        ->search(fn($item) => $item->id === $eleve->id);

    $rang = $classement === false ? null : $classement + 1;

    $totalEleves = Eleve::where('actif', true)->count();

    return view('eleve.dashboard.profil', compact(
        'eleve',
        'totalPoints',
        'rang',
        'totalEleves'
    ));
}
}

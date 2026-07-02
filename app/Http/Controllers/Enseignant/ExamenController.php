<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Examen;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Journal;
use App\Models\Copie;
use App\Models\User;
use App\Mail\RapportEleveMail;
use App\Notifications\RapportPartageNotification;
use App\Services\GroqService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class ExamenController extends Controller
{
    private GroqService $groq;

    public function __construct(GroqService $groq)
    {
        $this->groq = $groq;
    }

    /* ─── INDEX : mes examens ──────────────────────────────── */
    public function index(Request $request)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens) abort(403);

        $query = Examen::where('enseignant_id', $ens->id)->with(['classe', 'matiere']);

        if ($search = $request->get('search')) {
            $query->where('titre', 'like', "%$search%");
        }

        $examens = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total'   => Examen::where('enseignant_id', $ens->id)->count(),
            'envoyes' => Examen::where('enseignant_id', $ens->id)->where('statut', 'envoye')->count(),
            'brouillons' => Examen::where('enseignant_id', $ens->id)->where('statut', '!=', 'envoye')->count(),
        ];

        return view('enseignant.examens.index', compact('ens', 'examens', 'stats'));
    }

    /* ─── CREATE : formulaire de génération ─────────────────── */
    public function create()
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens) abort(403);

        // Uniquement les classes/matières de cet enseignant
        $matieres = Matiere::where('enseignant_id', $ens->id)->with('classe')->get();
        $classes  = $matieres->map(fn($m) => $m->classe)->filter()->unique('id')->values();

        $apiConfiguree = $this->groq->isConfigured();

        return view('enseignant.examens.create', compact('ens', 'classes', 'matieres', 'apiConfiguree'));
    }

    /* ─── GENERER : appel IA (AJAX) ──────────────────────────── */
    public function generer(Request $request)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens) abort(403);

        $data = $request->validate([
            'classe_id'     => 'nullable|exists:classes,id',
            'matiere_id'    => 'nullable|exists:matieres,id',
            'niveau'        => 'nullable|string|max:100',
            'langue'        => 'required|in:fr,ar,en',
            'difficulte'    => 'required|in:facile,moyen,difficile',
            'nb_qcm'        => 'required|integer|min:0|max:30',
            'nb_ouvertes'   => 'required|integer|min:0|max:15',
            'contenu_cours' => 'required|string|min:50',
            'fichier_pdf'   => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'contenu_cours.required' => 'Veuillez fournir le contenu du cours.',
            'contenu_cours.min'      => 'Le contenu du cours est trop court pour générer un examen pertinent.',
        ]);

        // Sécurité : si une matière est choisie, elle doit appartenir à l'enseignant
        $matiereNom = null;
        if ($data['matiere_id'] ?? null) {
            $matiere = Matiere::where('id', $data['matiere_id'])
                ->where('enseignant_id', $ens->id)
                ->first();
            if (!$matiere) {
                return response()->json(['success' => false, 'message' => "Matière non autorisée."], 403);
            }
            $matiereNom = $matiere->nom;
        }

        try {
            $resultat = $this->groq->genererExamen($data['contenu_cours'], [
                'langue'      => $data['langue'],
                'niveau'      => $data['niveau'] ?? 'Non spécifié',
                'matiere'     => $matiereNom ?? 'Général',
                'difficulte'  => $data['difficulte'],
                'nb_qcm'      => $data['nb_qcm'],
                'nb_ouvertes' => $data['nb_ouvertes'],
            ]);

            $fichierSource = null;
            if ($request->hasFile('fichier_pdf')) {
                $fichierSource = $request->file('fichier_pdf')->store('examens/sources', 'public');
            }

            $examen = Examen::create([
                'enseignant_id'  => $ens->id, // toujours l'enseignant connecté
                'classe_id'      => $data['classe_id'] ?? null,
                'matiere_id'     => $data['matiere_id'] ?? null,
                'titre'          => $resultat['titre'] ?? 'Examen généré',
                'langue'         => $data['langue'],
                'niveau'         => $data['niveau'] ?? null,
                'difficulte'     => $data['difficulte'],
                'nb_questions'   => $data['nb_qcm'] + $data['nb_ouvertes'],
                'contenu'        => json_encode($resultat, JSON_UNESCAPED_UNICODE),
                'fichier_source' => $fichierSource,
                'statut'         => 'genere',
            ]);

            Journal::log('creation', "a généré l'examen IA « {$examen->titre} »");

            return response()->json([
                'success'   => true,
                'examen_id' => $examen->id,
                'redirect'  => route('enseignant.examens.show', $examen),
            ]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /* ─── SHOW : affichage de l'examen ─────────────────────── */
    public function show(Examen $examen)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens || $examen->enseignant_id !== $ens->id) abort(403);

        $examen->load(['classe', 'matiere']);
        $contenu = json_decode($examen->contenu, true) ?? [];

        return view('enseignant.examens.show', compact('ens', 'examen', 'contenu'));
    }

    /* ─── ENVOYER AUX ÉLÈVES ───────────────────────────────── */
    public function envoyer(Examen $examen)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens || $examen->enseignant_id !== $ens->id) abort(403);

        if (!$examen->classe_id) {
            return back()->withErrors([
                'examen' => "Impossible d'envoyer : aucune classe n'est associée à cet examen. Régénérez-le en sélectionnant une classe.",
            ]);
        }

        $examen->update(['statut' => 'envoye']);

        Journal::log('creation', "a envoyé l'examen « {$examen->titre} » aux élèves de la classe");

        return back()->with('success', "L'examen a été envoyé aux élèves de la classe « {$examen->classe->nom} ». Ils peuvent désormais le consulter depuis leur espace.");
    }

    /* ─── RETIRER (annuler l'envoi) ────────────────────────── */
    public function retirer(Examen $examen)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens || $examen->enseignant_id !== $ens->id) abort(403);

        $examen->update(['statut' => 'genere']);
        Journal::log('modification', "a retiré l'examen « {$examen->titre} » de l'espace élèves");

        return back()->with('success', "L'examen a été retiré de l'espace élèves.");
    }

    /* ─── PDF : réutilise la vue d'impression de l'admin ───── */
    public function pdf(Examen $examen, Request $request)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens || $examen->enseignant_id !== $ens->id) abort(403);

        $examen->load(['classe', 'matiere']);
        $contenu = json_decode($examen->contenu, true) ?? [];
        $avecCorrige = $request->boolean('corrige', false);

        Journal::log('export', "a exporté l'examen « {$examen->titre} » en PDF");

        // On réutilise la vue déjà existante de l'espace admin
        return view('admin.examens.pdf', compact('examen', 'contenu', 'avecCorrige'));
    }

    /* ─── DESTROY ──────────────────────────────────────────── */
    public function destroy(Examen $examen)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens || $examen->enseignant_id !== $ens->id) abort(403);

        if ($examen->fichier_source) {
            Storage::disk('public')->delete($examen->fichier_source);
        }

        $titre = $examen->titre;
        $examen->delete();

        Journal::log('suppression', "a supprimé l'examen « {$titre} »");

        return redirect()->route('enseignant.examens.index')
            ->with('success', "L'examen « {$titre} » a été supprimé.");
    }

    /* ─── COPIES : résultats des élèves pour un examen ─────── */
    public function copies(Examen $examen)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens || $examen->enseignant_id !== $ens->id) abort(403);

        $examen->load(['classe', 'matiere']);
        $contenu = json_decode($examen->contenu, true) ?? [];

        $copies = Copie::where('examen_id', $examen->id)
            ->with('eleve')
            ->latest()
            ->get();

        // Nombre d'élèves de la classe (pour le taux de participation)
        $nbEleves = $examen->classe_id
            ? \App\Models\Eleve::where('classe_id', $examen->classe_id)->where('actif', true)->count()
            : 0;

        return view('enseignant.examens.copies', compact('ens', 'examen', 'contenu', 'copies', 'nbEleves'));
    }

    /* ─── NOTER : attribuer la note finale d'une copie ─────── */
    public function noterCopie(Examen $examen, Copie $copie)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens || $examen->enseignant_id !== $ens->id) abort(403);
        if ($copie->examen_id !== $examen->id) abort(404);

        $data = request()->validate([
            'note_finale' => 'required|numeric|min:0|max:' . (($contenu = json_decode($examen->contenu, true))['bareme_total'] ?? 20),
        ], [
            'note_finale.max' => 'La note ne peut pas dépasser le barème total.',
        ]);

        $copie->update([
            'note_finale' => $data['note_finale'],
            'statut'      => 'corrige',
        ]);

        Journal::log('modification', "a corrigé la copie de {$copie->eleve?->prenom} {$copie->eleve?->nom}");

        return back()->with('success', "Note enregistrée pour {$copie->eleve?->prenom} {$copie->eleve?->nom}.");
    }

    /* ─── RAPPORT IA : générer le rapport d'une copie ──────── */
    public function genererRapport(Examen $examen, Copie $copie)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens || $examen->enseignant_id !== $ens->id) abort(403);
        if ($copie->examen_id !== $examen->id) abort(404);

        if (!$this->groq->isConfigured()) {
            return back()->withErrors(['rapport' => "La clé API IA n'est pas configurée. Contactez l'administrateur."]);
        }

        $contenu = json_decode($examen->contenu, true) ?? [];
        $rep     = $copie->reponses ?? [];
        $repQcm  = $rep['qcm'] ?? [];
        $repOuv  = $rep['ouvertes'] ?? [];

        // Construction d'un résumé textuel de la performance de l'élève
        $lignes   = [];
        $lignes[] = "Examen : " . $examen->titre;
        $lignes[] = "Score QCM : {$copie->score_qcm}/{$copie->bareme_qcm}";
        $lignes[] = "Note finale : " . ($copie->note_finale ?? 'non encore attribuée') . "/" . ($contenu['bareme_total'] ?? 20);
        $lignes[] = "";
        $lignes[] = "Détail des QCM :";
        foreach (($contenu['qcm'] ?? []) as $i => $q) {
            $num     = $q['numero'] ?? ($i + 1);
            $choisi  = $repQcm[$num] ?? null;
            $bonne   = $q['bonne_reponse'] ?? -1;
            $ok      = ($choisi !== null && (int) $choisi === (int) $bonne);
            $choixTx = ($choisi !== null && isset($q['choix'][$choisi])) ? $q['choix'][$choisi] : 'aucune réponse';
            $bonneTx = $q['choix'][$bonne] ?? '';
            $lignes[] = "Q{$num} : " . ($q['question'] ?? '')
                . " | Réponse de l'élève : {$choixTx} | "
                . ($ok ? 'CORRECT' : "INCORRECT (réponse correcte : {$bonneTx})");
        }
        if (!empty($contenu['questions_ouvertes'])) {
            $lignes[] = "";
            $lignes[] = "Questions ouvertes :";
            foreach ($contenu['questions_ouvertes'] as $i => $q) {
                $num    = $q['numero'] ?? ($i + 1);
                $repTxt = $repOuv[$num] ?? '(sans réponse)';
                $lignes[] = "Q{$num} : " . ($q['question'] ?? '')
                    . " | Réponse attendue : " . ($q['reponse_attendue'] ?? '')
                    . " | Réponse de l'élève : {$repTxt}";
            }
        }
        $performance = implode("\n", $lignes);

        try {
            $rapport = $this->groq->genererRapportEleve($performance, [
                'langue'  => $examen->langue,
                'eleve'   => trim(($copie->eleve->prenom ?? '') . ' ' . ($copie->eleve->nom ?? '')),
                'matiere' => $contenu['matiere'] ?? $examen->matiere->nom ?? '',
                'niveau'  => $examen->niveau ?? '',
            ]);

            $copie->update(['rapport' => $rapport]);

            Journal::log('creation', "a généré un rapport IA pour {$copie->eleve?->prenom} {$copie->eleve?->nom}");

            return back()->with('success', "Rapport généré pour {$copie->eleve?->prenom} {$copie->eleve?->nom}.");
        } catch (Exception $e) {
            return back()->withErrors(['rapport' => 'Échec de la génération du rapport : ' . $e->getMessage()]);
        }
    }

    /* ─── RAPPORT : transmettre / retirer au parent ────────── */
    public function basculerRapportParent(Examen $examen, Copie $copie)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens || $examen->enseignant_id !== $ens->id) abort(403);
        if ($copie->examen_id !== $examen->id) abort(404);

        if (empty($copie->rapport)) {
            return back()->withErrors(['rapport' => "Générez d'abord le rapport avant de le transmettre au parent."]);
        }

        $nouvelEtat = !$copie->rapport_envoye_parent;
        $copie->update(['rapport_envoye_parent' => $nouvelEtat]);

        // À la transmission, on notifie le parent : in-app (toujours) + email (best-effort).
        $infoMail = '';
        if ($nouvelEtat) {
            $parent = $copie->eleve?->parent;

            // (1) Notification DANS l'application, visible dans l'espace parent
            if ($parent && $parent->user_id) {
                $parentUser = User::find($parent->user_id);
                if ($parentUser) {
                    try {
                        $parentUser->notify(new RapportPartageNotification($copie));
                    } catch (\Throwable $e) {
                        Log::error('Notification in-app rapport échouée', ['message' => $e->getMessage()]);
                    }
                }
            }

            // (2) Email best-effort si une adresse parent est renseignee
            $emailParent = $parent?->email;
            if ($emailParent) {
                try {
                    Mail::to($emailParent)->queue(new RapportEleveMail($copie, $parent->prenom ?? 'Madame, Monsieur'));
                    $infoMail = " Le parent a reçu une notification dans son espace" . ($emailParent ? " et un email à {$emailParent}." : ".");
                } catch (\Throwable $e) {
                    Log::error('Envoi email rapport échoué', ['message' => $e->getMessage()]);
                    $infoMail = " Le parent a reçu une notification dans son espace.";
                }
            } else {
                $infoMail = " Le parent a reçu une notification dans son espace.";
            }
        }

        return back()->with('success', $nouvelEtat
            ? "Le rapport a été transmis au parent de {$copie->eleve?->prenom} {$copie->eleve?->nom}." . $infoMail
            : "Le rapport a été retiré de l'espace parent.");
    }

    /* ─── RAPPORT DE CLASSE IA ─────────────────────────────── */
    public function genererRapportClasse(Examen $examen)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens || $examen->enseignant_id !== $ens->id) abort(403);

        if (!$this->groq->isConfigured()) {
            return back()->withErrors(['rapport_classe' => "La clé API IA n'est pas configurée. Contactez l'administrateur."]);
        }

        $copies = Copie::where('examen_id', $examen->id)->get();
        if ($copies->isEmpty()) {
            return back()->withErrors(['rapport_classe' => "Aucune copie reçue : impossible de générer une synthèse de classe."]);
        }

        $contenu = json_decode($examen->contenu, true) ?? [];
        $qcm     = $contenu['qcm'] ?? [];
        $nb      = $copies->count();

        // Moyenne (note finale si attribuée, sinon score QCM)
        $moyenne = round($copies->map(fn($c) => $c->note_finale ?? $c->score_qcm ?? 0)->avg(), 2);

        $lignes   = [];
        $lignes[] = "Examen : " . $examen->titre;
        $lignes[] = "Nombre de copies : {$nb}";
        $lignes[] = "Moyenne de la classe : {$moyenne}/" . ($contenu['bareme_total'] ?? 20);
        $lignes[] = "";
        $lignes[] = "Taux de réussite par question QCM :";

        foreach ($qcm as $i => $q) {
            $num   = $q['numero'] ?? ($i + 1);
            $bonne = $q['bonne_reponse'] ?? -1;
            $correct = 0;
            foreach ($copies as $c) {
                $rep = $c->reponses['qcm'][$num] ?? null;
                if ($rep !== null && (int) $rep === (int) $bonne) $correct++;
            }
            $taux = $nb > 0 ? round($correct / $nb * 100) : 0;
            $lignes[] = "Q{$num} (" . ($q['question'] ?? '') . ") : {$taux}% de réussite ({$correct}/{$nb})";
        }

        if (!empty($contenu['questions_ouvertes'])) {
            $lignes[] = "";
            $lignes[] = "Questions ouvertes de l'examen (corrigées manuellement) :";
            foreach ($contenu['questions_ouvertes'] as $i => $q) {
                $num = $q['numero'] ?? ($i + 1);
                $lignes[] = "Q{$num} : " . ($q['question'] ?? '');
            }
        }

        $statistiques = implode("\n", $lignes);

        try {
            $rapport = $this->groq->genererRapportClasse($statistiques, [
                'langue'  => $examen->langue,
                'matiere' => $contenu['matiere'] ?? $examen->matiere->nom ?? '',
                'niveau'  => $examen->niveau ?? '',
                'classe'  => $examen->classe->nom ?? '',
            ]);

            $examen->update(['rapport_classe' => $rapport]);

            Journal::log('creation', "a généré un rapport de classe IA pour l'examen « {$examen->titre} »");

            return back()->with('success', "Rapport de classe généré.");
        } catch (Exception $e) {
            return back()->withErrors(['rapport_classe' => 'Échec de la génération : ' . $e->getMessage()]);
        }
    }

    /* ─── AJAX : matières d'une classe (limité à l'enseignant) ─ */
    public function matieresParClasse(Classe $classe)
    {
        $ens = DashboardController::enseignantActuel();
        if (!$ens) abort(403);

        return response()->json(
            Matiere::where('classe_id', $classe->id)
                ->where('enseignant_id', $ens->id)
                ->get()
                ->map(fn($m) => ['id' => $m->id, 'nom' => $m->nom])
        );
    }
}

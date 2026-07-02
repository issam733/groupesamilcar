<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Examen;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Enseignant;
use App\Models\Journal;
use App\Services\GroqService;
use Illuminate\Support\Facades\Storage;
use Exception;

class ExamenController extends Controller
{
    private GroqService $groq;

    public function __construct(GroqService $groq)
    {
        $this->groq = $groq;
    }

    /* ─── INDEX : historique des examens ────────────────────── */
    public function index(Request $request)
    {
        $query = Examen::with(['classe', 'matiere', 'enseignant']);

        if ($classeId = $request->get('classe_id')) {
            $query->where('classe_id', $classeId);
        }
        if ($langue = $request->get('langue')) {
            $query->where('langue', $langue);
        }
        if ($search = $request->get('search')) {
            $query->where('titre', 'like', "%$search%");
        }

        $examens = $query->latest()->paginate(15)->withQueryString();
        $classes = Classe::where('active', true)->orderBy('nom')->get();

        $stats = [
            'total'     => Examen::count(),
            'ce_mois'   => Examen::whereMonth('created_at', now()->month)->count(),
            'fr'        => Examen::where('langue', 'fr')->count(),
            'ar'        => Examen::where('langue', 'ar')->count(),
            'en'        => Examen::where('langue', 'en')->count(),
        ];

        return view('admin.examens.index', compact('examens', 'classes', 'stats'));
    }

    /* ─── CREATE : formulaire de génération ─────────────────── */
    public function create()
    {
        $classes     = Classe::where('active', true)->with('matieres')->orderBy('nom')->get();
        $enseignants = Enseignant::where('actif', true)->orderBy('nom')->get();
        $apiConfiguree = $this->groq->isConfigured();

        return view('admin.examens.create', compact('classes', 'enseignants', 'apiConfiguree'));
    }

    /* ─── GENERER : appel IA (AJAX) ──────────────────────────── */
    public function generer(Request $request)
    {
        $data = $request->validate([
            'classe_id'      => 'nullable|exists:classes,id',
            'matiere_id'     => 'nullable|exists:matieres,id',
            'enseignant_id'  => 'nullable|exists:enseignants,id',
            'matiere_nom'    => 'nullable|string|max:100',
            'niveau'         => 'nullable|string|max:100',
            'langue'         => 'required|in:fr,ar,en',
            'difficulte'     => 'required|in:facile,moyen,difficile',
            'nb_qcm'         => 'required|integer|min:0|max:30',
            'nb_ouvertes'    => 'required|integer|min:0|max:15',
            'contenu_cours'  => 'required|string|min:50',
            'fichier_pdf'    => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'contenu_cours.required' => 'Veuillez fournir le contenu du cours (texte collé ou extrait du PDF).',
            'contenu_cours.min'      => 'Le contenu du cours est trop court pour générer un examen pertinent.',
        ]);

        try {
            $matiereNom = $data['matiere_nom'] ?? null;
            if ($data['matiere_id'] ?? null) {
                $matiereNom = Matiere::find($data['matiere_id'])?->nom ?? $matiereNom;
            }

            $resultat = $this->groq->genererExamen($data['contenu_cours'], [
                'langue'      => $data['langue'],
                'niveau'      => $data['niveau'] ?? 'Non spécifié',
                'matiere'     => $matiereNom ?? 'Général',
                'difficulte'  => $data['difficulte'],
                'nb_qcm'      => $data['nb_qcm'],
                'nb_ouvertes' => $data['nb_ouvertes'],
            ]);

            // Sauvegarder le fichier PDF source si fourni
            $fichierSource = null;
            if ($request->hasFile('fichier_pdf')) {
                $fichierSource = $request->file('fichier_pdf')->store('examens/sources', 'public');
            }

            $examen = Examen::create([
                'enseignant_id'  => $data['enseignant_id'] ?? null,
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
                'success'    => true,
                'examen_id'  => $examen->id,
                'redirect'   => route('admin.examens.show', $examen),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /* ─── SHOW : affichage de l'examen généré ───────────────── */
    public function show(Examen $examen)
    {
        $examen->load(['classe', 'matiere', 'enseignant']);
        $contenu = json_decode($examen->contenu, true) ?? [];

        return view('admin.examens.show', compact('examen', 'contenu'));
    }

    /* ─── PDF : version imprimable (sujet + corrigé) ────────── */
    public function pdf(Examen $examen, Request $request)
    {
        $examen->load(['classe', 'matiere', 'enseignant']);
        $contenu = json_decode($examen->contenu, true) ?? [];
        $avecCorrige = $request->boolean('corrige', false);

        Journal::log('export', "a exporté l'examen « {$examen->titre} » en PDF" . ($avecCorrige ? ' (avec corrigé)' : ''));

        return view('admin.examens.pdf', compact('examen', 'contenu', 'avecCorrige'));
    }

    /* ─── DESTROY ────────────────────────────────────────────── */
    public function destroy(Examen $examen)
    {
        if ($examen->fichier_source) {
            Storage::disk('public')->delete($examen->fichier_source);
        }

        $titre = $examen->titre;
        $examen->delete();

        Journal::log('suppression', "a supprimé l'examen « {$titre} »");

        return redirect()->route('admin.examens.index')
            ->with('success', "L'examen « {$titre} » a été supprimé.");
    }

    /* ─── AJAX : récupérer les matières d'une classe ────────── */
    public function matieresParClasse(Classe $classe)
    {
        return response()->json(
            $classe->matieres()->with('enseignant')->get()->map(fn($m) => [
                'id'   => $m->id,
                'nom'  => $m->nom,
                'enseignant_id' => $m->enseignant_id,
            ])
        );
    }
}

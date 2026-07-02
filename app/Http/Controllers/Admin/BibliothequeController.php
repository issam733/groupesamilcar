<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ressource;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Enseignant;
use App\Models\Journal;
use Illuminate\Support\Facades\Storage;

class BibliothequeController extends Controller
{
    /* ─── INDEX : vue organisée par niveau > matière ────────── */
    public function index(Request $request)
    {
        $query = Ressource::with(['classe', 'matiere', 'enseignant']);

        if ($niveau = $request->get('niveau')) {
            $query->whereHas('classe', fn($q) => $q->where('niveau', $niveau));
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($search = $request->get('search')) {
            $query->where('titre', 'like', "%$search%");
        }

        $ressources = $query->latest()->get();

        // Organisation hiérarchique : Niveau > Matière > Ressources
        $arbre = [];
        foreach ($ressources as $r) {
            $niveau = $r->classe->niveau ?? 'Général';
            $matiere = $r->matiere->nom ?? 'Non classé';
            $arbre[$niveau][$matiere][] = $r;
        }

        $stats = [
            'total'  => Ressource::count(),
            'pdf'    => Ressource::where('type', 'pdf')->count(),
            'video'  => Ressource::where('type', 'video')->count(),
            'lien'   => Ressource::where('type', 'lien')->count(),
        ];

        $classes = Classe::where('active', true)->orderBy('nom')->get();

        return view('admin.bibliotheque.index', compact('arbre', 'stats', 'classes'));
    }

    /* ─── UPLOAD : ajout d'une ressource ─────────────────────── */
    public function upload(Request $request)
    {
        $data = $request->validate([
            'titre'         => 'required|string|max:255',
            'type'          => 'required|in:pdf,video,lien,autre',
            'classe_id'     => 'nullable|exists:classes,id',
            'matiere_id'    => 'nullable|exists:matieres,id',
            'enseignant_id' => 'nullable|exists:enseignants,id',
            'niveau'        => 'nullable|string|max:50',
            'fichier'       => 'required_if:type,pdf,autre|file|max:20480',
            'lien_externe'  => 'required_if:type,video,lien|nullable|url',
        ], [
            'titre.required'        => 'Le titre est obligatoire.',
            'fichier.required_if'   => 'Veuillez sélectionner un fichier pour ce type de ressource.',
            'lien_externe.required_if' => 'Veuillez fournir un lien valide pour ce type de ressource.',
            'lien_externe.url'      => 'Le lien fourni n\'est pas une URL valide.',
        ]);

        if ($request->hasFile('fichier')) {
            $data['fichier'] = $request->file('fichier')->store('bibliotheque', 'public');
        }

        if ($classeId = ($data['classe_id'] ?? null)) {
            $data['niveau'] = Classe::find($classeId)?->niveau;
        }

        $ressource = Ressource::create($data);

        Journal::log('creation', "a ajouté la ressource « {$ressource->titre} » à la bibliothèque");

        return redirect()->route('admin.bibliotheque.index')
            ->with('success', "La ressource « {$ressource->titre} » a été ajoutée.");
    }

    /* ─── DESTROY ────────────────────────────────────────────── */
    public function destroy(Ressource $ressource)
    {
        if ($ressource->fichier) {
            Storage::disk('public')->delete($ressource->fichier);
        }

        $titre = $ressource->titre;
        $ressource->delete();

        Journal::log('suppression', "a supprimé la ressource « {$titre} » de la bibliothèque");

        return redirect()->route('admin.bibliotheque.index')
            ->with('success', "La ressource « {$titre} » a été supprimée.");
    }

    /* ─── AJAX : matières d'une classe (pour formulaire) ────── */
    public function matieresParClasse(Classe $classe)
    {
        return response()->json(
            $classe->matieres()->get()->map(fn($m) => ['id' => $m->id, 'nom' => $m->nom])
        );
    }
}

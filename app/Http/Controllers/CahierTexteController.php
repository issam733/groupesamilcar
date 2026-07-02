<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CahierTexte;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\ParentEleve;
use App\Http\Controllers\Enseignant\DashboardController as EnseignantDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CahierTexteController extends Controller
{
    private function layout(): string
    {
        return [
            'admin'      => 'admin.layouts.app',
            'enseignant' => 'enseignant.layouts.app',
            'parent'     => 'parent.layouts.app',
            'eleve'      => 'eleve.layouts.app',
        ][Auth::user()->role] ?? 'eleve.layouts.app';
    }

    private function enseignantCourant(): ?Enseignant
    {
        return Enseignant::where('user_id', Auth::id())->first();
    }

    /* ─────────────── CONSULTATION / GESTION (index) ─────────────── */
    public function index(Request $request)
    {
        $user   = Auth::user();
        $layout = $this->layout();

        /* ---- ENSEIGNANT : gestion de ses entrées ---- */
        if ($user->role === 'enseignant') {
            $ens = $this->enseignantCourant();
            if (!$ens) abort(403, "Aucun profil enseignant associé à ce compte.");

            $query = CahierTexte::where('enseignant_id', $ens->id)->with(['classe', 'matiere']);
            if ($request->filled('matiere_id')) {
                $query->where('matiere_id', $request->matiere_id);
            }
            $entrees  = $query->orderByDesc('date_cours')->orderByDesc('id')->limit(200)->get();
            $matieres = Matiere::where('enseignant_id', $ens->id)->with('classe')->get();

            return view('cahier.gestion', compact('layout', 'entrees', 'matieres') + ['matiereId' => $request->matiere_id]);
        }

        /* ---- CONSULTATION (élève / parent / admin) ---- */
        $classeIds = null;      // null = toutes (admin)
        $enfants   = collect();
        $eleveSel  = null;

        if ($user->role === 'eleve') {
            $eleve = Eleve::where('user_id', $user->id)->first();
            $classeIds = $eleve ? [$eleve->classe_id] : [];
        } elseif ($user->role === 'parent') {
            $parent  = ParentEleve::where('user_id', $user->id)->first();
            $enfants = $parent ? Eleve::where('parent_id', $parent->id)->where('actif', true)->get() : collect();

            $eleveSel = $request->filled('eleve_id')
                ? $enfants->firstWhere('id', (int) $request->eleve_id)
                : $enfants->first();

            $classeIds = $eleveSel ? [$eleveSel->classe_id] : [];
        }

        $query = CahierTexte::with(['classe', 'matiere', 'enseignant']);
        if (is_array($classeIds)) {
            $query->whereIn('classe_id', $classeIds ?: [0]);
        }
        // Filtre classe pour l'admin
        $classesAdmin = collect();
        if ($user->role === 'admin') {
            $classesAdmin = Classe::where('active', true)->orderBy('nom')->get();
            if ($request->filled('classe_id')) {
                $query->where('classe_id', $request->classe_id);
            }
        }

        $entrees = $query->orderByDesc('date_cours')->orderByDesc('id')->limit(200)->get();

        return view('cahier.consultation', [
            'layout'       => $layout,
            'entrees'      => $entrees,
            'enfants'      => $enfants,
            'eleveSel'     => $eleveSel,
            'classesAdmin' => $classesAdmin,
            'classeId'     => $request->classe_id,
            'role'         => $user->role,
        ]);
    }

    /* ─────────────── ENSEIGNANT : créer ─────────────── */
    public function create()
    {
        $ens = $this->enseignantCourant();
        if (!$ens) abort(403);

        $matieres = Matiere::where('enseignant_id', $ens->id)->with('classe')->get();

        return view('cahier.form', [
            'layout'   => $this->layout(),
            'matieres' => $matieres,
            'entree'   => null,
        ]);
    }

    public function store(Request $request)
    {
        $ens = $this->enseignantCourant();
        if (!$ens) abort(403);

        $data = $this->valider($request, $ens);

        if ($request->hasFile('fichier')) {
            $data['fichier'] = $request->file('fichier')->store('cahier', 'public');
        }

        CahierTexte::create($data);

        return redirect()->route('cahier.index')->with('success', 'Entrée du cahier de texte ajoutée.');
    }

    /* ─────────────── ENSEIGNANT : modifier ─────────────── */
    public function edit(CahierTexte $cahier)
    {
        $ens = $this->enseignantCourant();
        if (!$ens || $cahier->enseignant_id !== $ens->id) abort(403);

        $matieres = Matiere::where('enseignant_id', $ens->id)->with('classe')->get();

        return view('cahier.form', [
            'layout'   => $this->layout(),
            'matieres' => $matieres,
            'entree'   => $cahier,
        ]);
    }

    public function update(Request $request, CahierTexte $cahier)
    {
        $ens = $this->enseignantCourant();
        if (!$ens || $cahier->enseignant_id !== $ens->id) abort(403);

        $data = $this->valider($request, $ens);

        // Remplacement du fichier
        if ($request->hasFile('fichier')) {
            if ($cahier->fichier) {
                Storage::disk('public')->delete($cahier->fichier);
            }
            $data['fichier'] = $request->file('fichier')->store('cahier', 'public');
        } elseif ($request->boolean('supprimer_fichier') && $cahier->fichier) {
            // Suppression demandée sans remplacement
            Storage::disk('public')->delete($cahier->fichier);
            $data['fichier'] = null;
        }

        $cahier->update($data);

        return redirect()->route('cahier.index')->with('success', 'Entrée mise à jour.');
    }

    public function destroy(CahierTexte $cahier)
    {
        $ens = $this->enseignantCourant();
        if (!$ens || $cahier->enseignant_id !== $ens->id) abort(403);

        if ($cahier->fichier) {
            Storage::disk('public')->delete($cahier->fichier);
        }
        $cahier->delete();

        return redirect()->route('cahier.index')->with('success', 'Entrée supprimée.');
    }

    /* ─────────────── Validation commune ─────────────── */
    private function valider(Request $request, Enseignant $ens): array
    {
        $data = $request->validate([
            'matiere_id'  => 'required|integer|exists:matieres,id',
            'date_cours'  => 'required|date',
            'contenu'     => 'required|string|max:5000',
            'devoirs'     => 'nullable|string|max:5000',
            'date_remise' => 'nullable|date|after_or_equal:date_cours',
            'fichier'     => 'nullable|file|max:20480|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip',
        ], [
            'date_remise.after_or_equal' => 'La date de remise doit être postérieure à la date du cours.',
            'fichier.max'                => 'Le fichier ne doit pas dépasser 20 Mo.',
            'fichier.mimes'              => 'Format accepté : PDF, Word, PowerPoint, Excel, image ou ZIP.',
        ]);

        // La matière doit être enseignée par cet enseignant
        $matiere = Matiere::where('id', $data['matiere_id'])
            ->where('enseignant_id', $ens->id)
            ->first();
        if (!$matiere) abort(403, "Cette matière ne vous est pas attribuée.");

        return [
            'matiere_id'    => $matiere->id,
            'classe_id'     => $matiere->classe_id,   // dérivée de la matière
            'enseignant_id' => $ens->id,
            'date_cours'    => $data['date_cours'],
            'contenu'       => $data['contenu'],
            'devoirs'       => $data['devoirs'] ?? null,
            'date_remise'   => $data['date_remise'] ?? null,
        ];
    }
}

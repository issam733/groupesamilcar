<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\EmploiDuTemps;
use App\Models\Matiere;
use App\Models\Journal;

class EmploiController extends Controller
{
    /* ─── INDEX : liste des classes pour choisir ───────────── */
    public function index()
    {
        $classes = Classe::where('active', true)
            ->withCount('emplois')
            ->orderByRaw("FIELD(niveau,'Préparatoire','Primaire','Collège','Lycée')")
            ->orderBy('nom')
            ->get();

        return view('admin.emplois.index', compact('classes'));
    }

    /* ─── SHOW : grille emploi du temps d'une classe ───────── */
    public function show(Classe $classe)
    {
        $classe->load(['matieres.enseignant', 'emplois.matiere', 'emplois.enseignant']);

        $jours  = EmploiDuTemps::jours();
        $creneaux = $this->genererCreneaux();

        // Construire la grille [jour][heure_debut] = créneau
        $grille = [];
        foreach ($classe->emplois as $creneau) {
            $jour = $creneau->jour;
            $heure = substr($creneau->heure_debut, 0, 5);
            $grille[$jour][$heure] = $creneau;
        }

        return view('admin.emplois.show', compact('classe', 'jours', 'creneaux', 'grille'));
    }

    /* ─── STORE : ajouter/mettre à jour un créneau (AJAX) ──── */
    public function store(Request $request)
    {
        $data = $request->validate([
            'classe_id'     => 'required|exists:classes,id',
            'matiere_id'    => 'required|exists:matieres,id',
            'enseignant_id' => 'nullable|exists:enseignants,id',
            'jour'          => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
            'heure_debut'   => 'required|date_format:H:i',
            'heure_fin'     => 'required|date_format:H:i|after:heure_debut',
        ]);

        // Vérifier conflit : même classe, même jour, même créneau horaire
        $conflit = EmploiDuTemps::where('classe_id', $data['classe_id'])
            ->where('jour', $data['jour'])
            ->where('heure_debut', $data['heure_debut'])
            ->first();

        if ($conflit) {
            $conflit->update($data);
            $creneau = $conflit;
        } else {
            $creneau = EmploiDuTemps::create($data);
        }

        Journal::log('modification', "a mis à jour l'emploi du temps");

        return response()->json([
            'success' => true,
            'creneau' => $creneau->load(['matiere', 'enseignant']),
        ]);
    }

    /* ─── DESTROY : supprimer un créneau (AJAX) ────────────── */
    public function destroy(EmploiDuTemps $emploi)
    {
        $emploi->delete();
        return response()->json(['success' => true]);
    }

    /* ─── Génère les créneaux horaires standards ───────────── */
    private function genererCreneaux(): array
    {
        return [
            ['debut' => '08:00', 'fin' => '09:00'],
            ['debut' => '09:00', 'fin' => '10:00'],
            ['debut' => '10:00', 'fin' => '11:00'],
            ['debut' => '11:00', 'fin' => '12:00'],
            ['debut' => '12:00', 'fin' => '13:00', 'pause' => true],
            ['debut' => '13:00', 'fin' => '14:00'],
            ['debut' => '14:00', 'fin' => '15:00'],
            ['debut' => '15:00', 'fin' => '16:00'],
            ['debut' => '16:00', 'fin' => '17:00'],
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Annonce;
use App\Models\User;
use App\Models\Journal;
use App\Mail\NouvelleAnnonceMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AnnonceController extends Controller
{
    /* ─── INDEX ─────────────────────────────────────────────── */
    public function index(Request $request)
    {
        $query = Annonce::with('auteur');

        if ($cible = $request->get('cible')) {
            $query->where('cible', $cible);
        }
        if ($search = $request->get('search')) {
            $query->where('titre', 'like', "%$search%");
        }

        $annonces = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'       => Annonce::count(),
            'publiees'    => Annonce::where('publie', true)->count(),
            'brouillons'  => Annonce::where('publie', false)->count(),
            'ce_mois'     => Annonce::whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.annonces.index', compact('annonces', 'stats'));
    }

    /* ─── CREATE ────────────────────────────────────────────── */
    public function create()
    {
        return view('admin.annonces.create');
    }

    /* ─── STORE : crée + déclenche l'envoi email ────────────── */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'           => 'required|string|max:255',
            'contenu'         => 'required|string|min:10',
            'cible'           => 'required|in:all,enseignants,parents,eleves',
            'publie'          => 'nullable|boolean',
            'envoyer_email'   => 'nullable|boolean',
        ], [
            'titre.required'   => 'Le titre est obligatoire.',
            'contenu.required' => 'Le contenu est obligatoire.',
            'contenu.min'      => 'Le contenu est trop court.',
        ]);

        $envoyerEmail = $request->boolean('envoyer_email');
        unset($data['envoyer_email']);

        $data['publie']     = $request->boolean('publie', true);
        $data['created_by'] = auth()->id();

        $annonce = Annonce::create($data);

        Journal::log('creation', "a publié l'annonce « {$annonce->titre} » (cible: {$annonce->cible})");

        $nbEnvoyes = 0;
        if ($envoyerEmail && $annonce->publie) {
            $nbEnvoyes = $this->envoyerNotifications($annonce);
        }

        $message = "L'annonce « {$annonce->titre} » a été créée.";
        if ($envoyerEmail) {
            $message .= $nbEnvoyes > 0
                ? " {$nbEnvoyes} notification(s) email envoyée(s)."
                : " Aucun destinataire avec email trouvé pour cette cible.";
        }

        return redirect()->route('admin.annonces.index')->with('success', $message);
    }

    /* ─── EDIT ──────────────────────────────────────────────── */
    public function edit(Annonce $annonce)
    {
        return view('admin.annonces.edit', compact('annonce'));
    }

    /* ─── UPDATE ────────────────────────────────────────────── */
    public function update(Request $request, Annonce $annonce)
    {
        $data = $request->validate([
            'titre'   => 'required|string|max:255',
            'contenu' => 'required|string|min:10',
            'cible'   => 'required|in:all,enseignants,parents,eleves',
            'publie'  => 'nullable|boolean',
        ]);

        $data['publie'] = $request->boolean('publie', true);
        $annonce->update($data);

        Journal::log('modification', "a modifié l'annonce « {$annonce->titre} »");

        return redirect()->route('admin.annonces.index')->with('success', "L'annonce a été mise à jour.");
    }

    /* ─── DESTROY ────────────────────────────────────────────── */
    public function destroy(Annonce $annonce)
    {
        $titre = $annonce->titre;
        $annonce->delete();

        Journal::log('suppression', "a supprimé l'annonce « {$titre} »");

        return redirect()->route('admin.annonces.index')->with('success', "L'annonce « {$titre} » a été supprimée.");
    }

    /* ─── RENVOYER les notifications pour une annonce existante ─ */
    public function renvoyerEmail(Annonce $annonce)
    {
        $nb = $this->envoyerNotifications($annonce);

        Journal::log('export', "a renvoyé les notifications pour l'annonce « {$annonce->titre} »");

        return back()->with('success', "{$nb} notification(s) email envoyée(s) ou mise(s) en file d'attente.");
    }

    /**
     * Détermine les destinataires selon la cible et envoie l'email.
     * Utilise Mail::queue() pour ne pas bloquer la requête HTTP si
     * la file d'attente (queue) est configurée ; sinon Laravel l'envoie
     * de façon synchrone avec le driver 'sync' par défaut.
     */
    private function envoyerNotifications(Annonce $annonce): int
    {
        $roles = match($annonce->cible) {
            'enseignants' => ['enseignant'],
            'parents'     => ['parent'],
            'eleves'      => ['eleve'],
            default       => ['enseignant', 'parent', 'eleve'], // 'all'
        };

        $destinataires = User::whereIn('role', $roles)
            ->where('actif', true)
            ->whereNotNull('email')
            ->get();

        $count = 0;
        foreach ($destinataires as $user) {
            try {
                Mail::to($user->email)->queue(new NouvelleAnnonceMail($annonce, $user));
                $count++;
            } catch (\Exception $e) {
                Log::error('Échec envoi email annonce', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }
}

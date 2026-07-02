<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\ParentEleve;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class MessagerieController extends Controller
{
    /** Layout à utiliser selon le rôle de l'utilisateur connecté. */
    private function layout(): string
    {
        return [
            'admin'      => 'admin.layouts.app',
            'enseignant' => 'enseignant.layouts.app',
            'parent'     => 'parent.layouts.app',
            'eleve'      => 'eleve.layouts.app',
        ][Auth::user()->role] ?? 'eleve.layouts.app';
    }

    /**
     * Liste des utilisateurs à qui l'utilisateur courant a le droit d'écrire.
     * - admin / enseignant : tout le monde
     * - parent / eleve     : uniquement enseignants + administration
     */
    private function destinatairesAutorises(User $u): Collection
    {
        $query = User::where('id', '!=', $u->id)->where('actif', true);

        if (!in_array($u->role, ['admin', 'enseignant'])) {
            $query->whereIn('role', ['enseignant', 'admin']);
        }

        return $query->orderByRaw("FIELD(role,'admin','enseignant','parent','eleve')")
            ->orderBy('nom')->orderBy('prenom')
            ->get();
    }

    private function peutEcrireA(User $from, User $to): bool
    {
        if ($from->id === $to->id) return false;
        return $this->destinatairesAutorises($from)->contains('id', $to->id);
    }

    /* ─── BOÎTE DE RÉCEPTION ───────────────────────────────── */
    public function index()
    {
        $user = Auth::user();

        $conversations = Conversation::pour($user->id)
            ->with(['userUn', 'userDeux', 'dernierMessage'])
            ->withCount(['messages as non_lus_count' => function ($q) use ($user) {
                $q->where('expediteur_id', '!=', $user->id)->whereNull('lu_at');
            }])
            ->orderByDesc('dernier_message_at')
            ->orderByDesc('updated_at')
            ->get();

        return view('messagerie.index', [
            'layout'        => $this->layout(),
            'user'          => $user,
            'conversations' => $conversations,
        ]);
    }

    /* ─── NOUVEAU MESSAGE / DIFFUSION ──────────────────────── */
    public function nouveau()
    {
        $user = Auth::user();
        $destinataires = $this->destinatairesAutorises($user)->groupBy('role');

        // Diffusion par classe réservée à l'admin et à l'enseignant
        $classes = collect();
        if (in_array($user->role, ['admin', 'enseignant'])) {
            $classes = Classe::orderBy('nom')->get(['id', 'nom']);
        }

        return view('messagerie.nouveau', [
            'layout'        => $this->layout(),
            'user'          => $user,
            'destinataires' => $destinataires,
            'classes'       => $classes,
        ]);
    }

    /**
     * Étend une sélection de classes + audience en une liste d'IDs utilisateurs.
     */
    private function expanseClasses(array $classeIds, string $audience, bool $toutes): array
    {
        $eleveQuery = Eleve::query()->where('actif', true);
        if (!$toutes) {
            if (empty($classeIds)) return [];
            $eleveQuery->whereIn('classe_id', $classeIds);
        }
        $eleves = $eleveQuery->get(['id', 'user_id', 'parent_id']);

        $userIds = [];

        if (in_array($audience, ['eleves', 'tous'])) {
            foreach ($eleves as $e) {
                if ($e->user_id) $userIds[] = (int) $e->user_id;
            }
        }

        if (in_array($audience, ['parents', 'tous'])) {
            $parentIds = $eleves->pluck('parent_id')->filter()->unique()->all();
            if ($parentIds) {
                $parentUserIds = ParentEleve::whereIn('id', $parentIds)
                    ->whereNotNull('user_id')
                    ->pluck('user_id');
                foreach ($parentUserIds as $uid) $userIds[] = (int) $uid;
            }
        }

        return array_values(array_unique($userIds));
    }

    public function envoyer(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'destinataires'   => 'nullable|array',
            'destinataires.*' => 'integer|exists:users,id',
            'classe_ids'      => 'nullable|array',
            'classe_ids.*'    => 'integer',
            'audience'        => 'nullable|in:eleves,parents,tous',
            'toutes_classes'  => 'nullable',
            'corps'           => 'required|string|max:5000',
        ], [
            'corps.required' => 'Le message ne peut pas être vide.',
        ]);

        // Destinataires choisis individuellement
        $cibleIds = $data['destinataires'] ?? [];

        // Expansion par classe (admin / enseignant uniquement)
        if (in_array($user->role, ['admin', 'enseignant'])) {
            $toutes    = !empty($data['toutes_classes']);
            $classeIds = $data['classe_ids'] ?? [];
            $audience  = $data['audience'] ?? 'tous';

            if ($toutes || !empty($classeIds)) {
                $cibleIds = array_merge($cibleIds, $this->expanseClasses($classeIds, $audience, $toutes));
            }
        }

        $cibleIds = array_values(array_unique(array_map('intval', $cibleIds)));

        if (empty($cibleIds)) {
            return back()->withErrors(['destinataires' => "Choisissez au moins un destinataire (des personnes et/ou une classe)."])->withInput();
        }

        $envoyes = 0;
        $derniereConv = null;

        foreach ($cibleIds as $toId) {
            $to = User::find($toId);
            if (!$to || !$this->peutEcrireA($user, $to)) {
                continue; // on ignore silencieusement un destinataire non autorisé
            }

            $conv = Conversation::entre($user->id, $to->id);
            Message::create([
                'conversation_id' => $conv->id,
                'expediteur_id'   => $user->id,
                'corps'           => $data['corps'],
            ]);
            $conv->update(['dernier_message_at' => now()]);

            $derniereConv = $conv;
            $envoyes++;
        }

        if ($envoyes === 0) {
            return back()->withErrors(['destinataires' => "Aucun destinataire autorisé dans votre sélection."])->withInput();
        }

        if ($envoyes === 1) {
            return redirect()->route('messagerie.show', $derniereConv)->with('success', 'Message envoyé.');
        }

        return redirect()->route('messagerie.index')->with('success', "Message envoyé à {$envoyes} destinataires.");
    }

    /* ─── FIL DE CONVERSATION ──────────────────────────────── */
    public function show(Conversation $conversation)
    {
        $user = Auth::user();
        if (!$conversation->participe($user->id)) abort(403);

        // Marquer comme lus les messages reçus
        Message::where('conversation_id', $conversation->id)
            ->where('expediteur_id', '!=', $user->id)
            ->whereNull('lu_at')
            ->update(['lu_at' => now()]);

        $conversation->load(['messages.expediteur']);
        $autre = $conversation->autre($user->id);

        return view('messagerie.show', [
            'layout'       => $this->layout(),
            'user'         => $user,
            'conversation' => $conversation,
            'autre'        => $autre,
        ]);
    }

    public function repondre(Request $request, Conversation $conversation)
    {
        $user = Auth::user();
        if (!$conversation->participe($user->id)) abort(403);

        $autre = $conversation->autre($user->id);
        if (!$autre || !$this->peutEcrireA($user, $autre)) {
            abort(403, "Vous n'êtes pas autorisé à écrire à ce destinataire.");
        }

        $data = $request->validate(['corps' => 'required|string|max:5000']);

        Message::create([
            'conversation_id' => $conversation->id,
            'expediteur_id'   => $user->id,
            'corps'           => $data['corps'],
        ]);
        $conversation->update(['dernier_message_at' => now()]);

        return redirect()->route('messagerie.show', $conversation)->with('success', 'Message envoyé.');
    }
}

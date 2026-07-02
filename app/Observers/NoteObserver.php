<?php

namespace App\Observers;

use App\Models\Note;
use App\Mail\NouvelleNoteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NoteObserver
{
    /**
     * Déclenché automatiquement à chaque création d'une note.
     * Envoie un email au parent de l'élève, si :
     * - l'élève a un parent rattaché
     * - ce parent a un email valide
     * - ce parent a un compte actif (pour respecter ses préférences si on
     *   ajoute plus tard une option "désactiver les notifications")
     */
    public function created(Note $note): void
    {
        $this->notifierParent($note);
    }

    /**
     * On notifie aussi en cas de modification d'une note existante
     * (ex: correction d'une erreur de saisie), pour garder le parent informé.
     */
    public function updated(Note $note): void
    {
        if ($note->wasChanged('valeur')) {
            $this->notifierParent($note);
        }
    }

    private function notifierParent(Note $note): void
    {
        try {
            $note->loadMissing('eleve.parent', 'matiere');

            $eleve  = $note->eleve;
            $parent = $eleve?->parent;

            if (!$parent || !$parent->email) {
                return; // pas de parent rattaché ou pas d'email — on ignore silencieusement
            }

            Mail::to($parent->email)->queue(new NouvelleNoteMail($note, $parent->prenom));

        } catch (\Exception $e) {
            // Une erreur d'email ne doit JAMAIS faire échouer la sauvegarde de la note
            Log::error('Échec notification email nouvelle note', [
                'note_id' => $note->id ?? null,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}

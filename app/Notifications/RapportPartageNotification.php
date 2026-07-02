<?php

namespace App\Notifications;

use App\Models\Copie;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RapportPartageNotification extends Notification
{
    use Queueable;

    public function __construct(public Copie $copie) {}

    /**
     * Canal "database" uniquement : la notification s'affiche dans l'app.
     * (L'email reste géré séparément, en best-effort, par le contrôleur.)
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->copie->loadMissing(['eleve', 'examen']);
        $eleve = $this->copie->eleve;

        return [
            'type'         => 'rapport_examen',
            'titre'        => "Nouveau rapport d'examen",
            'message'      => "Un bilan a été partagé pour " . trim(($eleve->prenom ?? '') . ' ' . ($eleve->nom ?? ''))
                              . " — examen « " . ($this->copie->examen->titre ?? 'Examen') . " ».",
            'eleve_id'     => $this->copie->eleve_id,
            'eleve_nom'    => trim(($eleve->prenom ?? '') . ' ' . ($eleve->nom ?? '')),
            'examen_titre' => $this->copie->examen->titre ?? 'Examen',
        ];
    }
}

<?php

namespace App\Observers;

use App\Models\Absence;
use App\Mail\NouvelleAbsenceMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AbsenceObserver
{
    /**
     * Déclenché automatiquement à chaque création d'une absence.
     * Notifie le parent immédiatement — c'est le cas d'usage le plus
     * sensible en termes de délai (le parent veut savoir le jour même).
     */
    public function created(Absence $absence): void
    {
        try {
            $absence->loadMissing('eleve.parent');

            $eleve  = $absence->eleve;
            $parent = $eleve?->parent;

            if (!$parent || !$parent->email) {
                return;
            }

            Mail::to($parent->email)->queue(new NouvelleAbsenceMail($absence, $parent->prenom));

        } catch (\Exception $e) {
            Log::error('Échec notification email nouvelle absence', [
                'absence_id' => $absence->id ?? null,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}

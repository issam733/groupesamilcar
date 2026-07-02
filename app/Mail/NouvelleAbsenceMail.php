<?php

namespace App\Mail;

use App\Models\Absence;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NouvelleAbsenceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Absence $absence;
    public string $prenomParent;

    public function __construct(Absence $absence, string $prenomParent)
    {
        $this->absence      = $absence;
        $this->prenomParent = $prenomParent;
    }

    public function build()
    {
        $eleve = $this->absence->eleve;

        return $this->subject('⚠️ Absence signalée — ' . $eleve->prenom . ' ' . $eleve->nom)
            ->view('emails.nouvelle-absence')
            ->with([
                'prenom'  => $this->prenomParent,
                'eleve'   => $eleve,
                'absence' => $this->absence,
            ]);
    }
}

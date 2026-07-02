<?php

namespace App\Mail;

use App\Models\Copie;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RapportEleveMail extends Mailable
{
    use Queueable, SerializesModels;

    public Copie $copie;
    public string $prenomParent;

    public function __construct(Copie $copie, string $prenomParent)
    {
        $this->copie        = $copie;
        $this->prenomParent = $prenomParent;
    }

    public function build()
    {
        $this->copie->loadMissing(['examen.matiere', 'eleve']);

        $eleve = trim(($this->copie->eleve->prenom ?? '') . ' ' . ($this->copie->eleve->nom ?? ''));

        return $this->subject('📄 Rapport d\'examen de ' . $eleve . ' — Groupe Scolaire Amilcar')
            ->view('emails.rapport-eleve')
            ->with([
                'copie'     => $this->copie,
                'prenom'    => $this->prenomParent,
                'eleveNom'  => $eleve,
            ]);
    }
}

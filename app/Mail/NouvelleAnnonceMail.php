<?php

namespace App\Mail;

use App\Models\Annonce;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NouvelleAnnonceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Annonce $annonce;
    public User $destinataire;

    public function __construct(Annonce $annonce, User $destinataire)
    {
        $this->annonce      = $annonce;
        $this->destinataire = $destinataire;
    }

    public function build()
    {
        return $this->subject('📢 ' . $this->annonce->titre . ' — Groupe Scolaire Amilcar')
            ->view('emails.nouvelle-annonce')
            ->with([
                'annonce'     => $this->annonce,
                'prenom'      => $this->destinataire->prenom,
            ]);
    }
}

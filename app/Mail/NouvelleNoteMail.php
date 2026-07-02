<?php

namespace App\Mail;

use App\Models\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NouvelleNoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public Note $note;
    public string $prenomParent;

    public function __construct(Note $note, string $prenomParent)
    {
        $this->note         = $note;
        $this->prenomParent = $prenomParent;
    }

    public function build()
    {
        $eleve   = $this->note->eleve;
        $matiere = $this->note->matiere;

        return $this->subject('📝 Nouvelle note pour ' . $eleve->prenom . ' — Groupe Scolaire Amilcar')
            ->view('emails.nouvelle-note')
            ->with([
                'prenom'  => $this->prenomParent,
                'eleve'   => $eleve,
                'matiere' => $matiere,
                'note'    => $this->note,
            ]);
    }
}

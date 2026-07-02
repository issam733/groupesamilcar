<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CahierTexte extends Model
{
    protected $table = 'cahier_textes';

    protected $fillable = [
        'classe_id', 'matiere_id', 'enseignant_id',
        'date_cours', 'contenu', 'devoirs', 'date_remise', 'fichier',
    ];

    protected $casts = [
        'date_cours'  => 'date',
        'date_remise' => 'date',
    ];

    public function classe()     { return $this->belongsTo(Classe::class); }
    public function matiere()    { return $this->belongsTo(Matiere::class); }
    public function enseignant() { return $this->belongsTo(Enseignant::class); }

    public function aDesDevoirs(): bool
    {
        return filled($this->devoirs);
    }

    public function aUnFichier(): bool
    {
        return filled($this->fichier);
    }

    /** Nom lisible du fichier joint. */
    public function fichierNom(): string
    {
        return $this->fichier ? basename($this->fichier) : '';
    }
}

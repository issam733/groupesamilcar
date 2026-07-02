<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    protected $fillable = [
        'enseignant_id', 'classe_id', 'matiere_id',
        'titre', 'langue', 'niveau', 'difficulte',
        'nb_questions', 'contenu', 'fichier_source', 'statut',
        'rapport_classe',
    ];

    protected $casts = [
        'rapport_classe' => 'array',
    ];

    public function enseignant() { return $this->belongsTo(Enseignant::class); }
    public function classe()     { return $this->belongsTo(Classe::class); }
    public function matiere()    { return $this->belongsTo(Matiere::class); }

    public function getContenuArrayAttribute(): array
    {
        return json_decode($this->contenu, true) ?? [];
    }

    public static function langues(): array
    {
        return ['fr' => 'Français', 'ar' => 'العربية', 'en' => 'English'];
    }

    public static function difficultes(): array
    {
        return ['facile' => 'Facile', 'moyen' => 'Moyen', 'difficile' => 'Difficile'];
    }
}

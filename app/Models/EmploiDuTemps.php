<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploiDuTemps extends Model
{
    protected $table = 'emplois_du_temps';
    protected $fillable = ['classe_id','matiere_id','enseignant_id','jour','heure_debut','heure_fin'];

    public function classe()     { return $this->belongsTo(Classe::class); }
    public function matiere()    { return $this->belongsTo(Matiere::class); }
    public function enseignant() { return $this->belongsTo(Enseignant::class); }

    /** Jours de la semaine gérés par l'emploi du temps. */
    public static function jours(): array
    {
        return ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    }
}

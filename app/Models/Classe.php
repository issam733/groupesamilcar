<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    protected $fillable = ['nom','niveau','enseignant_id','annee_scolaire','effectif_max','active'];
    protected $casts = ['active' => 'boolean'];

    public function enseignant() { return $this->belongsTo(Enseignant::class); }
    public function eleves()     { return $this->hasMany(Eleve::class)->where('actif', true); }
    public function matieres()   { return $this->hasMany(Matiere::class); }
    public function emplois()    { return $this->hasMany(EmploiDuTemps::class, 'classe_id'); }
    public function effectif(): int
    {
        return  $this->eleves()->count();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ressource extends Model
{
    protected $fillable = ['classe_id','matiere_id','enseignant_id','titre','type','fichier','lien_externe','niveau'];

    public function classe()     { return $this->belongsTo(Classe::class); }
    public function matiere()    { return $this->belongsTo(Matiere::class); }
    public function enseignant() { return $this->belongsTo(Enseignant::class); }
}

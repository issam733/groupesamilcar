<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    protected $fillable = ['classe_id','enseignant_id','nom','coefficient','heures_semaine'];
    protected $casts    = ['coefficient' => 'float'];

    public function classe()     { return $this->belongsTo(Classe::class); }
    public function enseignant() { return $this->belongsTo(Enseignant::class); }
}

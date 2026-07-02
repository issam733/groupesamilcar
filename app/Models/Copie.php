<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Copie extends Model
{
    protected $table = 'examen_copies';

    protected $fillable = [
        'examen_id', 'eleve_id', 'reponses',
        'score_qcm', 'bareme_qcm', 'note_finale', 'statut',
        'rapport', 'rapport_envoye_parent',
    ];

    protected $casts = [
        'reponses'              => 'array',
        'score_qcm'             => 'float',
        'bareme_qcm'            => 'float',
        'note_finale'           => 'float',
        'rapport'               => 'array',
        'rapport_envoye_parent' => 'boolean',
    ];

    public function examen() { return $this->belongsTo(Examen::class); }
    public function eleve()  { return $this->belongsTo(Eleve::class); }
}

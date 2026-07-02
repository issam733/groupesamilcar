<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'eleve_id','matiere_id','classe_id',
        'type','valeur','trimestre','commentaire','saisi_par',
    ];
    protected $casts = ['valeur' => 'float'];

    public function eleve()   { return $this->belongsTo(Eleve::class); }
    public function matiere() { return $this->belongsTo(Matiere::class); }
    public function classe()  { return $this->belongsTo(Classe::class); }

    public static function types(): array
    {
        return ['devoir' => 'Devoir', 'controle' => 'Contrôle', 'examen' => 'Examen'];
    }
}

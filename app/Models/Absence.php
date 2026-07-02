<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = ['eleve_id', 'date', 'justifie', 'motif', 'saisi_par'];
    protected $casts    = ['justifie' => 'boolean', 'date' => 'date'];

    public function eleve()   { return $this->belongsTo(Eleve::class); }
    public function saiseur() { return $this->belongsTo(User::class, 'saisi_par'); }
}

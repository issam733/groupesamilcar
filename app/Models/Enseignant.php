<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    protected $fillable = ['user_id','nom','prenom','matiere','telephone','email','photo','diplome','actif'];
    protected $casts = ['actif' => 'boolean'];

    public function classes() { return $this->hasMany(Classe::class); }
}

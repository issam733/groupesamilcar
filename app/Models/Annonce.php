<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    protected $fillable = ['titre', 'contenu', 'cible', 'created_by', 'publie'];
    protected $casts    = ['publie' => 'boolean'];

    public function auteur() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopePubliees($q)            { return $q->where('publie', true); }
    public function scopeParCible($q, string $c) { return $q->where('cible', $c); }

    public static function cibles(): array
    {
        return [
            'all'         => 'Tous',
            'enseignants' => 'Enseignants',
            'parents'     => 'Parents',
            'eleves'      => 'Élèves',
        ];
    }
}

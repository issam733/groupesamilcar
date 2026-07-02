<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attestation extends Model
{
    protected $fillable = [
        'eleve_id', 'type', 'langue',
        'numero_unique', 'qr_code',
        'annee_scolaire', 'genere_par',
    ];

    public function eleve()     { return $this->belongsTo(Eleve::class); }
    public function generePar() { return $this->belongsTo(User::class, 'genere_par'); }

    public function getTypeLibelleAttribute(): string
    {
        return match($this->type) {
            'inscription' => 'Attestation d\'inscription',
            'presence'    => 'Attestation de présence',
            'reussite'    => 'Attestation de réussite',
            default       => $this->type,
        };
    }

    /**
     * Génère un numéro unique au format AMI2026000001
     */
    public static function genererNumero(): string
    {
        $annee = date('Y');
        $count = static::whereYear('created_at', $annee)->count();
        return 'AMI' . $annee . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    }
}

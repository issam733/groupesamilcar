<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eleve extends Model
{
    protected $fillable = [
        'user_id','matricule','nom','prenom','date_naissance','sexe',
        'adresse','telephone','email','photo','classe_id','parent_id',
        'annee_scolaire','actif',
    ];
    protected $casts = ['actif' => 'boolean', 'date_naissance' => 'date'];

    public function classe() { return $this->belongsTo(Classe::class); }
    public function parent() { return $this->belongsTo(ParentEleve::class, 'parent_id'); }
    public function notes()  { return $this->hasMany(Note::class); }
    public function absences(){ return $this->hasMany(Absence::class); }
    public function attestations() { return $this->hasMany(Attestation::class); }

    /**
     * Moyenne générale d'un trimestre, pondérée par le coefficient de chaque matière.
     * Retourne null si aucune note n'est saisie pour ce trimestre.
     */
    public function moyenneTrimestre(int $trimestre): ?float
    {
        $notes = $this->notes()
            ->where('trimestre', $trimestre)
            ->with('matiere')
            ->get();

        if ($notes->isEmpty()) {
            return null;
        }

        $totalPoints = 0.0;
        $totalCoef   = 0.0;

        foreach ($notes->groupBy('matiere_id') as $notesMatiere) {
            $moyenneMatiere = $notesMatiere->avg('valeur');
            if ($moyenneMatiere === null) {
                continue;
            }
            $coef = $notesMatiere->first()->matiere->coefficient ?? 1;
            $totalPoints += $moyenneMatiere * $coef;
            $totalCoef   += $coef;
        }

        return $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : null;
    }

    /**
     * Nombre d'absences non justifiées de l'élève.
     */
    public function absencesNonJustifiees(): int
    {
        return $this->absences()->where('justifie', false)->count();
    }
    public static function mention(float $moyenne): string
    {
        return match (true) {
            $moyenne >= 16 => 'Excellent',
            $moyenne >= 14 => 'Très bien',
            $moyenne >= 12 => 'Bien',
            $moyenne >= 10 => 'Passable',
            default        => 'Insuffisant',
        };
    }
    public function pointsEleves()
    {
        return $this->hasMany(PointEleve::class);
    }

    public function totalPoints(): int
    {
        return (int) $this->pointsEleves()->sum('points');
    }
}

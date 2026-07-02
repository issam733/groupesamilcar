<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointEleve extends Model
{
    protected $table = 'points_eleves';

    protected $fillable = [
        'eleve_id',
        'points',
        'motif',
        'date_action',
    ];

    protected $casts = [
        'date_action' => 'date',
    ];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }
}

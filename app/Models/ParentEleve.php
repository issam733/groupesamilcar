<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentEleve extends Model
{
    protected $table    = 'parents';
    protected $fillable = ['user_id','nom','prenom','telephone','email','profession','actif'];
    protected $casts    = ['actif' => 'boolean'];

    public function user()   { return $this->belongsTo(User::class); }
    public function eleves() { return $this->hasMany(Eleve::class, 'parent_id'); }
}

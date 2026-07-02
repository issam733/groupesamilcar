<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'email', 'password',
        'role', 'telephone', 'photo', 'actif',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'actif'             => 'boolean',
    ];

    // Relations
    public function enseignant() { return $this->hasOne(Enseignant::class); }
    public function parent()     { return $this->hasOne(ParentEleve::class); }
    public function journal()    { return $this->hasMany(Journal::class); }
    public function sentMessages() { return $this->hasMany(Message::class, 'sender_id'); }
    public function receivedMessages() { return $this->hasMany(Message::class, 'recipient_id'); }
    // Scopes
    public function scopeAdmins($q)      { return $q->where('role', 'admin'); }
    public function scopeEnseignants($q) { return $q->where('role', 'enseignant'); }
    public function scopeParents($q)     { return $q->where('role', 'parent'); }
    public function scopeEleves($q)      { return $q->where('role', 'eleve'); }

    // Helpers
    public function isAdmin()      { return $this->role === 'admin'; }
    public function isEnseignant() { return $this->role === 'enseignant'; }
    public function isParent()     { return $this->role === 'parent'; }
    public function isEleve()      { return $this->role === 'eleve'; }

    public function nomComplet()   { return "{$this->prenom} {$this->nom}"; }

    public function initialesAvatar()
    {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1));
    }
}

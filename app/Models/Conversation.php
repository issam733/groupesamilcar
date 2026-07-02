<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user_un_id', 'user_deux_id', 'dernier_message_at'];

    protected $casts = ['dernier_message_at' => 'datetime'];

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function dernierMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function userUn()   { return $this->belongsTo(User::class, 'user_un_id'); }
    public function userDeux() { return $this->belongsTo(User::class, 'user_deux_id'); }

    /** L'autre participant que $userId. */
    public function autre($userId): ?User
    {
        return $this->user_un_id == $userId ? $this->userDeux : $this->userUn;
    }

    public function participe($userId): bool
    {
        return $this->user_un_id == $userId || $this->user_deux_id == $userId;
    }

    /** Récupère (ou crée) la conversation entre deux utilisateurs. */
    public static function entre(int $aId, int $bId): self
    {
        return static::firstOrCreate([
            'user_un_id'   => min($aId, $bId),
            'user_deux_id' => max($aId, $bId),
        ]);
    }

    public function scopePour($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_un_id', $userId)->orWhere('user_deux_id', $userId);
        });
    }
}

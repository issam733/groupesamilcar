<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'expediteur_id', 'corps', 'lu_at'];

    protected $casts = ['lu_at' => 'datetime'];

    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function expediteur()   { return $this->belongsTo(User::class, 'expediteur_id'); }

    /** Nombre de messages non lus reçus par un utilisateur (tous fils confondus). */
    public static function nonLusPour(int $userId): int
    {
        return static::whereHas('conversation', function ($q) use ($userId) {
                $q->where('user_un_id', $userId)->orWhere('user_deux_id', $userId);
            })
            ->where('expediteur_id', '!=', $userId)
            ->whereNull('lu_at')
            ->count();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Journal extends Model
{
    protected $table    = 'journal_actions';
    protected $fillable = ['user_id', 'type', 'action', 'details', 'ip'];

    public function user() { return $this->belongsTo(User::class); }

    public static function log(string $type, string $action, ?int $userId = null, ?string $details = null): void
    {
        try {
            static::create([
                'user_id' => $userId ?? Auth::id(),
                'type'    => strtolower($type),
                'action'  => $action,
                'details' => $details,
                'ip'      => request()->ip(),
            ]);
        } catch (\Exception) {}
    }
}

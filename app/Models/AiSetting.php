<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $table = 'ia_settings';

    protected $fillable = [
        'provider', 'groq_api_key', 'anthropic_api_key',
        'groq_model', 'anthropic_model', 'updated_by',
    ];

    // Les clés API sont chiffrées en base (jamais stockées en clair)
    protected $casts = [
        'groq_api_key'      => 'encrypted',
        'anthropic_api_key' => 'encrypted',
    ];

    /**
     * Retourne l'unique ligne de configuration IA (la crée si elle n'existe pas).
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'provider'        => 'groq',
            'groq_model'      => 'llama-3.3-70b-versatile',
            'anthropic_model' => 'claude-sonnet-5',
        ]);
    }

    public function updatedBy() { return $this->belongsTo(User::class, 'updated_by'); }

    /**
     * Masque une clé API pour l'affichage (sk-ant-••••1234)
     */
    public static function masquer(?string $cle): ?string
    {
        if (empty($cle)) return null;
        $len = strlen($cle);
        if ($len <= 8) return str_repeat('•', $len);
        return substr($cle, 0, 4) . str_repeat('•', max(4, $len - 8)) . substr($cle, -4);
    }
}

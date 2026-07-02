<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stocke le rapport de synthèse de classe généré par l'IA pour un examen
 * (lacunes récurrentes, questions problématiques, recommandations pédagogiques).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examens', function (Blueprint $table) {
            $table->longText('rapport_classe')->nullable()->after('contenu');
        });
    }

    public function down(): void
    {
        Schema::table('examens', function (Blueprint $table) {
            $table->dropColumn('rapport_classe');
        });
    }
};

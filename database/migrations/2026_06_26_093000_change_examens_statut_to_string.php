<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La colonne `examens.statut` était un ENUM restreint (ex. 'genere') qui
 * refusait toute autre valeur — d'où l'erreur "Data truncated for column
 * 'statut'" au moment d'envoyer un examen aux élèves (statut = 'envoye').
 *
 * On la convertit en VARCHAR : elle accepte désormais 'genere', 'envoye',
 * et toute valeur future. Les données existantes sont conservées telles quelles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examens', function (Blueprint $table) {
            $table->string('statut', 30)->default('genere')->change();
        });
    }

    public function down(): void
    {
        // On ne recrée pas l'ENUM au rollback pour éviter de tronquer des
        // valeurs ('envoye') qui pourraient déjà exister en base.
        Schema::table('examens', function (Blueprint $table) {
            $table->string('statut', 30)->default('genere')->change();
        });
    }
};

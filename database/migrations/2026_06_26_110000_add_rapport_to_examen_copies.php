<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute le rapport pédagogique IA à chaque copie, et un indicateur précisant
 * si l'enseignant a choisi de transmettre ce rapport au parent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examen_copies', function (Blueprint $table) {
            $table->longText('rapport')->nullable()->after('note_finale');
            $table->boolean('rapport_envoye_parent')->default(false)->after('rapport');
        });
    }

    public function down(): void
    {
        Schema::table('examen_copies', function (Blueprint $table) {
            $table->dropColumn(['rapport', 'rapport_envoye_parent']);
        });
    }
};

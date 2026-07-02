<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crée la table emplois_du_temps si elle n'existe pas déjà.
 * (Aucune migration ne la créait : si elle n'a jamais été créée à la main,
 *  la page "Emplois du temps" plante sur withCount('emplois').)
 * Guardée par hasTable : ne touche pas une table existante.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('emplois_du_temps')) {
            return;
        }

        Schema::create('emplois_du_temps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classe_id');
            $table->unsignedBigInteger('matiere_id');
            $table->unsignedBigInteger('enseignant_id')->nullable();
            $table->string('jour', 15);
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->timestamps();

            $table->index('classe_id');
            $table->index(['classe_id', 'jour']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emplois_du_temps');
    }
};

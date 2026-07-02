<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cahier de texte numérique : une entrée = un cours (contenu détaillé + devoirs).
 * Guardée par hasTable. Sans clés étrangères contraintes (cohérent avec le projet).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cahier_textes')) {
            return;
        }

        Schema::create('cahier_textes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classe_id');
            $table->unsignedBigInteger('matiere_id');
            $table->unsignedBigInteger('enseignant_id');
            $table->date('date_cours');
            $table->text('contenu');                 // contenu détaillé du cours
            $table->text('devoirs')->nullable();      // devoirs à la maison
            $table->date('date_remise')->nullable();  // date de remise des devoirs
            $table->timestamps();

            $table->index('classe_id');
            $table->index('enseignant_id');
            $table->index('date_cours');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cahier_textes');
    }
};

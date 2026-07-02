<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table des copies d'examen : une ligne = les réponses d'un élève à un examen.
 * Pas de contraintes de clés étrangères (les tables existantes n'ont pas été
 * créées via migrations) ; on se contente d'index + unicité (examen, élève).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examen_copies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examen_id');
            $table->unsignedBigInteger('eleve_id');
            $table->json('reponses')->nullable();      // {qcm:{num:idx}, ouvertes:{num:texte}}
            $table->float('score_qcm')->nullable();     // points obtenus au QCM (auto)
            $table->float('bareme_qcm')->nullable();    // total possible du QCM
            $table->float('note_finale')->nullable();   // note attribuée par l'enseignant
            $table->string('statut', 20)->default('soumis'); // soumis | corrige
            $table->timestamps();

            $table->index('examen_id');
            $table->index('eleve_id');
            $table->unique(['examen_id', 'eleve_id']); // une seule copie par élève et par examen
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examen_copies');
    }
};

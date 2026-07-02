<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crée la table absences si elle n'existe pas déjà.
 * Guardée par hasTable : ne touche pas une table existante.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('absences')) {
            return;
        }

        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('eleve_id');
            $table->date('date');
            $table->boolean('justifie')->default(false);
            $table->string('motif')->nullable();
            $table->unsignedBigInteger('saisi_par')->nullable();
            $table->timestamps();

            $table->index('eleve_id');
            $table->index('date');
            $table->unique(['eleve_id', 'date']); // une seule absence par élève et par jour
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};

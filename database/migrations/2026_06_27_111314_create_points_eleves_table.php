<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_eleves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->cascadeOnDelete();
            $table->integer('points');
            $table->string('motif');
            $table->date('date_action');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_eleves');
    }
};

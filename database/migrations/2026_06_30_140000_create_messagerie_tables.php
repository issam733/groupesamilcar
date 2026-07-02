<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Messagerie : conversations privées 1-à-1 entre deux utilisateurs + messages.
 * Pas de clés étrangères contraintes (cohérent avec le reste du projet).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_un_id');   // toujours le plus petit id
                $table->unsignedBigInteger('user_deux_id'); // toujours le plus grand id
                $table->timestamp('dernier_message_at')->nullable();
                $table->timestamps();

                $table->unique(['user_un_id', 'user_deux_id']);
                $table->index('user_un_id');
                $table->index('user_deux_id');
            });
        }

        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->unsignedBigInteger('expediteur_id');
                $table->text('corps');
                $table->timestamp('lu_at')->nullable(); // lu par le destinataire
                $table->timestamps();

                $table->index('conversation_id');
                $table->index('expediteur_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};

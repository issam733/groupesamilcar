<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('groq'); // 'groq' ou 'anthropic'
            $table->text('groq_api_key')->nullable();
            $table->text('anthropic_api_key')->nullable();
            $table->string('groq_model')->default('llama-3.3-70b-versatile');
            $table->string('anthropic_model')->default('claude-sonnet-5');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute une pièce jointe (support de cours PDF/image/doc) aux entrées du cahier.
 * Guardée par hasColumn.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cahier_textes') && !Schema::hasColumn('cahier_textes', 'fichier')) {
            Schema::table('cahier_textes', function (Blueprint $table) {
                $table->string('fichier')->nullable()->after('devoirs');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cahier_textes') && Schema::hasColumn('cahier_textes', 'fichier')) {
            Schema::table('cahier_textes', function (Blueprint $table) {
                $table->dropColumn('fichier');
            });
        }
    }
};

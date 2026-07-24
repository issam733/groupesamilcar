<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le modèle User (et plusieurs contrôleurs : Admin\ParentController,
 * Admin\ParametreController...) utilisent les colonnes nom, prenom, role,
 * telephone, photo, actif — absentes de la migration Laravel par défaut
 * 0001_01_01_000000_create_users_table.php.
 *
 * Sur l'environnement de production (Render), ces colonnes existent déjà
 * (ajoutées manuellement ou via une migration antérieure non présente dans
 * ce dépôt). Sur une installation fraîche, elles manqueraient et l'appli
 * planterait à la première requête sur users. Cette migration comble l'écart
 * de façon idempotente : chaque colonne n'est ajoutée que si elle n'existe
 * pas déjà, donc sans risque ni pour une base neuve ni pour la prod existante.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nom')) {
                $table->string('nom')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'prenom')) {
                $table->string('prenom')->nullable()->after('nom');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('eleve')->after('password');
            }
            if (!Schema::hasColumn('users', 'telephone')) {
                $table->string('telephone')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('telephone');
            }
            if (!Schema::hasColumn('users', 'actif')) {
                $table->boolean('actif')->default(true)->after('photo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['nom', 'prenom', 'role', 'telephone', 'photo', 'actif'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

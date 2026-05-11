<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica se a coluna 'user_id' já existe antes de tentar adicioná-la
        if (!Schema::hasColumn('pessoas', 'user_id')) {
            Schema::table('pessoas', function (Blueprint $table) {
                // Adiciona a coluna user_id como chave estrangeira para a tabela users
                // e permite que seja nula, pois nem toda pessoa terá um usuário.
                $table->foreignId('user_id')->nullable()->constrained()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verifica se a coluna 'user_id' existe antes de tentar removê-la
        if (Schema::hasColumn('pessoas', 'user_id')) {
            Schema::table('pessoas', function (Blueprint $table) {
                // Remove a chave estrangeira primeiro
                $table->dropConstrainedForeignId('user_id');
                // Depois remove a coluna
                $table->dropColumn('user_id');
            });
        }
    }
};
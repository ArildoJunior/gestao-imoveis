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
        Schema::table('alertas', function (Blueprint $table) {
            // Adiciona a coluna 'resolvido_em' como um timestamp que pode ser nulo
            // e a posiciona após a coluna 'visualizado_em' para manter a organização.
            $table->timestamp('resolvido_em')->nullable()->after('visualizado_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            // Remove a coluna 'resolvido_em' se a migration for revertida.
            $table->dropColumn('resolvido_em');
        });
    }
};
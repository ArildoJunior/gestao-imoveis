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
            // Adiciona a coluna 'visualizado_em' como um timestamp que pode ser nulo
            // e a posiciona após a coluna 'status' para manter a organização.
            $table->timestamp('visualizado_em')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            // Remove a coluna 'visualizado_em' se a migration for revertida.
            $table->dropColumn('visualizado_em');
        });
    }
};
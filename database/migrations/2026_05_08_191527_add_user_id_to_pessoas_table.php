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
        Schema::table('pessoas', function (Blueprint $table) {
            // Adiciona a coluna user_id
            // Ela será nullable para permitir que existam pessoas sem um usuário associado (ex: inquilinos, proprietários que não acessam o sistema)
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('id') // Posiciona a coluna após 'id'
                  ->constrained('users') // Cria a chave estrangeira para a tabela 'users'
                  ->onDelete('set null'); // Se o usuário for deletado, o user_id na tabela pessoas será setado para NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pessoas', function (Blueprint $table) {
            // Remove a chave estrangeira antes de remover a coluna
            $table->dropConstrainedForeignId('user_id');
            // Remove a coluna user_id
            $table->dropColumn('user_id');
        });
    }
};
<?php

use App\Models\User; // Importar o modelo User
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
        Schema::table('users', function (Blueprint $table) {
            // Adiciona a coluna 'perfil' com ENUM e valor padrão
            $table->enum('perfil', User::PERFIS)->default('PENDENTE')->after('password');
            // Adiciona a coluna 'status' com ENUM e valor padrão
            $table->enum('status', ['ATIVO', 'INATIVO'])->default('ATIVO')->after('perfil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove as colunas 'status' e 'perfil'
            $table->dropColumn('status');
            $table->dropColumn('perfil');
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            // Altera a coluna para permitir NULL e define um valor padrão
            // Se a coluna já existe e é NOT NULL, esta linha a torna NULLABLE e define um default.
            // Se já for NULLABLE, apenas define o default.
            $table->integer('carencia_dias')->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            // Reverte a alteração: torna a coluna NOT NULL novamente e mantém o default 0.
            // CUIDADO: Se houver registros com NULL em 'carencia_dias', esta operação falhará.
            // Para evitar falhas, você precisaria primeiro preencher os NULLs com 0 ou outro valor.
            $table->integer('carencia_dias')->default(0)->change(); // Sintaxe corrigida
        });
    }
};
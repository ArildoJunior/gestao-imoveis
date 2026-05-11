<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabela de reajustes de contrato.
     *
     * Cada registro representa um reajuste aplicado em um contrato em uma data,
     * guardando valores antes/depois e o percentual efetivo.
     */
    public function up(): void
    {
        Schema::create('reajustes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('contrato_id');
            $table->date('data_reajuste');

            // Índice utilizado: IPCA, IGP_M, INPC, NEGOCIADO, SEM_REAJUSTE, etc.
            $table->string('indice_reajuste', 50)->nullable();

            // Percentual aplicado no reajuste (ex.: 6.50)
            $table->decimal('percentual_aplicado', 8, 4)->nullable();

            // Valores antes/depois do reajuste
            $table->decimal('valor_anterior', 12, 2);
            $table->decimal('valor_novo', 12, 2);

            // Observações livres para explicar o reajuste
            $table->string('descricao', 255)->nullable();

            // Usuário que aprovou/aplicou o reajuste (opcional por enquanto)
            $table->unsignedBigInteger('aprovado_por_user_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contrato_id')
                ->references('id')
                ->on('contratos')
                ->onDelete('cascade');

            $table->foreign('aprovado_por_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reajustes');
    }
};
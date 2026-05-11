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
        Schema::create('parcelas_aluguel', function (Blueprint $table) {
            $table->id();

            // Relacionamento com contrato
            $table->foreignId('contrato_id')
                  ->constrained('contratos')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // Competência (ex: "2024-03")
            $table->string('competencia', 7)->comment('Formato YYYY-MM');

            // Número da parcela dentro do ciclo de origem
            $table->unsignedSmallInteger('numero_parcela')->default(1);
            $table->unsignedSmallInteger('total_parcelas')->default(1);

            // Datas e valores
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();

            $table->decimal('valor_original', 12, 2);
            $table->decimal('valor_devido', 12, 2);
            $table->decimal('valor_pago', 12, 2)->default(0);

            // Status
            $table->enum('status', [
                'ABERTA',
                'PAGA',
                'PAGA_PARCIALMENTE',
                'EM_ATRASO',
                'RENEGOCIADA',
                'CANCELADA',
            ])->default('ABERTA');

            // Origem da parcela
            $table->enum('tipo_origem', [
                'ALUGUEL_NORMAL',
                'ACORDO_ATRASO',
                'MULTA',
                'TAXA_EXTRA',
                'TEMPORADA',
                'CAUCAO',
            ])->default('ALUGUEL_NORMAL');

            // Ajustes financeiros
            $table->decimal('multa_aplicada', 12, 2)->default(0);
            $table->decimal('juros_aplicados', 12, 2)->default(0);
            $table->decimal('desconto_aplicado', 12, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('contrato_id');
            $table->index('competencia');
            $table->index('status');
            $table->index('tipo_origem');
            $table->index('data_vencimento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcelas_aluguel');
    }
};
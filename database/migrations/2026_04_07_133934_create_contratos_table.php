<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo')->unique(); // ex: CT-2024-001

            // Relacionamentos principais
            $table->foreignId('imovel_id')
                  ->constrained('imoveis')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreignId('locatario_id')
                  ->constrained('pessoas')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreignId('proprietario_id')
                  ->constrained('pessoas')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // Tipo de contrato
            $table->enum('tipo_contrato', [
                'RESIDENCIAL',
                'COMERCIAL',
            ])->default('RESIDENCIAL');

            // Datas principais
            $table->date('data_inicio');
            $table->date('data_fim_prevista')->nullable();
            $table->date('data_fim_real')->nullable();

            // Valores de aluguel
            $table->decimal('valor_aluguel_base', 12, 2);
            $table->decimal('valor_aluguel_atual', 12, 2);

            $table->unsignedTinyInteger('dia_vencimento'); // 1..31

            // Reajuste
            $table->enum('indice_reajuste', [
                'IPCA',
                'IGP-M',
                'INPC',
                'NEGOCIADO',
                'SEM_REAJUSTE',
            ])->default('SEM_REAJUSTE');

            $table->unsignedTinyInteger('mes_reajuste')->nullable(); // 1..12
            $table->decimal('percentual_reajuste_padrao', 5, 2)->nullable();

            // Status do contrato
            $table->enum('status', [
                'ATIVO',
                'ENCERRADO',
                'EM_COBRANCA_JUDICIAL',
                'RESCINDIDO',
            ])->default('ATIVO');

            // Campos de caução (simplificados)
            $table->boolean('possui_caucao')->default(false);
            $table->unsignedTinyInteger('meses_caucao')->default(0); // 0-3
            $table->decimal('valor_caucao', 12, 2)->default(0);

            $table->boolean('caucao_paga_integralmente')->default(false);
            $table->date('data_pagamento_total_caucao')->nullable();

            $table->boolean('caucao_devolvida')->default(false);
            $table->date('data_devolucao_caucao')->nullable();
            $table->text('motivo_nao_devolucao_caucao')->nullable();

            // Multa e juros de atraso (simplificados)
            $table->boolean('possui_multa_atraso')->default(false);
            $table->decimal('percentual_multa', 5, 2)->nullable(); // % sobre valor
            $table->boolean('possui_juros_moratorios')->default(false);
            $table->decimal('percentual_juros_mensal', 5, 2)->nullable();
            $table->integer('carencia_dias')->default(0)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('codigo');
            $table->index('imovel_id');
            $table->index('locatario_id');
            $table->index('proprietario_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
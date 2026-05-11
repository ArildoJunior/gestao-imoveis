<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            // Campos específicos para contrato de temporada
            $table->date('data_entrada_prevista')->nullable()->after('data_fim_prevista');
            $table->time('hora_entrada')->nullable()->after('data_entrada_prevista');

            $table->date('data_saida_prevista')->nullable()->after('hora_entrada');
            $table->time('hora_saida')->nullable()->after('data_saida_prevista');

            $table->unsignedInteger('numero_hospedes')->nullable()->after('hora_saida');

            $table->decimal('valor_total_temporada', 12, 2)->nullable()->after('numero_hospedes');

            // Informativo: quantas vezes pretende receber (não gera N parcelas nesta fase)
            $table->unsignedInteger('numero_parcelas_temporada')->nullable()->after('valor_total_temporada');

            // Dia padrão, se quiser usar depois
            $table->unsignedTinyInteger('dia_vencimento_parcelas_temporada')->nullable()->after('numero_parcelas_temporada');

            // Prazo máximo em dias (ex.: tudo pago até X dias antes da entrada)
            $table->unsignedInteger('prazo_maximo_pagamento_dias')->nullable()->after('dia_vencimento_parcelas_temporada');

            $table->text('regras_especiais')->nullable()->after('prazo_maximo_pagamento_dias');
            $table->text('restricoes')->nullable()->after('regras_especiais');
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn([
                'data_entrada_prevista',
                'hora_entrada',
                'data_saida_prevista',
                'hora_saida',
                'numero_hospedes',
                'valor_total_temporada',
                'numero_parcelas_temporada',
                'dia_vencimento_parcelas_temporada',
                'prazo_maximo_pagamento_dias',
                'regras_especiais',
                'restricoes',
            ]);
        });
    }
};
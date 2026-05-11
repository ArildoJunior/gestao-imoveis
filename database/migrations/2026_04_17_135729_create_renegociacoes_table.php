<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renegociacoes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contrato_id')
                  ->constrained('contratos')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->date('data_acordo');

            $table->decimal('valor_original_total', 12, 2);
            $table->decimal('valor_acordado', 12, 2);
            $table->decimal('descontos_concedidos', 12, 2)->default(0);

            $table->text('descricao_acordo')->nullable();

            $table->unsignedSmallInteger('numero_parcelas_acordo');
            $table->unsignedTinyInteger('dia_vencimento_acordo');
            $table->date('primeiro_vencimento_acordo');

            $table->foreignId('aprovado_por_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('contrato_id');
            $table->index('data_acordo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renegociacoes');
    }
};
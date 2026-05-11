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
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcela_id')->constrained('parcelas_aluguel');
            $table->foreignId('registrado_por_user_id')->nullable()->constrained('users'); // Adicionado ->nullable()
            $table->date('data_pagamento');
            $table->decimal('valor_pago', 10, 2);
            $table->string('forma_pagamento'); // PIX, DINHEIRO, TED_DOC, BOLETO, CARTAO, OUTRO
            $table->string('numero_comprovante')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
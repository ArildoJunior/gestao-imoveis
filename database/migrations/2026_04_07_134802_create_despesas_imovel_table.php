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
        Schema::create('despesas_imovel', function (Blueprint $table) {
            $table->id();

            // Relacionamento principal: imóvel
            $table->foreignId('imovel_id')
                  ->constrained('imoveis')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // Opcionalmente ligada a um contrato
            $table->foreignId('contrato_id')
                  ->nullable()
                  ->constrained('contratos')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            // Usuário que registrou
            $table->foreignId('registrado_por_user_id')
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->date('data_despesa');

            $table->enum('tipo_despesa', [
                'MANUTENCAO',
                'REFORMA',
                'IPTU',
                'CONDOMINIO',
                'AGUA',
                'LUZ',
                'SEGURO',
                'HONORARIO_ADVOCATICIO',
                'OUTROS',
            ])->default('OUTROS');

            $table->string('descricao')->nullable();
            $table->decimal('valor', 12, 2);

            $table->enum('responsavel', [
                'PROPRIETARIO',
                'LOCATARIO',
                'A_DEFINIR',
            ])->default('A_DEFINIR');

            $table->enum('status', [
                'PENDENTE',
                'PAGA',
                'REEMBOLSADA',
                'CANCELADA',
            ])->default('PENDENTE');

            // Dados de fornecedor / nota fiscal (simplificado)
            $table->string('fornecedor')->nullable();
            $table->string('numero_nota_fiscal')->nullable();

            // Dados de reembolso
            $table->date('data_reembolso')->nullable();
            $table->decimal('valor_reembolso', 12, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('imovel_id');
            $table->index('contrato_id');
            $table->index('data_despesa');
            $table->index('tipo_despesa');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despesas_imovel');
    }
};
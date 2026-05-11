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
        Schema::create('imoveis', function (Blueprint $table) {
            $table->id();

            // Dados básicos do imóvel
            $table->string('descricao')->nullable();
            $table->string('logradouro');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cidade');
            $table->char('estado', 2);
            $table->string('cep', 9);

            // Tipo do imóvel (enum) - ATUALIZADO CONFORME SUA SOLICITAÇÃO
            $table->enum('tipo_imovel', [
                'CASA',
                'APARTAMENTO',
                'SITIO', // Alterado de CHACARA para SITIO
                'LOJA', // Novo tipo
                'SALA_COMERCIAL',
                'GALPAO', // Novo tipo
                'TERRENO',
                'OUTRO'
            ])->default('OUTRO');

            // Área em metros quadrados
            $table->decimal('area_m2', 8, 2)->nullable();

            // Dados cadastrais
            $table->string('matricula')->nullable();
            $table->string('inscricao_iptu')->nullable();

            // Custos fixos
            $table->decimal('valor_iptu_anual', 12, 2)->default(0);
            $table->boolean('possui_condominio')->default(false);
            $table->decimal('valor_condominio_mensal', 12, 2)->default(0);
            $table->boolean('possui_agua_incluida')->default(false);
            $table->boolean('possui_luz_incluida')->default(false);

            // Status
            $table->boolean('ativo')->default(true);

            // Relacionamento com proprietário (Pessoa)
            $table->foreignId('proprietario_id')
                  ->constrained('pessoas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // Timestamps e soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Índices para consultas frequentes
            $table->index('tipo_imovel');
            $table->index('ativo');
            $table->index('proprietario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imoveis');
    }
};
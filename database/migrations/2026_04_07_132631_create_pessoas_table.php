<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            // Campos criptografados (serão tratados no Model)
            $table->string('nome');
            $table->string('cpf_cnpj')->nullable();
            $table->string('rg')->nullable();
            // Campos não sensíveis
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->string('celular')->nullable();
            // Endereço
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->char('estado', 2)->nullable();
            $table->string('cep', 9)->nullable();
            // Tipo e status
            $table->enum('tipo', ['PROPRIETARIO', 'LOCATARIO', 'AMBOS'])->default('PROPRIETARIO');
            $table->boolean('ativo')->default(true);
            // LGPD
            $table->boolean('consentimento_lgpd')->default(false);
            $table->timestamp('data_consentimento')->nullable();
            $table->string('ip_consentimento')->nullable();
            $table->boolean('pedido_exclusao')->default(false);
            $table->timestamp('data_exclusao')->nullable();
            // Timestamps
            $table->timestamps();
            $table->softDeletes(); // Para "exclusão suave"

            // Índices para otimização de busca
            $table->index('tipo');
            $table->index('ativo');
            // Nota: Índices em campos criptografados podem ter limitações de performance.
            // Para buscas eficientes em campos criptografados, geralmente se usa uma abordagem diferente (ex: hash de busca).
            // Por enquanto, manteremos o índice para o campo 'cpf_cnpj' para referência.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pessoas');
    }
};
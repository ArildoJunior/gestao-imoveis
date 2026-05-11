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
        Schema::create('acoes_judiciais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->onDelete('cascade');
            $table->string('tipo_acao'); // DESPEJO, COBRANCA, DESPEJO_E_COBRANCA, OUTRA
            $table->string('status'); // EM_ANDAMENTO, ACORDO_REALIZADO, ENCERRADA_SEM_ACORDO, SUSPENSA
            $table->string('numero_processo')->nullable()->unique();
            $table->string('vara')->nullable();
            $table->string('comarca')->nullable();
            $table->string('advogado_nome')->nullable();
            $table->string('advogado_telefone')->nullable();
            $table->decimal('valor_cobrado', 10, 2)->default(0.00);
            $table->decimal('valor_recuperado', 10, 2)->default(0.00);
            $table->decimal('custo_advocaticio', 10, 2)->default(0.00);
            $table->boolean('imovel_devolvido')->default(false);
            $table->date('data_entrega_chaves')->nullable();
            $table->string('condicao_imovel_entrega')->nullable(); // BOM, REGULAR, RUIM
            $table->boolean('houve_acordo')->default(false);
            $table->text('descricao_acordo')->nullable();
            $table->decimal('valor_acordo', 10, 2)->default(0.00);
            $table->integer('parcelas_acordo')->nullable();
            $table->boolean('novo_contrato_apos_decisao')->default(false);
            $table->date('data_encerramento')->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('registrado_por_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acoes_judiciais');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_alerta', [
                'PARCELA_ATRASO',
                'PARCELA_A_VENCER',
                'CONTRATO_VENCENDO',
                'CAUCAO_PENDENTE',
                'REAJUSTE_PREVISTO',
                'DESPESA_PENDENTE',
                'PAGAMENTO_TEMPORADA_PENDENTE',
                'ACAO_JUDICIAL', // <--- NOME DO TIPO DE ALERTA ALTERADO AQUI
            ])->default('PARCELA_ATRASO');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->date('data_alerta');
            $table->enum('status', ['PENDENTE', 'VISUALIZADO', 'RESOLVIDO', 'IGNORADO'])->default('PENDENTE');

            // Vínculos opcionais
            $table->foreignId('contrato_id')->nullable()->constrained('contratos')->onDelete('cascade');
            $table->foreignId('parcela_id')->nullable()->constrained('parcelas_aluguel')->onDelete('cascade');
            $table->foreignId('imovel_id')->nullable()->constrained('imoveis')->onDelete('cascade');
            $table->foreignId('despesa_imovel_id')->nullable()->constrained('despesas_imovel')->onDelete('cascade');
            // A foreignId para acao_judicial_id será adicionada por outra migration, conforme o seu projeto.
            // Por enquanto, vamos garantir que a coluna acao_judicial_id exista na tabela alertas.
            // Se a migration '2026_05_06_144751_add_acao_judicial_id_to_alertas_table_after_acoes_judiciais.php' já foi criada e executada,
            // esta linha abaixo não é estritamente necessária aqui, mas é bom para referência.
            $table->foreignId('acao_judicial_id')->nullable()->constrained('acoes_judiciais')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            // Índices para campos de data e relacionamento, se aplicável
            $table->index('contrato_id');
            $table->index('data_alerta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
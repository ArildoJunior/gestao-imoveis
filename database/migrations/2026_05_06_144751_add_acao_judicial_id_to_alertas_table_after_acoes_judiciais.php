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
        Schema::table('alertas', function (Blueprint $table) {
            // Verifica se a coluna 'acao_judicial_id' já existe antes de tentar adicioná-la.
            // Isso previne o erro de coluna duplicada em ambientes onde a coluna já foi criada
            // por outra migração (como a create_alertas_table).
            if (!Schema::hasColumn('alertas', 'acao_judicial_id')) {
                // Adiciona a coluna acao_judicial_id
                $table->unsignedBigInteger('acao_judicial_id')->nullable()->after('despesa_imovel_id');

                // Adiciona a chave estrangeira
                $table->foreign('acao_judicial_id')
                      ->references('id')
                      ->on('acoes_judiciais')
                      ->onDelete('set null'); // Ou 'cascade', dependendo da sua regra de negócio
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            // Verifica se a coluna existe antes de tentar removê-la
            if (Schema::hasColumn('alertas', 'acao_judicial_id')) {
                $table->dropForeign(['acao_judicial_id']);
                $table->dropColumn('acao_judicial_id');
            }
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Importar DB

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            // Se o up() original tornava nullable, ele deve estar assim:
            $table->decimal('valor_aluguel_base', 12, 2)->nullable()->change();
            $table->decimal('valor_aluguel_atual', 12, 2)->nullable()->change();
            $table->string('indice_reajuste', 50)->nullable()->change();
            $table->integer('mes_reajuste')->nullable()->change();
            $table->decimal('percentual_reajuste_padrao', 5, 2)->nullable()->change();
            $table->integer('dia_vencimento')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            // Antes de tornar NOT NULL, atualize os valores NULL para 0
            // Isso é crucial para evitar o erro "Null value not allowed"
            DB::statement("UPDATE contratos SET valor_aluguel_base = 0 WHERE valor_aluguel_base IS NULL");
            DB::statement("UPDATE contratos SET valor_aluguel_atual = 0 WHERE valor_aluguel_atual IS NULL");
            DB::statement("UPDATE contratos SET mes_reajuste = 1 WHERE mes_reajuste IS NULL"); // Mês padrão, ex: 1
            DB::statement("UPDATE contratos SET percentual_reajuste_padrao = 0 WHERE percentual_reajuste_padrao IS NULL");
            DB::statement("UPDATE contratos SET dia_vencimento = 1 WHERE dia_vencimento IS NULL"); // Dia padrão, ex: 1

            // Agora você pode reverter para NOT NULL
            $table->decimal('valor_aluguel_base', 12, 2)->nullable(false)->change();
            $table->decimal('valor_aluguel_atual', 12, 2)->nullable(false)->change();
            // Para strings, você pode definir um valor padrão ou deixar nullable se for o caso
            // Se o original era NOT NULL, você pode definir um valor padrão como ''
            DB::statement("UPDATE contratos SET indice_reajuste = 'SEM_REAJUSTE' WHERE indice_reajuste IS NULL");
            $table->string('indice_reajuste', 50)->nullable(false)->change();
            $table->integer('mes_reajuste')->nullable(false)->change();
            $table->decimal('percentual_reajuste_padrao', 5, 2)->nullable(false)->change();
            $table->integer('dia_vencimento')->nullable(false)->change();
        });
    }
};
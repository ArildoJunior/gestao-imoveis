<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acoes_judiciais', function (Blueprint $table) {
            $table->decimal('valor_cobrado', 10, 2)->nullable()->change();
            $table->decimal('valor_recuperado', 10, 2)->nullable()->change();
            $table->decimal('custo_advocaticio', 10, 2)->nullable()->change();
            $table->decimal('valor_acordo', 10, 2)->nullable()->change();
            $table->integer('parcelas_acordo')->nullable()->change();
        });
    }
public function down(): void
{
    Schema::table('acoes_judiciais', function (Blueprint $table) {
        // Reverter para NOT NULL se for o caso, ou deixar como estava
        // $table-&gt;decimal('valor_cobrado', 10, 2)-&gt;nullable(false)-&gt;change();
        // ... e assim por diante para os outros campos
    });
}
};
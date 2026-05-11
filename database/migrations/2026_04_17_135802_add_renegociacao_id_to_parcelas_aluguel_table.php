<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcelas_aluguel', function (Blueprint $table) {
            $table->foreignId('renegociacao_id')
                  ->nullable()
                  ->after('tipo_origem')
                  ->constrained('renegociacoes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('parcelas_aluguel', function (Blueprint $table) {
            $table->dropForeign(['renegociacao_id']);
            $table->dropColumn('renegociacao_id');
        });
    }
};
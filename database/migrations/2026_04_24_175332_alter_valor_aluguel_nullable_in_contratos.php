<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->decimal('valor_aluguel_base', 12, 2)->nullable()->change();
            $table->decimal('valor_aluguel_atual', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->decimal('valor_aluguel_base', 12, 2)->nullable(false)->change();
            $table->decimal('valor_aluguel_atual', 12, 2)->nullable(false)->change();
        });
    }
};
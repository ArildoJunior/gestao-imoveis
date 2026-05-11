<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // IMPORTANTE: listar TODOS os valores usados no enum
        DB::statement("
            ALTER TABLE contratos
            MODIFY COLUMN tipo_contrato
            ENUM('RESIDENCIAL', 'COMERCIAL', 'TEMPORADA')
            NOT NULL
            DEFAULT 'RESIDENCIAL'
        ");
    }

    public function down(): void
    {
        // Reverte para o ENUM anterior (sem TEMPORADA)
        DB::statement("
            ALTER TABLE contratos
            MODIFY COLUMN tipo_contrato
            ENUM('RESIDENCIAL', 'COMERCIAL')
            NOT NULL
            DEFAULT 'RESIDENCIAL'
        ");
    }
};
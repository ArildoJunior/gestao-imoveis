<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $enumTypeName = 'parcelas_aluguel_status';
        $oldEnumValues = ['ABERTA', 'PAGA', 'PAGA_PARCIALMENTE', 'EM_ATRASO', 'RENEGOCIADA', 'CANCELADA'];
        $newEnumValues = array_merge($oldEnumValues, ['EM_ACORDO', 'JURIDICO', 'PERDIDA']);

        // 1. Remover o tipo ENUM se ele já existir.
        // Isso garante que não haverá erro de "Duplicate object" em caso de re-execução ou migrate:fresh.
        DB::statement("DROP TYPE IF EXISTS {$enumTypeName} CASCADE"); // Adicionado CASCADE para remover dependências

        // 2. Criar o novo tipo ENUM com todos os valores
        DB::statement("CREATE TYPE {$enumTypeName} AS ENUM ('" . implode("','", $newEnumValues) . "')");

        // 3. Remover o valor padrão da coluna antes de alterar o tipo
        DB::statement("ALTER TABLE parcelas_aluguel ALTER COLUMN status DROP DEFAULT");

        // 4. Alterar o tipo da coluna para o novo ENUM
        // O USING status::{$enumTypeName} é crucial para converter os dados existentes
        DB::statement("ALTER TABLE parcelas_aluguel ALTER COLUMN status TYPE {$enumTypeName} USING status::{$enumTypeName}");

        // 5. Adicionar novamente o valor padrão
        DB::statement("ALTER TABLE parcelas_aluguel ALTER COLUMN status SET DEFAULT 'ABERTA'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $enumTypeName = 'parcelas_aluguel_status';
        $originalEnumValues = ['ABERTA', 'PAGA', 'PAGA_PARCIALMENTE', 'EM_ATRASO', 'RENEGOCIADA', 'CANCELADA'];

        // 1. Remover o valor padrão da coluna
        DB::statement("ALTER TABLE parcelas_aluguel ALTER COLUMN status DROP DEFAULT");

        // 2. Alterar o tipo da coluna de volta para TEXT (ou um ENUM com os valores originais)
        // Para simplificar o rollback, podemos voltar para TEXT temporariamente.
        DB::statement("ALTER TABLE parcelas_aluguel ALTER COLUMN status TYPE VARCHAR(50) USING status::VARCHAR(50)");

        // 3. Remover o tipo ENUM
        DB::statement("DROP TYPE IF EXISTS {$enumTypeName} CASCADE"); // Adicionado CASCADE para remover dependências

        // 4. Recriar o tipo ENUM com os valores originais
        DB::statement("CREATE TYPE {$enumTypeName} AS ENUM ('" . implode("','", $originalEnumValues) . "')");

        // 5. Alterar a coluna de volta para o tipo ENUM original
        DB::statement("ALTER TABLE parcelas_aluguel ALTER COLUMN status TYPE {$enumTypeName} USING status::{$enumTypeName}");

        // 6. Adicionar novamente o valor padrão
        DB::statement("ALTER TABLE parcelas_aluguel ALTER COLUMN status SET DEFAULT 'ABERTA'");
    }
};
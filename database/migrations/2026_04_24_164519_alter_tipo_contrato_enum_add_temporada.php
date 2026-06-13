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
        $enumTypeName = 'contratos_tipo_contrato_enum';
        $newEnumValue = 'TEMPORADA';
        $existingEnumValues = ['RESIDENCIAL', 'COMERCIAL']; // Valores que já deveriam existir

        // 1. Verificar se o tipo ENUM já existe. Se não, criá-lo com os valores base.
        $typeExists = DB::select("SELECT 1 FROM pg_type WHERE typname = ?", [$enumTypeName]);

        if (empty($typeExists)) {
            // Se o tipo não existe, criamos ele com os valores base
            DB::statement("CREATE TYPE {$enumTypeName} AS ENUM ('" . implode("','", $existingEnumValues) . "')");
            // E então alteramos a coluna para usar este novo tipo
            // Assumimos que a coluna 'tipo_contrato' já existe como TEXT ou VARCHAR
            // Removendo o DEFAULT antes de alterar o tipo
            DB::statement("ALTER TABLE contratos ALTER COLUMN tipo_contrato DROP DEFAULT");
            DB::statement("ALTER TABLE contratos ALTER COLUMN tipo_contrato TYPE {$enumTypeName} USING tipo_contrato::TEXT::{$enumTypeName}");
            // Adicionando o DEFAULT novamente
            DB::statement("ALTER TABLE contratos ALTER COLUMN tipo_contrato SET DEFAULT 'RESIDENCIAL'");
        }

        // 2. Adicionar o novo valor 'TEMPORADA' ao tipo ENUM, se ainda não existir
        $valueExists = DB::select("SELECT 1 FROM pg_enum WHERE enumtypid = (SELECT oid FROM pg_type WHERE typname = ?) AND enumlabel = ?", [$enumTypeName, $newEnumValue]);

        if (empty($valueExists)) {
            // Adiciona o novo valor 'TEMPORADA' após 'COMERCIAL'
            DB::statement("ALTER TYPE {$enumTypeName} ADD VALUE '{$newEnumValue}' AFTER 'COMERCIAL'");
        }

        // Se o tipo ENUM já existia e a coluna já era do tipo ENUM,
        // precisamos garantir que o DEFAULT seja tratado.
        // Se a coluna já é do tipo ENUM, o DEFAULT já deve ser compatível.
        // No entanto, para garantir, podemos redefinir o DEFAULT.
        // DB::statement("ALTER TABLE contratos ALTER COLUMN tipo_contrato DROP DEFAULT");
        // DB::statement("ALTER TABLE contratos ALTER COLUMN tipo_contrato SET DEFAULT 'RESIDENCIAL'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $enumTypeName = 'contratos_tipo_contrato_enum';
        $originalEnumValues = ['RESIDENCIAL', 'COMERCIAL'];

        // 1. Remover o valor padrão da coluna
        DB::statement("ALTER TABLE contratos ALTER COLUMN tipo_contrato DROP DEFAULT");

        // 2. Alterar a coluna para um tipo temporário (VARCHAR)
        DB::statement("ALTER TABLE contratos ALTER COLUMN tipo_contrato TYPE VARCHAR(50) USING tipo_contrato::TEXT");

        // 3. Remover o tipo ENUM existente, se houver
        DB::statement("DROP TYPE IF EXISTS {$enumTypeName} CASCADE");

        // 4. Recriar o tipo ENUM com os valores originais (sem 'TEMPORADA')
        DB::statement("CREATE TYPE {$enumTypeName} AS ENUM ('" . implode("','", $originalEnumValues) . "')");

        // 5. Alterar a coluna de volta para o tipo ENUM original
        DB::statement("ALTER TABLE contratos ALTER COLUMN tipo_contrato TYPE {$enumTypeName} USING tipo_contrato::TEXT::{$enumTypeName}");

        // 6. Adicionar novamente o valor padrão
        DB::statement("ALTER TABLE contratos ALTER COLUMN tipo_contrato SET DEFAULT 'RESIDENCIAL'");
    }
};
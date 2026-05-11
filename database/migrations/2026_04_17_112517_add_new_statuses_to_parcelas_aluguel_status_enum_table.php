<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Adicione esta linha

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para MySQL, alterar ENUM diretamente pode ser complicado com Doctrine/DBAL.
        // A forma mais segura é usar o DB::statement.
        // Certifique-se de listar TODOS os status que você usa/usará, incluindo os antigos.
        DB::statement("ALTER TABLE parcelas_aluguel CHANGE status status ENUM('ABERTA', 'PAGA', 'PAGA_PARCIALMENTE', 'EM_ATRASO', 'RENEGOCIADA', 'CANCELADA', 'EM_ACORDO', 'JURIDICO', 'PERDIDA') DEFAULT 'ABERTA'");
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para reverter, você pode voltar para a lista original de ENUMs
        DB::statement("ALTER TABLE parcelas_aluguel CHANGE status status ENUM('ABERTA', 'PAGA', 'PAGA_PARCIALMENTE', 'EM_ATRASO', 'RENEGOCIADA', 'CANCELADA') DEFAULT 'ABERTA'");
    }
};
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
        Schema::create('backups', function (Blueprint $table) {
            $table->id();

            // Tipo de backup: LOCAL, NUVEM, MANUAL, AUTOMATICO
            $table->string('tipo', 20);

            // Caminho completo do arquivo de backup (disco + path)
            $table->string('caminho_arquivo')->nullable();

            // Tamanho do arquivo em bytes (quando sucesso)
            $table->unsignedBigInteger('tamanho_bytes')->nullable();

            // Status: SUCESSO, FALHA
            $table->string('status', 20);

            // Mensagem de erro (se falha)
            $table->text('mensagem_erro')->nullable();

            // Usuário que disparou o backup (quando for manual)
            $table->unsignedBigInteger('realizado_por_user_id')->nullable();

            $table->timestamps();

            $table->foreign('realizado_por_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LimparDadosSeeder extends Seeder
{
    public function run(): void
    {
        // Desativa verificação de chaves estrangeiras
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpa todas as tabelas de dados
        DB::table('acoes_judiciais')->truncate();
        DB::table('alertas')->truncate();
        DB::table('pagamentos')->truncate();
        DB::table('parcelas_aluguel')->truncate();
        DB::table('renegociacoes')->truncate();
        DB::table('reajustes')->truncate();
        DB::table('despesas_imovel')->truncate();
        DB::table('contratos')->truncate();
        DB::table('imoveis')->truncate();
        DB::table('pessoas')->truncate();

        // Limpa usuários EXCETO o administrador
        DB::table('users')
            ->where('email', '!=', 'arildoajunior@gmail.com')
            ->delete();

        // Reativa verificação de chaves estrangeiras
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Banco de dados limpo com sucesso!');
        $this->command->info('✅ Usuário administrador preservado.');
    }
}
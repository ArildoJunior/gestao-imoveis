<?php

namespace App\Console\Commands;

use App\Models\ParcelaAluguel;
use Illuminate\Console\Command;

class LimparParcelasCanceladas extends Command
{
    protected $signature   = 'parcelas:limpar-canceladas {--dry-run : Apenas exibe o que seria removido, sem deletar}';
    protected $description = 'Remove permanentemente parcelas com status CANCELADA geradas por encerramento de contrato';

    public function handle(): int
    {
        $query = ParcelaAluguel::where('status', 'CANCELADA')
            ->where('tipo_origem', 'ALUGUEL_NORMAL');

        $total = $query->count();

        if ($total === 0) {
            $this->info('Nenhuma parcela CANCELADA encontrada.');
            return self::SUCCESS;
        }

        $this->warn("Encontradas {$total} parcela(s) com status CANCELADA.");

        if ($this->option('dry-run')) {
            $this->info('[dry-run] Nenhuma alteração realizada.');
            return self::SUCCESS;
        }

        if (!$this->confirm("Confirma a remoção permanente dessas {$total} parcela(s)?")) {
            $this->info('Operação cancelada.');
            return self::SUCCESS;
        }

        $removidas = $query->forceDelete();

        $this->info("{$removidas} parcela(s) removida(s) com sucesso.");

        return self::SUCCESS;
    }
}
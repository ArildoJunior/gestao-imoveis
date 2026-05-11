<?php

namespace App\Console\Commands;

use App\Models\Backup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class RunAndLogBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * --tipo=MANUAL|AUTOMATICO|LOCAL|NUVEM (default AUTOMATICO se não informado)
     */
    protected $signature = 'sistema:backup
                            {--tipo=AUTOMATICO : Tipo de backup (MANUAL, AUTOMATICO, LOCAL, NUVEM)}';

    /**
     * The console command description.
     */
    protected $description = 'Executa o backup do sistema (via Spatie) e registra o resultado na tabela backups';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tipo = strtoupper($this->option('tipo') ?? 'AUTOMATICO');

        if (! in_array($tipo, ['MANUAL', 'AUTOMATICO', 'LOCAL', 'NUVEM'])) {
            $this->warn("Tipo de backup inválido '{$tipo}', usando AUTOMATICO.");
            $tipo = 'AUTOMATICO';
        }

        $this->info("Iniciando backup do sistema. Tipo: {$tipo}");

        $status = 'SUCESSO';
        $mensagemErro = null;

        try {
            // Executa o command do Spatie (full backup)
            Artisan::call('backup:run', [
                '--only-db' => false,
            ]);

            $output = Artisan::output();
            $this->line($output);
        } catch (Throwable $e) {
            $status = 'FALHA';
            $mensagemErro = $e->getMessage();
            $this->error('Falha ao executar backup: ' . $mensagemErro);
        }

        Backup::create([
            'tipo'                  => $tipo,
            'caminho_arquivo'       => null,
            'tamanho_bytes'         => null,
            'status'                => $status,
            'mensagem_erro'         => $mensagemErro,
            'realizado_por_user_id' => null, // via CLI não temos usuário
        ]);

        $this->info("Registro de backup salvo com status: {$status}");

        return $status === 'SUCESSO' ? self::SUCCESS : self::FAILURE;
    }
}
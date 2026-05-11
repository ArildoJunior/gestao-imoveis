<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Agendamento para gerar alertas a cada 1 hora
        // Você pode ajustar a frequência conforme a necessidade (ex: daily(), everyMinute(), everyFifteenMinutes())
        $schedule->command('sistema:gerar-alertas')->hourly();

        // Exemplo: Limpar cache de views diariamente (opcional, mas boa prática)
        // $schedule->command('view:clear')->daily();

        // Exemplo: Rodar backups diariamente (se o comando de backup for Artisan)
        // $schedule->command('backup:run')->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
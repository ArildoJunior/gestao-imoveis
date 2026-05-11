<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------
        | Middleware global do grupo 'web'
        | Roda em TODAS as requisições web, autenticadas ou não.
        |--------------------------------------------------------------
        | CheckUserStatus:
        |   - Usuário INATIVO  → desloga e redireciona para /login
        |   - Usuário PENDENTE → redireciona para /aguardando-liberacao
        |   (CheckReadOnlyAccess foi removido — substituído pelo
        |    middleware 'perfil' aplicado individualmente nas rotas)
        */
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\CheckUserStatus::class,
        ]);

        /*
        |--------------------------------------------------------------
        | Alias de middleware para uso nas rotas
        | Uso: Route::middleware(['perfil:SECRETARIA,FINANCEIRO'])
        |--------------------------------------------------------------
        */
        $middleware->alias([
            'perfil' => \App\Http\Middleware\CheckProfileAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
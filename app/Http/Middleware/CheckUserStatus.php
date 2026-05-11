<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Rotas que usuários PENDENTES podem acessar mesmo sem perfil liberado.
     */
    private const ROTAS_LIBERADAS_PENDENTE = [
        'aguardando-liberacao',
        'logout',
        'profile',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Usuário INATIVO → desloga imediatamente
        if ($user->status === 'INATIVO') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => 'Sua conta foi inativada. Entre em contato com o administrador.',
            ]);
        }

        // Usuário PENDENTE → só pode acessar rotas liberadas
        if ($user->isPendente()) {
            $rotaAtual = $request->path();

            foreach (self::ROTAS_LIBERADAS_PENDENTE as $rotaLiberada) {
                if (str_starts_with($rotaAtual, $rotaLiberada)) {
                    return $next($request);
                }
            }

            return redirect()->route('aguardando.liberacao');
        }

        return $next($request);
    }
}
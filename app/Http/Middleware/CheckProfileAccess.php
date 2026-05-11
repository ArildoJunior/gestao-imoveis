<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileAccess
{
    /**
     * Mapeamento de perfis para os módulos que eles podem acessar.
     * As chaves são os perfis, e os valores são arrays dos módulos permitidos.
     *
     * IMPORTANTE: O ADMINISTRADOR tem acesso total e não precisa ser listado aqui.
     * O PENDENTE é tratado pelo CheckUserStatus e redirecionado para a tela de espera.
     *
     * Módulos disponíveis (baseados nas rotas e seus prefixos):
     * 'users', 'backups', 'pessoas', 'imoveis', 'contratos', 'caucao',
     * 'despesas-imovel', 'pagamentos', 'financeiro', 'renegociacoes',
     * 'reajustes', 'encerrar', 'relatorios', 'alertas', 'acoes-judiciais'
     */
    private const PERFIL_MODULOS = [
        'SECRETARIA' => [
            'pessoas', 'imoveis', 'contratos', 'caucao', 'despesas-imovel',
            'pagamentos', 'financeiro', 'reajustes', 'encerrar', 'relatorios',
            'alertas', 'acoes-judiciais'
        ],
        'FINANCEIRO' => [
            'pagamentos', 'financeiro', 'renegociacoes', 'reajustes', 'encerrar',
            'relatorios', 'alertas', 'acoes-judiciais'
        ],
        'CORRETOR' => [
            'pessoas', 'imoveis', 'contratos', 'alertas'
        ],
        'PROPRIETARIO' => [
            'pessoas', 'imoveis', 'contratos', 'alertas' // Acesso filtrado por dados próprios será feito no Controller/Query
        ],
        'LOCATARIO' => [
            'pessoas', 'contratos', 'pagamentos', 'alertas' // Acesso filtrado por dados próprios será feito no Controller/Query
        ],
        'PRESTADOR_DE_SERVICO' => [
            'despesas-imovel', 'alertas' // Acesso filtrado por dados próprios será feito no Controller/Query
        ],
    ];

    /**
     * Verifica se o perfil do usuário tem permissão de acesso à rota.
     *
     * Uso nas rotas: ->middleware('perfil:modulo1,modulo2')
     *
     * O ADMINISTRADOR sempre tem acesso total, independente da lista.
     * O PENDENTE é tratado pelo CheckUserStatus.
     */
    public function handle(Request $request, Closure $next, string ...$modulosRequeridos): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        // --- INÍCIO DA DEPURACÃO TEMPORÁRIA ---
        // dd([
        //     'user_id' => $user->id ?? 'N/A',
        //     'user_name' => $user->name ?? 'N/A',
        //     'user_perfil_db' => $user->perfil ?? 'N/A',
        //     'is_administrador_method' => $user->isAdministrador() ?? 'N/A',
        //     'modulos_requeridos' => $modulosRequeridos,
        //     'request_path' => $request->path(),
        // ]);
        // --- FIM DA DEPURACÃO TEMPORÁRIA ---

        // Se não há usuário autenticado, redireciona para o login
        if (! $user) {
            return redirect()->route('login');
        }

        // ADMINISTRADOR tem acesso irrestrito
        if ($user->isAdministrador()) {
            return $next($request);
        }

        // Se o usuário é PENDENTE, ele já deveria ter sido redirecionado pelo CheckUserStatus.
        // Mas, por segurança, se chegar aqui, redirecionamos.
        if ($user->isPendente()) {
            return redirect()->route('aguardando.liberacao');
        }

        // Se nenhum módulo foi declarado na rota, bloqueia por segurança
        if (empty($modulosRequeridos)) {
            return redirect()->route('dashboard')
                ->with('error', 'Acesso negado: nenhum módulo autorizado configurado para esta rota.');
        }

        // Obtém os módulos permitidos para o perfil do usuário
        $modulosPermitidosParaPerfil = self::PERFIL_MODULOS[$user->perfil] ?? [];

        // Verifica se o usuário tem permissão para acessar TODOS os módulos requeridos
        foreach ($modulosRequeridos as $modulo) {
            if (! in_array($modulo, $modulosPermitidosParaPerfil, strict: true)) {
                return redirect()->route('dashboard')
                    ->with('error', 'Acesso negado: seu perfil não tem permissão para acessar o módulo "' . $modulo . '".');
            }
        }

        return $next($request);
    }
}
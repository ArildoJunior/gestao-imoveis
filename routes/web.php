<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\ImovelController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\DespesaImovelController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\RenegociacaoController;
use App\Http\Controllers\ReajusteController;
use App\Http\Controllers\RelatorioFinanceiroImovelController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AcaoJudicialController;
use App\Http\Controllers\UserController;
use App\Models\ParcelaAluguel;
use App\Models\Contrato;
use App\Models\Pagamento;
use App\Models\Alerta;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Redireciona raiz para o dashboard
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('dashboard');
});

require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Rotas protegidas — requerem login
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |----------------------------------------------------------------------
    | AGUARDANDO LIBERAÇÃO
    | Acessível a TODOS os perfis autenticados, incluindo PENDENTE.
    | O CheckUserStatus redireciona perfis PENDENTE para cá.
    |----------------------------------------------------------------------
    */
    Route::get('/aguardando-liberacao', function () {
        return view('aguardando-liberacao');
    })->name('aguardando.liberacao');

    /*
    |----------------------------------------------------------------------
    | DASHBOARD
    | Acessível a todos os perfis (exceto PENDENTE, que é interceptado
    | antes pelo middleware CheckUserStatus).
    |----------------------------------------------------------------------
    */
    Route::get('/dashboard', function () {

        $hoje          = now()->startOfDay();
        $limiteAVencer = now()->addDays(7)->endOfDay();

        // Parcelas em atraso
        $parcelasEmAtraso = ParcelaAluguel::whereIn('status', ['ABERTA', 'EM_ATRASO', 'PAGA_PARCIALMENTE'])
            ->whereDate('data_vencimento', '<', $hoje)
            ->get();

        $totalEmAtrasoQtd   = $parcelasEmAtraso->count();
        $totalEmAtrasoValor = $parcelasEmAtraso->sum(fn($p) => $p->valor_devido - $p->valor_pago);

        // Parcelas a vencer nos próximos 7 dias
        $parcelasAVencer = ParcelaAluguel::whereIn('status', ['ABERTA', 'EM_ATRASO', 'PAGA_PARCIALMENTE'])
            ->whereDate('data_vencimento', '>=', $hoje)
            ->whereDate('data_vencimento', '<=', $limiteAVencer)
            ->get();

        $totalAVencerQtd   = $parcelasAVencer->count();
        $totalAVencerValor = $parcelasAVencer->sum(fn($p) => $p->valor_devido - $p->valor_pago);

        // Recebimentos do mês atual
        $primeiroDiaMes = Carbon::now()->startOfMonth();
        $ultimoDiaMes   = Carbon::now()->endOfMonth();

        $pagamentosMes    = Pagamento::whereBetween('data_pagamento', [$primeiroDiaMes, $ultimoDiaMes])->get();
        $qtdPagamentosMes = $pagamentosMes->count();
        $totalRecebidoMes = $pagamentosMes->sum('valor_pago');

        // Contratos
        $qtdContratosAtivos     = Contrato::where('status', 'ATIVO')->count();
        $qtdContratosEmCobranca = Contrato::where('status', 'EM_COBRANCA_JUDICIAL')->count();

        // Alertas pendentes
        $alertasPendentesQtd = Alerta::where('status', 'PENDENTE')->count();

        return view('dashboard', compact(
            'totalEmAtrasoQtd',
            'totalEmAtrasoValor',
            'totalAVencerQtd',
            'totalAVencerValor',
            'qtdPagamentosMes',
            'totalRecebidoMes',
            'qtdContratosAtivos',
            'qtdContratosEmCobranca',
            'alertasPendentesQtd'
        ));

    })->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | PERFIL DO USUÁRIO
    | Acessível a todos os perfis autenticados.
    |----------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | USUÁRIOS — apenas ADMINISTRADOR
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:users'])->group(function () {

        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggleStatus');

    });

    /*
    |----------------------------------------------------------------------
    | BACKUPS — apenas ADMINISTRADOR
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:backups'])->group(function () {

        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [BackupController::class, 'store'])->name('backups.store');

    });

    /*
    |----------------------------------------------------------------------
    | PESSOAS
    | SECRETARIA, CORRETOR, PROPRIETARIO, LOCATARIO
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:pessoas'])->group(function () {

        Route::resource('pessoas', PessoaController::class);

    });

    /*
    |----------------------------------------------------------------------
    | IMÓVEIS
    | SECRETARIA, CORRETOR, PROPRIETARIO
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:imoveis'])->group(function () {

        Route::resource('imoveis', ImovelController::class)->parameters([
            'imoveis' => 'imovel',
        ]);

    });

    /*
    |----------------------------------------------------------------------
    | CONTRATOS
    | SECRETARIA, CORRETOR, PROPRIETARIO, LOCATARIO
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:contratos'])->group(function () {

        Route::resource('contratos', ContratoController::class)->parameters([
            'contratos' => 'contrato',
        ]);

    });

    /*
    |----------------------------------------------------------------------
    | CAUÇÃO — SECRETARIA
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:caucao'])->group(function () {

        Route::get('contratos/{contrato}/caucao/devolucao', [ContratoController::class, 'devolucaoCaucaoForm'])
            ->name('contratos.caucao.devolucao.form');
        Route::post('contratos/{contrato}/caucao/devolucao', [ContratoController::class, 'devolucaoCaucaoStore'])
            ->name('contratos.caucao.devolucao.store');

    });

    /*
    |----------------------------------------------------------------------
    | DESPESAS DE IMÓVEL
    | SECRETARIA, PRESTADOR_DE_SERVICO
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:despesas-imovel'])->group(function () {

        Route::resource('despesas-imovel', DespesaImovelController::class)->parameters([
            'despesas-imovel' => 'despesa_imovel',
        ]);

    });

    /*
    |----------------------------------------------------------------------
    | PAGAMENTOS DE PARCELAS
    | SECRETARIA, FINANCEIRO, LOCATARIO
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:pagamentos'])->group(function () {

        Route::get('parcelas/{parcela}/pagar', [PagamentoController::class, 'create'])
            ->name('pagamentos.create');
        Route::post('parcelas/{parcela}/pagar', [PagamentoController::class, 'store'])
            ->name('pagamentos.store');
        Route::get('parcelas/{parcela}/pagamentos', [PagamentoController::class, 'showParcelPayments'])
            ->name('pagamentos.parcela.show');

    });

    /*
    |----------------------------------------------------------------------
    | FINANCEIRO
    | FINANCEIRO, SECRETARIA
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:financeiro'])->group(function () {

        Route::get('/financeiro', [FinanceiroController::class, 'index'])
            ->name('financeiro.index');

        Route::post('financeiro/parcelas/{parcela}/acordo', [FinanceiroController::class, 'marcarComoAcordo'])
            ->name('financeiro.parcelas.acordo');
        Route::post('financeiro/parcelas/{parcela}/cancelar', [FinanceiroController::class, 'cancelarParcela'])
            ->name('financeiro.parcelas.cancelar');
        Route::post('financeiro/parcelas/{parcela}/perdida', [FinanceiroController::class, 'marcarComoPerdida'])
            ->name('financeiro.parcelas.perdida');
        Route::post('financeiro/parcelas/{parcela}/juridico', [FinanceiroController::class, 'enviarParaJuridico'])
            ->name('financeiro.parcelas.juridico');

    });

    /*
    |----------------------------------------------------------------------
    | RENEGOCIAÇÃO — FINANCEIRO
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:renegociacoes'])->group(function () {

        Route::get('renegociacoes/create', [RenegociacaoController::class, 'create'])
            ->name('renegociacoes.create');
        Route::post('renegociacoes', [RenegociacaoController::class, 'store'])
            ->name('renegociacoes.store');

    });

    /*
    |----------------------------------------------------------------------
    | REAJUSTE DE CONTRATO
    | SECRETARIA, FINANCEIRO
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:reajustes'])->group(function () {

        Route::get('contratos/{contrato}/reajustes/create', [ReajusteController::class, 'create'])
            ->name('reajustes.create');
        Route::post('contratos/{contrato}/reajustes', [ReajusteController::class, 'store'])
            ->name('reajustes.store');

    });

    /*
    |----------------------------------------------------------------------
    | ENCERRAMENTO DE CONTRATO
    | SECRETARIA, FINANCEIRO
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:encerrar'])->group(function () {

        Route::get('contratos/{contrato}/encerrar', [ContratoController::class, 'encerrarForm'])
            ->name('contratos.encerrar.form');
        Route::post('contratos/{contrato}/encerrar', [ContratoController::class, 'encerrarStore'])
            ->name('contratos.encerrar.store');

    });

    /*
    |----------------------------------------------------------------------
    | RELATÓRIO FINANCEIRO POR IMÓVEL
    | FINANCEIRO, SECRETARIA
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:relatorios'])->group(function () {

        Route::get('relatorios/imovel', [RelatorioFinanceiroImovelController::class, 'index'])
            ->name('relatorios.imovel');

    });

    /*
    |----------------------------------------------------------------------
    | ALERTAS
    | SECRETARIA, FINANCEIRO, CORRETOR, PROPRIETARIO, LOCATARIO
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:alertas'])->group(function () {

        Route::get('alertas', [AlertaController::class, 'index'])
            ->name('alertas.index');
        Route::post('alertas/{alerta}/visualizar', [AlertaController::class, 'marcarComoVisualizado'])
            ->name('alertas.visualizar');
        Route::post('alertas/{alerta}/resolver', [AlertaController::class, 'marcarComoResolvido'])
            ->name('alertas.resolver');
        Route::post('alertas/{alerta}/ignorar', [AlertaController::class, 'marcarComoIgnorado'])
            ->name('alertas.ignorar');

    });

    /*
    |----------------------------------------------------------------------
    | AÇÕES JUDICIAIS
    | SECRETARIA, FINANCEIRO
    |----------------------------------------------------------------------
    */
    Route::middleware(['perfil:acoes-judiciais'])->group(function () {

        Route::resource('acoes-judiciais', AcaoJudicialController::class)->parameters([
            'acoes-judiciais' => 'acao_judicial',
        ]);

    });

});
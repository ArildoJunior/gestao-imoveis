<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\DespesaImovel;
use App\Models\Imovel;
use App\Models\Pagamento;
use App\Models\ParcelaAluguel;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje          = now()->startOfDay();
        $limiteAVencer = now()->addDays(7)->endOfDay();

        // ── Parcelas em atraso ──────────────────────────────────────────
        $parcelasEmAtraso = ParcelaAluguel::whereIn('status', [
                'ABERTA', 'EM_ATRASO', 'PAGA_PARCIALMENTE',
            ])
            ->whereDate('data_vencimento', '<', $hoje)
            ->get();

        $totalEmAtrasoQtd   = $parcelasEmAtraso->count();
        $totalEmAtrasoValor = $parcelasEmAtraso->sum(
            fn($p) => $p->valor_devido - $p->valor_pago
        );

        // ── Parcelas a vencer nos próximos 7 dias ───────────────────────
        $parcelasAVencer = ParcelaAluguel::whereIn('status', [
                'ABERTA', 'EM_ATRASO', 'PAGA_PARCIALMENTE',
            ])
            ->whereDate('data_vencimento', '>=', $hoje)
            ->whereDate('data_vencimento', '<=', $limiteAVencer)
            ->get();

        $totalAVencerQtd   = $parcelasAVencer->count();
        $totalAVencerValor = $parcelasAVencer->sum(
            fn($p) => $p->valor_devido - $p->valor_pago
        );

        // ── Recebimentos do mês atual ───────────────────────────────────
        $primeiroDiaMes = Carbon::now()->startOfMonth();
        $ultimoDiaMes   = Carbon::now()->endOfMonth();

        $pagamentosMes    = Pagamento::whereBetween('data_pagamento', [
            $primeiroDiaMes, $ultimoDiaMes,
        ])->get();

        $qtdPagamentosMes = $pagamentosMes->count();
        $totalRecebidoMes = $pagamentosMes->sum('valor_pago');

        // ── Despesas do mês atual ───────────────────────────────────────
        $totalDespesasMes = DespesaImovel::whereBetween('data_despesa', [
            $primeiroDiaMes, $ultimoDiaMes,
        ])->sum('valor');

        $resultadoMes = $totalRecebidoMes - $totalDespesasMes;

        // ── Contratos ───────────────────────────────────────────────────
        $qtdContratosAtivos     = Contrato::where('status', 'ATIVO')->count();
        $qtdContratosEmCobranca = Contrato::where('status', 'EM_COBRANCA_JUDICIAL')->count();

        // ── Imóveis ─────────────────────────────────────────────────────
        $totalImoveis  = Imovel::where('ativo', true)->count();
        $imoveisOcupados = Imovel::where('ativo', true)
            ->whereHas('contratos', fn($q) => $q->where('status', 'ATIVO'))
            ->count();

        $taxaOcupacao = $totalImoveis > 0
            ? round(($imoveisOcupados / $totalImoveis) * 100, 1)
            : 0;

        // ── Alertas pendentes ───────────────────────────────────────────
        $alertasPendentesQtd = Alerta::where('status', 'PENDENTE')->count();

        // ── Gráfico: Receitas x Despesas (últimos 6 meses) ──────────────
        $chartLabels    = [];
        $chartReceitas  = [];
        $chartDespesas  = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);

            $chartLabels[] = ucfirst($mes->translatedFormat('M/y'));

            $chartReceitas[] = (float) Pagamento::whereYear('data_pagamento', $mes->year)
                ->whereMonth('data_pagamento', $mes->month)
                ->sum('valor_pago');

            $chartDespesas[] = (float) DespesaImovel::whereYear('data_despesa', $mes->year)
                ->whereMonth('data_despesa', $mes->month)
                ->sum('valor');
        }

        // ── Gráfico: Status das parcelas (doughnut) ─────────────────────
        $parcelasStatus = [
            'Pagas'            => ParcelaAluguel::where('status', 'PAGA')->count(),
            'Em Atraso'        => ParcelaAluguel::where('status', 'EM_ATRASO')->count(),
            'Abertas'          => ParcelaAluguel::where('status', 'ABERTA')->count(),
            'Pagas Parcial'    => ParcelaAluguel::where('status', 'PAGA_PARCIALMENTE')->count(),
        ];

        return view('dashboard', compact(
            // Cards principais
            'totalEmAtrasoQtd',
            'totalEmAtrasoValor',
            'totalAVencerQtd',
            'totalAVencerValor',
            'qtdPagamentosMes',
            'totalRecebidoMes',
            'totalDespesasMes',
            'resultadoMes',
            'qtdContratosAtivos',
            'qtdContratosEmCobranca',
            'totalImoveis',
            'imoveisOcupados',
            'taxaOcupacao',
            'alertasPendentesQtd',
            // Gráficos
            'chartLabels',
            'chartReceitas',
            'chartDespesas',
            'parcelasStatus',
        ));
    }
}
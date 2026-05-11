<?php

namespace App\Http\Controllers;

use App\Models\Imovel;
use App\Models\Contrato;
use App\Models\ParcelaAluguel;
use App\Models\Pagamento;
use App\Models\DespesaImovel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RelatorioFinanceiroImovelController extends Controller
{
    public function index(Request $request)
    {
        $imoveis = Imovel::where('ativo', true)
            ->orderBy('descricao')
            ->get();

        $imovelId = $request->input('imovel_id');
        $contratos = collect();
        $dadosRelatorio = null;

        if ($imovelId) {
            $contratos = Contrato::where('imovel_id', $imovelId)
                ->orderBy('codigo')
                ->get();

            $dadosRelatorio = $this->montarRelatorio($request);
        }

        $dataInicioDefault = Carbon::today()->startOfMonth()->format('Y-m-d');
        $dataFimDefault    = Carbon::today()->endOfMonth()->format('Y-m-d');

        return view('relatorios.imovel', compact(
            'imoveis',
            'contratos',
            'dadosRelatorio',
            'dataInicioDefault',
            'dataFimDefault'
        ));
    }

    private function montarRelatorio(Request $request): ?array
    {
        $imovelId      = $request->input('imovel_id');
        $contratoId    = $request->input('contrato_id');
        $dataInicio    = $request->input('data_inicio');
        $dataFim       = $request->input('data_fim');
        $statusDespesa = $request->input('status_despesa');

        if (!$imovelId || !$dataInicio || !$dataFim) {
            return null;
        }

        $dataInicioCarbon = Carbon::parse($dataInicio)->startOfDay();
        $dataFimCarbon    = Carbon::parse($dataFim)->endOfDay();

        $imovel = Imovel::find($imovelId);
        if (!$imovel) {
            return null;
        }

        // ── Receitas (Pagamentos) ──────────────────────────────────────
        $pagamentosQuery = Pagamento::query()
            ->whereBetween('data_pagamento', [$dataInicioCarbon, $dataFimCarbon])
            ->whereHas('parcela.contrato', function ($q) use ($imovelId, $contratoId) {
                $q->where('imovel_id', $imovelId);
                if ($contratoId) {
                    $q->where('id', $contratoId);
                }
            })
            ->with(['parcela.contrato.locatario']);

        $pagamentos   = $pagamentosQuery->get();
        $receitaTotal = $pagamentos->sum('valor_pago');

        // ── Despesas ───────────────────────────────────────────────────
        $despesasQuery = DespesaImovel::query()
            ->where('imovel_id', $imovelId)
            ->whereBetween('data_despesa', [$dataInicioCarbon, $dataFimCarbon])
            ->with(['imovel', 'contrato', 'registradoPor']);

        if ($contratoId) {
            $despesasQuery->where(function ($q) use ($contratoId) {
                $q->whereNull('contrato_id')
                  ->orWhere('contrato_id', $contratoId);
            });
        }

        if ($statusDespesa) {
            $despesasQuery->where('status', $statusDespesa);
        }

        $despesas     = $despesasQuery->get();
        $despesaTotal = $despesas->sum('valor');
        $resultado    = $receitaTotal - $despesaTotal;

        // ── Parcelas em atraso por inquilino ───────────────────────────
        // Busca todas as parcelas não quitadas cujo vencimento já passou
        // (até o fim do período do relatório), agrupadas por contrato/locatário
        $parcelasAtrasoQuery = ParcelaAluguel::query()
            ->whereIn('status', ['ABERTA', 'EM_ATRASO', 'PAGA_PARCIALMENTE'])
            ->whereDate('data_vencimento', '<=', $dataFimCarbon)
            ->whereHas('contrato', function ($q) use ($imovelId, $contratoId) {
                $q->where('imovel_id', $imovelId);
                if ($contratoId) {
                    $q->where('id', $contratoId);
                }
            })
            ->with(['contrato.locatario'])
            ->orderBy('data_vencimento');

        $parcelasEmAtraso = $parcelasAtrasoQuery->get();

        // Agrupa por locatário para exibição resumida
        $inadimplenciasPorLocatario = $parcelasEmAtraso
            ->groupBy(fn($p) => $p->contrato?->locatario?->id ?? 0)
            ->map(function ($parcelas) {
                $primeiraParcelaComContrato = $parcelas->first();
                $saldoDevedor = $parcelas->sum(fn($p) => $p->valor_devido - $p->valor_pago);

                return [
                    'locatario'      => $primeiraParcelaComContrato->contrato?->locatario,
                    'contrato'       => $primeiraParcelaComContrato->contrato,
                    'qtd_parcelas'   => $parcelas->count(),
                    'saldo_devedor'  => $saldoDevedor,
                    'parcelas'       => $parcelas,
                    'mais_antiga'    => $parcelas->min('data_vencimento'),
                ];
            })
            ->values();

        $totalInadimplencia = $parcelasEmAtraso->sum(
            fn($p) => $p->valor_devido - $p->valor_pago
        );

        return [
            'imovel'                       => $imovel,
            'pagamentos'                   => $pagamentos,
            'despesas'                     => $despesas,
            'receita_total'                => $receitaTotal,
            'despesa_total'                => $despesaTotal,
            'resultado'                    => $resultado,
            'data_inicio'                  => $dataInicioCarbon,
            'data_fim'                     => $dataFimCarbon,
            'contrato_id'                  => $contratoId,
            'status_despesa'               => $statusDespesa,
            // Novos
            'parcelas_em_atraso'           => $parcelasEmAtraso,
            'inadimplencias_por_locatario' => $inadimplenciasPorLocatario,
            'total_inadimplencia'          => $totalInadimplencia,
        ];
    }
}
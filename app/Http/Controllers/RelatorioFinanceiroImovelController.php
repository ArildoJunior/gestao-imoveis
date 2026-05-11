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
    /**
     * Exibe o formulário de filtros e o relatório (se filtros preenchidos).
     */
    public function index(Request $request)
    {
        $imoveis = Imovel::where('ativo', true)
            ->orderBy('descricao')
            ->get();

        // Se ainda não escolheu imóvel, não carrega nada pesado
        $imovelId = $request->input('imovel_id');

        $contratos = collect();

        $dadosRelatorio = null;

        if ($imovelId) {
            $contratos = Contrato::where('imovel_id', $imovelId)
                ->orderBy('codigo')
                ->get();

            $dadosRelatorio = $this->montarRelatorio($request);
        }

        // Data início/fim defaults
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

    /**
     * Monta os dados do relatório a partir dos filtros.
     */
    private function montarRelatorio(Request $request): ?array
    {
        $imovelId   = $request->input('imovel_id');
        $contratoId = $request->input('contrato_id');
        $dataInicio = $request->input('data_inicio');
        $dataFim    = $request->input('data_fim');
        $statusDespesa = $request->input('status_despesa'); // opcional

        if (!$imovelId || !$dataInicio || !$dataFim) {
            return null;
        }

        $dataInicioCarbon = Carbon::parse($dataInicio)->startOfDay();
        $dataFimCarbon    = Carbon::parse($dataFim)->endOfDay();

        // --------- Imóvel selecionado ---------
        $imovel = Imovel::find($imovelId);

        if (!$imovel) {
            return null;
        }

        // --------- Receitas (Pagamentos) ---------

        $pagamentosQuery = Pagamento::query()
            ->whereBetween('data_pagamento', [$dataInicioCarbon, $dataFimCarbon])
            ->whereHas('parcela.contrato', function ($q) use ($imovelId, $contratoId) {
                $q->where('imovel_id', $imovelId);
                if ($contratoId) {
                    $q->where('id', $contratoId);
                }
            })
            ->with(['parcela.contrato.locatario']);

        $pagamentos = $pagamentosQuery->get();

        $receitaTotal = $pagamentos->sum('valor_pago');

        // --------- Despesas ---------

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

        $despesas = $despesasQuery->get();

        $despesaTotal = $despesas->sum('valor');

        // --------- Resultado ---------

        $resultado = $receitaTotal - $despesaTotal;

        return [
            'imovel'         => $imovel,
            'pagamentos'     => $pagamentos,
            'despesas'       => $despesas,
            'receita_total'  => $receitaTotal,
            'despesa_total'  => $despesaTotal,
            'resultado'      => $resultado,
            'data_inicio'    => $dataInicioCarbon,
            'data_fim'       => $dataFimCarbon,
            'contrato_id'    => $contratoId,
            'status_despesa' => $statusDespesa,
        ];
    }
}
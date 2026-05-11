<?php

namespace App\Http\Controllers;

use App\Models\Imovel;
use App\Models\Pessoa;
use App\Models\Pagamento;
use App\Models\DespesaImovel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RelatorioDespesasReceitasController extends Controller
{
    public function index(Request $request)
    {
        // Filtros com valores padrão
        $dataInicio = $request->input('data_inicio', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $dataFim    = $request->input('data_fim', Carbon::today()->endOfMonth()->format('Y-m-d'));
        $proprietarioId = $request->input('proprietario_id');
        $imovelId = $request->input('imovel_id');

        $dadosRelatorio = null;
        $imoveisComDados = collect();

        // Se houver data de início e fim, processa o relatório
        if ($dataInicio && $dataFim) {
            $dataInicioCarbon = Carbon::parse($dataInicio)->startOfDay();
            $dataFimCarbon    = Carbon::parse($dataFim)->endOfDay();

            // Query base para imóveis
            $imoveisQuery = Imovel::query();

            if ($proprietarioId) {
                $imoveisQuery->where('proprietario_id', $proprietarioId);
            }
            if ($imovelId) {
                $imoveisQuery->where('id', $imovelId);
            }

            $imoveis = $imoveisQuery->get();

            $receitaTotalGeral = 0;
            $despesaTotalGeral = 0;

            foreach ($imoveis as $imovel) {
                // Receitas (Pagamentos) para o imóvel
                $pagamentos = Pagamento::whereBetween('data_pagamento', [$dataInicioCarbon, $dataFimCarbon])
                    ->whereHas('parcela.contrato', function ($q) use ($imovel) {
                        $q->where('imovel_id', $imovel->id);
                    })
                    ->sum('valor_pago');

                // Despesas para o imóvel
                $despesas = DespesaImovel::whereBetween('data_despesa', [$dataInicioCarbon, $dataFimCarbon])
                    ->where('imovel_id', $imovel->id)
                    ->sum('valor');

                $resultadoImovel = $pagamentos - $despesas;

                // Adiciona o imóvel apenas se houver movimentação financeira
                if ($pagamentos > 0 || $despesas > 0) {
                    $imoveisComDados->push((object)[
                        'id' => $imovel->id,
                        'descricao' => $imovel->descricao,
                        'proprietario_nome' => $imovel->proprietario->nome ?? 'N/A',
                        'receita' => $pagamentos,
                        'despesa' => $despesas,
                        'resultado' => $resultadoImovel,
                    ]);
                    $receitaTotalGeral += $pagamentos;
                    $despesaTotalGeral += $despesas;
                }
            }

            $dadosRelatorio = [
                'imoveis_com_dados' => $imoveisComDados->sortByDesc('resultado'), // Ordena por resultado
                'receita_total_geral' => $receitaTotalGeral,
                'despesa_total_geral' => $despesaTotalGeral,
                'resultado_total_geral' => $receitaTotalGeral - $despesaTotalGeral,
                'data_inicio' => $dataInicioCarbon,
                'data_fim' => $dataFimCarbon,
            ];
        }

        // Dados para os filtros
        $proprietarios = Pessoa::whereIn('tipo', ['PROPRIETARIO', 'AMBOS'])
            ->orderBy('nome')
            ->get();
        $todosImoveis = Imovel::orderBy('descricao')->get();

        return view('relatorios.despesas-receitas', compact(
            'dadosRelatorio',
            'proprietarios',
            'todosImoveis',
            'dataInicio',
            'dataFim',
            'proprietarioId',
            'imovelId'
        ));
    }
}
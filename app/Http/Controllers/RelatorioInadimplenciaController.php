<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RelatorioInadimplenciaController extends Controller
{
    public function index(Request $request)
    {
        $dataFim     = $request->input('data_fim', Carbon::today()->format('Y-m-d'));
        $locatarioId = $request->input('locatario_id');
        $contratoId  = $request->input('contrato_id');

        // Inclui ABERTA pois muitos sistemas não atualizam status automaticamente
        $statusInadimplentes = ['ABERTA', 'EM_ATRASO', 'PAGA_PARCIALMENTE'];

        $contratosQuery = Contrato::with([
            'locatario',
            'imovel',
            'parcelas' => function ($query) use ($dataFim, $statusInadimplentes) {
                $query->whereIn('status', $statusInadimplentes)
                      ->where('data_vencimento', '<', Carbon::today()->startOfDay())
                      ->where('data_vencimento', '<=', $dataFim)
                      ->where(function ($q) {
                          $q->whereColumn('valor_pago', '<', 'valor_devido')
                            ->orWhereNull('valor_pago');
                      })
                      ->orderBy('data_vencimento');
            }
        ])
        ->whereHas('parcelas', function ($query) use ($dataFim, $statusInadimplentes) {
            $query->whereIn('status', $statusInadimplentes)
                  ->where('data_vencimento', '<', Carbon::today()->startOfDay())
                  ->where('data_vencimento', '<=', $dataFim)
                  ->where(function ($q) {
                      $q->whereColumn('valor_pago', '<', 'valor_devido')
                        ->orWhereNull('valor_pago');
                  });
        });

        if ($locatarioId) {
            $contratosQuery->where('locatario_id', $locatarioId);
        }

        if ($contratoId) {
            $contratosQuery->where('id', $contratoId);
        }

        $contratos = $contratosQuery->orderBy('codigo')->get();

        // Calcula total devido por contrato e total geral
        $totalGeral = 0;
        foreach ($contratos as $contrato) {
            $contrato->total_devido = $contrato->parcelas->sum(function ($parcela) {
                return ($parcela->valor_devido ?? 0) - ($parcela->valor_pago ?? 0);
            });
            $totalGeral += $contrato->total_devido;
        }

        $locatarios = Pessoa::whereIn('tipo', ['LOCATARIO', 'AMBOS'])
            ->orderBy('nome')
            ->get();

        $todosContratos = Contrato::orderBy('codigo')->get();

        return view('relatorios.inadimplencia', compact(
            'contratos',
            'totalGeral',
            'locatarios',
            'todosContratos',
            'dataFim',
            'locatarioId',
            'contratoId'
        ));
    }
}
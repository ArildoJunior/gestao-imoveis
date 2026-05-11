<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\ParcelaAluguel;
use App\Models\Reajuste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReajusteController extends Controller
{
    public function create(Contrato $contrato)
    {
        $indiceReajuste   = $contrato->indice_reajuste ?? null;
        $percentualPadrao = $contrato->percentual_reajuste_padrao ?? null;
        $valorAtual       = $contrato->valor_aluguel_atual;

        return view('reajustes.create', compact(
            'contrato',
            'indiceReajuste',
            'percentualPadrao',
            'valorAtual'
        ));
    }

    public function store(Request $request, Contrato $contrato)
    {
        $valorAnterior = (float) $contrato->valor_aluguel_atual;

        $request->validate([
            'percentual_aplicado' => 'required|numeric|min:-100|max:500',
            'descricao'           => 'nullable|string|max:255',
        ]);

        $percentual = (float) $request->input('percentual_aplicado');

        $valorNovo = round($valorAnterior * (1 + $percentual / 100), 2);
        if ($valorNovo < 0) {
            $valorNovo = 0.00;
        }

        $hoje = Carbon::today();

        // DATA DE CORTE DO REAJUSTE:
        // 1 ano após data_inicio do contrato
        $dataInicioContrato = $contrato->data_inicio
            ? Carbon::parse($contrato->data_inicio)->startOfDay()
            : $hoje;

        $dataCorte = $dataInicioContrato->copy()->addYear()->startOfDay();

        DB::beginTransaction();

        try {
            // registra reajuste (tabela reajustes precisa existir)
            Reajuste::create([
                'contrato_id'         => $contrato->id,
                'data_reajuste'       => $hoje,
                'indice_reajuste'     => $contrato->indice_reajuste ?? 'NEGOCIADO',
                'percentual_aplicado' => $percentual,
                'valor_anterior'      => $valorAnterior,
                'valor_novo'          => $valorNovo,
                'descricao'           => $request->input('descricao'),
                'aprovado_por_user_id'=> Auth::id(),
            ]);

            // atualiza contrato
            $contrato->valor_aluguel_atual = $valorNovo;
            $contrato->save();

            // atualiza parcelas A PARTIR da data de corte (ex.: 01/01/2026)
            $parcelasParaAtualizar = ParcelaAluguel::where('contrato_id', $contrato->id)
                ->whereIn('status', ['ABERTA', 'EM_ATRASO', 'PAGA_PARCIALMENTE'])
                ->whereDate('data_vencimento', '>=', $dataCorte)
                ->get();

            foreach ($parcelasParaAtualizar as $parcela) {
                $parcela->valor_original = $valorNovo;

                if (bccomp($parcela->valor_pago, 0, 2) === 0) {
                    $parcela->valor_devido = $valorNovo;
                } else {
                    $parcela->valor_devido = max($valorNovo, (float) $parcela->valor_pago);
                }

                $parcela->save();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('contratos.show', $contrato->id)
                ->with('error', 'Erro ao aplicar reajuste: ' . $e->getMessage());
        }

        return redirect()
            ->route('contratos.show', $contrato->id)
            ->with('success', 'Reajuste aplicado com sucesso. Valor anterior: R$ ' .
                number_format($valorAnterior, 2, ',', '.') . ' → Valor novo: R$ ' .
                number_format($valorNovo, 2, ',', '.') . ' (' . number_format($percentual, 2, ',', '.') . '%).');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\ParcelaAluguel;
use App\Models\Renegociacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RenegociacaoController extends Controller
{
    /**
     * Exibe o formulário para iniciar uma nova renegociação.
     * Recebe os IDs das parcelas selecionadas via GET (parcelas_ids[]).
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        $parcelasIds = $request->input('parcelas_ids', []);

        // Garante que é um array e remove valores vazios
        $parcelasIds = array_filter((array) $parcelasIds);

        if (empty($parcelasIds)) {
            return redirect()->route('financeiro.index')
                ->with('error', 'Nenhuma parcela selecionada para renegociação.');
        }

        // Busca as parcelas com relacionamentos necessários
        $parcelas = ParcelaAluguel::whereIn('id', $parcelasIds)
            ->whereIn('status', ['ABERTA', 'EM_ATRASO'])
            ->with('contrato.locatario', 'contrato.imovel')
            ->get();

        // Verifica se sobrou alguma parcela após o filtro de status
        if ($parcelas->isEmpty()) {
            return redirect()->route('financeiro.index')
                ->with('error', 'Nenhuma das parcelas selecionadas está com status elegível para renegociação (ABERTA ou EM_ATRASO).');
        }

        // Verifica se todas as parcelas pertencem ao mesmo contrato
        $contratosUnicos = $parcelas->pluck('contrato_id')->unique();

        if ($contratosUnicos->count() > 1) {
            return redirect()->route('financeiro.index')
                ->with('error', 'Selecione parcelas de apenas um contrato por vez para renegociar.');
        }

        $contratoId = $contratosUnicos->first();
        $contrato   = Contrato::with('locatario', 'imovel')->find($contratoId);

        if (!$contrato) {
            return redirect()->route('financeiro.index')
                ->with('error', 'Contrato não encontrado.');
        }

        // Calcula o valor total original das parcelas selecionadas
        $valorOriginalTotal = $parcelas->sum('valor_devido');

        return view('renegociacoes.create', compact('parcelas', 'contrato', 'valorOriginalTotal'));
    }

    /**
     * Armazena uma nova renegociação e gera as novas parcelas de acordo.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'contrato_id'                => 'required|exists:contratos,id',
            'parcelas_ids'               => 'required|array|min:1',
            'parcelas_ids.*'             => 'required|integer|exists:parcelas_aluguel,id',
            'valor_acordado'             => 'required|numeric|min:0.01',
            'descontos_concedidos'       => 'nullable|numeric|min:0',
            'descricao_acordo'           => 'nullable|string|max:500',
            'numero_parcelas_acordo'     => 'required|integer|min:1|max:360',
            'dia_vencimento_acordo'      => 'required|integer|min:1|max:31',
            'primeiro_vencimento_acordo' => 'required|date|after_or_equal:today',
        ]);

        DB::beginTransaction();

        try {
            // Re-valida as parcelas no backend: devem pertencer ao contrato informado
            // e ainda estar com status elegível (evita manipulação de requisição)
            $parcelasOriginais = ParcelaAluguel::whereIn('id', $request->parcelas_ids)
                ->where('contrato_id', $request->contrato_id)
                ->whereIn('status', ['ABERTA', 'EM_ATRASO'])
                ->get();

            if ($parcelasOriginais->isEmpty()) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Nenhuma parcela válida encontrada para renegociação. Verifique os status e o contrato.');
            }

            if ($parcelasOriginais->count() !== count($request->parcelas_ids)) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Algumas parcelas selecionadas não são elegíveis para renegociação ou não pertencem ao contrato informado.');
            }

            // Calcula o valor original total com base nas parcelas validadas no backend
            $valorOriginalTotal = $parcelasOriginais->sum('valor_devido');

            // Calcula desconto concedido
            $descontoConcedido = $valorOriginalTotal - $request->valor_acordado;
            $descontoConcedido = max(0, $descontoConcedido); // Nunca negativo

            // 1. Cria o registro de Renegociacao
            $renegociacao = Renegociacao::create([
                'contrato_id'                => $request->contrato_id,
                'data_acordo'                => Carbon::now(),
                'valor_original_total'       => $valorOriginalTotal,
                'valor_acordado'             => $request->valor_acordado,
                'descontos_concedidos'       => $descontoConcedido,
                'descricao_acordo'           => $request->descricao_acordo,
                'numero_parcelas_acordo'     => $request->numero_parcelas_acordo,
                'dia_vencimento_acordo'      => $request->dia_vencimento_acordo,
                'primeiro_vencimento_acordo' => $request->primeiro_vencimento_acordo,
                'aprovado_por_user_id'       => Auth::id(),
            ]);

            // 2. Marca as parcelas originais como RENEGOCIADA e vincula à renegociação
            ParcelaAluguel::whereIn('id', $parcelasOriginais->pluck('id'))
                ->update([
                    'status'          => 'RENEGOCIADA',
                    'renegociacao_id' => $renegociacao->id,
                ]);

            // 3. Gera as novas parcelas do acordo
            $primeiroVencimento = Carbon::parse($request->primeiro_vencimento_acordo);
            $numeroParcelas     = (int) $request->numero_parcelas_acordo;
            $diaVencimento      = (int) $request->dia_vencimento_acordo;

            // Divide o valor acordado igualmente, ajustando centavos na última parcela
            $valorTotal       = round((float) $request->valor_acordado, 2);
            $valorPorParcela  = round($valorTotal / $numeroParcelas, 2);
            $somaParcelas     = round($valorPorParcela * $numeroParcelas, 2);
            $ajusteCentavos   = round($valorTotal - $somaParcelas, 2); // Diferença de arredondamento

            for ($i = 0; $i < $numeroParcelas; $i++) {
                // Calcula a data de vencimento de cada parcela do acordo
                $dataVencimento = $primeiroVencimento->copy()
                    ->addMonths($i)
                    ->setDay($diaVencimento);

                // Aplica ajuste de centavos apenas na última parcela
                $valorEstaParcela = ($i === $numeroParcelas - 1)
                    ? round($valorPorParcela + $ajusteCentavos, 2)
                    : $valorPorParcela;

                ParcelaAluguel::create([
                    'contrato_id'     => $request->contrato_id,
                    'renegociacao_id' => $renegociacao->id,
                    'competencia'     => $dataVencimento->format('Y-m'),
                    'numero_parcela'  => $i + 1,
                    'total_parcelas'  => $numeroParcelas,
                    'data_vencimento' => $dataVencimento->toDateString(),
                    'valor_original'  => $valorEstaParcela,
                    'valor_devido'    => $valorEstaParcela,
                    'valor_pago'      => 0,
                    'status'          => 'ABERTA',
                    'tipo_origem'     => 'ACORDO_ATRASO',
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar renegociação: ' . $e->getMessage());
        }

        return redirect()->route('financeiro.index')
            ->with('success', 'Renegociação criada com sucesso! ' . $numeroParcelas . ' parcela(s) de acordo gerada(s).');
    }
}
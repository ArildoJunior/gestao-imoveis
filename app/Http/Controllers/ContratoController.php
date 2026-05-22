<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Imovel;
use App\Models\Pessoa;
use App\Models\ParcelaAluguel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ContratoController extends Controller
{
    public function index()
    {
        $contratos = Contrato::with(['imovel', 'locatario', 'proprietario'])
            ->orderBy('codigo')
            ->paginate(10);

        return view('contratos.index', compact('contratos'));
    }

    private function getContratoOptions(): array
    {
        return [
            'tiposContrato' => [
                'RESIDENCIAL' => 'Residencial',
                'COMERCIAL'   => 'Comercial',
                'TEMPORADA'   => 'Temporada',
            ],
            'indicesReajuste' => [
                'IPCA'         => 'IPCA',
                'IGP-M'        => 'IGP-M',
                'INPC'         => 'INPC',
                'NEGOCIADO'    => 'Negociado',
                'SEM_REAJUSTE' => 'Sem reajuste',
            ],
            'statusContrato' => [
                'ATIVO'                => 'Ativo',
                'ENCERRADO'            => 'Encerrado',
                'EM_COBRANCA_JUDICIAL' => 'Em cobrança judicial',
                'RESCINDIDO'           => 'Rescindido',
            ],
        ];
    }

    public function create()
    {
        $imoveis       = Imovel::where('ativo', true)->orderBy('descricao')->get();
        $locatarios    = Pessoa::whereIn('tipo', ['LOCATARIO', 'AMBOS'])->where('ativo', true)->orderBy('nome')->get();
        $proprietarios = Pessoa::whereIn('tipo', ['PROPRIETARIO', 'AMBOS'])->where('ativo', true)->orderBy('nome')->get();

        $options         = $this->getContratoOptions();
        $tiposContrato   = $options['tiposContrato'];
        $indicesReajuste = $options['indicesReajuste'];
        $statusContrato  = $options['statusContrato'];

        return view('contratos.create', compact(
            'imoveis',
            'locatarios',
            'proprietarios',
            'tiposContrato',
            'indicesReajuste',
            'statusContrato'
        ));
    }

    public function store(Request $request)
    {
        $options = $this->getContratoOptions();
        $data    = $this->validateData($request, $options);

        $data['codigo'] = $this->generateContratoCode();

        $data['possui_caucao']             = $request->has('possui_caucao');
        $data['caucao_paga_integralmente'] = $request->has('caucao_paga_integralmente');
        $data['caucao_devolvida']          = $request->has('caucao_devolvida');
        $data['possui_multa_atraso']       = $request->has('possui_multa_atraso');
        $data['possui_juros_moratorios']   = $request->has('possui_juros_moratorios');

        if (!$data['possui_caucao']) {
            $data['meses_caucao']                = 0;
            $data['valor_caucao']                = 0;
            $data['data_pagamento_total_caucao'] = null;
            $data['data_devolucao_caucao']       = null;
            $data['motivo_nao_devolucao_caucao'] = null;
            $data['caucao_paga_integralmente']   = false;
            $data['caucao_devolvida']            = false;
        }

        if (!$data['possui_multa_atraso']) {
            $data['percentual_multa'] = 0;
        }

        if (!$data['possui_juros_moratorios']) {
            $data['percentual_juros_mensal'] = 0;
        }

        DB::beginTransaction();

        try {
            $contrato = Contrato::create($data);

            if ($contrato->tipo_contrato !== 'TEMPORADA') {
                $contrato->gerarParcelasAluguel();
                $contrato->gerarParcelasCaucao();
            } else {
                $contrato->gerarParcelaTemporada();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('contratos.index')
                ->with('error', 'Erro ao criar contrato: ' . $e->getMessage());
        }

        return redirect()
            ->route('contratos.show', $contrato->id)
            ->with('success', 'Contrato criado com sucesso!');
    }

    public function show(Contrato $contrato)
    {
        $contrato->load(['parcelas', 'acoesJudiciais']);

        $totalCaucaoPago = $contrato->parcelas
            ->where('tipo_origem', 'CAUCAO')
            ->sum('valor_pago');

        $contrato->total_caucao_pago = $totalCaucaoPago;

        $dadosTemporada = null;

        if ($contrato->tipo_contrato === 'TEMPORADA') {
            $parcelaTemporada = $contrato->parcelas
                ->where('tipo_origem', 'TEMPORADA')
                ->first();

            if ($parcelaTemporada) {
                $valorTotal     = (float) $parcelaTemporada->valor_devido;
                $valorPago      = (float) $parcelaTemporada->valor_pago;
                $faltaPagar     = max(0, $valorTotal - $valorPago);
                $hoje           = Carbon::today();
                $dataEntrada    = $contrato->data_entrada_prevista
                    ? $contrato->data_entrada_prevista->copy()->startOfDay()
                    : null;

                $diasAteEntrada = null;
                if ($dataEntrada) {
                    $diasAteEntrada = $hoje->diffInDays($dataEntrada, false);
                }

                $dadosTemporada = [
                    'parcela'          => $parcelaTemporada,
                    'valor_total'      => $valorTotal,
                    'valor_pago'       => $valorPago,
                    'falta_pagar'      => $faltaPagar,
                    'data_entrada'     => $dataEntrada,
                    'dias_ate_entrada' => $diasAteEntrada,
                    'pago_integral'    => $faltaPagar <= 0.009,
                    'alerta_7_dias'    => $diasAteEntrada !== null
                        && $diasAteEntrada >= 0
                        && $diasAteEntrada <= 7
                        && $faltaPagar > 0,
                    'alerta_30_dias'   => $diasAteEntrada !== null
                        && $diasAteEntrada > 7
                        && $diasAteEntrada <= 30
                        && $faltaPagar > 0,
                ];
            }
        }

        return view('contratos.show', compact('contrato', 'dadosTemporada'));
    }

    public function edit(Contrato $contrato)
    {
        $imoveis       = Imovel::where('ativo', true)->orderBy('descricao')->get();
        $locatarios    = Pessoa::whereIn('tipo', ['LOCATARIO', 'AMBOS'])->where('ativo', true)->orderBy('nome')->get();
        $proprietarios = Pessoa::whereIn('tipo', ['PROPRIETARIO', 'AMBOS'])->where('ativo', true)->orderBy('nome')->get();

        $options         = $this->getContratoOptions();
        $tiposContrato   = $options['tiposContrato'];
        $indicesReajuste = $options['indicesReajuste'];
        $statusContrato  = $options['statusContrato'];

        return view('contratos.edit', compact(
            'contrato',
            'imoveis',
            'locatarios',
            'proprietarios',
            'tiposContrato',
            'indicesReajuste',
            'statusContrato'
        ));
    }

    public function update(Request $request, Contrato $contrato)
    {
        $options = $this->getContratoOptions();
        $data    = $this->validateData($request, $options);

        $data['possui_caucao']             = $request->has('possui_caucao');
        $data['caucao_paga_integralmente'] = $request->has('caucao_paga_integralmente');
        $data['caucao_devolvida']          = $request->has('caucao_devolvida');
        $data['possui_multa_atraso']       = $request->has('possui_multa_atraso');
        $data['possui_juros_moratorios']   = $request->has('possui_juros_moratorios');

        if (!$data['possui_caucao']) {
            $data['meses_caucao']                = 0;
            $data['valor_caucao']                = 0;
            $data['data_pagamento_total_caucao'] = null;
            $data['data_devolucao_caucao']       = null;
            $data['motivo_nao_devolucao_caucao'] = null;
            $data['caucao_paga_integralmente']   = false;
            $data['caucao_devolvida']            = false;
        }

        if (!$data['possui_multa_atraso']) {
            $data['percentual_multa'] = 0;
        }

        if (!$data['possui_juros_moratorios']) {
            $data['percentual_juros_mensal'] = 0;
        }

        $contrato->update($data);

        return redirect()
            ->route('contratos.show', $contrato->id)
            ->with('success', 'Contrato atualizado com sucesso!');
    }

    public function destroy(Contrato $contrato)
    {
        $contrato->delete();

        return redirect()
            ->route('contratos.index')
            ->with('success', 'Contrato excluído com sucesso!');
    }

    public function encerrarForm(Contrato $contrato)
    {
        if (in_array($contrato->status, ['ENCERRADO', 'RESCINDIDO'])) {
            return redirect()
                ->route('contratos.show', $contrato->id)
                ->with('error', 'Contrato já está encerrado ou rescindido.');
        }

        $hoje = Carbon::today();

        $dataSugestao = $contrato->data_fim_prevista && $contrato->data_fim_prevista->lt($hoje)
            ? $contrato->data_fim_prevista
            : $hoje;

        $valorAluguelAtual = $contrato->valor_aluguel_atual;

        return view('contratos.encerrar', compact(
            'contrato',
            'dataSugestao',
            'valorAluguelAtual'
        ));
    }

    public function encerrarStore(Request $request, Contrato $contrato)
    {
        if (in_array($contrato->status, ['ENCERRADO', 'RESCINDIDO'])) {
            return redirect()
                ->route('contratos.show', $contrato->id)
                ->with('error', 'Contrato já está encerrado ou rescindido.');
        }

        $request->validate([
            'tipo_encerramento' => 'required|in:ENCERRAMENTO_NORMAL,RESCISAO_ANTECIPADA',
            'data_encerramento' => 'required|date',
            'motivo'            => 'nullable|string|max:500',
            'multa_meses'       => 'nullable|integer|min:0|max:3',
        ]);

        $tipoEncerramento  = $request->input('tipo_encerramento');
        $dataEncerramento  = Carbon::parse($request->input('data_encerramento'))->startOfDay();
        $motivo            = $request->input('motivo');
        $multaMeses        = (int) $request->input('multa_meses', 0);
        $valorAluguelAtual = (float) $contrato->valor_aluguel_atual;

        DB::beginTransaction();

        try {
            $novoStatus = 'ENCERRADO';

            if ($tipoEncerramento === 'RESCISAO_ANTECIPADA') {
                $novoStatus = 'RESCINDIDO';

                if ($multaMeses > 0 && $valorAluguelAtual > 0) {
                    $valorMulta = round($multaMeses * $valorAluguelAtual, 2);

                    ParcelaAluguel::create([
                        'contrato_id'     => $contrato->id,
                        'competencia'     => $dataEncerramento->format('Y-m'),
                        'numero_parcela'  => 1,
                        'total_parcelas'  => 1,
                        'data_vencimento' => $dataEncerramento->toDateString(),
                        'valor_original'  => $valorMulta,
                        'valor_devido'    => $valorMulta,
                        'valor_pago'      => 0,
                        'status'          => 'ABERTA',
                        'tipo_origem'     => 'MULTA_RESCISAO',
                    ]);
                }
            }

            /*
             * Remove (soft delete) as parcelas futuras de aluguel sem dívida real.
             * Critérios:
             *   - tipo_origem ALUGUEL_NORMAL
             *   - status ABERTA (sem pagamento, sem atraso)
             *   - vencimento APÓS a data de encerramento
             *
             * O SoftDeletes do Laravel preenche deleted_at automaticamente,
             * fazendo com que essas parcelas sumam de todas as queries normais
             * do sistema — dashboard, financeiro e tela do contrato incluídos.
             *
             * Parcelas EM_ATRASO e PAGA_PARCIALMENTE são mantidas pois
             * representam dívida real do locatário.
             */
            ParcelaAluguel::where('contrato_id', $contrato->id)
                ->where('tipo_origem', 'ALUGUEL_NORMAL')
                ->where('status', 'ABERTA')
                ->whereDate('data_vencimento', '>', $dataEncerramento->toDateString())
                ->delete();

            $contrato->status        = $novoStatus;
            $contrato->data_fim_real = $dataEncerramento;

            if (!empty($motivo)) {
                $contrato->motivo_nao_devolucao_caucao = $motivo;
            }

            $contrato->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('contratos.show', $contrato->id)
                ->with('error', 'Erro ao encerrar/rescindir contrato: ' . $e->getMessage());
        }

        $msg = $tipoEncerramento === 'RESCISAO_ANTECIPADA'
            ? 'Contrato rescindido com sucesso.'
            : 'Contrato encerrado com sucesso.';

        return redirect()
            ->route('contratos.show', $contrato->id)
            ->with('success', $msg);
    }

    private function validateData(Request $request, array $options): array
    {
        $tiposContratoKeys   = array_keys($options['tiposContrato']);
        $indicesReajusteKeys = array_keys($options['indicesReajuste']);
        $statusContratoKeys  = array_keys($options['statusContrato']);

        $tipo = $request->input('tipo_contrato');

        $rules = [
            'imovel_id'       => 'required|exists:imoveis,id',
            'locatario_id'    => 'required|exists:pessoas,id',
            'proprietario_id' => 'required|exists:pessoas,id',
            'tipo_contrato'   => ['required', Rule::in($tiposContratoKeys)],
            'status'          => ['required', Rule::in($statusContratoKeys)],
        ];

        if ($tipo === 'TEMPORADA') {
            $rules = array_merge($rules, [
                'data_inicio'                 => 'required|date',
                'data_fim_prevista'           => 'nullable|date|after_or_equal:data_inicio',
                'data_entrada_prevista'       => 'required|date|after_or_equal:data_inicio',
                'hora_entrada'                => 'nullable|date_format:H:i',
                'data_saida_prevista'         => 'required|date|after_or_equal:data_entrada_prevista',
                'hora_saida'                  => 'nullable|date_format:H:i',
                'numero_hospedes'             => 'nullable|integer|min:1',
                'valor_total_temporada'       => 'required|numeric|min:0.01',
                'numero_parcelas_temporada'   => 'nullable|integer|min:1',
                'prazo_maximo_pagamento_dias' => 'nullable|integer|min:0',
                'regras_especiais'            => 'nullable|string',
                'restricoes'                  => 'nullable|string',
                'valor_aluguel_base'          => 'nullable|numeric|min:0',
                'valor_aluguel_atual'         => 'nullable|numeric|min:0',
                'dia_vencimento'              => 'nullable|integer|min:1|max:31',
                'indice_reajuste'             => 'nullable|string',
                'mes_reajuste'                => 'nullable|integer|min:1|max:12',
                'percentual_reajuste_padrao'  => 'nullable|numeric|min:0|max:100',
                'carencia_dias'               => 'nullable|integer|min:0',
                'data_fim_real'               => 'nullable|date|after_or_equal:data_inicio',
            ]);
        } else {
            $rules = array_merge($rules, [
                'data_inicio'                 => 'required|date',
                'data_fim_prevista'           => 'nullable|date|after_or_equal:data_inicio',
                'data_fim_real'               => 'nullable|date|after_or_equal:data_inicio',
                'valor_aluguel_base'          => 'required|numeric|min:0',
                'valor_aluguel_atual'         => 'required|numeric|min:0',
                'dia_vencimento'              => 'required|integer|min:1|max:31',
                'indice_reajuste'             => ['required', Rule::in($indicesReajusteKeys)],
                'mes_reajuste'                => 'nullable|integer|min:1|max:12',
                'percentual_reajuste_padrao'  => 'nullable|numeric|min:0|max:100',
                'carencia_dias'               => 'nullable|integer|min:0',
                'data_entrada_prevista'       => 'nullable|date',
                'hora_entrada'                => 'nullable|date_format:H:i',
                'data_saida_prevista'         => 'nullable|date',
                'hora_saida'                  => 'nullable|date_format:H:i',
                'numero_hospedes'             => 'nullable|integer|min:1',
                'valor_total_temporada'       => 'nullable|numeric|min:0',
                'numero_parcelas_temporada'   => 'nullable|integer|min:1',
                'prazo_maximo_pagamento_dias' => 'nullable|integer|min:0',
                'regras_especiais'            => 'nullable|string',
                'restricoes'                  => 'nullable|string',
            ]);
        }

        if ($request->has('possui_caucao')) {
            $rules['meses_caucao']                = 'required|integer|min:0|max:3';
            $rules['valor_caucao']                = 'required|numeric|min:0';
            $rules['data_pagamento_total_caucao'] = 'nullable|date';
            $rules['data_devolucao_caucao']       = 'nullable|date|after_or_equal:data_pagamento_total_caucao';
            $rules['motivo_nao_devolucao_caucao'] = 'nullable|string|max:500';
        } else {
            $rules['meses_caucao']                = 'nullable|integer|min:0|max:3';
            $rules['valor_caucao']                = 'nullable|numeric|min:0';
            $rules['data_pagamento_total_caucao'] = 'nullable|date';
            $rules['data_devolucao_caucao']        = 'nullable|date';
            $rules['motivo_nao_devolucao_caucao'] = 'nullable|string|max:500';
        }

        if ($request->has('possui_multa_atraso')) {
            $rules['percentual_multa'] = 'required|numeric|min:0|max:100';
        } else {
            $rules['percentual_multa'] = 'nullable|numeric|min:0|max:100';
        }

        if ($request->has('possui_juros_moratorios')) {
            $rules['percentual_juros_mensal'] = 'required|numeric|min:0|max:100';
        } else {
            $rules['percentual_juros_mensal'] = 'nullable|numeric|min:0|max:100';
        }

        return $request->validate($rules);
    }

    private function generateContratoCode(): string
    {
        $year = date('Y');

        $lastContrato = Contrato::withTrashed()
            ->where('codigo', 'like', "CT-{$year}-%")
            ->orderBy('codigo', 'desc')
            ->first();

        $sequence = 1;

        if ($lastContrato) {
            $lastCodeParts = explode('-', $lastContrato->codigo);
            $lastSequence  = (int) end($lastCodeParts);
            $sequence      = $lastSequence + 1;
        }

        return "CT-{$year}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function devolucaoCaucaoForm(Contrato $contrato)
    {
        if (!$contrato->possui_caucao || !$contrato->caucao_paga_integralmente || $contrato->caucao_devolvida) {
            return redirect()
                ->route('contratos.show', $contrato->id)
                ->with('error', 'A caução não pode ser registrada como devolvida neste momento.');
        }

        return view('contratos.caucao_devolucao', compact('contrato'));
    }

    public function devolucaoCaucaoStore(Request $request, Contrato $contrato)
    {
        if (!$contrato->possui_caucao || !$contrato->caucao_paga_integralmente || $contrato->caucao_devolvida) {
            return redirect()
                ->route('contratos.show', $contrato->id)
                ->with('error', 'A caução não pode ser registrada como devolvida neste momento.');
        }

        $request->validate([
            'data_devolucao_caucao'       => 'required|date|after_or_equal:' . $contrato->data_pagamento_total_caucao?->format('Y-m-d'),
            'motivo_nao_devolucao_caucao' => 'nullable|string|max:500',
        ]);

        $contrato->update([
            'caucao_devolvida'            => true,
            'data_devolucao_caucao'       => $request->input('data_devolucao_caucao'),
            'motivo_nao_devolucao_caucao' => $request->input('motivo_nao_devolucao_caucao'),
        ]);

        return redirect()
            ->route('contratos.show', $contrato->id)
            ->with('success', 'Devolução da caução registrada com sucesso!');
    }
}
<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contrato extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contratos';

    protected $fillable = [
        'codigo',
        'imovel_id',
        'locatario_id',
        'proprietario_id',
        'tipo_contrato',
        'data_inicio',
        'data_fim_prevista',
        'data_fim_real',
        'valor_aluguel_base',
        'valor_aluguel_atual',
        'dia_vencimento',
        'indice_reajuste',
        'mes_reajuste',
        'percentual_reajuste_padrao',
        'status',
        'possui_caucao',
        'meses_caucao',
        'valor_caucao',
        'caucao_paga_integralmente',
        'data_pagamento_total_caucao',
        'caucao_devolvida',
        'data_devolucao_caucao',
        'motivo_nao_devolucao_caucao',
        'possui_multa_atraso',
        'percentual_multa',
        'possui_juros_moratorios',
        'percentual_juros_mensal',
        'carencia_dias',
        // Campos de Temporada
        'data_entrada_prevista',
        'hora_entrada',
        'data_saida_prevista',
        'hora_saida',
        'numero_hospedes',
        'valor_total_temporada',
        'numero_parcelas_temporada',
        'dia_vencimento_parcelas_temporada',
        'prazo_maximo_pagamento_dias',
        'regras_especiais',
        'restricoes',
    ];

    protected $casts = [
        'data_inicio'                 => 'date',
        'data_fim_prevista'           => 'date',
        'data_fim_real'               => 'date',
        'data_pagamento_total_caucao' => 'date',
        'data_devolucao_caucao'       => 'date',
        'possui_caucao'               => 'boolean',
        'caucao_paga_integralmente'   => 'boolean',
        'caucao_devolvida'            => 'boolean',
        'possui_multa_atraso'         => 'boolean',
        'possui_juros_moratorios'     => 'boolean',
        // Casts de Temporada
        'data_entrada_prevista'       => 'date',
        'data_saida_prevista'         => 'date',
    ];

    // Relacionamentos
    public function imovel()
    {
        return $this->belongsTo(Imovel::class);
    }

    public function locatario()
    {
        return $this->belongsTo(Pessoa::class, 'locatario_id');
    }

    public function proprietario()
    {
        return $this->belongsTo(Pessoa::class, 'proprietario_id');
    }

    public function parcelas()
    {
        return $this->hasMany(ParcelaAluguel::class);
    }

    public function reajustes()
    {
        return $this->hasMany(Reajuste::class);
    }

    public function alertas()
    {
        return $this->hasMany(Alerta::class);
    }

    /**
     * Relacionamento com Ações Judiciais.
     * Um contrato pode ter várias ações judiciais.
     */
    public function acoesJudiciais()
    {
        return $this->hasMany(AcaoJudicial::class);
    }

    // Accessors
    public function getProximaDataReajusteAttribute(): ?Carbon
    {
        if (!$this->mes_reajuste || $this->indice_reajuste === 'SEM_REAJUSTE') {
            return null;
        }

        $hoje = Carbon::today();
        $dataBaseReajuste = $this->data_inicio;

        // Se já passou o mês de reajuste no ano atual, o próximo reajuste é no próximo ano
        $anoReajuste = $hoje->year;
        if ($hoje->month > $this->mes_reajuste || ($hoje->month == $this->mes_reajuste && $hoje->day > $this->dia_vencimento)) {
            $anoReajuste++;
        }

        // Tenta criar a data com o dia de vencimento, mês de reajuste e ano calculado
        try {
            $dataReajuste = Carbon::create($anoReajuste, $this->mes_reajuste, $this->dia_vencimento);
        } catch (\Exception $e) {
            // Se o dia de vencimento for inválido para o mês (ex: 31 de fevereiro), ajusta para o último dia do mês
            $dataReajuste = Carbon::create($anoReajuste, $this->mes_reajuste)->endOfMonth();
        }

        // Garante que a data de reajuste seja sempre após a data de início do contrato
        if ($dataReajuste->lt($dataBaseReajuste)) {
            $dataReajuste->addYear();
        }

        return $dataReajuste->startOfDay();
    }

    /**
     * Verifica se a parcela de temporada está integralmente paga.
     */
    public function getTemporadaPagaIntegralmenteAttribute(): bool
    {
        if ($this->tipo_contrato !== 'TEMPORADA') {
            return true; // Não se aplica a outros tipos de contrato
        }

        $parcelaTemporada = $this->parcelas
            ->where('tipo_origem', 'TEMPORADA')
            ->first();

        if (!$parcelaTemporada) {
            return false; // Não há parcela de temporada, então não está paga
        }

        // Considera pago integralmente se o valor devido for muito próximo de zero (lidar com float)
        return ($parcelaTemporada->valor_devido - $parcelaTemporada->valor_pago) <= 0.009;
    }

    // Métodos de Negócio
    public function gerarParcelasAluguel(): void
    {
        if ($this->tipo_contrato === 'TEMPORADA') {
            return;
        }

        $dataInicioContrato = $this->data_inicio;
        $diaVencimento      = (int) $this->dia_vencimento; // Corrigido: Garante que seja um inteiro
        $valorAluguel       = $this->valor_aluguel_base;

        // Calcula o primeiro vencimento
        $primeiroVencimento = $dataInicioContrato->copy()->addMonth();
        if ($primeiroVencimento->day > $diaVencimento) {
            $primeiroVencimento->day = $diaVencimento;
        } else {
            $primeiroVencimento->day = $diaVencimento;
        }

        // Garante que o primeiro vencimento não seja antes da data de início do contrato
        if ($primeiroVencimento->lt($dataInicioContrato)) {
            $primeiroVencimento->addMonth();
        }

        // Gera 12 parcelas iniciais (ou mais, conforme necessidade)
        for ($i = 0; $i < 12; $i++) {
            $dataVencimento = $primeiroVencimento->copy()->addMonths($i);
            $competencia    = $dataVencimento->copy()->subMonth()->format('Y-m'); // Competência é o mês anterior ao vencimento

            // Verifica se já existe uma parcela para esta competência e contrato
            $parcelaExistente = $this->parcelas()
                ->where('competencia', $competencia)
                ->where('tipo_origem', 'ALUGUEL_NORMAL')
                ->first();

            if (!$parcelaExistente) {
                $this->parcelas()->create([
                    'competencia'     => $competencia,
                    'numero_parcela'  => $i + 1, // Pode ser ajustado para ser o número da parcela no contrato
                    'total_parcelas'  => 0, // Pode ser ajustado para ser o total de parcelas do contrato
                    'data_vencimento' => $dataVencimento,
                    'valor_original'  => $valorAluguel,
                    'valor_devido'    => $valorAluguel,
                    'valor_pago'      => 0,
                    'status'          => 'ABERTA',
                    'tipo_origem'     => 'ALUGUEL_NORMAL',
                ]);
            }
        }
    }

    public function gerarParcelasCaucao(): void
    {
        if (!$this->possui_caucao || $this->meses_caucao == 0) {
            return;
        }

        $valorCaucaoPorMes = $this->valor_aluguel_base; // Ou um valor específico para caução

        for ($i = 0; $i < $this->meses_caucao; $i++) {
            $dataVencimento = $this->data_inicio->copy()->addDays(7)->addMonths($i); // Exemplo: 7 dias após início + parcelamento mensal
            $competencia    = $dataVencimento->format('Y-m');

            $parcelaExistente = $this->parcelas()
                ->where('competencia', $competencia)
                ->where('tipo_origem', 'CAUCAO')
                ->first();

            if (!$parcelaExistente) {
                $this->parcelas()->create([
                    'competencia'     => $competencia,
                    'numero_parcela'  => $i + 1,
                    'total_parcelas'  => $this->meses_caucao,
                    'data_vencimento' => $dataVencimento,
                    'valor_original'  => $valorCaucaoPorMes,
                    'valor_devido'    => $valorCaucaoPorMes,
                    'valor_pago'      => 0,
                    'status'          => 'ABERTA',
                    'tipo_origem'     => 'CAUCAO',
                ]);
            }
        }
    }

    public function atualizarStatusCaucaoSeNecessario(): void
    {
        if (!$this->possui_caucao) {
            return;
        }

        $parcelasCaucao = $this->parcelas()->where('tipo_origem', 'CAUCAO')->get();

        $totalPago = $parcelasCaucao->sum('valor_pago');
        $totalDevido = $parcelasCaucao->sum('valor_original');

        if ($totalPago >= $totalDevido && !$this->caucao_paga_integralmente) {
            $this->caucao_paga_integralmente = true;
            $this->data_pagamento_total_caucao = now();
            $this->save();
        } elseif ($totalPago < $totalDevido && $this->caucao_paga_integralmente) {
            // Caso haja estorno ou algo que faça o valor pago ser menor novamente
            $this->caucao_paga_integralmente = false;
            $this->data_pagamento_total_caucao = null;
            $this->save();
        }
    }

    public function gerarParcelaTemporada(): void
    {
        if ($this->tipo_contrato !== 'TEMPORADA' || !$this->valor_total_temporada) {
            return;
        }

        // Verifica se já existe uma parcela de temporada para este contrato
        $parcelaExistente = $this->parcelas()
            ->where('tipo_origem', 'TEMPORADA')
            ->first();

        // Se já existe e o valor não mudou, não precisa recriar/atualizar
        if ($parcelaExistente && $parcelaExistente->valor_original == $this->valor_total_temporada) {
            return;
        }

        $valorTotal = (float) $this->valor_total_temporada;

        if ($valorTotal <= 0) {
            return;
        }

        // Data de vencimento: data_entrada_prevista menos o prazo_maximo_pagamento_dias
        $dataEntrada = Carbon::parse($this->data_entrada_prevista)->startOfDay();
        $prazoMaximo = (int) ($this->prazo_maximo_pagamento_dias ?? 0);
        $dataVencimento = $dataEntrada->copy()->subDays($prazoMaximo);

        // Garantir que o vencimento não seja antes da data de início do contrato
        if ($this->data_inicio) {
            $dataInicio = Carbon::parse($this->data_inicio)->startOfDay();
            if ($dataVencimento->lt($dataInicio)) {
                $dataVencimento = $dataInicio->copy();
            }
        }

        $competencia = $dataVencimento->format('Y-m');

        if (!$parcelaExistente) {
            $this->parcelas()->create([
                'competencia'     => $competencia,
                'numero_parcela'  => 1,
                'total_parcelas'  => 1,
                'data_vencimento' => $dataVencimento,
                'valor_original'  => $valorTotal,
                'valor_devido'    => $valorTotal,
                'valor_pago'      => 0,
                'status'          => 'ABERTA',
                'tipo_origem'     => 'TEMPORADA',
            ]);
        } else {
            // Se já existe, atualiza o valor e data de vencimento caso tenham sido alterados no contrato
            $parcelaExistente->update([
                'competencia'     => $competencia,
                'data_vencimento' => $dataVencimento,
                'valor_original'  => $valorTotal,
                'valor_devido'    => $valorTotal,
                // Mantém valor_pago e status, a menos que haja uma lógica específica para resetar
            ]);
        }
    }
}
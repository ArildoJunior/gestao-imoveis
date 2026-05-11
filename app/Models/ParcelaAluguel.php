<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Contrato;
use App\Models\Pagamento;
use App\Models\Renegociacao;
use Carbon\Carbon;

class ParcelaAluguel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parcelas_aluguel';

    protected $fillable = [
        'contrato_id',
        'competencia',
        'numero_parcela',
        'total_parcelas',
        'data_vencimento',
        'data_pagamento',
        'valor_original',
        'valor_devido',
        'valor_pago',
        'status',
        'tipo_origem',
        'multa_aplicada',
        'juros_aplicados',
        'desconto_aplicado',
        'renegociacao_id',
    ];

    protected $casts = [
        'data_vencimento'      => 'date',
        'data_pagamento'       => 'date',
        'valor_original'       => 'decimal:2',
        'valor_devido'         => 'decimal:2',
        'valor_pago'           => 'decimal:2',
        'multa_aplicada'       => 'decimal:2',
        'juros_aplicados'      => 'decimal:2',
        'desconto_aplicado'    => 'decimal:2',
        'numero_parcela'       => 'integer',
        'total_parcelas'       => 'integer',
    ];

    protected $dates = ['deleted_at'];

    // ----------------------- Relacionamentos -----------------------

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class, 'parcela_id');
    }

    public function renegociacao()
    {
        return $this->belongsTo(Renegociacao::class, 'renegociacao_id');
    }

    // ----------------- Multa / Juros (cálculo em memória) -----------------

    /**
     * Calcula multa e juros em memória, sem salvar.
     *
     * Retorna:
     *  - multa (float)
     *  - juros (float)
     *  - valor_devido_calculado (float)
     */
    public function calcularMultaJurosEmMemoria(?Carbon $dataBase = null): array
    {
        if (!$this->data_vencimento || !$this->contrato) {
            return [
                'multa'                  => 0.0,
                'juros'                  => 0.0,
                'valor_devido_calculado' => (float) $this->valor_devido,
            ];
        }

        $hoje = $dataBase ? $dataBase->copy()->startOfDay() : Carbon::today();

        if ($hoje->lte($this->data_vencimento)) {
            return [
                'multa'                  => 0.0,
                'juros'                  => 0.0,
                'valor_devido_calculado' => (float) $this->valor_devido,
            ];
        }

        $contrato = $this->contrato;

        $possuiMulta        = (bool) ($contrato->possui_multa_atraso ?? false);
        $percentualMulta    = (float) ($contrato->percentual_multa ?? 0.0);
        $possuiJuros        = (bool) ($contrato->possui_juros_moratorios ?? false);
        $percentualJurosMes = (float) ($contrato->percentual_juros_mensal ?? 0.0);
        $carenciaDias       = (int) ($contrato->carencia_dias ?? 0);

        $diasAtrasoTotal = $this->data_vencimento->diffInDays($hoje);

        if ($diasAtrasoTotal <= $carenciaDias) {
            return [
                'multa'                  => 0.0,
                'juros'                  => 0.0,
                'valor_devido_calculado' => (float) $this->valor_devido,
            ];
        }

        $diasAtrasoConsiderado = $diasAtrasoTotal - $carenciaDias;
        $baseCalculo           = (float) $this->valor_original;

        $multa = 0.0;
        $juros = 0.0;

        if ($possuiMulta && $percentualMulta > 0) {
            $multa = round($baseCalculo * ($percentualMulta / 100), 2);
        }

        if ($possuiJuros && $percentualJurosMes > 0 && $diasAtrasoConsiderado > 0) {
            $taxaDia = ($percentualJurosMes / 100) / 30;
            $juros   = round($baseCalculo * $taxaDia * $diasAtrasoConsiderado, 2);
        }

        $desconto = (float) $this->desconto_aplicado;
        $valorDevidoCalculado = $baseCalculo + $multa + $juros - $desconto;
        $valorDevidoCalculado = max(0, round($valorDevidoCalculado, 2));

        return [
            'multa'                  => $multa,
            'juros'                  => $juros,
            'valor_devido_calculado' => $valorDevidoCalculado,
        ];
    }

    public function aplicarMultaEJurosSeAtraso(?Carbon $dataBase = null): void
    {
        $resultado = $this->calcularMultaJurosEmMemoria($dataBase);

        $this->multa_aplicada  = $resultado['multa'];
        $this->juros_aplicados = $resultado['juros'];
        $this->valor_devido    = $resultado['valor_devido_calculado'];
    }

    // ----------------- Registro de pagamento (parcial/total) -----------------

    public function registrarPagamento(
        float $valorPagarAtual,
        string $formaPagamento,
        ?string $numeroComprovante = null,
        ?string $observacoes = null,
        ?int $registradoPorUserId = null
    ): Pagamento {
        $agora = Carbon::now();

        $this->aplicarMultaEJurosSeAtraso($agora);

        $pagamento = $this->pagamentos()->create([
            'registrado_por_user_id' => $registradoPorUserId,
            'data_pagamento'         => $agora,
            'valor_pago'             => $valorPagarAtual,
            'forma_pagamento'        => $formaPagamento,
            'numero_comprovante'     => $numeroComprovante,
            'observacoes'            => $observacoes,
        ]);

        $this->valor_pago     = bcadd($this->valor_pago, $valorPagarAtual, 2);
        $this->data_pagamento = $agora;

        if (bccomp($this->valor_pago, $this->valor_devido, 2) >= 0) {
            $this->status = 'PAGA';
        } elseif (bccomp($this->valor_pago, 0, 2) > 0) {
            $this->status = 'PAGA_PARCIALMENTE';
        } else {
            if ($this->data_vencimento && $this->data_vencimento->lt(Carbon::today())) {
                $this->status = 'EM_ATRASO';
            } else {
                $this->status = 'ABERTA';
            }
        }

        $this->save();

        // Se for parcela de caução, atualiza status de caução no contrato
        if ($this->tipo_origem === 'CAUCAO' && $this->contrato) {
            $this->contrato->atualizarStatusCaucaoSeNecessario();
        }

        return $pagamento;
    }

    // ----------------- Accessor para tela -----------------

    public function getValorDevidoAtualAttribute(): float
    {
        $resultado = $this->calcularMultaJurosEmMemoria(Carbon::today());
        return $resultado['valor_devido_calculado'];
    }
}
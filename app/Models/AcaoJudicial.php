<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcaoJudicial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acoes_judiciais';

    protected $fillable = [
        'contrato_id',
        'tipo_acao',
        'status',
        'numero_processo',
        'vara',
        'comarca',
        'advogado_nome',
        'advogado_telefone',
        'valor_cobrado',
        'valor_recuperado',
        'custo_advocaticio',
        'imovel_devolvido',
        'data_entrega_chaves',
        'condicao_imovel_entrega',
        'houve_acordo',
        'descricao_acordo',
        'valor_acordo',
        'parcelas_acordo',
        'novo_contrato_apos_decisao',
        'data_encerramento',
        'observacoes',
        'registrado_por_user_id',
    ];

    protected $casts = [
        'valor_cobrado'             => 'decimal:2',
        'valor_recuperado'          => 'decimal:2',
        'custo_advocaticio'         => 'decimal:2',
        'valor_acordo'              => 'decimal:2',
        'imovel_devolvido'          => 'boolean',
        'houve_acordo'              => 'boolean',
        'novo_contrato_apos_decisao'=> 'boolean',
        'data_entrega_chaves'       => 'date',
        'data_encerramento'         => 'date',
        'parcelas_acordo'           => 'integer',
    ];

    protected $dates = ['deleted_at'];

    // Relacionamentos
    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }

    // Métodos auxiliares para status
    public function isEmAndamento(): bool
    {
        return $this->status === 'EM_ANDAMENTO';
    }

    public function isAcordoRealizado(): bool
    {
        return $this->status === 'ACORDO_REALIZADO';
    }

    public function isEncerradaSemAcordo(): bool
    {
        return $this->status === 'ENCERRADA_SEM_ACORDO';
    }

    public function isSuspensa(): bool
    {
        return $this->status === 'SUSPENSA';
    }
}
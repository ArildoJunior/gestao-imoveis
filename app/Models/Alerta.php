<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alerta extends Model
{
    use HasFactory;

    protected $table = 'alertas';

    protected $fillable = [
        'tipo_alerta',
        'titulo',
        'descricao',
        'data_alerta',
        'status',
        'contrato_id',
        'parcela_id',
        'imovel_id',
        'despesa_imovel_id',
        'acao_judicial_id',
        'visualizado_em',
        'resolvido_em',
    ];

    protected $casts = [
        'data_alerta'    => 'datetime',
        'visualizado_em' => 'datetime',
        'resolvido_em'   => 'datetime',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function parcela()
    {
        return $this->belongsTo(ParcelaAluguel::class, 'parcela_id');
    }

    public function imovel()
    {
        return $this->belongsTo(Imovel::class);
    }

    public function despesaImovel()
    {
        // Especifica a chave estrangeira, pois não segue a convenção padrão (despesa_id)
        return $this->belongsTo(DespesaImovel::class, 'despesa_imovel_id');
    }

    public function acaoJudicial()
    {
        return $this->belongsTo(AcaoJudicial::class);
    }

    public function isPendente(): bool
    {
        return $this->status === 'PENDENTE';
    }

    public function isResolvido(): bool
    {
        return $this->status === 'RESOLVIDO';
    }

    public function isVisualizado(): bool
    {
        return $this->status === 'VISUALIZADO';
    }

    public function isIgnorado(): bool
    {
        return $this->status === 'IGNORADO';
    }
}
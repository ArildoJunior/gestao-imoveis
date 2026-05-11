<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DespesaImovel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'despesas_imovel';

    protected $fillable = [
        'imovel_id',
        'contrato_id',
        'registrado_por_user_id',
        'data_despesa',
        'tipo_despesa',
        'descricao',
        'valor',
        'responsavel',
        'status',
        'fornecedor',
        'numero_nota_fiscal',
        'data_reembolso',
        'valor_reembolso',
    ];

    protected $casts = [
        'data_despesa' => 'date',
        'data_reembolso' => 'date',
        'valor' => 'decimal:2',
        'valor_reembolso' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    // Relacionamentos
    public function imovel()
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }
}
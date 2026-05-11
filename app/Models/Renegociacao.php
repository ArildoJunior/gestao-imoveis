<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Contrato;
use App\Models\ParcelaAluguel;
use App\Models\User;

class Renegociacao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'renegociacoes';

    protected $fillable = [
        'contrato_id',
        'data_acordo',
        'valor_original_total',
        'valor_acordado',
        'descontos_concedidos',
        'descricao_acordo',
        'numero_parcelas_acordo',
        'dia_vencimento_acordo',
        'primeiro_vencimento_acordo',
        'aprovado_por_user_id',
    ];

    protected $casts = [
        'data_acordo'               => 'date',
        'valor_original_total'      => 'decimal:2',
        'valor_acordado'            => 'decimal:2',
        'descontos_concedidos'      => 'decimal:2',
        'numero_parcelas_acordo'    => 'integer',
        'dia_vencimento_acordo'     => 'integer',
        'primeiro_vencimento_acordo'=> 'date',
    ];

    protected $dates = ['deleted_at'];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function parcelas()
    {
        return $this->hasMany(ParcelaAluguel::class, 'renegociacao_id');
    }

    public function aprovadoPor()
    {
        return $this->belongsTo(User::class, 'aprovado_por_user_id');
    }
}
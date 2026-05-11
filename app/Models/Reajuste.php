<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Contrato;
use App\Models\User;

class Reajuste extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reajustes';

    protected $fillable = [
        'contrato_id',
        'data_reajuste',
        'indice_reajuste',
        'percentual_aplicado',
        'valor_anterior',
        'valor_novo',
        'descricao',
        'aprovado_por_user_id',
    ];

    protected $casts = [
        'data_reajuste'       => 'date',
        'percentual_aplicado' => 'decimal:4',
        'valor_anterior'      => 'decimal:2',
        'valor_novo'          => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function aprovadoPor()
    {
        return $this->belongsTo(User::class, 'aprovado_por_user_id');
    }
}
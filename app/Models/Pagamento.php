<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ParcelaAluguel;
use App\Models\User;

class Pagamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagamentos';

    protected $fillable = [
        'parcela_id',
        'registrado_por_user_id',
        'data_pagamento',
        'valor_pago',
        'forma_pagamento',
        'numero_comprovante',
        'observacoes',
    ];

    protected $casts = [
        // Usando datetime para ter hora se precisar, mas date também funciona
        'data_pagamento' => 'datetime',
        'valor_pago'     => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    // Relacionamentos
    public function parcela()
    {
        return $this->belongsTo(ParcelaAluguel::class, 'parcela_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }
}
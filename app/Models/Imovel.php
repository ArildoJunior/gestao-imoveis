<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Pessoa;
use App\Models\Contrato;
use App\Models\DespesaImovel;

class Imovel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'imoveis';

    protected $fillable = [
        'descricao',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'tipo_imovel',
        'area_m2',
        'matricula',
        'inscricao_iptu',
        'valor_iptu_anual',
        'possui_condominio',
        'valor_condominio_mensal',
        'possui_agua_incluida',
        'possui_luz_incluida',
        'ativo',
        'proprietario_id',
    ];

    protected $casts = [
        'area_m2' => 'decimal:2',
        'valor_iptu_anual' => 'decimal:2',
        'possui_condominio' => 'boolean',
        'valor_condominio_mensal' => 'decimal:2',
        'possui_agua_incluida' => 'boolean',
        'possui_luz_incluida' => 'boolean',
        'ativo' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    public function proprietario()
    {
        return $this->belongsTo(Pessoa::class, 'proprietario_id');
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'imovel_id');
    }

    public function despesas()
    {
        return $this->hasMany(DespesaImovel::class, 'imovel_id');
    }
}
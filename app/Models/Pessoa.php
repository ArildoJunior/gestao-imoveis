<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar BelongsTo
use App\Models\Imovel;
use App\Models\Contrato;

class Pessoa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pessoas';

    protected $fillable = [
        'user_id', // Adicionado user_id ao fillable
        'nome',
        'cpf_cnpj',
        'rg',
        'email',
        'telefone',
        'celular',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'tipo',
        'ativo',
        'consentimento_lgpd',
        'data_consentimento',
        'ip_consentimento',
        'pedido_exclusao',
        'data_exclusao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'consentimento_lgpd' => 'boolean',
        'pedido_exclusao' => 'boolean',
        'data_consentimento' => 'datetime',
        'data_exclusao' => 'datetime',
        'nome' => 'encrypted',
        'cpf_cnpj' => 'encrypted',
        'rg' => 'encrypted',
    ];

    protected $dates = ['deleted_at'];

    /* ==== Relacionamentos ==== */

    /**
     * Uma pessoa pode pertencer a um usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imoveisComoProprietario()
    {
        return $this->hasMany(Imovel::class, 'proprietario_id');
    }

    public function contratosComoLocatario()
    {
        return $this->hasMany(Contrato::class, 'locatario_id');
    }

    /**
     * Imóveis onde esta pessoa é proprietária.
     */
    public function imoveisProprietario()
    {
        return $this->hasMany(\App\Models\Imovel::class, 'proprietario_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne; // Importar HasOne

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Perfis disponíveis – fonte única de verdade.
     */
    public const PERFIS = [
        'ADMINISTRADOR',
        'PROPRIETARIO',
        'LOCATARIO',
        'SECRETARIA',
        'FINANCEIRO',
        'CORRETOR',
        'PRESTADOR_DE_SERVICO',
        'PENDENTE',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'perfil',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'perfil'            => 'string',
            'status'            => 'string',
        ];
    }

    /* ==== Relacionamentos ==== */
    /**
     * Um usuário pode estar associado a uma pessoa.
     */
    public function pessoa(): HasOne
    {
        return $this->hasOne(Pessoa::class);
    }

    /* ==== Métodos auxiliares ==== */
    public function isAdministrador(): bool        { return $this->perfil === 'ADMINISTRADOR'; }
    public function isProprietario(): bool         { return $this->perfil === 'PROPRIETARIO'; }
    public function isLocatario(): bool            { return $this->perfil === 'LOCATARIO'; }
    public function isSecretaria(): bool           { return $this->perfil === 'SECRETARIA'; }
    public function isFinanceiro(): bool           { return $this->perfil === 'FINANCEIRO'; }
    public function isCorretor(): bool             { return $this->perfil === 'CORRETOR'; }
    public function isPrestadorDeServico(): bool   { return $this->perfil === 'PRESTADOR_DE_SERVICO'; }
    public function isPendente(): bool             { return $this->perfil === 'PENDENTE'; }
    public function isActive(): bool               { return $this->status === 'ATIVO'; }
}
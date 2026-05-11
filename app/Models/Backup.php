<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $table = 'backups';

    protected $fillable = [
        'tipo',
        'caminho_arquivo',
        'tamanho_bytes',
        'status',
        'mensagem_erro',
        'realizado_por_user_id',
    ];

    /**
     * Usuário que disparou o backup (quando manual).
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'realizado_por_user_id');
    }

    /**
     * Helper: se o backup foi bem sucedido.
     */
    public function isSucesso(): bool
    {
        return $this->status === 'SUCESSO';
    }

    /**
     * Helper: se o backup falhou.
     */
    public function isFalha(): bool
    {
        return $this->status === 'FALHA';
    }
}
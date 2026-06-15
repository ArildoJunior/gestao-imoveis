<?php

namespace App\Policies;

use App\Models\ParcelaAluguel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParcelaAluguelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any parcela aluguel.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // Permite que usuários com perfil ADMINISTRADOR ou FINANCEIRO visualizem a lista
        return in_array($user->perfil, ['ADMINISTRADOR', 'FINANCEIRO']);
    }

    /**
     * Determine whether the user can view the parcela aluguel.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ParcelaAluguel  $parcelaAluguel
     * @return mixed
     */
    public function view(User $user, ParcelaAluguel $parcelaAluguel)
    {
        // Usuário pode visualizar a parcela se for do mesmo contrato ou se for ADMINISTRADOR/FINANCEIRO
        return $user->perfil === 'ADMINISTRADOR'
            || $user->perfil === 'FINANCEIRO'
            || $user->id === $parcelaAluguel->contrato->locatario_id;
    }

    /**
     * Determine whether the user can create parcela aluguel.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        // Apenas ADMINISTRADOR e FINANCEIRO podem criar parcelas
        return in_array($user->perfil, ['ADMINISTRADOR', 'FINANCEIRO']);
    }

    /**
     * Determine whether the user can update the parcela aluguel.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ParcelaAluguel  $parcelaAluguel
     * @return mixed
     */
    public function update(User $user, ParcelaAluguel $parcelaAluguel)
    {
        // Permite atualização apenas para ADMINISTRADOR ou FINANCEIRO
        return in_array($user->perfil, ['ADMINISTRADOR', 'FINANCEIRO']);
    }

    /**
     * Determine whether the user can delete the parcela aluguel.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ParcelaAluguel  $parcelaAluguel
     * @return mixed
     */
    public function delete(User $user, ParcelaAluguel $parcelaAluguel)
    {
        // Apenas ADMINISTRADOR pode excluir parcelas
        return $user->perfil === 'ADMINISTRADOR';
    }

    /**
     * Determine whether the user can restore the parcela aluguel.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ParcelaAluguel  $parcelaAluguel
     * @return mixed
     */
    public function restore(User $user, ParcelaAluguel $parcelaAluguel)
    {
        // Não há necessidade de restauração; retorna false
        return false;
    }

    /**
     * Determine whether the user can permanently delete the parcela aluguel.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ParcelaAluguel  $parcelaAluguel
     * @return mixed
     */
    public function forceDelete(User $user, ParcelaAluguel $parcelaAluguel)
    {
        // Apenas ADMINISTRADOR pode forçar exclusão permanente
        return $user->perfil === 'ADMINISTRADOR';
    }
}
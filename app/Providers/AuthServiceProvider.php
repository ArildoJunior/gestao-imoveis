<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Policies a serem registradas
use App\Policies\ImovelPolicy;
use App\Policies\ContratoPolicy;
use App\Policies\PessoaPolicy;
use App\Policies\UserPolicy;
use App\Policies\DespesaImovelPolicy;
use App\Policies\AcaoJudicialPolicy;
use App\Policies\AlertaPolicy;
use App\Policies\RenegociacaoPolicy;
use App\Policies\ReajustePolicy;
use App\Policies\ParcelaAluguelPolicy; // Adicione esta linha

// Models
use App\Models\Imovel;
use App\Models\Contrato;
use App\Models\Pessoa;
use App\Models\User;
use App\Models\DespesaImovel;
use App\Models\AcaoJudicial;
use App\Models\Alerta;
use App\Models\Renegociacao;
use App\Models\Reajuste;
use App\Models\ParcelaAluguel; // Adicione esta linha

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Mapeamento de Model => Policy
        Imovel::class          => ImovelPolicy::class,
        Contrato::class        => ContratoPolicy::class,
        Pessoa::class          => PessoaPolicy::class,
        User::class            => UserPolicy::class,
        DespesaImovel::class   => DespesaImovelPolicy::class,
        AcaoJudicial::class    => AcaoJudicialPolicy::class,
        Alerta::class          => AlertaPolicy::class,
        Renegociacao::class    => RenegociacaoPolicy::class,
        Reajuste::class        => ReajustePolicy::class,
        ParcelaAluguel::class  => ParcelaAluguelPolicy::class, // Adicione esta linha
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates adicionais (exemplo)
        Gate::define('admin-only', fn ($user) => $user->perfil === 'ADMINISTRADOR'); // Ajustado para 'ADMINISTRADOR'
    }
}
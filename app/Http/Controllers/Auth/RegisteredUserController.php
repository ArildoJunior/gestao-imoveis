<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pessoa; // Importar o modelo Pessoa
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB; // Para transações

class RegisteredUserController extends Controller
{
    /**
     * Exibe o formulário de registro.
     */
    public function create(): View
    {
        // Perfis que podem ser solicitados no cadastro (exceto ADMINISTRADOR e PENDENTE)
        $perfis = collect(User::PERFIS)
            ->filter(fn($p) => !in_array($p, ['ADMINISTRADOR', 'PENDENTE']))
            ->values()
            ->toArray();

        return view('auth.register', compact('perfis'));
    }

    /**
     * Processa o registro.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $perfisPermitidos = collect(User::PERFIS)
            ->filter(fn($p) => !in_array($p, ['ADMINISTRADOR', 'PENDENTE']))
            ->values()
            ->toArray();

        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password'              => ['required', 'confirmed', Rules\Password::defaults()],
            'perfil'                => ['required', Rule::in($perfisPermitidos)],
            // Campos da Pessoa a serem criados junto com o usuário
            'pessoa_nome'           => ['required', 'string', 'max:255'],
            'pessoa_cpf_cnpj'       => ['required', 'string', 'max:20', 'unique:pessoas,cpf_cnpj'],
            'pessoa_email'          => ['nullable', 'email', 'max:255'],
            'pessoa_telefone'       => ['nullable', 'string', 'max:20'],
            'pessoa_celular'        => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($request) {
            // Cria o usuário com perfil PENDENTE
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'perfil'   => 'PENDENTE',          // Bloqueia acesso até liberação
                'status'   => 'ATIVO',             // Permite login para ver a tela de espera
            ]);

            // Cria a Pessoa e a vincula ao usuário
            Pessoa::create([
                'user_id'   => $user->id,
                'nome'      => $request->pessoa_nome,
                'cpf_cnpj'  => $request->pessoa_cpf_cnpj,
                'email'     => $request->pessoa_email,
                'telefone'  => $request->pessoa_telefone,
                'celular'   => $request->pessoa_celular,
                'tipo'      => 'FISICA', // Valor padrão, ajuste conforme sua lógica
                'ativo'     => true,
            ]);

            // Salva a escolha do usuário em um campo temporário (opcional)
            // Se quiser manter a escolha, crie uma coluna `perfil_solicitado` e descomente:
            // $user->update(['perfil_solicitado' => $request->perfil]);

            event(new Registered($user));

            // Login automático → middleware redireciona para a tela de “aguardando liberação”
            Auth::login($user);
        });


        return redirect(route('dashboard', absolute: false));
    }
}
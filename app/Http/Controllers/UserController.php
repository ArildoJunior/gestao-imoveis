<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pessoa; // Importar o modelo Pessoa
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Para transações

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $perfis = collect(User::PERFIS)
            ->filter(fn($p) => !in_array($p, ['ADMINISTRADOR', 'PENDENTE']))
            ->values()
            ->toArray();

        // Pessoas que ainda não estão vinculadas a um usuário
        $pessoasDisponiveis = Pessoa::doesntHave('user')->orderBy('nome')->get();

        return view('users.create', compact('perfis', 'pessoasDisponiveis'));
    }

    public function store(Request $request)
    {
        $perfisPermitidos = collect(User::PERFIS)
            ->filter(fn($p) => !in_array($p, ['ADMINISTRADOR', 'PENDENTE']))
            ->values()
            ->toArray();

        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password'              => ['required', 'confirmed', Rules\Password::defaults()],
            'perfil'                => ['required', Rule::in($perfisPermitidos)],
            'vincular_pessoa'       => ['required', 'boolean'], // Novo campo para decidir se vincula ou cria
            'pessoa_existente_id'   => ['nullable', 'required_if:vincular_pessoa,1', 'exists:pessoas,id'],
            'pessoa_nome'           => ['nullable', 'required_if:vincular_pessoa,0', 'string', 'max:255'],
            'pessoa_cpf_cnpj'       => ['nullable', 'required_if:vincular_pessoa,0', 'string', 'max:20', 'unique:pessoas,cpf_cnpj'],
            'pessoa_email'          => ['nullable', 'email', 'max:255'],
            'pessoa_telefone'       => ['nullable', 'string', 'max:20'],
            'pessoa_celular'        => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'perfil'   => $request->perfil,
                'status'   => 'ATIVO',
            ]);

            if ($request->vincular_pessoa) {
                // Vincular a uma pessoa existente
                $pessoa = Pessoa::findOrFail($request->pessoa_existente_id);
                $pessoa->user_id = $user->id;
                $pessoa->save();
            } else {
                // Criar uma nova pessoa
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
            }
        });

        return redirect()->route('users.index')
            ->with('success', 'Usuário e vínculo com pessoa criados com sucesso.');
    }

    public function edit(User $user)
    {
        $perfis = collect(User::PERFIS)
            ->filter(fn($p) => $p !== 'PENDENTE')
            ->values()
            ->toArray();

        // Pessoas que ainda não estão vinculadas a um usuário, OU a pessoa já vinculada a este usuário
        $pessoasDisponiveis = Pessoa::whereDoesntHave('user')
            ->orWhere('user_id', $user->id)
            ->orderBy('nome')
            ->get();

        return view('users.edit', compact('user', 'perfis', 'pessoasDisponiveis'));
    }

    public function update(Request $request, User $user)
    {
        $perfisPermitidos = collect(User::PERFIS)
            ->filter(fn($p) => $p !== 'PENDENTE')
            ->values()
            ->toArray();

        $rules = [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'perfil'                => ['required', Rule::in($perfisPermitidos)],
            'status'                => ['required', Rule::in(['ATIVO', 'INATIVO'])],
            'vincular_pessoa'       => ['required', 'boolean'],
            'pessoa_existente_id'   => ['nullable', 'required_if:vincular_pessoa,1', 'exists:pessoas,id'],
            'pessoa_nome'           => ['nullable', 'required_if:vincular_pessoa,0', 'string', 'max:255'],
            'pessoa_cpf_cnpj'       => ['nullable', 'required_if:vincular_pessoa,0', 'string', 'max:20', Rule::unique('pessoas', 'cpf_cnpj')->ignore($user->pessoa?->id)],
            'pessoa_email'          => ['nullable', 'email', 'max:255'],
            'pessoa_telefone'       => ['nullable', 'string', 'max:20'],
            'pessoa_celular'        => ['nullable', 'string', 'max:20'],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $user) {
            $user->name   = $request->name;
            $user->email  = $request->email;
            $user->perfil = $request->perfil;
            $user->status = $request->status;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // Lógica para vincular/criar/atualizar Pessoa
            if ($request->vincular_pessoa) {
                // Desvincular pessoa antiga, se houver
                if ($user->pessoa && $user->pessoa->id !== (int)$request->pessoa_existente_id) {
                    $user->pessoa->user_id = null;
                    $user->pessoa->save();
                }
                // Vincular a nova pessoa
                $pessoa = Pessoa::findOrFail($request->pessoa_existente_id);
                $pessoa->user_id = $user->id;
                $pessoa->save();
            } else {
                // Criar nova pessoa ou atualizar a existente
                if ($user->pessoa) {
                    // Atualizar pessoa existente
                    $user->pessoa->update([
                        'nome'      => $request->pessoa_nome,
                        'cpf_cnpj'  => $request->pessoa_cpf_cnpj,
                        'email'     => $request->pessoa_email,
                        'telefone'  => $request->pessoa_telefone,
                        'celular'   => $request->pessoa_celular,
                    ]);
                } else {
                    // Criar nova pessoa e vincular
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
                }
            }
        });

        return redirect()->route('users.index')
            ->with('success', 'Usuário e vínculo com pessoa atualizados com sucesso.');
    }

    public function toggleStatus(User $user)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if ($authUser->id === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'Você não pode alterar o status do seu próprio usuário.');
        }

        $user->status = ($user->status === 'ATIVO') ? 'INATIVO' : 'ATIVO';
        $user->save();

        $msg = ($user->status === 'ATIVO')
            ? 'Usuário ativado com sucesso.'
            : 'Usuário inativado com sucesso.';

        return redirect()->route('users.index')
            ->with('success', $msg);
    }

    public function destroy(User $user)
    {
        return redirect()->route('users.index')
            ->with('error', 'Exclusão de usuários não é permitida. Use a opção de inativar.');
    }
}
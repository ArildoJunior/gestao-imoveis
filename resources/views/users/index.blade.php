@extends('layouts.app')

@section('title', 'Gerenciar Usuários')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Gerenciar Usuários</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary">Adicionar Novo Usuário</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($users->isEmpty())
        <div class="alert alert-info">Nenhum usuário cadastrado.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th width="200">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $badge = match($user->perfil) {
                                        'ADMINISTRADOR'      => 'bg-danger',
                                        'SECRETARIA'         => 'bg-primary',
                                        'FINANCEIRO'         => 'bg-success',
                                        'CORRETOR'           => 'bg-info',
                                        'PROPRIETARIO'       => 'bg-warning',
                                        'LOCATARIO'          => 'bg-secondary',
                                        'PRESTADOR_DE_SERVICO'=> 'bg-dark',
                                        'PENDENTE'           => 'bg-light text-dark',
                                        default              => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $user->perfil }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $user->status === 'ATIVO' ? 'bg-success' : 'bg-warning' }}">
                                    {{ $user->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning me-2">Editar</a>

                                @php $acao = $user->status === 'ATIVO' ? 'inativar' : 'ativar'; @endphp
                                <form action="{{ route('users.toggleStatus', $user) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Tem certeza que deseja {{ $acao }} este usuário?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm {{ $user->status === 'ATIVO' ? 'btn-danger' : 'btn-success' }}">
                                        {{ ucfirst($acao) }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $users->links() }}</div>
    @endif
@endsection
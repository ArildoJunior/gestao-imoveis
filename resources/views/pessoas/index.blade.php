@extends('layouts.app')

@section('title', 'Pessoas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Pessoas</h1>
        <a href="{{ route('pessoas.create') }}" class="btn btn-primary">Nova Pessoa</a>
    </div>

    @if ($pessoas->count() === 0)
        <div class="alert alert-info">
            Nenhuma pessoa cadastrada.
        </div>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Ativo</th>
                    <th width="150">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pessoas as $pessoa)
                    <tr>
                        <td>{{ $pessoa->nome }}</td>
                        <td>{{ $pessoa->tipo }}</td>
                        <td>{{ $pessoa->email }}</td>
                        <td>{{ $pessoa->telefone ?? $pessoa->celular }}</td>
                        <td>{{ $pessoa->ativo ? 'Sim' : 'Não' }}</td>
                        <td>
                            <a href="{{ route('pessoas.edit', $pessoa) }}" class="btn btn-sm btn-warning">Editar</a>

                            <form action="{{ route('pessoas.destroy', $pessoa) }}" method="POST" style="display:inline-block"
                                  onsubmit="return confirm('Tem certeza que deseja excluir esta pessoa?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $pessoas->links() }}
    @endif
@endsection
@extends('layouts.app')

@section('title', 'Lista de Imóveis')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Lista de Imóveis</h1>
        <div>
            <a href="{{ route('imoveis.create') }}" class="btn btn-primary">Novo Imóvel</a>
        </div>
    </div>

    @if ($imoveis->isEmpty())
        <div class="alert alert-info">Nenhum imóvel encontrado.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <th>Endereço</th>
                        <th>Tipo</th>
                        <th>Proprietário</th>
                        <th>Ativo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($imoveis as $imovel)
                        <tr>
                            <td>{{ $imovel->id }}</td>
                            <td>{{ $imovel->descricao ?? 'N/A' }}</td>
                            <td>
                                {{ $imovel->logradouro }},
                                {{ $imovel->numero }}
                                @if($imovel->complemento)
                                    - {{ $imovel->complemento }}
                                @endif
                                - {{ $imovel->bairro }},
                                {{ $imovel->cidade }}/{{ $imovel->estado }}
                            </td>
                            <td>{{ $imovel->tipo_imovel }}</td>
                            <td>{{ $imovel->proprietario->nome ?? 'Proprietário não encontrado' }}</td>
                            <td>
                                @if ($imovel->ativo)
                                    <span class="badge bg-success">Sim</span>
                                @else
                                    <span class="badge bg-danger">Não</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('imoveis.edit', $imovel->id) }}" class="btn btn-sm btn-warning">
                                    Editar
                                </a>
                                <form action="{{ route('imoveis.destroy', $imovel->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este imóvel?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $imoveis->links() }}
    @endif
@endsection
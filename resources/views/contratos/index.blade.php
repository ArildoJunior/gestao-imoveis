@extends('layouts.app')

@section('title', 'Contratos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Contratos</h1>
        <div>
            <a href="{{ route('contratos.create') }}" class="btn btn-primary">Novo Contrato</a>
        </div>
    </div>

    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Código</th>
            <th>Imóvel</th>
            <th>Locatário</th>
            <th>Proprietário</th>
            <th>Tipo</th>
            <th>Aluguel Atual</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        @forelse($contratos as $contrato)
            <tr>
                <td>{{ $contrato->codigo }}</td>
                <td>{{ $contrato->imovel->descricao ?? $contrato->imovel->logradouro ?? 'N/A' }}</td>
                <td>{{ $contrato->locatario->nome ?? 'N/A' }}</td>
                <td>{{ $contrato->proprietario->nome ?? 'N/A' }}</td>
                <td>R$ {{ number_format($contrato->valor_aluguel_atual, 2, ',', '.') }}</td>
                <td>{{ $contrato->tipo_contrato }}</td>
                <td>{{ $contrato->status }}</td>
                <td>
                    <a href="{{ route('contratos.show', $contrato->id) }}" class="btn btn-sm btn-info me-1">Detalhes / Parcelas</a>
                    <a href="{{ route('contratos.edit', $contrato->id) }}" class="btn btn-sm btn-warning me-1">Editar</a>
                    <form action="{{ route('contratos.destroy', $contrato->id) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Tem certeza que deseja excluir este contrato? Isso pode afetar as parcelas geradas.')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8">Nenhum contrato encontrado.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $contratos->links() }}
@endsection
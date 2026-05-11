@extends('layouts.app')

@section('title', 'Despesas de Imóvel')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Despesas de Imóvel</h1>
        <a href="{{ route('despesas-imovel.create') }}" class="btn btn-primary">Nova Despesa</a>
    </div>

    @if ($despesas->isEmpty())
        <div class="alert alert-info">Nenhuma despesa de imóvel encontrada.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imóvel</th>
                        <th>Contrato</th>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Responsável</th>
                        <th>Status</th>
                        <th>Registrado Por</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($despesas as $despesa)
                        <tr>
                            <td>{{ $despesa->id }}</td>
                            <td>{{ $despesa->imovel->descricao }} ({{ $despesa->imovel->logradouro }}, {{ $despesa->imovel->numero }})</td>
                            <td>{{ $despesa->contrato ? $despesa->contrato->codigo : 'N/A' }}</td>
                            <td>{{ $despesa->data_despesa->format('d/m/Y') }}</td>
                            <td>{{ $despesa->tipo_despesa }}</td>
                            <td>R$ {{ number_format($despesa->valor, 2, ',', '.') }}</td>
                            <td>{{ $despesa->responsavel }}</td>
                            <td>{{ $despesa->status }}</td>
                            <td>{{ $despesa->registradoPor->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('despesas-imovel.edit', $despesa->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('despesas-imovel.destroy', $despesa->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta despesa?')">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $despesas->links() }}
    @endif
@endsection
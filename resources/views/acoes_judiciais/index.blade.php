@extends('layouts.app')

@section('title', 'Ações Judiciais')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Ações Judiciais</h1>
        <div>
            <a href="{{ route('acoes-judiciais.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Nova Ação Judicial
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Formulário de Filtro --}}
    <div class="card mb-4">
        <div class="card-header">
            Filtros
        </div>
        <div class="card-body">
            <form action="{{ route('acoes-judiciais.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="contrato_id" class="form-label">Contrato</label>
                        <select id="contrato_id" name="contrato_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($contratos as $contrato)
                                <option value="{{ $contrato->id }}"
                                    {{ request('contrato_id') == $contrato->id ? 'selected' : '' }}>
                                    {{ $contrato->codigo }} - {{ $contrato->locatario->nome ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tipo_acao" class="form-label">Tipo de Ação</label>
                        <select id="tipo_acao" name="tipo_acao" class="form-select">
                            <option value="">Todos</option>
                            @foreach($options['tiposAcao'] as $key => $label)
                                <option value="{{ $key }}"
                                    {{ request('tipo_acao') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Todos</option>
                            @foreach($options['statusAcao'] as $key => $label)
                                <option value="{{ $key }}"
                                    {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="numero_processo" class="form-label">Número do Processo</label>
                        <input type="text" id="numero_processo" name="numero_processo" class="form-control"
                               value="{{ request('numero_processo') }}">
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="{{ route('acoes-judiciais.index') }}" class="btn btn-secondary">Limpar Filtros</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela de Ações Judiciais --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Contrato</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Processo</th>
                            <th>Advogado</th>
                            <th>Registrado Por</th>
                            <th style="width: 150px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($acoesJudiciais as $acao_judicial)
                            @php
                                $badgeClass = match($acao_judicial->status) {
                                    'EM_ANDAMENTO'         => 'bg-warning text-dark',
                                    'ACORDO_REALIZADO'     => 'bg-success',
                                    'ENCERRADA_SEM_ACORDO' => 'bg-danger',
                                    'SUSPENSA'             => 'bg-info',
                                    default                => 'bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td>
                                    @if($acao_judicial->contrato)
                                        <a href="{{ route('contratos.show', $acao_judicial->contrato->id) }}" class="text-decoration-none">
                                            {{ $acao_judicial->contrato->codigo }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $options['tiposAcao'][$acao_judicial->tipo_acao] ?? $acao_judicial->tipo_acao }}</td>
                                <td>
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $options['statusAcao'][$acao_judicial->status] ?? $acao_judicial->status }}
                                    </span>
                                </td>
                                <td>{{ $acao_judicial->numero_processo ?? 'N/A' }}</td>
                                <td>{{ $acao_judicial->advogado_nome ?? 'N/A' }}</td>
                                <td>{{ $acao_judicial->registradoPor->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('acoes-judiciais.show', $acao_judicial->id) }}"
                                           class="btn btn-sm btn-outline-info" title="Ver Detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('acoes-judiciais.edit', $acao_judicial->id) }}"
                                           class="btn btn-sm btn-outline-warning" title="Editar Ação">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('acoes-judiciais.destroy', $acao_judicial->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Tem certeza que deseja excluir esta ação judicial? Esta ação é irreversível.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir Ação">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Nenhuma ação judicial encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $acoesJudiciais->links() }}
        </div>
    </div>
@endsection
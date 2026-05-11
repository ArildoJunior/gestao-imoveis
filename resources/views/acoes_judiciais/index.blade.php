@extends('layouts.app')

@section('title', 'Ações Judiciais')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1><i class="bi bi-bank me-2"></i>Ações Judiciais</h1>
    <a href="{{ route('acoes-judiciais.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nova Ação
    </a>
</div>

{{-- ===== CARD DE FILTROS ===== --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-1"></i> Filtros
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('acoes-judiciais.index') }}">
            <div class="row g-3">

                {{-- Proprietário --}}
                <div class="col-md-3">
                    <label for="proprietario_id" class="form-label">Proprietário</label>
                    <select name="proprietario_id" id="proprietario_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($proprietarios as $prop)
                            <option value="{{ $prop->id }}"
                                {{ request('proprietario_id') == $prop->id ? 'selected' : '' }}>
                                {{ $prop->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Imóvel --}}
                <div class="col-md-3">
                    <label for="imovel_id" class="form-label">Imóvel</label>
                    <select name="imovel_id" id="imovel_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($imoveis as $imovel)
                            <option value="{{ $imovel->id }}"
                                {{ request('imovel_id') == $imovel->id ? 'selected' : '' }}>
                                {{ $imovel->descricao }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo de Ação --}}
                <div class="col-md-2">
                    <label for="tipo_acao" class="form-label">Tipo de Ação</label>
                    <select name="tipo_acao" id="tipo_acao" class="form-select">
                        <option value="">Todos</option>
                        @foreach($options['tiposAcao'] as $key => $label)
                            <option value="{{ $key }}"
                                {{ request('tipo_acao') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach($options['statusAcao'] as $key => $label)
                            <option value="{{ $key }}"
                                {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Busca livre --}}
                <div class="col-md-2">
                    <label for="busca" class="form-label">Locatário / Processo</label>
                    <input type="text" name="busca" id="busca" class="form-control"
                           placeholder="Nome ou nº processo"
                           value="{{ request('busca') }}">
                </div>

                {{-- Botões --}}
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('acoes-judiciais.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Limpar
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== RESULTADO ===== --}}
@if(!$filtroAtivo)
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        Utilize os filtros acima para buscar ações judiciais.
    </div>

@elseif($acoes->isEmpty())
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Nenhuma ação judicial encontrada com os filtros informados.
    </div>

@else
    {{-- Totalizadores --}}
    @php
        $totalCobrado    = $acoes->sum('valor_cobrado');
        $totalRecuperado = $acoes->sum('valor_recuperado');
        $totalCusto      = $acoes->sum('custo_advocaticio');
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body py-2">
                    <div class="small">Total Cobrado</div>
                    <div class="fw-bold fs-5">R$ {{ number_format($totalCobrado, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body py-2">
                    <div class="small">Total Recuperado</div>
                    <div class="fw-bold fs-5">R$ {{ number_format($totalRecuperado, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body py-2">
                    <div class="small">Custo Advocatício</div>
                    <div class="fw-bold fs-5">R$ {{ number_format($totalCusto, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Contrato</th>
                            <th>Locatário</th>
                            <th>Imóvel</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Nº Processo</th>
                            <th>Vl. Cobrado</th>
                            <th>Vl. Recuperado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($acoes as $acao)
                        <tr>
                            <td>{{ $acao->contrato->codigo ?? 'N/A' }}</td>
                            <td>{{ $acao->contrato->locatario->nome ?? 'N/A' }}</td>
                            <td>{{ $acao->contrato->imovel->descricao ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $options['tiposAcao'][$acao->tipo_acao] ?? $acao->tipo_acao }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColor = match($acao->status) {
                                        'EM_ANDAMENTO'       => 'primary',
                                        'ACORDO_REALIZADO'   => 'success',
                                        'SENTENCA_FAVORAVEL' => 'success',
                                        'SENTENCA_CONTRARIA' => 'danger',
                                        'RECURSO'            => 'warning',
                                        'ENCERRADA'          => 'dark',
                                        'ARQUIVADA'          => 'secondary',
                                        default              => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ $options['statusAcao'][$acao->status] ?? $acao->status }}
                                </span>
                            </td>
                            <td>{{ $acao->numero_processo ?? '—' }}</td>
                            <td>R$ {{ number_format($acao->valor_cobrado, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($acao->valor_recuperado ?? 0, 2, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('acoes-judiciais.show', $acao) }}"
                                   class="btn btn-sm btn-outline-primary" title="Ver detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('acoes-judiciais.edit', $acao) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('acoes-judiciais.destroy', $acao) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Confirmar exclusão desta ação judicial?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Paginação --}}
    <div class="mt-3">
        {{ $acoes->links() }}
    </div>

@endif

@endsection
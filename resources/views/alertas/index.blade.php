@extends('layouts.app')

@section('title', 'Alertas')

@section('content')
    <h1 class="mb-3">Alertas</h1>

    <div class="card mb-3">
        <div class="card-header">Filtros</div>
        <div class="card-body">
            <form method="GET" action="{{ route('alertas.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Todos</option>
                            @foreach($statusPossiveis as $key => $label)
                                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="tipo_alerta" class="form-label">Tipo de Alerta</label>
                        <select name="tipo_alerta" id="tipo_alerta" class="form-select">
                            <option value="">Todos</option>
                            @foreach($tiposPossiveis as $key => $label)
                                <option value="{{ $key }}" {{ request('tipo_alerta') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="data_de" class="form-label">Data de</label>
                        <input type="date" name="data_de" id="data_de"
                               class="form-control" value="{{ request('data_de') }}">
                    </div>

                    <div class="col-md-2">
                        <label for="data_ate" class="form-label">Data até</label>
                        <input type="date" name="data_ate" id="data_ate"
                               class="form-control" value="{{ request('data_ate') }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            Filtrar
                        </button>
                        <a href="{{ route('alertas.index') }}" class="btn btn-secondary">
                            Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($alertas->isEmpty())
        <div class="alert alert-info">
            Nenhum alerta encontrado com os filtros aplicados.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Título</th>
                        <th>Vínculo</th>
                        <th>Detalhes do Vínculo</th>
                        <th>Status</th>
                        <th style="width: 260px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alertas as $alerta)
                        @php
                            $tipoLabel = $tiposPossiveis[$alerta->tipo_alerta] ?? $alerta->tipo_alerta;
                            $statusClass = match($alerta->status) {
                                'PENDENTE'    => 'bg-danger',
                                'VISUALIZADO' => 'bg-warning text-dark',
                                'RESOLVIDO'   => 'bg-success',
                                'IGNORADO'    => 'bg-secondary',
                                default       => 'bg-secondary',
                            };
                        @endphp
                        <tr>
                            <td>{{ $alerta->data_alerta?->format('d/m/Y H:i') }}</td>
                            <td>{{ $tipoLabel }}</td>
                            <td>
                                <strong>{{ $alerta->titulo }}</strong><br>
                                <small class="text-muted">
                                    {!! nl2br(e(\Illuminate\Support\Str::limit($alerta->descricao, 200))) !!}
                                </small>
                            </td>
                            <td>
                                @if($alerta->contrato)
                                    <a href="{{ route('contratos.show', $alerta->contrato->id) }}">
                                        Contrato {{ $alerta->contrato->codigo }}
                                    </a>
                                @elseif($alerta->parcela)
                                    <a href="{{ route('contratos.show', $alerta->parcela->contrato->id) }}">
                                        Contrato {{ $alerta->parcela->contrato->codigo }}
                                    </a>
                                @elseif($alerta->imovel)
                                    <a href="{{ route('imoveis.show', $alerta->imovel->id) }}">
                                        Imóvel {{ $alerta->imovel->descricao }}
                                    </a>
                                @elseif($alerta->despesaImovel)
                                    <a href="{{ route('despesas-imovel.show', $alerta->despesaImovel->id) }}">
                                        Despesa #{{ $alerta->despesaImovel->id }}
                                    </a>
                                @elseif($alerta->acaoJudicial)
                                    <a href="{{ route('acoes-judiciais.show', $alerta->acaoJudicial->id) }}">
                                        Ação Judicial #{{ $alerta->acaoJudicial->id }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($alerta->parcela)
                                    Parcela #{{ $alerta->parcela->id }}<br>
                                    <small class="text-muted">
                                        Venc. {{ $alerta->parcela->data_vencimento?->format('d/m/Y') }}
                                    </small>
                                @elseif($alerta->despesaImovel)
                                    <small class="text-muted">
                                        {{ $alerta->despesaImovel->tipo_despesa }}
                                        em {{ $alerta->despesaImovel->data_despesa?->format('d/m/Y') }}
                                    </small>
                                @elseif($alerta->acaoJudicial)
                                    <small class="text-muted">
                                        Processo: {{ $alerta->acaoJudicial->numero_processo ?? 'N/A' }}
                                        <br>
                                        Status: {{ $alerta->acaoJudicial->status ?? 'N/A' }}
                                    </small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $statusClass }}">
                                    {{ $statusPossiveis[$alerta->status] ?? $alerta->status }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($alerta->status === 'PENDENTE')
                                        <form action="{{ route('alertas.visualizar', $alerta->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                Visualizado
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($alerta->status, ['PENDENTE', 'VISUALIZADO']))
                                        <form action="{{ route('alertas.resolver', $alerta->id) }}" method="POST" class="ms-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                Resolvido
                                            </button>
                                        </form>
                                    @endif

                                    @if($alerta->status === 'PENDENTE')
                                        <form action="{{ route('alertas.ignorar', $alerta->id) }}" method="POST" class="ms-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                Ignorar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $alertas->links() }}
    @endif
@endsection
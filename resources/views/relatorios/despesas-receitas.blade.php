@extends('layouts.app')

@section('title', 'Relatório de Despesas x Receitas')

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .card { break-inside: avoid; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .table thead { display: table-header-group; }
        .table tr { page-break-inside: avoid; }
    }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h1><i class="bi bi-bar-chart-line-fill me-2"></i>Relatório de Despesas x Receitas</h1>
    <button class="btn btn-outline-secondary" onclick="window.print();">
        <i class="bi bi-printer me-2"></i>Imprimir
    </button>
</div>

{{-- Filtros --}}
<div class="card mb-4 no-print">
    <div class="card-header">Filtros</div>
    <div class="card-body">
        <form method="GET" action="{{ route('relatorios.despesas-receitas') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                           value="{{ $dataInicio }}">
                </div>

                <div class="col-md-3">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control"
                           value="{{ $dataFim }}">
                </div>

                <div class="col-md-3">
                    <label for="proprietario_id" class="form-label">Proprietário</label>
                    <select name="proprietario_id" id="proprietario_id" class="form-select">
                        <option value="">Todos os proprietários</option>
                        @foreach($proprietarios as $proprietario)
                            <option value="{{ $proprietario->id }}"
                                {{ $proprietarioId == $proprietario->id ? 'selected' : '' }}>
                                {{ $proprietario->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="imovel_id" class="form-label">Imóvel</label>
                    <select name="imovel_id" id="imovel_id" class="form-select">
                        <option value="">Todos os imóveis</option>
                        @foreach($todosImoveis as $imovel)
                            <option value="{{ $imovel->id }}"
                                {{ $imovelId == $imovel->id ? 'selected' : '' }}>
                                {{ $imovel->descricao }} ({{ $imovel->cidade ?? '' }}/{{ $imovel->estado ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">Aplicar Filtros</button>
                    <a href="{{ route('relatorios.despesas-receitas') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>
</div>

@if(!$dadosRelatorio)
    <div class="alert alert-info">
        Selecione um período e clique em "Aplicar Filtros" para visualizar o relatório.
    </div>
@else
    @php
        $dataInicioFormatada = $dadosRelatorio['data_inicio']->format('d/m/Y');
        $dataFimFormatada    = $dadosRelatorio['data_fim']->format('d/m/Y');
    @endphp

    {{-- Cabeçalho do relatório (aparece na impressão) --}}
    <div class="mb-3 print-header">
        <h2>Relatório de Despesas x Receitas</h2>
        <p>
            <strong>Período:</strong> {{ $dataInicioFormatada }} a {{ $dataFimFormatada }}
        </p>
        @if($proprietarioId)
            <p><strong>Proprietário:</strong> {{ $proprietarios->firstWhere('id', $proprietarioId)->nome ?? 'N/A' }}</p>
        @endif
        @if($imovelId)
            <p><strong>Imóvel:</strong> {{ $todosImoveis->firstWhere('id', $imovelId)->descricao ?? 'N/A' }}</p>
        @endif
    </div>

    {{-- Cards de Resumo Geral --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <h5 class="card-title">Receita Total Geral</h5>
                    <p class="card-text fs-4 text-success">
                        R$ {{ number_format($dadosRelatorio['receita_total_geral'], 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body">
                    <h5 class="card-title">Despesa Total Geral</h5>
                    <p class="card-text fs-4 text-danger">
                        R$ {{ number_format($dadosRelatorio['despesa_total_geral'], 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        @php
            $resultadoClasse = $dadosRelatorio['resultado_total_geral'] >= 0 ? 'text-primary' : 'text-danger';
        @endphp
        <div class="col-md-4">
            <div class="card border-{{ $dadosRelatorio['resultado_total_geral'] >= 0 ? 'primary' : 'warning' }}">
                <div class="card-body">
                    <h5 class="card-title">Resultado Geral</h5>
                    <p class="card-text fs-4 {{ $resultadoClasse }}">
                        R$ {{ number_format($dadosRelatorio['resultado_total_geral'], 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela Detalhada por Imóvel --}}
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="bi bi-house-fill me-2"></i> Detalhamento por Imóvel
        </div>
        <div class="card-body p-0">
            @if($dadosRelatorio['imoveis_com_dados']->isEmpty())
                <p class="p-3 mb-0">Nenhum imóvel com movimentação financeira encontrada no período e com os filtros selecionados.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Imóvel</th>
                                <th>Proprietário</th>
                                <th class="text-end">Receitas</th>
                                <th class="text-end">Despesas</th>
                                <th class="text-end">Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dadosRelatorio['imoveis_com_dados'] as $imovelDados)
                                @php
                                    $resultadoImovelClasse = $imovelDados->resultado >= 0 ? 'text-success' : 'text-danger';
                                @endphp
                                <tr>
                                    <td>{{ $imovelDados->descricao }}</td>
                                    <td>{{ $imovelDados->proprietario_nome }}</td>
                                    <td class="text-end text-success">R$ {{ number_format($imovelDados->receita, 2, ',', '.') }}</td>
                                    <td class="text-end text-danger">R$ {{ number_format($imovelDados->despesa, 2, ',', '.') }}</td>
                                    <td class="text-end {{ $resultadoImovelClasse }} fw-bold">R$ {{ number_format($imovelDados->resultado, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2">Total Geral</td>
                                <td class="text-end text-success">R$ {{ number_format($dadosRelatorio['receita_total_geral'], 2, ',', '.') }}</td>
                                <td class="text-end text-danger">R$ {{ number_format($dadosRelatorio['despesa_total_geral'], 2, ',', '.') }}</td>
                                <td class="text-end {{ $dadosRelatorio['resultado_total_geral'] >= 0 ? 'text-primary' : 'text-danger' }}">R$ {{ number_format($dadosRelatorio['resultado_total_geral'], 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endif

@endsection
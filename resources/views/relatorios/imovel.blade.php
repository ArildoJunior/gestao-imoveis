@extends('layouts.app')

@section('title', 'Relatório Financeiro por Imóvel')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h1>Relatório Financeiro por Imóvel</h1>
    </div>

    {{-- Filtros --}}
    <div class="card mb-4 no-print">
        <div class="card-header">Filtros</div>
        <div class="card-body">
            <form method="GET" action="{{ route('relatorios.imovel') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="imovel_id" class="form-label">Imóvel *</label>
                        <select name="imovel_id" id="imovel_id" class="form-select" required>
                            <option value="">Selecione um imóvel</option>
                            @foreach($imoveis as $imovel)
                                <option value="{{ $imovel->id }}"
                                    {{ request('imovel_id') == $imovel->id ? 'selected' : '' }}>
                                    {{ $imovel->descricao }} ({{ $imovel->cidade ?? '' }}/{{ $imovel->estado ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="contrato_id" class="form-label">Contrato (opcional)</label>
                        <select name="contrato_id" id="contrato_id" class="form-select">
                            <option value="">Todos os contratos</option>
                            @foreach($contratos as $contrato)
                                <option value="{{ $contrato->id }}"
                                    {{ request('contrato_id') == $contrato->id ? 'selected' : '' }}>
                                    {{ $contrato->codigo }} ({{ $contrato->locatario->nome ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="data_inicio" class="form-label">Data Início *</label>
                        <input type="date"
                               name="data_inicio"
                               id="data_inicio"
                               class="form-control"
                               value="{{ request('data_inicio', $dataInicioDefault) }}"
                               required>
                    </div>

                    <div class="col-md-2">
                        <label for="data_fim" class="form-label">Data Fim *</label>
                        <input type="date"
                               name="data_fim"
                               id="data_fim"
                               class="form-control"
                               value="{{ request('data_fim', $dataFimDefault) }}"
                               required>
                    </div>

                    <div class="col-md-3">
                        <label for="status_despesa" class="form-label">Status da Despesa</label>
                        <select name="status_despesa" id="status_despesa" class="form-select">
                            <option value="">Todas</option>
                            <option value="PENDENTE" {{ request('status_despesa') === 'PENDENTE' ? 'selected' : '' }}>Pendentes</option>
                            <option value="PAGA" {{ request('status_despesa') === 'PAGA' ? 'selected' : '' }}>Pagas</option>
                            <option value="REEMBOLSADA" {{ request('status_despesa') === 'REEMBOLSADA' ? 'selected' : '' }}>Reembolsadas</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            Gerar Relatório
                        </button>
                        <a href="{{ route('relatorios.imovel') }}" class="btn btn-secondary">
                            Limpar
                        </a>
                    </div>

                    <div class="col-md-3 d-flex align-items-end justify-content-end">
                        @if($dadosRelatorio)
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="window.print();">
                                Imprimir
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!$dadosRelatorio)
        <div class="alert alert-info">
            Selecione um imóvel e um período para visualizar o relatório.
        </div>
    @else
        @php
            $imovel = $dadosRelatorio['imovel'];
            $dataInicio = $dadosRelatorio['data_inicio'];
            $dataFim    = $dadosRelatorio['data_fim'];
            $receitaTotal = $dadosRelatorio['receita_total'];
            $despesaTotal = $dadosRelatorio['despesa_total'];
            $resultado    = $dadosRelatorio['resultado'];
        @endphp

        {{-- Cabeçalho do relatório (aparece na impressão) --}}
        <div class="mb-3 print-header">
            <h2>Relatório Financeiro por Imóvel</h2>
            <p>
                <strong>Imóvel:</strong>
                {{ $imovel->descricao }} ({{ $imovel->logradouro }}, {{ $imovel->numero }})
                @if($imovel->cidade || $imovel->estado)
                    - {{ $imovel->cidade ?? '' }}/{{ $imovel->estado ?? '' }}
                @endif
            </p>
            <p>
                <strong>Período:</strong>
                {{ $dataInicio->format('d/m/Y') }} a {{ $dataFim->format('d/m/Y') }}
            </p>
            @if($dadosRelatorio['contrato_id'])
                <p>
                    <strong>Contrato filtrado:</strong>
                    {{ optional($imovel->contratos->firstWhere('id', $dadosRelatorio['contrato_id']))->codigo ?? 'N/A' }}
                </p>
            @endif
        </div>

        {{-- Cards de resumo --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title">Receita Total</h5>
                        <p class="card-text fs-4 text-success">
                            R$ {{ number_format($receitaTotal, 2, ',', '.') }}
                        </p>
                        <p class="mb-0 text-muted small">
                            {{ $dadosRelatorio['pagamentos']->count() }} pagamento(s)
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body">
                        <h5 class="card-title">Despesa Total</h5>
                        <p class="card-text fs-4 text-danger">
                            R$ {{ number_format($despesaTotal, 2, ',', '.') }}
                        </p>
                        <p class="mb-0 text-muted small">
                            {{ $dadosRelatorio['despesas']->count() }} despesa(s)
                        </p>
                    </div>
                </div>
            </div>

            @php
                $resultadoClasse = $resultado >= 0 ? 'text-success' : 'text-danger';
            @endphp
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title">Resultado</h5>
                        <p class="card-text fs-4 {{ $resultadoClasse }}">
                            R$ {{ number_format($resultado, 2, ',', '.') }}
                        </p>
                        <p class="mb-0 text-muted small">
                            Receita - Despesa
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Receitas --}}
        <div class="card mb-4">
            <div class="card-header">
                Receitas (Pagamentos)
            </div>
            <div class="card-body p-0">
                @if($dadosRelatorio['pagamentos']->isEmpty())
                    <p class="p-3 mb-0">Nenhum pagamento encontrado no período.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Data Pagamento</th>
                                    <th>Valor Pago</th>
                                    <th>Contrato</th>
                                    <th>Locatário</th>
                                    <th>Competência</th>
                                    <th>Tipo Origem</th>
                                    <th>Forma Pagamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dadosRelatorio['pagamentos'] as $pagamento)
                                    @php
                                        $parcela  = $pagamento->parcela;
                                        $contrato = $parcela?->contrato;
                                    @endphp
                                    <tr>
                                        <td>{{ $pagamento->data_pagamento?->format('d/m/Y') }}</td>
                                        <td>R$ {{ number_format($pagamento->valor_pago, 2, ',', '.') }}</td>
                                        <td>{{ $contrato?->codigo ?? 'N/A' }}</td>
                                        <td>{{ $contrato?->locatario?->nome ?? 'N/A' }}</td>
                                        <td>{{ $parcela?->competencia ?? '-' }}</td>
                                        <td>{{ $parcela?->tipo_origem ?? '-' }}</td>
                                        <td>{{ $pagamento->forma_pagamento ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Despesas --}}
        <div class="card mb-4">
            <div class="card-header">
                Despesas do Imóvel
            </div>
            <div class="card-body p-0">
                @if($dadosRelatorio['despesas']->isEmpty())
                    <p class="p-3 mb-0">Nenhuma despesa encontrada no período.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                    <th>Responsável</th>
                                    <th>Status</th>
                                    <th>Contrato</th>
                                    <th>Registrado Por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dadosRelatorio['despesas'] as $despesa)
                                    <tr>
                                        <td>{{ $despesa->data_despesa?->format('d/m/Y') }}</td>
                                        <td>{{ $despesa->tipo_despesa }}</td>
                                        <td>{{ $despesa->descricao ?? '-' }}</td>
                                        <td>R$ {{ number_format($despesa->valor, 2, ',', '.') }}</td>
                                        <td>{{ $despesa->responsavel }}</td>
                                        <td>{{ $despesa->status }}</td>
                                        <td>{{ $despesa->contrato?->codigo ?? '-' }}</td>
                                        <td>{{ $despesa->registradoPor->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection

@push('styles')
<style>
    /* Esconde elementos na impressão */
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-header {
            margin-bottom: 20px;
        }

        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush
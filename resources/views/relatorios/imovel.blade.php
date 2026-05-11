@extends('layouts.app')

@section('title', 'Relatório Financeiro por Imóvel')

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .print-header { display: block !important; }
        .card { border: 1px solid #dee2e6 !important; }
        .card-header { background-color: #f8f9fa !important; color: #000 !important; }
        body { background-color: #fff !important; }
    }
    .print-header { display: none; }
</style>
@endpush

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h1 class="h3 mb-0">
            <i class="bi bi-bar-chart-line me-2"></i> Relatório Financeiro por Imóvel
        </h1>
    </div>

    {{-- ── FILTROS ──────────────────────────────────────────────────────── --}}
    <div class="card mb-4 no-print">
        <div class="card-header">
            <i class="bi bi-funnel me-1"></i> Filtros
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('relatorios.imovel') }}">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label for="imovel_id" class="form-label">Imóvel <span class="text-danger">*</span></label>
                        <select name="imovel_id" id="imovel_id" class="form-select" required>
                            <option value="">Selecione um imóvel</option>
                            @foreach($imoveis as $imovel)
                                <option value="{{ $imovel->id }}"
                                    {{ request('imovel_id') == $imovel->id ? 'selected' : '' }}>
                                    {{ $imovel->descricao }}
                                    ({{ $imovel->cidade ?? '' }}/{{ $imovel->estado ?? '' }})
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
                                    {{ $contrato->codigo }}
                                    ({{ $contrato->locatario->nome ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="data_inicio" class="form-label">Data Início <span class="text-danger">*</span></label>
                        <input type="date"
                               name="data_inicio"
                               id="data_inicio"
                               class="form-control"
                               value="{{ request('data_inicio', $dataInicioDefault) }}"
                               required>
                    </div>

                    <div class="col-md-2">
                        <label for="data_fim" class="form-label">Data Fim <span class="text-danger">*</span></label>
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
                            <option value="PENDENTE"    {{ request('status_despesa') === 'PENDENTE'    ? 'selected' : '' }}>Pendentes</option>
                            <option value="PAGA"        {{ request('status_despesa') === 'PAGA'        ? 'selected' : '' }}>Pagas</option>
                            <option value="REEMBOLSADA" {{ request('status_despesa') === 'REEMBOLSADA' ? 'selected' : '' }}>Reembolsadas</option>
                        </select>
                    </div>

                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Gerar Relatório
                        </button>
                        <a href="{{ route('relatorios.imovel') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Limpar
                        </a>
                        @if($dadosRelatorio)
                            <button type="button" class="btn btn-outline-secondary ms-auto" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Imprimir
                            </button>
                        @endif
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ── SEM RELATÓRIO ────────────────────────────────────────────────── --}}
    @if(!$dadosRelatorio)
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Selecione um imóvel e um período para visualizar o relatório.
        </div>

    @else

        @php
            $imovel       = $dadosRelatorio['imovel'];
            $dataInicio   = $dadosRelatorio['data_inicio'];
            $dataFim      = $dadosRelatorio['data_fim'];
            $receitaTotal = $dadosRelatorio['receita_total'];
            $despesaTotal = $dadosRelatorio['despesa_total'];
            $resultado    = $dadosRelatorio['resultado'];
            $totalInadimplencia = $dadosRelatorio['total_inadimplencia'];
            $inadimplenciasPorLocatario = $dadosRelatorio['inadimplencias_por_locatario'];
        @endphp

        {{-- ── CABEÇALHO IMPRESSÃO ─────────────────────────────────────── --}}
        <div class="mb-3 print-header">
            <h2>Relatório Financeiro por Imóvel</h2>
            <p>
                <strong>Imóvel:</strong>
                {{ $imovel->descricao }}
                ({{ $imovel->logradouro ?? '' }}, {{ $imovel->numero ?? '' }})
                @if($imovel->cidade || $imovel->estado)
                    — {{ $imovel->cidade ?? '' }}/{{ $imovel->estado ?? '' }}
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

        {{-- ── CARDS DE RESUMO ──────────────────────────────────────────── --}}
        <div class="row mb-4">

            {{-- Receita --}}
            <div class="col-md-3 mb-3">
                <div class="card border-success h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">
                            <i class="bi bi-arrow-down-circle-fill text-success me-1"></i> Receita Total
                        </h6>
                        <p class="card-text fs-5 fw-bold text-success mb-1">
                            R$ {{ number_format($receitaTotal, 2, ',', '.') }}
                        </p>
                        <p class="mb-0 text-muted small">
                            {{ $dadosRelatorio['pagamentos']->count() }} pagamento(s)
                        </p>
                    </div>
                </div>
            </div>

            {{-- Despesa --}}
            <div class="col-md-3 mb-3">
                <div class="card border-danger h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">
                            <i class="bi bi-arrow-up-circle-fill text-danger me-1"></i> Despesa Total
                        </h6>
                        <p class="card-text fs-5 fw-bold text-danger mb-1">
                            R$ {{ number_format($despesaTotal, 2, ',', '.') }}
                        </p>
                        <p class="mb-0 text-muted small">
                            {{ $dadosRelatorio['despesas']->count() }} despesa(s)
                        </p>
                    </div>
                </div>
            </div>

            {{-- Resultado --}}
            <div class="col-md-3 mb-3">
                @php $resultadoClasse = $resultado >= 0 ? 'text-success' : 'text-danger'; @endphp
                <div class="card border-primary h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">
                            <i class="bi bi-calculator text-primary me-1"></i> Resultado
                        </h6>
                        <p class="card-text fs-5 fw-bold {{ $resultadoClasse }} mb-1">
                            R$ {{ number_format($resultado, 2, ',', '.') }}
                        </p>
                        <p class="mb-0 text-muted small">Receita − Despesa</p>
                    </div>
                </div>
            </div>

            {{-- Inadimplência — NOVO CARD --}}
            <div class="col-md-3 mb-3">
                <div class="card h-100 {{ $totalInadimplencia > 0 ? 'border-warning' : 'border-success' }}">
                    <div class="card-body">
                        <h6 class="card-title text-muted">
                            <i class="bi bi-exclamation-triangle-fill {{ $totalInadimplencia > 0 ? 'text-warning' : 'text-success' }} me-1"></i>
                            Aluguéis em Aberto
                        </h6>
                        <p class="card-text fs-5 fw-bold {{ $totalInadimplencia > 0 ? 'text-warning' : 'text-success' }} mb-1">
                            R$ {{ number_format($totalInadimplencia, 2, ',', '.') }}
                        </p>
                        <p class="mb-0 text-muted small">
                            {{ $dadosRelatorio['parcelas_em_atraso']->count() }} parcela(s) pendente(s)
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── SEÇÃO: INADIMPLÊNCIA POR INQUILINO — NOVA ────────────────── --}}
        <div class="card mb-4 {{ $totalInadimplencia > 0 ? 'border-warning' : '' }}">
            <div class="card-header {{ $totalInadimplencia > 0 ? 'bg-warning text-dark' : 'bg-success text-white' }}">
                <i class="bi bi-person-exclamation me-2"></i>
                Situação de Inadimplência por Inquilino
            </div>
            <div class="card-body p-0">

                @if($inadimplenciasPorLocatario->isEmpty())
                    <p class="p-3 mb-0 text-success">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Nenhuma parcela em aberto para o imóvel e período selecionados.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Inquilino</th>
                                    <th>Contrato</th>
                                    <th>Parcelas em Aberto</th>
                                    <th>Vencimento mais Antigo</th>
                                    <th class="text-end">Saldo Devedor</th>
                                    <th>Situação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inadimplenciasPorLocatario as $item)
                                    <tr>
                                        <td>
                                            <i class="bi bi-person-fill me-1 text-muted"></i>
                                            {{ $item['locatario']->nome ?? 'N/A' }}
                                        </td>
                                        <td>{{ $item['contrato']->codigo ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ $item['qtd_parcelas'] }} parcela(s)
                                            </span>
                                        </td>
                                        <td>
                                            @if($item['mais_antiga'])
                                                {{ \Carbon\Carbon::parse($item['mais_antiga'])->format('d/m/Y') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            R$ {{ number_format($item['saldo_devedor'], 2, ',', '.') }}
                                        </td>
                                        <td>
                                            @if($item['saldo_devedor'] > 0)
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Devedor
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Quitado
                                                </span>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- Detalhamento das parcelas do inquilino --}}
                                    <tr class="table-light">
                                        <td colspan="6" class="p-0">
                                            <div class="px-4 py-2">
                                                <small class="text-muted fw-semibold">Detalhamento das parcelas:</small>
                                                <table class="table table-sm table-bordered mb-0 mt-1">
                                                    <thead>
                                                        <tr class="table-secondary">
                                                            <th>Vencimento</th>
                                                            <th>Competência</th>
                                                            <th>Tipo</th>
                                                            <th>Valor Devido</th>
                                                            <th>Valor Pago</th>
                                                            <th>Saldo</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($item['parcelas'] as $parcela)
                                                            <tr>
                                                                <td>{{ $parcela->data_vencimento?->format('d/m/Y') ?? '—' }}</td>
                                                                <td>{{ $parcela->competencia ?? '—' }}</td>
                                                                <td>{{ $parcela->tipo_origem ?? '—' }}</td>
                                                                <td>R$ {{ number_format($parcela->valor_devido, 2, ',', '.') }}</td>
                                                                <td>R$ {{ number_format($parcela->valor_pago, 2, ',', '.') }}</td>
                                                                <td class="text-danger fw-bold">
                                                                    R$ {{ number_format($parcela->valor_devido - $parcela->valor_pago, 2, ',', '.') }}
                                                                </td>
                                                                <td>
                                                                    @php
                                                                        $badgeClass = match($parcela->status) {
                                                                            'EM_ATRASO'         => 'bg-danger',
                                                                            'PAGA_PARCIALMENTE' => 'bg-warning text-dark',
                                                                            'ABERTA'            => 'bg-secondary',
                                                                            default             => 'bg-light text-dark',
                                                                        };
                                                                    @endphp
                                                                    <span class="badge {{ $badgeClass }}">
                                                                        {{ $parcela->status }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>

                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="4">Total Geral</td>
                                    <td class="text-end text-danger">
                                        R$ {{ number_format($totalInadimplencia, 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

            </div>
        </div>

        {{-- ── RECEITAS ─────────────────────────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-cash-coin me-2"></i> Receitas (Pagamentos)
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
                                        <td>{{ $pagamento->data_pagamento?->format('d/m/Y') ?? '—' }}</td>
                                        <td>R$ {{ number_format($pagamento->valor_pago, 2, ',', '.') }}</td>
                                        <td>{{ $contrato->codigo ?? 'N/A' }}</td>
                                        <td>{{ $contrato->locatario->nome ?? 'N/A' }}</td>
                                        <td>{{ $parcela->competencia ?? '—' }}</td>
                                        <td>{{ $parcela->tipo_origem ?? 'N/A' }}</td>
                                        <td>{{ $pagamento->forma_pagamento ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── DESPESAS ─────────────────────────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-receipt-cutoff me-2"></i> Despesas do Imóvel
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
                                        <td>{{ $despesa->data_despesa?->format('d/m/Y') ?? '—' }}</td>
                                        <td>{{ $despesa->tipo_despesa ?? 'N/A' }}</td>
                                        <td>{{ $despesa->descricao ?? 'N/A' }}</td>
                                        <td>R$ {{ number_format($despesa->valor, 2, ',', '.') }}</td>
                                        <td>{{ $despesa->responsavel ?? 'N/A' }}</td>
                                        <td>{{ $despesa->status ?? 'N/A' }}</td>
                                        <td>{{ $despesa->contrato->codigo ?? 'N/A' }}</td>
                                        <td>{{ $despesa->registradoPor->name ?? 'N/A' }}</td>
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
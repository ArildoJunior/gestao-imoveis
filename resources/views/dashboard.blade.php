@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </h1>
    <span class="text-muted small">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('d \d\e F \d\e Y') }}
    </span>
</div>

{{-- ════════════════════════════════════════════════════════════
     LINHA 1 — KPIs principais
═════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Parcelas em atraso --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card h-100 shadow-sm border-start border-danger border-4">
            <div class="card-body px-3 py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Em Atraso</p>
                        <h4 class="fw-bold text-danger mb-0">{{ $totalEmAtrasoQtd }}</h4>
                        <p class="small text-muted mb-0">
                            R$ {{ number_format($totalEmAtrasoValor, 2, ',', '.') }}
                        </p>
                    </div>
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- A vencer (7 dias) --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card h-100 shadow-sm border-start border-warning border-4">
            <div class="card-body px-3 py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Vencem em 7d</p>
                        <h4 class="fw-bold text-warning mb-0">{{ $totalAVencerQtd }}</h4>
                        <p class="small text-muted mb-0">
                            R$ {{ number_format($totalAVencerValor, 2, ',', '.') }}
                        </p>
                    </div>
                    <i class="bi bi-calendar-event-fill text-warning fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Recebido no mês --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card h-100 shadow-sm border-start border-success border-4">
            <div class="card-body px-3 py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Recebido/Mês</p>
                        <h4 class="fw-bold text-success mb-0">
                            R$ {{ number_format($totalRecebidoMes, 0, ',', '.') }}
                        </h4>
                        <p class="small text-muted mb-0">{{ $qtdPagamentosMes }} pagto(s)</p>
                    </div>
                    <i class="bi bi-cash-coin text-success fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Despesas do mês --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card h-100 shadow-sm border-start border-primary border-4">
            <div class="card-body px-3 py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Despesas/Mês</p>
                        <h4 class="fw-bold text-danger mb-0">
                            R$ {{ number_format($totalDespesasMes, 0, ',', '.') }}
                        </h4>
                        <p class="small text-muted mb-0">&nbsp;</p>
                    </div>
                    <i class="bi bi-receipt-cutoff text-danger fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Resultado do mês --}}
    <div class="col-6 col-md-4 col-lg-2">
        @php $corResultado = $resultadoMes >= 0 ? 'success' : 'danger'; @endphp
        <div class="card h-100 border-0 shadow-sm border-start border-{{ $corResultado }} border-4">
            <div class="card-body px-3 py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Resultado/Mês</p>
                        <h4 class="fw-bold text-{{ $corResultado }} mb-0">
                            R$ {{ number_format($resultadoMes, 0, ',', '.') }}
                        </h4>
                        <p class="small text-muted mb-0">Receita − Despesa</p>
                    </div>
                    <i class="bi bi-graph-up-arrow text-{{ $corResultado }} fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Taxa de Ocupação --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card h-100 shadow-sm border-start border-info border-4">
            <div class="card-body px-3 py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Ocupação</p>
                        <h4 class="fw-bold text-info mb-0">{{ $taxaOcupacao }}%</h4>
                        <p class="small text-muted mb-0">
                            {{ $imoveisOcupados }}/{{ $totalImoveis }} imóveis
                        </p>
                    </div>
                    <i class="bi bi-house-check-fill text-info fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════════════════════════
     LINHA 2 — Gráficos
═════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Gráfico de barras: Receitas x Despesas (6 meses) --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-bar-chart-fill me-2 text-primary"></i>
                    Receitas × Despesas — Últimos 6 meses
                </h6>
            </div>
            <div class="card-body">
                <canvas id="chartReceitasDespesas" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Gráfico doughnut: Status das parcelas --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-pie-chart-fill me-2 text-secondary"></i>
                    Status das Parcelas
                </h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="chartStatusParcelas" height="200"></canvas>
            </div>
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════════════════════════
     LINHA 3 — Contratos + Alertas + Ações Rápidas
═════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Contratos --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-file-earmark-text-fill me-2 text-primary"></i>
                    Contratos
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Ativos</span>
                    <span class="badge bg-success fs-6">{{ $qtdContratosAtivos }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted">Em Cobrança Judicial</span>
                    <span class="badge bg-danger fs-6">{{ $qtdContratosEmCobranca }}</span>
                </div>
                <div class="mt-3">
                    <a href="{{ route('contratos.index') }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-arrow-right-circle me-1"></i> Ver todos os contratos
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-bell-fill me-2 text-warning"></i>
                    Alertas Pendentes
                </h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div class="display-4 fw-bold
                    {{ $alertasPendentesQtd > 0 ? 'text-warning' : 'text-success' }}">
                    {{ $alertasPendentesQtd }}
                </div>
                <p class="text-muted mb-3">
                    {{ $alertasPendentesQtd == 1 ? 'alerta pendente' : 'alertas pendentes' }}
                </p>
                <a href="{{ route('alertas.index') }}" class="btn btn-outline-warning btn-sm w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i> Gerenciar alertas
                </a>
            </div>
        </div>
    </div>

    {{-- Ações Rápidas --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-lightning-fill me-2 text-info"></i>
                    Ações Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('pessoas.index') }}" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-people-fill me-2"></i> Pessoas
                    </a>
                    <a href="{{ route('imoveis.index') }}" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-house-fill me-2"></i> Imóveis
                    </a>
                    <a href="{{ route('financeiro.index') }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="bi bi-currency-dollar me-2"></i> Financeiro
                    </a>
                    <a href="{{ route('despesas-imovel.index') }}" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-receipt-cutoff me-2"></i> Despesas
                    </a>
                    <a href="{{ route('relatorios.imovel') }}" class="btn btn-outline-dark btn-sm text-start">
                        <i class="bi bi-bar-chart-line me-2"></i> Relatórios
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script type="text/javascript">
// @ts-nocheck
(function () {

    // ── Dados vindos do PHP ────────────────────────────────────────────────
    var chartLabels   = {!! json_encode($chartLabels) !!};
    var chartReceitas = {!! json_encode($chartReceitas) !!};
    var chartDespesas = {!! json_encode($chartDespesas) !!};

    var statusLabels  = {!! json_encode(array_keys($parcelasStatus)) !!};
    var statusData    = {!! json_encode(array_values($parcelasStatus)) !!};

    // ── Formatador BRL ─────────────────────────────────────────────────────
    function formatBRL(value) {
        return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    }

    // ── Gráfico de Barras: Receitas x Despesas ─────────────────────────────
    var ctxBar = document.getElementById('chartReceitasDespesas');
    if (ctxBar) {
        new Chart(ctxBar.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Receitas',
                        data: chartReceitas,
                        backgroundColor: 'rgba(25, 135, 84, 0.75)',
                        borderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Despesas',
                        data: chartDespesas,
                        backgroundColor: 'rgba(220, 53, 69, 0.75)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.dataset.label + ': ' + formatBRL(ctx.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(v) {
                                return 'R$ ' + v.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });
    }

    // ── Gráfico Doughnut: Status das Parcelas ──────────────────────────────
    var ctxDoughnut = document.getElementById('chartStatusParcelas');
    if (ctxDoughnut) {
        new Chart(ctxDoughnut.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(13, 110, 253, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.label + ': ' + ctx.raw + ' parcela(s)';
                            }
                        }
                    }
                }
            }
        });
    }

}());
</script>
@endpush
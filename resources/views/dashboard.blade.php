@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="mb-4">Dashboard</h1>

    <div class="row">
        {{-- Parcelas em atraso --}}
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-danger h-100">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Parcelas em atraso
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        {{ $totalEmAtrasoQtd }} parcela(s)
                    </h5>
                    <p class="card-text">
                        Valor em aberto:
                        <strong>R$ {{ number_format($totalEmAtrasoValor, 2, ',', '.') }}</strong>
                    </p>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        Considera parcelas vencidas e ainda não quitadas.
                    </p>
                </div>
            </div>
        </div>

        {{-- Parcelas a vencer (7 dias) --}}
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-warning h-100">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-calendar-week-fill me-2"></i> Parcelas a vencer (7 dias)
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        {{ $totalAVencerQtd }} parcela(s)
                    </h5>
                    <p class="card-text">
                        Valor em aberto:
                        <strong>R$ {{ number_format($totalAVencerValor, 2, ',', '.') }}</strong>
                    </p>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        Considera status ABERTA / PAGA PARCIALMENTE com vencimento até +7 dias.
                    </p>
                </div>
            </div>
        </div>

        {{-- Recebimentos do mês --}}
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-success h-100">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-cash-coin me-2"></i> Recebimentos do Mês
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        {{ $qtdPagamentosMes }} pagamento(s)
                    </h5>
                    <p class="card-text">
                        Total recebido:
                        <strong>R$ {{ number_format($totalRecebidoMes, 2, ',', '.') }}</strong>
                    </p>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        Soma dos pagamentos registrados no mês atual.
                    </p>
                </div>
            </div>
        </div>

        {{-- Contratos --}}
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-info h-100">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-file-earmark-text-fill me-2"></i> Contratos
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        {{ $qtdContratosAtivos }} ativo(s)
                    </h5>
                    <p class="card-text">
                        {{ $qtdContratosEmCobranca }} em cobrança judicial
                    </p>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        Visão geral dos contratos.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Segunda linha --}}
    <div class="row mt-2">
        {{-- Alertas pendentes --}}
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-secondary h-100">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-bell-fill me-2"></i> Alertas Pendentes
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        {{ $alertasPendentesQtd }} alerta(s)
                    </h5>
                    <p class="card-text">
                        <a href="{{ route('alertas.index') }}" class="btn btn-sm btn-light">
                            Ver alertas
                        </a>
                    </p>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        Inclui parcelas, contratos, caução, reajuste e despesas pendentes.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Atalhos principais --}}
    <h2 class="mt-4 mb-3">Ações Rápidas</h2>
    <div class="row">
        <div class="col-md-3 mb-2">
            <a href="{{ route('pessoas.index') }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-people-fill me-2"></i> Pessoas
            </a>
        </div>
        <div class="col-md-3 mb-2">
            <a href="{{ route('imoveis.index') }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-house-fill me-2"></i> Imóveis
            </a>
        </div>
        <div class="col-md-3 mb-2">
            <a href="{{ route('contratos.index') }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-journal-text me-2"></i> Contratos
            </a>
        </div>
        <div class="col-md-3 mb-2">
            <a href="{{ route('despesas-imovel.index') }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-receipt-cutoff me-2"></i> Despesas
            </a>
        </div>
        <div class="col-md-3 mb-2">
            <a href="{{ route('financeiro.index') }}" class="btn btn-outline-primary w-100">
                <i class="bi bi-currency-dollar me-2"></i> Financeiro
            </a>
        </div>
        <div class="col-md-3 mb-2">
            <a href="{{ route('alertas.index') }}" class="btn btn-outline-warning w-100">
                <i class="bi bi-bell-fill me-2"></i> Alertas
            </a>
        </div>
        <div class="col-md-3 mb-2">
            <a href="{{ route('backups.index') }}" class="btn btn-outline-dark w-100">
                <i class="bi bi-hdd-network me-2"></i> Backups
            </a>
        </div>
    </div>
@endsection
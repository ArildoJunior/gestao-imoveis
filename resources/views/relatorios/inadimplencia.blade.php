@extends('layouts.app')

@section('title', 'Relatório de Inadimplência')

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .card { break-inside: avoid; }
    }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h1><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Relatório de Inadimplência</h1>
    <button class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Imprimir
    </button>
</div>

{{-- ── FILTROS ──────────────────────────────────────────────────── --}}
<div class="card mb-4 no-print">
    <div class="card-header">Filtros</div>
    <div class="card-body">
        <form method="GET" action="{{ route('relatorios.inadimplencia') }}">
            <div class="row g-3">

                <div class="col-md-3">
                    <label for="data_fim" class="form-label">Vencido até</label>
                    <input type="date"
                           name="data_fim"
                           id="data_fim"
                           class="form-control"
                           value="{{ $dataFim }}">
                </div>

                <div class="col-md-3">
                    <label for="locatario_id" class="form-label">Locatário</label>
                    <select name="locatario_id" id="locatario_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($locatarios as $locatario)
                            <option value="{{ $locatario->id }}"
                                {{ $locatarioId == $locatario->id ? 'selected' : '' }}>
                                {{ $locatario->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="contrato_id" class="form-label">Contrato</label>
                    <select name="contrato_id" id="contrato_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($todosContratos as $c)
                            <option value="{{ $c->id }}"
                                {{ $contratoId == $c->id ? 'selected' : '' }}>
                                {{ $c->codigo }}
                                @if($c->locatario)
                                    ({{ $c->locatario->nome }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('relatorios.inadimplencia') }}" class="btn btn-secondary w-100">
                        Limpar
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── RESUMO GERAL ─────────────────────────────────────────────── --}}
@if($contratos->isEmpty())
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill me-2"></i>
        Nenhuma parcela em atraso encontrada com os filtros selecionados.
    </div>
@else

    {{-- Cards de resumo --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-danger text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Contratos Inadimplentes</h6>
                    <p class="display-6 text-danger fw-bold">{{ $contratos->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Parcelas em Aberto</h6>
                    <p class="display-6 text-danger fw-bold">
                        {{ $contratos->sum(fn($c) => $c->parcelas->count()) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total em Aberto</h6>
                    <p class="display-6 text-danger fw-bold">
                        R$ {{ number_format($totalGeral, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela resumo --}}
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <i class="bi bi-table me-1"></i> Resumo por Contrato
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Contrato</th>
                            <th>Locatário</th>
                            <th>Telefone</th>
                            <th>Imóvel</th>
                            <th>Parcelas</th>
                            <th>Total Devido</th>
                            <th>Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contratos as $contrato)
                        <tr>
                            <td>{{ $contrato->codigo }}</td>
                            <td>{{ $contrato->locatario->nome ?? 'N/A' }}</td>
                            <td>{{ $contrato->locatario->telefone ?? 'N/A' }}</td>
                            <td>{{ $contrato->imovel->descricao ?? 'N/A' }}</td>
                            <td>{{ $contrato->parcelas->count() }}</td>
                            <td class="text-danger fw-bold">
                                R$ {{ number_format($contrato->total_devido, 2, ',', '.') }}
                            </td>
                            <td>
                                <a href="#contrato-{{ $contrato->id }}" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4">Total Geral</td>
                            <td>{{ $contratos->sum(fn($c) => $c->parcelas->count()) }}</td>
                            <td class="text-danger">
                                R$ {{ number_format($totalGeral, 2, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ── DETALHAMENTO POR CONTRATO ────────────────────────────── --}}
    @foreach($contratos as $contrato)
    <div class="card mb-4" id="contrato-{{ $contrato->id }}">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-text me-1"></i>
                Contrato: <strong>{{ $contrato->codigo }}</strong>
            </h5>
            <span class="badge bg-danger fs-6">
                Total Devido: R$ {{ number_format($contrato->total_devido, 2, ',', '.') }}
            </span>
        </div>
        <div class="card-body">

            {{-- Dados do locatário e imóvel --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong><i class="bi bi-person me-1"></i> Locatário:</strong>
                    {{ $contrato->locatario->nome ?? 'N/A' }}<br>
                    <strong>CPF/CNPJ:</strong>
                    {{ $contrato->locatario->cpf_cnpj ?? 'N/A' }}<br>
                    <strong>Telefone:</strong>
                    {{ $contrato->locatario->telefone ?? 'N/A' }}<br>
                    <strong>E-mail:</strong>
                    {{ $contrato->locatario->email ?? 'N/A' }}
                </div>
                <div class="col-md-6">
                    <strong><i class="bi bi-house me-1"></i> Imóvel:</strong>
                    {{ $contrato->imovel->descricao ?? 'N/A' }}<br>
                    <strong>Endereço:</strong>
                    {{ $contrato->imovel->logradouro ?? '' }}
                    {{ $contrato->imovel->numero ? ', ' . $contrato->imovel->numero : '' }}<br>
                    <strong>Cidade:</strong>
                    {{ $contrato->imovel->cidade ?? 'N/A' }} /
                    {{ $contrato->imovel->estado ?? 'N/A' }}
                </div>
            </div>

            {{-- Parcelas em atraso --}}
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-danger">
                        <tr>
                            <th>Competência</th>
                            <th>Vencimento</th>
                            <th>Dias em Atraso</th>
                            <th>Valor Devido</th>
                            <th>Valor Pago</th>
                            <th>Saldo Devedor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contrato->parcelas as $parcela)
                        @php
                            $vencimento  = \Carbon\Carbon::parse($parcela->data_vencimento);
                            $diasAtraso  = $vencimento->diffInDays(\Carbon\Carbon::today());
                            $valorPago   = $parcela->valor_pago ?? 0;
                            $valorDevido = $parcela->valor_devido ?? 0;
                            $saldo       = $valorDevido - $valorPago;
                            $badgeClass  = match($parcela->status) {
                                'EM_ATRASO'        => 'bg-danger',
                                'PAGA_PARCIALMENTE'=> 'bg-warning text-dark',
                                default            => 'bg-secondary',
                            };
                        @endphp
                        <tr>
                            <td>{{ $parcela->competencia ?? '—' }}</td>
                            <td>{{ $vencimento->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-danger">{{ $diasAtraso }}d</span>
                            </td>
                            <td>R$ {{ number_format($valorDevido, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($valorPago, 2, ',', '.') }}</td>
                            <td class="text-danger fw-bold">
                                R$ {{ number_format($saldo, 2, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ $parcela->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5">Total do Contrato</td>
                            <td class="text-danger">
                                R$ {{ number_format($contrato->total_devido, 2, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
    @endforeach

@endif

@endsection
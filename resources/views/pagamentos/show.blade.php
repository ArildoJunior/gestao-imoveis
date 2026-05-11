@extends('layouts.app')

@section('title', 'Pagamentos da Parcela')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Pagamentos da Parcela #{{ $parcela->id }}</h1>

        <div>
            <a href="{{ route('contratos.show', $parcela->contrato_id) }}" class="btn btn-secondary">
                Voltar ao Contrato
            </a>
            <a href="{{ route('pagamentos.create', $parcela->id) }}" class="btn btn-primary">
                Registrar Novo Pagamento
            </a>
        </div>
    </div>

    {{-- Dados gerais da parcela --}}
    <div class="card mb-4">
        <div class="card-header">
            Dados da Parcela
        </div>
        <div class="card-body">
            <p>
                <strong>Contrato:</strong> {{ $parcela->contrato->codigo ?? 'N/A' }}
                @if($parcela->contrato && $parcela->contrato->locatario)
                    – Locatário: {{ $parcela->contrato->locatario->nome }}
                @endif
            </p>
            <p>
                <strong>Imóvel:</strong> {{ $parcela->contrato->imovel->descricao ?? 'N/A' }}
            </p>
            <p><strong>Competência:</strong> {{ $parcela->competencia }}</p>
            <p><strong>Vencimento:</strong> {{ $parcela->data_vencimento?->format('d/m/Y') }}</p>

            @php
                $origemLabel = match($parcela->tipo_origem) {
                    'ALUGUEL_NORMAL'  => 'Aluguel',
                    'CAUCAO'          => 'Caução',
                    'ACORDO_ATRASO',
                    'EM_ACORDO'       => 'Acordo',
                    'MULTA_RESCISAO'  => 'Multa Rescisão',
                    'TAXA_EXTRA'      => 'Taxa Extra',
                    'TEMPORADA'       => 'Temporada',
                    default           => $parcela->tipo_origem,
                };
            @endphp
            <p><strong>Origem:</strong> {{ $origemLabel }}</p>

            <p>
                <strong>Valor Original:</strong>
                R$ {{ number_format($parcela->valor_original, 2, ',', '.') }}
            </p>
            <p>
                <strong>Valor Devido Atual (base cadastro):</strong>
                R$ {{ number_format($parcela->valor_devido, 2, ',', '.') }}
            </p>
            <p>
                <strong>Valor Pago Total:</strong>
                R$ {{ number_format($parcela->valor_pago, 2, ',', '.') }}
            </p>
            <p>
                <strong>Status Atual:</strong> {{ $parcela->status }}
            </p>
        </div>
    </div>

    {{-- Histórico de pagamentos --}}
    <div class="card">
        <div class="card-header">
            Histórico de Pagamentos
        </div>
        <div class="card-body p-0">
            @if($parcela->pagamentos->isEmpty())
                <p class="p-3 mb-0 text-muted">
                    Nenhum pagamento registrado para esta parcela.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Data Pagamento</th>
                                <th>Valor Pago</th>
                                <th>Forma</th>
                                <th>Comprovante</th>
                                <th>Observações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parcela->pagamentos as $pagamento)
                                <tr>
                                    <td>{{ $pagamento->data_pagamento?->format('d/m/Y') }}</td>
                                    <td>R$ {{ number_format($pagamento->valor_pago, 2, ',', '.') }}</td>
                                    <td>{{ $pagamento->forma_pagamento }}</td>
                                    <td>{{ $pagamento->numero_comprovante ?? '-' }}</td>
                                    <td>{{ $pagamento->observacoes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
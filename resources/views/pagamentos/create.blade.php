@extends('layouts.app')

@section('title', 'Registrar Pagamento')

@section('content')
    <h1 class="mb-3">Registrar Pagamento para Parcela #{{ $parcela->id }}</h1>

    {{-- Dados da parcela --}}
    <div class="mb-3">
        <p>
            Contrato:
            <strong>{{ $parcela->contrato->codigo ?? 'N/A' }}</strong>
            - Competência:
            <strong>{{ $parcela->competencia }}</strong>
        </p>
        <p>
            Vencimento:
            <strong>{{ $parcela->data_vencimento?->format('d/m/Y') }}</strong>
        </p>
        <p>Valor Original: <strong>R$ {{ number_format($parcela->valor_original, 2, ',', '.') }}</strong></p>

        <p>
            Multa (calculada até hoje):
            <strong>R$ {{ number_format($multaAtual, 2, ',', '.') }}</strong>
        </p>
        <p>
            Juros (calculados até hoje):
            <strong>R$ {{ number_format($jurosAtuais, 2, ',', '.') }}</strong>
        </p>

        <p>
            Valor Devido (até hoje, com multa/juros/desconto):
            <strong>R$ {{ number_format($valorDevidoAtual, 2, ',', '.') }}</strong>
        </p>

        <p>Valor Já Pago: <strong>R$ {{ number_format($parcela->valor_pago, 2, ',', '.') }}</strong></p>
        <p>
            Valor Restante:
            <strong>R$ {{ number_format($valorRestante, 2, ',', '.') }}</strong>
        </p>
    </div>

    {{-- Histórico de pagamentos desta parcela --}}
    <div class="card mb-4">
        <div class="card-header">
            Histórico de Pagamentos desta Parcela
        </div>
        <div class="card-body p-0">
            @if($parcela->pagamentos->isEmpty())
                <p class="p-3 mb-0 text-muted">
                    Nenhum pagamento registrado ainda para esta parcela.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
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

    {{-- Erro geral de pagamento --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Formulário de novo pagamento --}}
    <form action="{{ route('pagamentos.store', $parcela->id) }}"
          method="POST"
          id="paymentForm">
        @csrf

        {{-- Valor a pagar agora --}}
        <div class="mb-3">
            <label for="valor_pago" class="form-label">Valor a Pagar Agora</label>
            <input type="number"
                   name="valor_pago"
                   id="valor_pago"
                   class="form-control @error('valor_pago') is-invalid @enderror"
                   step="0.01"
                   min="0.01"
                   max="{{ $valorRestante + 0.01 }}"
                   value="{{ old('valor_pago', $valorRestante) }}"
                   required>
            @error('valor_pago')
                <div class="text-danger">{{ $message }}</div>
            @enderror
            <small class="text-muted">
                Pode ser pagamento parcial. O sistema acumula até quitar a parcela.
            </small>
        </div>

        {{-- Desconto / Perdão --}}
        <div class="mb-3">
            <label for="desconto" class="form-label">
                Desconto / Perdão (R$)
            </label>
            <input type="number"
                   name="desconto"
                   id="desconto"
                   class="form-control @error('desconto') is-invalid @enderror"
                   step="0.01"
                   min="0"
                   value="{{ old('desconto', 0) }}">
            @error('desconto')
                <div class="text-danger">{{ $message }}</div>
            @enderror
            <small class="text-muted">
                Use este campo para perdoar parte da multa/juros ou conceder desconto no valor devido.
            </small>
        </div>

        {{-- Forma de pagamento --}}
        <div class="mb-3">
            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
            <select name="forma_pagamento"
                    id="forma_pagamento"
                    class="form-select @error('forma_pagamento') is-invalid @enderror"
                    required>
                <option value="">Selecione</option>
                @foreach($formasPagamento as $key => $label)
                    <option value="{{ $key }}" {{ old('forma_pagamento') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('forma_pagamento')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        {{-- Comprovante --}}
        <div class="mb-3">
            <label for="numero_comprovante" class="form-label">Número do Comprovante (opcional)</label>
            <input type="text"
                   name="numero_comprovante"
                   id="numero_comprovante"
                   class="form-control @error('numero_comprovante') is-invalid @enderror"
                   value="{{ old('numero_comprovante') }}">
            @error('numero_comprovante')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        {{-- Observações --}}
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações (opcional)</label>
            <textarea name="observacoes"
                      id="observacoes"
                      class="form-control @error('observacoes') is-invalid @enderror"
                      rows="3">{{ old('observacoes') }}</textarea>
            @error('observacoes')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit"
                class="btn btn-primary mt-3"
                id="submitPaymentBtn">
            Registrar Pagamento
        </button>
        <a href="{{ route('contratos.show', $parcela->contrato->id) }}"
           class="btn btn-secondary mt-3">
            Cancelar
        </a>
    </form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form      = document.getElementById('paymentForm');
        const submitBtn = document.getElementById('submitPaymentBtn');

        if (!form || !submitBtn) {
            return;
        }

        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registrando...';
        });
    });
</script>
@endpush
@extends('layouts.app')

@section('title', 'Nova Renegociação')

@section('content')
    <h1 class="mb-4">Nova Renegociação de Débitos</h1>

    {{-- Erros de validação --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Problemas ao salvar o acordo:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Resumo do contrato --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Contrato</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1">
                        <strong>Código:</strong> {{ $contrato->codigo }}
                    </p>
                    <p class="mb-1">
                        <strong>Imóvel:</strong> {{ $contrato->imovel->descricao ?? '-' }}
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1">
                        <strong>Locatário:</strong> {{ $contrato->locatario->nome ?? '-' }}
                    </p>
                    <p class="mb-1">
                        <strong>Status do Contrato:</strong>
                        <span class="badge bg-info">{{ $contrato->status }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Parcelas selecionadas para o acordo --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Parcelas selecionadas para renegociação</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Competência</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th class="text-end">Valor Devido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($parcelas as $parcela)
                            <tr>
                                <td>{{ $parcela->id }}</td>
                                <td>{{ $parcela->competencia }}</td>
                                <td>
                                    {{ optional($parcela->data_vencimento)->format('d/m/Y') }}
                                </td>
                                <td>
                                    @php
                                        $badgeStatus = $parcela->status === 'EM_ATRASO'
                                            ? 'danger'
                                            : 'warning';
                                    @endphp
                                    <span class="badge bg-{{ $badgeStatus }}">
                                        {{ $parcela->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    R$ {{ number_format($parcela->valor_devido, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end">
                                Total original dos débitos
                            </td>
                            <td class="text-end">
                                R$ {{ number_format($valorOriginalTotal, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{--
        O valor original total é passado para o JS via atributo data-valor-original
        no elemento do formulário, evitando conflito entre Blade e o parser JS.
    --}}
    <form
        action="{{ route('renegociacoes.store') }}"
        method="POST"
        id="formAcordo"
        data-valor-original="{{ $valorOriginalTotal }}"
    >
        @csrf

        {{-- Hidden: contrato_id --}}
        <input type="hidden" name="contrato_id" value="{{ $contrato->id }}">

        {{-- Hidden: parcelas_ids[] alinhado com RenegociacaoController@store --}}
        @foreach ($parcelas as $parcela)
            <input type="hidden" name="parcelas_ids[]" value="{{ $parcela->id }}">
        @endforeach

        <div class="card mb-4">
            <div class="card-header">
                <strong>Condições do Acordo</strong>
            </div>
            <div class="card-body">

                <div class="row">

                    {{-- Valor original (somente leitura) --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Valor original total dos débitos</label>
                        <input
                            type="text"
                            class="form-control"
                            value="R$ {{ number_format($valorOriginalTotal, 2, ',', '.') }}"
                            readonly
                            tabindex="-1"
                        >
                        <small class="text-muted">
                            Calculado automaticamente com base nas parcelas selecionadas.
                        </small>
                    </div>

                    {{-- Valor acordado --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Valor acordado <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="valor_acordado"
                                id="valor_acordado"
                                class="form-control @error('valor_acordado') is-invalid @enderror"
                                value="{{ old('valor_acordado', number_format($valorOriginalTotal, 2, '.', '')) }}"
                                required
                            >
                        </div>
                        @error('valor_acordado')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Pode ser igual ou menor que o total (concessão de desconto).
                        </small>
                    </div>

                    {{-- Desconto estimado (somente leitura, calculado via JS) --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Desconto concedido (estimado)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input
                                type="text"
                                id="desconto_estimado"
                                class="form-control"
                                value="0,00"
                                readonly
                                tabindex="-1"
                            >
                        </div>
                        <small class="text-muted">
                            Calculado automaticamente ao alterar o valor acordado.
                        </small>
                    </div>

                </div>

                <div class="row">

                    {{-- Número de parcelas --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Número de parcelas do acordo <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="numero_parcelas_acordo"
                            id="numero_parcelas_acordo"
                            min="1"
                            max="360"
                            class="form-control @error('numero_parcelas_acordo') is-invalid @enderror"
                            value="{{ old('numero_parcelas_acordo', 1) }}"
                            required
                        >
                        @error('numero_parcelas_acordo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Dia de vencimento --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Dia de vencimento das parcelas <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="dia_vencimento_acordo"
                            min="1"
                            max="31"
                            class="form-control @error('dia_vencimento_acordo') is-invalid @enderror"
                            value="{{ old('dia_vencimento_acordo', $contrato->dia_vencimento) }}"
                            required
                        >
                        @error('dia_vencimento_acordo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Normalmente igual ao dia de vencimento do contrato.
                        </small>
                    </div>

                    {{-- Primeiro vencimento --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Primeiro vencimento do acordo <span class="text-danger">*</span>
                        </label>
                        <input
                            type="date"
                            name="primeiro_vencimento_acordo"
                            class="form-control @error('primeiro_vencimento_acordo') is-invalid @enderror"
                            value="{{ old('primeiro_vencimento_acordo', now()->addMonth()->toDateString()) }}"
                            min="{{ now()->toDateString() }}"
                            required
                        >
                        @error('primeiro_vencimento_acordo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Data da 1ª parcela do acordo. Deve ser hoje ou futura.
                        </small>
                    </div>

                </div>

                {{-- Previa do valor por parcela --}}
                <div class="alert alert-info py-2 mb-3" id="previewParcela">
                    <strong>Previa:</strong>
                    <span id="previewTexto">
                        1 parcela de R$ {{ number_format($valorOriginalTotal, 2, ',', '.') }}
                    </span>
                </div>

                {{-- Descrição do acordo --}}
                <div class="mb-3">
                    <label class="form-label">Descricao e Observacoes do acordo</label>
                    <textarea
                        name="descricao_acordo"
                        rows="3"
                        class="form-control @error('descricao_acordo') is-invalid @enderror"
                        placeholder="Ex: Acordo para quitar debitos em atraso referentes as competencias X, Y, Z."
                    >{{ old('descricao_acordo') }}</textarea>
                    @error('descricao_acordo')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Botoes de acao --}}
        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route('financeiro.index') }}" class="btn btn-secondary">
                Voltar ao Financeiro
            </a>
            <button
                type="submit"
                class="btn btn-success btn-lg"
                id="btnConfirmar"
            >
                Confirmar e Gerar Acordo
            </button>
        </div>

    </form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    var formAcordo    = document.getElementById('formAcordo');
    var btnConfirmar  = document.getElementById('btnConfirmar');
    var campoValor    = document.getElementById('valor_acordado');
    var campoParcelas = document.getElementById('numero_parcelas_acordo');
    var descontoField = document.getElementById('desconto_estimado');
    var previewTexto  = document.getElementById('previewTexto');

    if (!formAcordo) {
        return;
    }

    var valorOriginalTotal = parseFloat(formAcordo.getAttribute('data-valor-original')) || 0;

    function formatarReais(valor) {
        return valor.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function atualizarCalculo() {
        var valorAcordado = parseFloat(campoValor.value) || 0;
        var numParcelas   = parseInt(campoParcelas.value) || 1;

        var desconto = valorOriginalTotal - valorAcordado;
        if (desconto < 0) {
            desconto = 0;
        }

        if (descontoField) {
            descontoField.value = formatarReais(desconto);
        }

        var valorPorParcela = numParcelas > 0
            ? valorAcordado / numParcelas
            : valorAcordado;

        if (previewTexto) {
            previewTexto.textContent = numParcelas
                + ' parcela(s) de R$ '
                + formatarReais(valorPorParcela);
        }
    }

    if (campoValor) {
        campoValor.addEventListener('input', atualizarCalculo);
    }

    if (campoParcelas) {
        campoParcelas.addEventListener('input', atualizarCalculo);
    }

    formAcordo.addEventListener('submit', function () {
        if (btnConfirmar) {
            btnConfirmar.disabled    = true;
            btnConfirmar.textContent = 'Processando...';
        }
    });

    atualizarCalculo();

});
</script>
@endpush
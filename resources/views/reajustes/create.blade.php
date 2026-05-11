@extends('layouts.app')

@section('title', 'Reajuste de Contrato')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Reajuste de Contrato</h1>
        <a href="{{ route('contratos.show', $contrato->id) }}" class="btn btn-secondary">Voltar ao contrato</a>
    </div>

    {{-- Resumo do contrato --}}
    <div class="card mb-4">
        <div class="card-header">
            Dados do contrato
        </div>
        <div class="card-body">
            <p><strong>Código:</strong> {{ $contrato->codigo }}</p>
            <p><strong>Imóvel:</strong> {{ $contrato->imovel->descricao ?? '-' }}</p>
            <p><strong>Locatário:</strong> {{ $contrato->locatario->nome ?? '-' }}</p>
            <p><strong>Valor aluguel atual:</strong>
                <strong>R$ {{ number_format($valorAtual, 2, ',', '.') }}</strong>
            </p>
            <p>
                <strong>Índice configurado no contrato:</strong>
                {{ $indiceReajuste ?? 'Não definido' }}
                @if($percentualPadrao)
                    (Padrão: {{ number_format($percentualPadrao, 2, ',', '.') }}%)
                @endif
            </p>
        </div>
    </div>

    {{-- Formulário de reajuste --}}
    <div class="card">
        <div class="card-header">
            Aplicar reajuste
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Verifique os erros abaixo:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('reajustes.store', $contrato->id) }}"
                  method="POST"
                  id="formReajuste"
                  data-valor-atual="{{ $valorAtual }}">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Valor atual</label>
                        <input type="text"
                               class="form-control"
                               value="R$ {{ number_format($valorAtual, 2, ',', '.') }}"
                               readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="percentual_aplicado" class="form-label">
                            Percentual de reajuste (%) *
                        </label>
                        <input type="number"
                               step="0.01"
                               name="percentual_aplicado"
                               id="percentual_aplicado"
                               class="form-control @error('percentual_aplicado') is-invalid @enderror"
                               value="{{ old('percentual_aplicado', $percentualPadrao ?? 0) }}"
                               required>
                        @error('percentual_aplicado')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Você pode usar o percentual padrão do contrato ou informar outro valor.
                        </small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Valor novo (prévia)</label>
                        <input type="text"
                               id="valor_novo_preview"
                               class="form-control"
                               value="R$ {{ number_format($valorAtual, 2, ',', '.') }}"
                               readonly>
                        <small class="text-muted">
                            Calculado automaticamente conforme o percentual.
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição (opcional)</label>
                    <textarea name="descricao"
                              id="descricao"
                              class="form-control @error('descricao') is-invalid @enderror"
                              rows="2"
                              placeholder="Ex: Reajuste anual conforme índice IPCA acumulado.">{{ old('descricao') }}</textarea>
                    @error('descricao')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-warning">
                    <strong>Atenção:</strong> este reajuste irá atualizar o
                    <strong>valor_aluguel_atual</strong> do contrato. As próximas parcelas geradas
                    devem utilizar esse novo valor.
                </div>

                <button type="submit"
                        class="btn btn-primary"
                        id="btnAplicarReajuste">
                    Aplicar Reajuste
                </button>
                <a href="{{ route('contratos.show', $contrato->id) }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form       = document.getElementById('formReajuste');
    const campoPerc  = document.getElementById('percentual_aplicado');
    const preview    = document.getElementById('valor_novo_preview');
    const btnSubmit  = document.getElementById('btnAplicarReajuste');

    if (!form || !campoPerc || !preview) {
        return;
    }

    const valorAtual = parseFloat(form.getAttribute('data-valor-atual')) || 0;

    function formatarReais(valor) {
        return valor.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function atualizarPreview() {
        const perc = parseFloat(campoPerc.value) || 0;
        let valorNovo = valorAtual * (1 + perc / 100);
        valorNovo = Math.max(0, Math.round(valorNovo * 100) / 100);
        preview.value = 'R$ ' + formatarReais(valorNovo);
    }

    campoPerc.addEventListener('input', atualizarPreview);
    atualizarPreview();

    if (btnSubmit) {
        form.addEventListener('submit', function () {
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Aplicando...';
        });
    }
});
</script>
@endpush
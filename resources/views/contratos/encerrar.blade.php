@extends('layouts.app')

@section('title', 'Encerrar / Rescindir Contrato')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Encerrar / Rescindir Contrato</h1>
        <a href="{{ route('contratos.show', $contrato->id) }}" class="btn btn-secondary">
            Voltar ao Contrato
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Dados do contrato
        </div>
        <div class="card-body">
            <p><strong>Código:</strong> {{ $contrato->codigo }}</p>
            <p><strong>Imóvel:</strong> {{ $contrato->imovel->descricao ?? '-' }}</p>
            <p><strong>Locatário:</strong> {{ $contrato->locatario->nome ?? '-' }}</p>
            <p>
                <strong>Valor aluguel atual:</strong>
                R$ {{ number_format($valorAluguelAtual, 2, ',', '.') }}
            </p>
            <p><strong>Status atual:</strong> {{ $contrato->status }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Encerramento / Rescisão
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

            <form action="{{ route('contratos.encerrar.store', $contrato->id) }}"
                  method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Tipo de Encerramento *</label>
                    <select name="tipo_encerramento"
                            id="tipo_encerramento"
                            class="form-select"
                            required>
                        <option value="">Selecione</option>
                        <option value="ENCERRAMENTO_NORMAL" {{ old('tipo_encerramento') === 'ENCERRAMENTO_NORMAL' ? 'selected' : '' }}>
                            Encerramento normal (sem multa)
                        </option>
                        <option value="RESCISAO_ANTECIPADA" {{ old('tipo_encerramento') === 'RESCISAO_ANTECIPADA' ? 'selected' : '' }}>
                            Rescisão antecipada (com multa opcional)
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="data_encerramento" class="form-label">Data de Encerramento *</label>
                    <input type="date"
                           name="data_encerramento"
                           id="data_encerramento"
                           class="form-control"
                           value="{{ old('data_encerramento', $dataSugestao->format('Y-m-d')) }}"
                           required>
                </div>

                <div class="mb-3" id="campoMultaContainer">
                    <label class="form-label">
                        Multa de Rescisão (meses de aluguel)
                    </label>
                    <select name="multa_meses"
                            id="multa_meses"
                            class="form-select">
                        <option value="0" {{ old('multa_meses', 0) == 0 ? 'selected' : '' }}>
                            Não cobrar multa
                        </option>
                        <option value="1" {{ old('multa_meses') == 1 ? 'selected' : '' }}>
                            1 mês de aluguel
                        </option>
                        <option value="2" {{ old('multa_meses') == 2 ? 'selected' : '' }}>
                            2 meses de aluguel
                        </option>
                        <option value="3" {{ old('multa_meses') == 3 ? 'selected' : '' }}>
                            3 meses de aluguel
                        </option>
                    </select>
                    <small class="text-muted">
                        Em rescisão antecipada, você pode definir entre 0 e 3 meses de aluguel como multa.
                    </small>
                </div>

                <div class="mb-3">
                    <label for="motivo" class="form-label">Motivo (opcional)</label>
                    <textarea name="motivo"
                              id="motivo"
                              class="form-control"
                              rows="3">{{ old('motivo') }}</textarea>
                    <small class="text-muted">
                        Ex: acordo amigável, inadimplência, venda do imóvel, etc.
                    </small>
                </div>

                <div class="alert alert-warning">
                    <strong>Atenção:</strong> após encerrar ou rescindir, o contrato não será mais considerado
                    como ATIVO. As parcelas abertas permanecem para cobrança, mas nenhuma nova parcela de aluguel
                    será gerada.
                </div>

                <button type="submit" class="btn btn-danger">
                    Confirmar Encerramento / Rescisão
                </button>
                <a href="{{ route('contratos.show', $contrato->id) }}"
                   class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
@endsection
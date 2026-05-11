@extends('layouts.app')

@section('title', 'Devolução da Caução')

@section('content')
    <h1 class="mb-3">
        Devolução da Caução – Contrato {{ $contrato->codigo }}
    </h1>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            Resumo da Caução
        </div>
        <div class="card-body">
            <p>
                <strong>Imóvel:</strong>
                {{ $contrato->imovel->descricao ?? 'N/A' }}
            </p>
            <p>
                <strong>Locatário:</strong>
                {{ $contrato->locatario->nome ?? 'N/A' }}
            </p>
            <p>
                <strong>Valor da Caução:</strong>
                R$ {{ number_format($contrato->valor_caucao ?? 0, 2, ',', '.') }}
            </p>
            <p>
                <strong>Data quitação total da caução:</strong>
                {{ $contrato->data_pagamento_total_caucao
                    ? $contrato->data_pagamento_total_caucao->format('d/m/Y')
                    : 'Não informada' }}
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Registrar Devolução
        </div>
        <div class="card-body">
            <form action="{{ route('contratos.caucao.devolucao.store', $contrato->id) }}"
                  method="POST">
                @csrf

                <div class="mb-3">
                    <label for="data_devolucao_caucao" class="form-label">
                        Data da Devolução da Caução
                    </label>
                    <input type="date"
                           name="data_devolucao_caucao"
                           id="data_devolucao_caucao"
                           class="form-control @error('data_devolucao_caucao') is-invalid @enderror"
                           value="{{ old('data_devolucao_caucao', now()->format('Y-m-d')) }}"
                           required>
                    @error('data_devolucao_caucao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        Deve ser igual ou posterior à data em que a caução foi totalmente quitada.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Situação da devolução
                    </label>
                    <select name="foi_devolvida"
                            id="foi_devolvida"
                            class="form-select @error('foi_devolvida') is-invalid @enderror"
                            required>
                        <option value="">Selecione</option>
                        <option value="SIM" {{ old('foi_devolvida') === 'SIM' ? 'selected' : '' }}>
                            Devolvida integralmente ao locatário
                        </option>
                        <option value="PARCIAL" {{ old('foi_devolvida') === 'PARCIAL' ? 'selected' : '' }}>
                            Devolvida parcialmente (parte usada para abatimentos)
                        </option>
                        <option value="NAO" {{ old('foi_devolvida') === 'NAO' ? 'selected' : '' }}>
                            Não devolvida (totalmente usada para abatimentos/pendências)
                        </option>
                    </select>
                    @error('foi_devolvida')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="motivo_nao_devolucao_caucao" class="form-label">
                        Motivo da não devolução / devolução parcial
                    </label>
                    <textarea name="motivo_nao_devolucao_caucao"
                              id="motivo_nao_devolucao_caucao"
                              rows="3"
                              class="form-control @error('motivo_nao_devolucao_caucao') is-invalid @enderror">{{ old('motivo_nao_devolucao_caucao') }}</textarea>
                    @error('motivo_nao_devolucao_caucao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        Preencha principalmente quando houver devolução parcial ou não devolvida.
                    </small>
                </div>

                <div class="mb-3">
                    <label for="descricao_abatimento" class="form-label">
                        Descrição de abatimentos / observações internas
                    </label>
                    <textarea name="descricao_abatimento"
                              id="descricao_abatimento"
                              rows="3"
                              class="form-control @error('descricao_abatimento') is-invalid @enderror">{{ old('descricao_abatimento') }}</textarea>
                    @error('descricao_abatimento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        Campo apenas informativo. Se parte da caução foi usada para abater parcelas,
                        descreva aqui quais e quanto, mas o lançamento financeiro continua sendo feito
                        manualmente nas parcelas.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    Salvar Devolução da Caução
                </button>
                <a href="{{ route('contratos.show', $contrato->id) }}"
                   class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
@endsection
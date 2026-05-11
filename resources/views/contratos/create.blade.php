@extends('layouts.app')

@section('title', 'Novo Contrato')

@section('content')
    <h1 class="mb-4">Novo Contrato</h1>

    <form action="{{ route('contratos.store') }}" method="POST">
        @csrf

        {{-- DADOS PRINCIPAIS --}}
        <div class="card mb-4">
            <div class="card-header">
                Dados principais
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Imóvel --}}
                    <div class="col-md-4 mb-3">
                        <label for="imovel_id" class="form-label">Imóvel</label>
                        <select name="imovel_id" id="imovel_id"
                                class="form-select @error('imovel_id') is-invalid @enderror" required>
                            <option value="">Selecione um imóvel</option>
                            @foreach($imoveis as $imovel)
                                <option value="{{ $imovel->id }}"
                                    {{ old('imovel_id') == $imovel->id ? 'selected' : '' }}>
                                    {{ $imovel->descricao }} ({{ $imovel->cidade ?? '' }}/{{ $imovel->estado ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('imovel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Locatário --}}
                    <div class="col-md-4 mb-3">
                        <label for="locatario_id" class="form-label">Locatário</label>
                        <select name="locatario_id" id="locatario_id"
                                class="form-select @error('locatario_id') is-invalid @enderror" required>
                            <option value="">Selecione um locatário</option>
                            @foreach($locatarios as $locatario)
                                <option value="{{ $locatario->id }}"
                                    {{ old('locatario_id') == $locatario->id ? 'selected' : '' }}>
                                    {{ $locatario->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('locatario_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Proprietário --}}
                    <div class="col-md-4 mb-3">
                        <label for="proprietario_id" class="form-label">Proprietário</label>
                        <select name="proprietario_id" id="proprietario_id"
                                class="form-select @error('proprietario_id') is-invalid @enderror" required>
                            <option value="">Selecione um proprietário</option>
                            @foreach($proprietarios as $proprietario)
                                <option value="{{ $proprietario->id }}"
                                    {{ old('proprietario_id') == $proprietario->id ? 'selected' : '' }}>
                                    {{ $proprietario->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('proprietario_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Tipo de contrato --}}
                    <div class="col-md-4 mb-3">
                        <label for="tipo_contrato" class="form-label">Tipo de Contrato</label>
                        <select name="tipo_contrato" id="tipo_contrato"
                                class="form-select @error('tipo_contrato') is-invalid @enderror" required>
                            <option value="">Selecione</option>
                            @foreach($tiposContrato as $key => $label)
                                <option value="{{ $key }}"
                                    {{ old('tipo_contrato', 'RESIDENCIAL') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_contrato')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status"
                                class="form-select @error('status') is-invalid @enderror" required>
                            <option value="">Selecione</option>
                            @foreach($statusContrato as $key => $label)
                                <option value="{{ $key }}"
                                    {{ old('status', 'ATIVO') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Data início --}}
                    <div class="col-md-4 mb-3">
                        <label for="data_inicio" class="form-label">Data de Início</label>
                        <input type="date" name="data_inicio" id="data_inicio"
                               class="form-control @error('data_inicio') is-invalid @enderror"
                               value="{{ old('data_inicio') }}" required>
                        @error('data_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Data fim prevista --}}
                    <div class="col-md-4 mb-3">
                        <label for="data_fim_prevista" class="form-label">Data Fim Prevista</label>
                        <input type="date" name="data_fim_prevista" id="data_fim_prevista"
                               class="form-control @error('data_fim_prevista') is-invalid @enderror"
                               value="{{ old('data_fim_prevista') }}">
                        @error('data_fim_prevista')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Observações --}}
                    <div class="col-md-8 mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea name="observacoes" id="observacoes"
                                  class="form-control @error('observacoes') is-invalid @enderror"
                                  rows="2">{{ old('observacoes') }}</textarea>
                        @error('observacoes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- BLOCO ALUGUEL NORMAL (RESIDENCIAL/COMERCIAL) --}}
        <div class="card mb-4" id="bloco-aluguel-normal">
            <div class="card-header">
                Dados de Aluguel (Residencial/Comercial)
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Valor aluguel base --}}
                    <div class="col-md-4 mb-3">
                        <label for="valor_aluguel_base" class="form-label">Valor Aluguel Base</label>
                        <input type="number" step="0.01" name="valor_aluguel_base" id="valor_aluguel_base"
                               class="form-control @error('valor_aluguel_base') is-invalid @enderror"
                               value="{{ old('valor_aluguel_base') }}" min="0">
                        @error('valor_aluguel_base')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Valor aluguel atual --}}
                    <div class="col-md-4 mb-3">
                        <label for="valor_aluguel_atual" class="form-label">Valor Aluguel Atual</label>
                        <input type="number" step="0.01" name="valor_aluguel_atual" id="valor_aluguel_atual"
                               class="form-control @error('valor_aluguel_atual') is-invalid @enderror"
                               value="{{ old('valor_aluguel_atual') }}" min="0">
                        @error('valor_aluguel_atual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Caso deixe vazio, o sistema pode usar o valor base como atual.
                        </small>
                    </div>

                    {{-- Dia vencimento --}}
                    <div class="col-md-4 mb-3">
                        <label for="dia_vencimento" class="form-label">Dia de Vencimento</label>
                        <input type="number" name="dia_vencimento" id="dia_vencimento"
                               class="form-control @error('dia_vencimento') is-invalid @enderror"
                               value="{{ old('dia_vencimento') }}" min="1" max="31">
                        @error('dia_vencimento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Índice reajuste --}}
                    <div class="col-md-4 mb-3">
                        <label for="indice_reajuste" class="form-label">Índice de Reajuste</label>
                        <select name="indice_reajuste" id="indice_reajuste"
                                class="form-select @error('indice_reajuste') is-invalid @enderror">
                            <option value="">Selecione</option>
                            @foreach($indicesReajuste as $key => $label)
                                <option value="{{ $key }}"
                                    {{ old('indice_reajuste', 'IPCA') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('indice_reajuste')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mês do reajuste --}}
                    <div class="col-md-4 mb-3">
                        <label for="mes_reajuste" class="form-label">Mês do Reajuste</label>
                        <input type="number" name="mes_reajuste" id="mes_reajuste"
                               class="form-control @error('mes_reajuste') is-invalid @enderror"
                               value="{{ old('mes_reajuste') }}" min="1" max="12">
                        @error('mes_reajuste')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Percentual padrão --}}
                    <div class="col-md-4 mb-3">
                        <label for="percentual_reajuste_padrao" class="form-label">% Reajuste Padrão</label>
                        <input type="number" step="0.01" name="percentual_reajuste_padrao" id="percentual_reajuste_padrao"
                               class="form-control @error('percentual_reajuste_padrao') is-invalid @enderror"
                               value="{{ old('percentual_reajuste_padrao') }}" min="0" max="100">
                        @error('percentual_reajuste_padrao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- CAUÇÃO --}}
        <div class="card mb-4">
            <div class="card-header">
                Caução
            </div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox"
                           name="possui_caucao" id="possui_caucao" value="1"
                           {{ old('possui_caucao') ? 'checked' : '' }}>
                    <label class="form-check-label" for="possui_caucao">
                        Este contrato possui caução
                    </label>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="meses_caucao" class="form-label">Meses de Caução</label>
                        <input type="number" name="meses_caucao" id="meses_caucao"
                               class="form-control @error('meses_caucao') is-invalid @enderror"
                               value="{{ old('meses_caucao') }}" min="0" max="3">
                        @error('meses_caucao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="valor_caucao" class="form-label">Valor Caução</label>
                        <input type="number" step="0.01" name="valor_caucao" id="valor_caucao"
                               class="form-control @error('valor_caucao') is-invalid @enderror"
                               value="{{ old('valor_caucao') }}" min="0">
                        @error('valor_caucao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Normalmente meses × valor do aluguel base, mas você pode ajustar.
                        </small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox"
                                   name="caucao_paga_integralmente" id="caucao_paga_integralmente" value="1"
                                   {{ old('caucao_paga_integralmente') ? 'checked' : '' }}>
                            <label class="form-check-label" for="caucao_paga_integralmente">
                                Caução já está totalmente paga
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MULTA E JUROS --}}
        <div class="card mb-4">
            <div class="card-header">
                Multa e Juros
            </div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="possui_multa_atraso"
                           id="possui_multa_atraso" value="1"
                           {{ old('possui_multa_atraso') ? 'checked' : '' }}>
                    <label class="form-check-label" for="possui_multa_atraso">
                        Possui multa por atraso
                    </label>
                </div>

                <div class="mb-3">
                    <label for="percentual_multa" class="form-label">% Multa</label>
                    <input type="number" step="0.01" name="percentual_multa" id="percentual_multa"
                           class="form-control @error('percentual_multa') is-invalid @enderror"
                           value="{{ old('percentual_multa') }}" min="0" max="100">
                    @error('percentual_multa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="possui_juros_moratorios"
                           id="possui_juros_moratorios" value="1"
                           {{ old('possui_juros_moratorios') ? 'checked' : '' }}>
                    <label class="form-check-label" for="possui_juros_moratorios">
                        Possui juros moratórios
                    </label>
                </div>

                <div class="mb-3">
                    <label for="percentual_juros_mensal" class="form-label">% Juros Mensal</label>
                    <input type="number" step="0.01" name="percentual_juros_mensal" id="percentual_juros_mensal"
                           class="form-control @error('percentual_juros_mensal') is-invalid @enderror"
                           value="{{ old('percentual_juros_mensal') }}" min="0" max="100">
                    @error('percentual_juros_mensal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="carencia_dias" class="form-label">Carência (dias)</label>
                    <input type="number" name="carencia_dias" id="carencia_dias"
                           class="form-control @error('carencia_dias') is-invalid @enderror"
                           value="{{ old('carencia_dias') }}" min="0">
                    @error('carencia_dias')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- BLOCO TEMPORADA --}}
        <div class="card mb-4 d-none" id="bloco-temporada">
            <div class="card-header">
                Dados de Temporada
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Para contratos de <strong>temporada</strong>, será gerada uma única parcela com origem
                    <strong>TEMPORADA</strong>, vencendo 30 dias antes da data de entrada. Você pode registrar
                    pagamentos parciais nessa parcela.
                </p>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="data_entrada_prevista" class="form-label">Data Entrada Prevista</label>
                        <input type="date" name="data_entrada_prevista" id="data_entrada_prevista"
                               class="form-control @error('data_entrada_prevista') is-invalid @enderror"
                               value="{{ old('data_entrada_prevista') }}">
                        @error('data_entrada_prevista')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hora_entrada" class="form-label">Hora Entrada (opcional)</label>
                        <input type="time" name="hora_entrada" id="hora_entrada"
                               class="form-control @error('hora_entrada') is-invalid @enderror"
                               value="{{ old('hora_entrada') }}">
                        @error('hora_entrada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="data_saida_prevista" class="form-label">Data Saída Prevista</label>
                        <input type="date" name="data_saida_prevista" id="data_saida_prevista"
                               class="form-control @error('data_saida_prevista') is-invalid @enderror"
                               value="{{ old('data_saida_prevista') }}">
                        @error('data_saida_prevista')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hora_saida" class="form-label">Hora Saída (opcional)</label>
                        <input type="time" name="hora_saida" id="hora_saida"
                               class="form-control @error('hora_saida') is-invalid @enderror"
                               value="{{ old('hora_saida') }}">
                        @error('hora_saida')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="numero_hospedes" class="form-label">Número de Hóspedes</label>
                        <input type="number" name="numero_hospedes" id="numero_hospedes"
                               class="form-control @error('numero_hospedes') is-invalid @enderror"
                               value="{{ old('numero_hospedes') }}" min="1">
                        @error('numero_hospedes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="valor_total_temporada" class="form-label">Valor Total da Temporada</label>
                        <input type="number" step="0.01" name="valor_total_temporada" id="valor_total_temporada"
                               class="form-control @error('valor_total_temporada') is-invalid @enderror"
                               value="{{ old('valor_total_temporada') }}" min="0">
                        @error('valor_total_temporada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="numero_parcelas_temporada" class="form-label">Qtde de Parcelas (informativo)</label>
                        <input type="number" name="numero_parcelas_temporada" id="numero_parcelas_temporada"
                               class="form-control @error('numero_parcelas_temporada') is-invalid @enderror"
                               value="{{ old('numero_parcelas_temporada') }}" min="1">
                        @error('numero_parcelas_temporada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Nesta fase, os pagamentos serão registrados como parciais em uma única parcela TEMPORADA.
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="prazo_maximo_pagamento_dias" class="form-label">
                        Prazo Máximo de Pagamento (dias antes da entrada)
                    </label>
                    <input type="number" name="prazo_maximo_pagamento_dias" id="prazo_maximo_pagamento_dias"
                           class="form-control @error('prazo_maximo_pagamento_dias') is-invalid @enderror"
                           value="{{ old('prazo_maximo_pagamento_dias', 30) }}" min="0">
                    @error('prazo_maximo_pagamento_dias')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        Informativo para controle/alertas. Não altera automaticamente as parcelas.
                    </small>
                </div>

                <div class="mb-3">
                    <label for="regras_especiais" class="form-label">Regras Especiais</label>
                    <textarea name="regras_especiais" id="regras_especiais"
                              class="form-control @error('regras_especiais') is-invalid @enderror"
                              rows="3">{{ old('regras_especiais') }}</textarea>
                    @error('regras_especiais')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="restricoes" class="form-label">Restrições</label>
                    <textarea name="restricoes" id="restricoes"
                              class="form-control @error('restricoes') is-invalid @enderror"
                              rows="3">{{ old('restricoes') }}</textarea>
                    @error('restricoes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Salvar Contrato</button>
        <a href="{{ route('contratos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect     = document.getElementById('tipo_contrato');
        const blocoAluguel   = document.getElementById('bloco-aluguel-normal');
        const blocoTemporada = document.getElementById('bloco-temporada');

        function atualizarVisibilidade() {
            const tipo = tipoSelect.value;

            if (tipo === 'TEMPORADA') {
                blocoAluguel?.classList.add('d-none');
                blocoTemporada?.classList.remove('d-none');
            } else {
                blocoAluguel?.classList.remove('d-none');
                blocoTemporada?.classList.add('d-none');
            }
        }

        if (tipoSelect) {
            tipoSelect.addEventListener('change', atualizarVisibilidade);
            atualizarVisibilidade();
        }
    });
</script>
@endpush
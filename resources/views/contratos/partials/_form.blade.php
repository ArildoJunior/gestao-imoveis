<div class="row">
    {{-- Tipo contrato agora já inclui TEMPORADA via $tiposContrato --}}
    <div class="col-md-3 mb-3">
        <label for="tipo_contrato" class="form-label">Tipo de Contrato *</label>
        <select name="tipo_contrato" id="tipo_contrato" class="form-select" required>
            @php $tipoAtual = old('tipo_contrato', $contrato->tipo_contrato ?? 'RESIDENCIAL'); @endphp
            @foreach($tiposContrato as $key => $label)
                <option value="{{ $key }}" {{ $tipoAtual === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('tipo_contrato') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="imovel_id" class="form-label">Imóvel *</label>
        <select name="imovel_id" id="imovel_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($imoveis as $imovel)
                <option value="{{ $imovel->id }}"
                    {{ old('imovel_id', $contrato->imovel_id ?? '') == $imovel->id ? 'selected' : '' }}>
                    {{ $imovel->descricao ?? $imovel->logradouro }}
                </option>
            @endforeach
        </select>
        @error('imovel_id') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="locatario_id" class="form-label">Locatário *</label>
        <select name="locatario_id" id="locatario_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($locatarios as $locatario)
                <option value="{{ $locatario->id }}"
                    {{ old('locatario_id', $contrato->locatario_id ?? '') == $locatario->id ? 'selected' : '' }}>
                    {{ $locatario->nome }}
                </option>
            @endforeach
        </select>
        @error('locatario_id') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="proprietario_id" class="form-label">Proprietário *</label>
        <select name="proprietario_id" id="proprietario_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($proprietarios as $proprietario)
                <option value="{{ $proprietario->id }}"
                    {{ old('proprietario_id', $contrato->proprietario_id ?? '') == $proprietario->id ? 'selected' : '' }}>
                    {{ $proprietario->nome }}
                </option>
            @endforeach
        </select>
        @error('proprietario_id') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="data_inicio" class="form-label">Data Início *</label>
        <input type="date" name="data_inicio" id="data_inicio" class="form-control" required
               value="{{ old('data_inicio', isset($contrato->data_inicio) ? $contrato->data_inicio->format('Y-m-d') : '') }}">
        @error('data_inicio') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="data_fim_prevista" class="form-label">Data Fim Prevista</label>
        <input type="date" name="data_fim_prevista" id="data_fim_prevista" class="form-control"
               value="{{ old('data_fim_prevista', isset($contrato->data_fim_prevista) ? $contrato->data_fim_prevista->format('Y-m-d') : '') }}">
        @error('data_fim_prevista') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="valor_aluguel_base" class="form-label">Aluguel Base *</label>
        <input type="number" step="0.01" name="valor_aluguel_base" id="valor_aluguel_base" class="form-control" required
               value="{{ old('valor_aluguel_base', $contrato->valor_aluguel_base ?? '') }}">
        @error('valor_aluguel_base') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="valor_aluguel_atual" class="form-label">Aluguel Atual *</label>
        <input type="number" step="0.01" name="valor_aluguel_atual" id="valor_aluguel_atual" class="form-control" required
               value="{{ old('valor_aluguel_atual', $contrato->valor_aluguel_atual ?? '') }}">
        @error('valor_aluguel_atual') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label for="dia_vencimento" class="form-label">Dia Vencimento *</label>
        <input type="number" name="dia_vencimento" id="dia_vencimento" class="form-control" min="1" max="31" required
               value="{{ old('dia_vencimento', $contrato->dia_vencimento ?? 5) }}">
        @error('dia_vencimento') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="indice_reajuste" class="form-label">Índice Reajuste *</label>
        <select name="indice_reajuste" id="indice_reajuste" class="form-select" required>
            @php $indiceAtual = old('indice_reajuste', $contrato->indice_reajuste ?? 'SEM_REAJUSTE'); @endphp
            @foreach($indicesReajuste as $key => $label)
                <option value="{{ $key }}" {{ $indiceAtual === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('indice_reajuste') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label for="mes_reajuste" class="form-label">Mês Reajuste</label>
        <input type="number" name="mes_reajuste" id="mes_reajuste" class="form-control" min="1" max="12"
               value="{{ old('mes_reajuste', $contrato->mes_reajuste ?? '') }}">
        @error('mes_reajuste') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="percentual_reajuste_padrao" class="form-label">% Reajuste Padrão</label>
        <input type="number" step="0.01" name="percentual_reajuste_padrao" id="percentual_reajuste_padrao" class="form-control"
               value="{{ old('percentual_reajuste_padrao', $contrato->percentual_reajuste_padrao ?? '') }}">
        @error('percentual_reajuste_padrao') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="status" class="form-label">Status *</label>
        <select name="status" id="status" class="form-select" required>
            @php $statusAtual = old('status', $contrato->status ?? 'ATIVO'); @endphp
            @foreach($statusContrato as $key => $label)
                <option value="{{ $key }}" {{ $statusAtual === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <hr class="mt-4 mb-3">

    {{-- Dados de Temporada (somente serão usados se tipo_contrato = TEMPORADA) --}}
    <div class="col-12 mb-2">
        <h5>Dados de Temporada</h5>
        <p class="text-muted">
            Usados quando o tipo de contrato for TEMPORADA. Nesta versão, será gerada uma única parcela TEMPORADA,
            com vencimento 30 dias antes da data de entrada, permitindo pagamentos parciais.
        </p>
    </div>

    <div class="col-md-3 mb-3">
        <label for="data_entrada_prevista" class="form-label">Data Entrada Prevista</label>
        <input type="date" name="data_entrada_prevista" id="data_entrada_prevista" class="form-control"
               value="{{ old('data_entrada_prevista', isset($contrato->data_entrada_prevista) ? $contrato->data_entrada_prevista->format('Y-m-d') : '') }}">
        @error('data_entrada_prevista') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="hora_entrada" class="form-label">Hora Entrada</label>
        <input type="time" name="hora_entrada" id="hora_entrada" class="form-control"
               value="{{ old('hora_entrada', $contrato->hora_entrada ?? '') }}">
        @error('hora_entrada') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="data_saida_prevista" class="form-label">Data Saída Prevista</label>
        <input type="date" name="data_saida_prevista" id="data_saida_prevista" class="form-control"
               value="{{ old('data_saida_prevista', isset($contrato->data_saida_prevista) ? $contrato->data_saida_prevista->format('Y-m-d') : '') }}">
        @error('data_saida_prevista') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="hora_saida" class="form-label">Hora Saída</label>
        <input type="time" name="hora_saida" id="hora_saida" class="form-control"
               value="{{ old('hora_saida', $contrato->hora_saida ?? '') }}">
        @error('hora_saida') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="numero_hospedes" class="form-label">Número de Hóspedes</label>
        <input type="number" name="numero_hospedes" id="numero_hospedes" class="form-control"
               value="{{ old('numero_hospedes', $contrato->numero_hospedes ?? '') }}" min="1">
        @error('numero_hospedes') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="valor_total_temporada" class="form-label">Valor Total da Temporada</label>
        <input type="number" step="0.01" name="valor_total_temporada" id="valor_total_temporada" class="form-control"
               value="{{ old('valor_total_temporada', $contrato->valor_total_temporada ?? '') }}" min="0">
        @error('valor_total_temporada') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="numero_parcelas_temporada" class="form-label">Quantidade de Parcelas (informativo)</label>
        <input type="number" name="numero_parcelas_temporada" id="numero_parcelas_temporada" class="form-control"
               value="{{ old('numero_parcelas_temporada', $contrato->numero_parcelas_temporada ?? '') }}" min="1">
        @error('numero_parcelas_temporada') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="prazo_maximo_pagamento_dias" class="form-label">Prazo Máximo Pagamento (dias antes entrada)</label>
        <input type="number" name="prazo_maximo_pagamento_dias" id="prazo_maximo_pagamento_dias" class="form-control"
               value="{{ old('prazo_maximo_pagamento_dias', $contrato->prazo_maximo_pagamento_dias ?? 30) }}" min="0">
        @error('prazo_maximo_pagamento_dias') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="regras_especiais" class="form-label">Regras Especiais</label>
        <textarea name="regras_especiais" id="regras_especiais" rows="3" class="form-control">{{ old('regras_especiais', $contrato->regras_especiais ?? '') }}</textarea>
        @error('regras_especiais') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="restricoes" class="form-label">Restrições</label>
        <textarea name="restricoes" id="restricoes" rows="3" class="form-control">{{ old('restricoes', $contrato->restricoes ?? '') }}</textarea>
        @error('restricoes') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <hr class="mt-4 mb-3">

    {{-- Caução --}}
    <div class="col-12 mb-2">
        <h5>Caução</h5>
    </div>

    <div class="col-md-2 mb-3 form-check" style="margin-top: 32px;">
        <input type="hidden" name="possui_caucao" value="0">
        <input class="form-check-input" type="checkbox" name="possui_caucao" id="possui_caucao" value="1"
               {{ old('possui_caucao', $contrato->possui_caucao ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="possui_caucao">Possui Caução</label>
    </div>

    <div class="col-md-2 mb-3">
        <label for="meses_caucao" class="form-label">Meses Caução</label>
        <input type="number" name="meses_caucao" id="meses_caucao" class="form-control" min="0" max="3"
               value="{{ old('meses_caucao', $contrato->meses_caucao ?? 0) }}">
        @error('meses_caucao') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="valor_caucao" class="form-label">Valor Caução</label>
        <input type="number" step="0.01" name="valor_caucao" id="valor_caucao" class="form-control"
               value="{{ old('valor_caucao', $contrato->valor_caucao ?? 0) }}">
        @error('valor_caucao') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3 form-check" style="margin-top: 32px;">
        <input type="hidden" name="caucao_paga_integralmente" value="0">
        <input class="form-check-input" type="checkbox" name="caucao_paga_integralmente" id="caucao_paga_integralmente" value="1"
               {{ old('caucao_paga_integralmente', $contrato->caucao_paga_integralmente ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="caucao_paga_integralmente">Caução Paga</label>
    </div>

    <div class="col-md-3 mb-3">
        <label for="data_pagamento_total_caucao" class="form-label">Data Pagamento Caução</label>
        <input type="date" name="data_pagamento_total_caucao" id="data_pagamento_total_caucao" class="form-control"
               value="{{ old('data_pagamento_total_caucao', isset($contrato->data_pagamento_total_caucao) ? $contrato->data_pagamento_total_caucao->format('Y-m-d') : '') }}">
        @error('data_pagamento_total_caucao') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3 form-check" style="margin-top: 32px;">
        <input type="hidden" name="caucao_devolvida" value="0">
        <input class="form-check-input" type="checkbox" name="caucao_devolvida" id="caucao_devolvida" value="1"
               {{ old('caucao_devolvida', $contrato->caucao_devolvida ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="caucao_devolvida">Caução Devolvida</label>
    </div>

    <div class="col-md-3 mb-3">
        <label for="data_devolucao_caucao" class="form-label">Data Devolução Caução</label>
        <input type="date" name="data_devolucao_caucao" id="data_devolucao_caucao" class="form-control"
               value="{{ old('data_devolucao_caucao', isset($contrato->data_devolucao_caucao) ? $contrato->data_devolucao_caucao->format('Y-m-d') : '') }}">
        @error('data_devolucao_caucao') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-9 mb-3">
        <label for="motivo_nao_devolucao_caucao" class="form-label">Motivo não devolução da caução</label>
        <textarea name="motivo_nao_devolucao_caucao" id="motivo_nao_devolucao_caucao" rows="3" class="form-control">{{ old('motivo_nao_devolucao_caucao', $contrato->motivo_nao_devolucao_caucao ?? '') }}</textarea>
        @error('motivo_nao_devolucao_caucao') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <hr class="mt-4 mb-3">

    <div class="col-12 mb-2">
        <h5>Multa e Juros</h5>
    </div>

    <div class="col-md-2 mb-3 form-check" style="margin-top: 32px;">
        <input type="hidden" name="possui_multa_atraso" value="0">
        <input class="form-check-input" type="checkbox" name="possui_multa_atraso" id="possui_multa_atraso" value="1"
               {{ old('possui_multa_atraso', $contrato->possui_multa_atraso ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="possui_multa_atraso">Multa Atraso</label>
    </div>

    <div class="col-md-3 mb-3">
        <label for="percentual_multa" class="form-label">% Multa</label>
        <input type="number" step="0.01" name="percentual_multa" id="percentual_multa" class="form-control"
               value="{{ old('percentual_multa', $contrato->percentual_multa ?? 0) }}">
        @error('percentual_multa') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3 form-check" style="margin-top: 32px;">
        <input type="hidden" name="possui_juros_moratorios" value="0">
        <input class="form-check-input" type="checkbox" name="possui_juros_moratorios" id="possui_juros_moratorios" value="1"
               {{ old('possui_juros_moratorios', $contrato->possui_juros_moratorios ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="possui_juros_moratorios">Juros Moratórios</label>
    </div>

    <div class="col-md-3 mb-3">
        <label for="percentual_juros_mensal" class="form-label">% Juros Mensal</label>
        <input type="number" step="0.01" name="percentual_juros_mensal" id="percentual_juros_mensal" class="form-control"
               value="{{ old('percentual_juros_mensal', $contrato->percentual_juros_mensal ?? 0) }}">
        @error('percentual_juros_mensal') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label for="carencia_dias" class="form-label">Carência (dias)</label>
        <input type="number" name="carencia_dias" id="carencia_dias" class="form-control" min="0"
               value="{{ old('carencia_dias', $contrato->carencia_dias ?? 0) }}">
        @error('carencia_dias') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
</div>
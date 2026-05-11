<div class="row">
    <div class="col-md-6 mb-3">
        <label for="descricao" class="form-label">Descrição (Nome do Imóvel)</label>
        <input type="text" name="descricao" id="descricao"
               class="form-control @error('descricao') is-invalid @enderror"
               value="{{ old('descricao', $imovel->descricao ?? '') }}"
               placeholder="Ex: Apartamento 101, Casa na Praia">
        @error('descricao')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="proprietario_id" class="form-label">Proprietário *</label>
        <select name="proprietario_id" id="proprietario_id"
                class="form-select @error('proprietario_id') is-invalid @enderror" required>
            <option value="">Selecione um proprietário</option>
            @foreach($proprietarios as $proprietario)
                <option value="{{ $proprietario->id }}"
                        {{ old('proprietario_id', $imovel->proprietario_id ?? '') == $proprietario->id ? 'selected' : '' }}>
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
    <div class="col-md-6 mb-3">
        <label for="logradouro" class="form-label">Logradouro *</label>
        <input type="text" name="logradouro" id="logradouro"
               class="form-control @error('logradouro') is-invalid @enderror"
               value="{{ old('logradouro', $imovel->logradouro ?? '') }}" required>
        @error('logradouro')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label for="numero" class="form-label">Número *</label>
        <input type="text" name="numero" id="numero"
               class="form-control @error('numero') is-invalid @enderror"
               value="{{ old('numero', $imovel->numero ?? '') }}" required>
        @error('numero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="complemento" class="form-label">Complemento</label>
        <input type="text" name="complemento" id="complemento"
               class="form-control @error('complemento') is-invalid @enderror"
               value="{{ old('complemento', $imovel->complemento ?? '') }}">
        @error('complemento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="bairro" class="form-label">Bairro *</label>
        <input type="text" name="bairro" id="bairro"
               class="form-control @error('bairro') is-invalid @enderror"
               value="{{ old('bairro', $imovel->bairro ?? '') }}" required>
        @error('bairro')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="cidade" class="form-label">Cidade *</label>
        <input type="text" name="cidade" id="cidade"
               class="form-control @error('cidade') is-invalid @enderror"
               value="{{ old('cidade', $imovel->cidade ?? '') }}" required>
        @error('cidade')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label for="estado" class="form-label">Estado *</label>
        <input type="text" name="estado" id="estado" maxlength="2"
               class="form-control @error('estado') is-invalid @enderror"
               value="{{ old('estado', $imovel->estado ?? '') }}" required>
        @error('estado')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label for="cep" class="form-label">CEP *</label>
        <input type="text" name="cep" id="cep" maxlength="9"
               class="form-control @error('cep') is-invalid @enderror"
               value="{{ old('cep', $imovel->cep ?? '') }}" required>
        @error('cep')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="tipo_imovel" class="form-label">Tipo de Imóvel *</label>
        <select name="tipo_imovel" id="tipo_imovel"
                class="form-select @error('tipo_imovel') is-invalid @enderror" required>
            <option value="">Selecione</option>
            @foreach($tiposImovel as $key => $label)
                <option value="{{ $key }}"
                        {{ old('tipo_imovel', $imovel->tipo_imovel ?? '') == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('tipo_imovel')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="area_m2" class="form-label">Área (m²)</label>
        <input type="number" step="0.01" name="area_m2" id="area_m2"
               class="form-control @error('area_m2') is-invalid @enderror"
               value="{{ old('area_m2', $imovel->area_m2 ?? '') }}" min="0">
        @error('area_m2')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="matricula" class="form-label">Matrícula</label>
        <input type="text" name="matricula" id="matricula"
               class="form-control @error('matricula') is-invalid @enderror"
               value="{{ old('matricula', $imovel->matricula ?? '') }}">
        @error('matricula')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="inscricao_iptu" class="form-label">Inscrição IPTU</label>
        <input type="text" name="inscricao_iptu" id="inscricao_iptu"
               class="form-control @error('inscricao_iptu') is-invalid @enderror"
               value="{{ old('inscricao_iptu', $imovel->inscricao_iptu ?? '') }}">
        @error('inscricao_iptu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="valor_iptu_anual" class="form-label">Valor IPTU Anual</label>
        <input type="number" step="0.01" name="valor_iptu_anual" id="valor_iptu_anual"
               class="form-control @error('valor_iptu_anual') is-invalid @enderror"
               value="{{ old('valor_iptu_anual', $imovel->valor_iptu_anual ?? '') }}" min="0">
        @error('valor_iptu_anual')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3 form-check d-flex align-items-end">
        <div>
            <input type="hidden" name="ativo" value="0"> {{-- Hidden field para garantir que o valor 0 seja enviado se o checkbox não for marcado --}}
            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" value="1"
                   {{ old('ativo', $imovel->ativo ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="ativo">
                Imóvel Ativo
            </label>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4 mb-3 form-check">
        <input type="hidden" name="possui_condominio" value="0">
        <input class="form-check-input" type="checkbox" name="possui_condominio" id="possui_condominio" value="1"
               {{ old('possui_condominio', $imovel->possui_condominio ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="possui_condominio">
            Possui Condomínio
        </label>
    </div>

    <div class="col-md-4 mb-3">
        <label for="valor_condominio_mensal" class="form-label">Valor Condomínio Mensal</label>
        <input type="number" step="0.01" name="valor_condominio_mensal" id="valor_condominio_mensal"
               class="form-control @error('valor_condominio_mensal') is-invalid @enderror"
               value="{{ old('valor_condominio_mensal', $imovel->valor_condominio_mensal ?? '') }}" min="0">
        @error('valor_condominio_mensal')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3 form-check">
        <input type="hidden" name="possui_agua_incluida" value="0">
        <input class="form-check-input" type="checkbox" name="possui_agua_incluida" id="possui_agua_incluida" value="1"
               {{ old('possui_agua_incluida', $imovel->possui_agua_incluida ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="possui_agua_incluida">
            Água Incluída no Condomínio
        </label>
    </div>

    <div class="col-md-4 mb-3 form-check">
        <input type="hidden" name="possui_luz_incluida" value="0">
        <input class="form-check-input" type="checkbox" name="possui_luz_incluida" id="possui_luz_incluida" value="1"
               {{ old('possui_luz_incluida', $imovel->possui_luz_incluida ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="possui_luz_incluida">
            Luz Incluída no Condomínio
        </label>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="imovel_id" class="form-label">Imóvel *</label>
        <select name="imovel_id" id="imovel_id" class="form-select" required>
            <option value="">Selecione um imóvel</option>
            @foreach($imoveis as $imovel)
                <option value="{{ $imovel->id }}" {{ old('imovel_id', $despesa_imovel->imovel_id ?? '') == $imovel->id ? 'selected' : '' }}>
                    {{ $imovel->descricao }} ({{ $imovel->logradouro }}, {{ $imovel->numero }})
                </option>
            @endforeach
        </select>
        @error('imovel_id') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="contrato_id" class="form-label">Contrato (Opcional)</label>
        <select name="contrato_id" id="contrato_id" class="form-select">
            <option value="">Nenhum contrato</option>
            @foreach($contratos as $contrato)
                <option value="{{ $contrato->id }}" {{ old('contrato_id', $despesa_imovel->contrato_id ?? '') == $contrato->id ? 'selected' : '' }}>
                    {{ $contrato->codigo }} ({{ $contrato->locatario->nome ?? 'N/A' }})
                </option>
            @endforeach
        </select>
        @error('contrato_id') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="registrado_por_user_id" class="form-label">Registrado Por *</label>
        <select name="registrado_por_user_id" id="registrado_por_user_id" class="form-select" required>
            <option value="">Selecione um usuário</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('registrado_por_user_id', $despesa_imovel->registrado_por_user_id ?? (Auth::check() ? Auth::id() : '')) == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
        @error('registrado_por_user_id') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="data_despesa" class="form-label">Data da Despesa *</label>
        <input type="date" name="data_despesa" id="data_despesa" class="form-control" required
               value="{{ old('data_despesa', isset($despesa_imovel->data_despesa) ? $despesa_imovel->data_despesa->format('Y-m-d') : '') }}">
        @error('data_despesa') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="tipo_despesa" class="form-label">Tipo de Despesa *</label>
        <select name="tipo_despesa" id="tipo_despesa" class="form-select" required>
            <option value="">Selecione o tipo</option>
            @foreach($tiposDespesa as $key => $label)
                <option value="{{ $key }}" {{ old('tipo_despesa', $despesa_imovel->tipo_despesa ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('tipo_despesa') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label for="descricao" class="form-label">Descrição (Opcional)</label>
        <textarea name="descricao" id="descricao" class="form-control" rows="3">{{ old('descricao', $despesa_imovel->descricao ?? '') }}</textarea>
        @error('descricao') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="valor" class="form-label">Valor *</label>
        <input type="number" step="0.01" name="valor" id="valor" class="form-control" required
               value="{{ old('valor', $despesa_imovel->valor ?? '') }}">
        @error('valor') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="responsavel" class="form-label">Responsável *</label>
        <select name="responsavel" id="responsavel" class="form-select" required>
            <option value="">Selecione o responsável</option>
            @foreach($responsaveis as $key => $label)
                <option value="{{ $key }}" {{ old('responsavel', $despesa_imovel->responsavel ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('responsavel') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Status *</label>
        <select name="status" id="status" class="form-select" required>
            <option value="">Selecione o status</option>
            @foreach($statusDespesa as $key => $label)
                <option value="{{ $key }}" {{ old('status', $despesa_imovel->status ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="fornecedor" class="form-label">Fornecedor (Opcional)</label>
        <input type="text" name="fornecedor" id="fornecedor" class="form-control"
               value="{{ old('fornecedor', $despesa_imovel->fornecedor ?? '') }}">
        @error('fornecedor') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="numero_nota_fiscal" class="form-label">Número da Nota Fiscal (Opcional)</label>
        <input type="text" name="numero_nota_fiscal" id="numero_nota_fiscal" class="form-control"
               value="{{ old('numero_nota_fiscal', $despesa_imovel->numero_nota_fiscal ?? '') }}">
        @error('numero_nota_fiscal') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="data_reembolso" class="form-label">Data de Reembolso (Opcional)</label>
        <input type="date" name="data_reembolso" id="data_reembolso" class="form-control"
               value="{{ old('data_reembolso', isset($despesa_imovel->data_reembolso) ? $despesa_imovel->data_reembolso->format('Y-m-d') : '') }}">
        @error('data_reembolso') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="valor_reembolso" class="form-label">Valor de Reembolso (Opcional)</label>
        <input type="number" step="0.01" name="valor_reembolso" id="valor_reembolso" class="form-control"
               value="{{ old('valor_reembolso', $despesa_imovel->valor_reembolso ?? '') }}">
        @error('valor_reembolso') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
</div>
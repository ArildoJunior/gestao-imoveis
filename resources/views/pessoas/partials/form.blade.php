<div class="row">
    <div class="col-md-6 mb-3">
        <label for="nome" class="form-label">Nome *</label>
        <input type="text" name="nome" id="nome" class="form-control" required
               value="{{ old('nome', $pessoa->nome ?? '') }}">
        @error('nome') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
        <input type="text" name="cpf_cnpj" id="cpf_cnpj" class="form-control"
               value="{{ old('cpf_cnpj', $pessoa->cpf_cnpj ?? '') }}">
        @error('cpf_cnpj') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="rg" class="form-label">RG</label>
        <input type="text" name="rg" id="rg" class="form-control"
               value="{{ old('rg', $pessoa->rg ?? '') }}">
        @error('rg') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" name="email" id="email" class="form-control"
               value="{{ old('email', $pessoa->email ?? '') }}">
        @error('email') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="telefone" class="form-label">Telefone</label>
        <input type="text" name="telefone" id="telefone" class="form-control"
               value="{{ old('telefone', $pessoa->telefone ?? '') }}">
        @error('telefone') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="celular" class="form-label">Celular</label>
        <input type="text" name="celular" id="celular" class="form-control"
               value="{{ old('celular', $pessoa->celular ?? '') }}">
        @error('celular') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8 mb-3">
        <label for="logradouro" class="form-label">Logradouro</label>
        <input type="text" name="logradouro" id="logradouro" class="form-control"
               value="{{ old('logradouro', $pessoa->logradouro ?? '') }}">
        @error('logradouro') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label for="numero" class="form-label">Número</label>
        <input type="text" name="numero" id="numero" class="form-control"
               value="{{ old('numero', $pessoa->numero ?? '') }}">
        @error('numero') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label for="complemento" class="form-label">Complemento</label>
        <input type="text" name="complemento" id="complemento" class="form-control"
               value="{{ old('complemento', $pessoa->complemento ?? '') }}">
        @error('complemento') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="bairro" class="form-label">Bairro</label>
        <input type="text" name="bairro" id="bairro" class="form-control"
               value="{{ old('bairro', $pessoa->bairro ?? '') }}">
        @error('bairro') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="cidade" class="form-label">Cidade</label>
        <input type="text" name="cidade" id="cidade" class="form-control"
               value="{{ old('cidade', $pessoa->cidade ?? '') }}">
        @error('cidade') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label for="estado" class="form-label">UF</label>
        <input type="text" name="estado" id="estado" class="form-control"
               value="{{ old('estado', $pessoa->estado ?? '') }}">
        @error('estado') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label for="cep" class="form-label">CEP</label>
        <input type="text" name="cep" id="cep" class="form-control"
               value="{{ old('cep', $pessoa->cep ?? '') }}">
        @error('cep') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="tipo" class="form-label">Tipo de Pessoa *</label>
        <select name="tipo" id="tipo" class="form-select" required>
            @php
                $tipoAtual = old('tipo', $pessoa->tipo ?? 'PROPRIETARIO');
            @endphp
            <option value="PROPRIETARIO" {{ $tipoAtual === 'PROPRIETARIO' ? 'selected' : '' }}>Proprietário</option>
            <option value="LOCATARIO" {{ $tipoAtual === 'LOCATARIO' ? 'selected' : '' }}>Locatário</option>
            <option value="AMBOS" {{ $tipoAtual === 'AMBOS' ? 'selected' : '' }}>Ambos</option>
        </select>
        @error('tipo') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2 mb-3 form-check" style="margin-top: 32px;">
        {{-- Campo hidden para garantir que 'ativo' sempre seja enviado (0 se não marcado, 1 se marcado) --}}
        <input type="hidden" name="ativo" value="0">
        <input class="form-check-input" type="checkbox" name="ativo" id="ativo" value="1"
               {{ old('ativo', $pessoa->ativo ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="ativo">
            Ativo
        </label>
    </div>
</div>
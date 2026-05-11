@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
    <h1>Editar Usuário: {{ $user->name }}</h1>

    <form method="POST" action="{{ route('users.update', $user->id) }}" class="mt-3">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nome de Usuário</label>
            <input type="text"
                   class="form-control @error('name') is-invalid @enderror"
                   id="name"
                   name="name"
                   value="{{ old('name', $user->name) }}"
                   required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email de Login</label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   id="email"
                   name="email"
                   value="{{ old('email', $user->email) }}"
                   required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="perfil" class="form-label">Perfil</label>
            <select class="form-select @error('perfil') is-invalid @enderror"
                    id="perfil"
                    name="perfil"
                    required>
                <option value="">Selecione um perfil</option>
                @foreach ($perfis as $p)
                    <option value="{{ $p }}" {{ old('perfil', $user->perfil) == $p ? 'selected' : '' }}>
                        {{ $p }}
                    </option>
                @endforeach
            </select>
            @error('perfil')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select @error('status') is-invalid @enderror"
                    id="status"
                    name="status"
                    required>
                <option value="ATIVO" {{ old('status', $user->status) == 'ATIVO' ? 'selected' : '' }}>ATIVO</option>
                <option value="INATIVO" {{ old('status', $user->status) == 'INATIVO' ? 'selected' : '' }}>INATIVO</option>
                <option value="PENDENTE" {{ old('status', $user->status) == 'PENDENTE' ? 'selected' : '' }}>PENDENTE</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nova Senha (deixe em branco para não alterar)</label>
            <input type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   id="password"
                   name="password"
                   autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
            <input type="password"
                   class="form-control"
                   id="password_confirmation"
                   name="password_confirmation"
                   autocomplete="new-password">
        </div>

        <hr class="my-4">

        <h3>Vincular a uma Pessoa</h3>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="vincular_pessoa_checkbox" name="vincular_pessoa" value="1" {{ old('vincular_pessoa', $user->pessoa ? 1 : 0) ? 'checked' : '' }}>
            <label class="form-check-label" for="vincular_pessoa_checkbox">Vincular a uma pessoa existente?</label>
        </div>

        <div id="pessoa_existente_fields"> {{-- Removido style inline --}}
            <div class="mb-3">
                <label for="pessoa_existente_id" class="form-label">Pessoa Existente</label>
                <select class="form-select @error('pessoa_existente_id') is-invalid @enderror"
                        id="pessoa_existente_id"
                        name="pessoa_existente_id">
                    <option value="">Selecione uma pessoa</option>
                    @foreach ($pessoasDisponiveis as $pessoa)
                        <option value="{{ $pessoa->id }}" {{ old('pessoa_existente_id', $user->pessoa?->id) == $pessoa->id ? 'selected' : '' }}>
                            {{ $pessoa->nome }} ({{ $pessoa->cpf_cnpj }})
                        </option>
                    @endforeach
                </select>
                @error('pessoa_existente_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div id="nova_pessoa_fields"> {{-- Removido style inline --}}
            <p class="text-muted">Preencha os dados abaixo para criar uma nova pessoa e vinculá-la a este usuário.</p>
            <div class="mb-3">
                <label for="pessoa_nome" class="form-label">Nome da Pessoa</label>
                <input type="text"
                       class="form-control @error('pessoa_nome') is-invalid @enderror"
                       id="pessoa_nome"
                       name="pessoa_nome"
                       value="{{ old('pessoa_nome', $user->pessoa?->nome) }}">
                @error('pessoa_nome')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="pessoa_cpf_cnpj" class="form-label">CPF/CNPJ da Pessoa</label>
                <input type="text"
                       class="form-control @error('pessoa_cpf_cnpj') is-invalid @enderror"
                       id="pessoa_cpf_cnpj"
                       name="pessoa_cpf_cnpj"
                       value="{{ old('pessoa_cpf_cnpj', $user->pessoa?->cpf_cnpj) }}">
                @error('pessoa_cpf_cnpj')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="pessoa_email" class="form-label">Email da Pessoa (opcional)</label>
                <input type="email"
                       class="form-control @error('pessoa_email') is-invalid @enderror"
                       id="pessoa_email"
                       name="pessoa_email"
                       value="{{ old('pessoa_email', $user->pessoa?->email) }}">
                @error('pessoa_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="pessoa_telefone" class="form-label">Telefone da Pessoa (opcional)</label>
                <input type="text"
                       class="form-control @error('pessoa_telefone') is-invalid @enderror"
                       id="pessoa_telefone"
                       name="pessoa_telefone"
                       value="{{ old('pessoa_telefone', $user->pessoa?->telefone) }}">
                @error('pessoa_telefone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="pessoa_celular" class="form-label">Celular da Pessoa (opcional)</label>
                <input type="text"
                       class="form-control @error('pessoa_celular') is-invalid @enderror"
                       id="pessoa_celular"
                       name="pessoa_celular"
                       value="{{ old('pessoa_celular', $user->pessoa?->celular) }}">
                @error('pessoa_celular')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Atualizar Usuário</button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const vincularPessoaCheckbox = document.getElementById('vincular_pessoa_checkbox');
            const pessoaExistenteFields = document.getElementById('pessoa_existente_fields');
            const novaPessoaFields = document.getElementById('nova_pessoa_fields');

            function togglePessoaFields() {
                if (vincularPessoaCheckbox.checked) {
                    pessoaExistenteFields.style.display = 'block';
                    novaPessoaFields.style.display = 'none';
                    // Define required para os campos visíveis
                    document.getElementById('pessoa_existente_id').setAttribute('required', 'required');
                    document.getElementById('pessoa_nome').removeAttribute('required');
                    document.getElementById('pessoa_cpf_cnpj').removeAttribute('required');
                } else {
                    pessoaExistenteFields.style.display = 'none';
                    novaPessoaFields.style.display = 'block';
                    // Define required para os campos visíveis
                    document.getElementById('pessoa_existente_id').removeAttribute('required');
                    document.getElementById('pessoa_nome').setAttribute('required', 'required');
                    document.getElementById('pessoa_cpf_cnpj').setAttribute('required', 'required');
                }
            }

            // --- NOVO: Define o estado inicial com base no valor 'old' ou no estado atual do usuário ---
            // Passa o valor 'old' ou o estado do usuário como string e converte para booleano no JS
            const oldVincularPessoaValue = "{{ old('vincular_pessoa', $user->pessoa ? 1 : 0) }}";
            vincularPessoaCheckbox.checked = (oldVincularPessoaValue === "1");
            // Garante que o estado inicial esteja correto ao carregar a página
            togglePessoaFields();
            // --- FIM NOVO ---

            vincularPessoaCheckbox.addEventListener('change', togglePessoaFields);
        });
    </script>
@endsection
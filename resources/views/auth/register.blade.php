<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nome de Usuário -->
        <div>
            <x-input-label for="name" :value="__('Nome de Usuário')" />
            <x-text-input id="name" class="block mt-1 w-full"
                          type="text"
                          name="name"
                          :value="old('name')"
                          required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email de Login -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email de Login')" />
            <x-text-input id="email" class="block mt-1 w-full"
                          type="email"
                          name="email"
                          :value="old('email')"
                          required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Perfil desejado -->
        <div class="mt-4">
            <x-input-label for="perfil" :value="__('Perfil desejado')" />
            <select id="perfil"
                    name="perfil"
                    class="form-select block mt-1 w-full @error('perfil') is-invalid @enderror"
                    required>
                <option value="">Selecione um perfil</option>
                @foreach ($perfis as $p)
                    <option value="{{ $p }}" {{ old('perfil') == $p ? 'selected' : '' }}>
                        {{ $p }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('perfil')" class="mt-2" />
        </div>

        <hr class="my-4">

        <!-- Dados da Pessoa -->
        <h3 class="text-lg font-medium text-gray-900">Seus Dados Pessoais</h3>

        <div class="mt-4">
            <x-input-label for="pessoa_nome" :value="__('Nome Completo ou Razão Social')" />
            <x-text-input id="pessoa_nome" class="block mt-1 w-full"
                          type="text"
                          name="pessoa_nome"
                          :value="old('pessoa_nome')"
                          required autocomplete="name" />
            <x-input-error :messages="$errors->get('pessoa_nome')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="pessoa_cpf_cnpj" :value="__('CPF ou CNPJ')" />
            <x-text-input id="pessoa_cpf_cnpj" class="block mt-1 w-full"
                          type="text"
                          name="pessoa_cpf_cnpj"
                          :value="old('pessoa_cpf_cnpj')"
                          required />
            <x-input-error :messages="$errors->get('pessoa_cpf_cnpj')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="pessoa_email" :value="__('Email para Contato (Pessoa)')" />
            <x-text-input id="pessoa_email" class="block mt-1 w-full"
                          type="email"
                          name="pessoa_email"
                          :value="old('pessoa_email')"
                          autocomplete="email" />
            <x-input-error :messages="$errors->get('pessoa_email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="pessoa_telefone" :value="__('Telefone (Pessoa) - opcional')" />
            <x-text-input id="pessoa_telefone" class="block mt-1 w-full"
                          type="text"
                          name="pessoa_telefone"
                          :value="old('pessoa_telefone')" />
            <x-input-error :messages="$errors->get('pessoa_telefone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="pessoa_celular" :value="__('Celular (Pessoa) - opcional')" />
            <x-text-input id="pessoa_celular" class="block mt-1 w-full"
                          type="text"
                          name="pessoa_celular"
                          :value="old('pessoa_celular')" />
            <x-input-error :messages="$errors->get('pessoa_celular')" class="mt-2" />
        </div>

        <hr class="my-4">

        <!-- Senha -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar Senha -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Senha')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                          type="password"
                          name="password_confirmation"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md"
               href="{{ route('login') }}">
                {{ __('Já tem cadastro?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Registrar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
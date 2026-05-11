<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">Gestão Imobiliária</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('pessoas.*') ? 'active' : '' }}" href="{{ route('pessoas.index') }}">Pessoas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('imoveis.*') ? 'active' : '' }}" href="{{ route('imoveis.index') }}">Imóveis</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('contratos.*') ? 'active' : '' }}" href="{{ route('contratos.index') }}">Contratos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('despesas-imovel.*') ? 'active' : '' }}" href="{{ route('despesas-imovel.index') }}">Despesas Imóvel</a>
                </li>
                {{-- Adicione outros itens de menu aqui conforme for criando --}}
            </ul>
        </div>
    </div>
</nav>
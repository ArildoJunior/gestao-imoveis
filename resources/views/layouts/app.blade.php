<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Imóveis — @yield('title', 'Início')</title>

    {{-- Bootstrap CSS --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .nav-link.active {
            font-weight: 600;
        }

        .badge-perfil {
            font-size: 0.7rem;
            vertical-align: middle;
        }

        main.container {
            padding-top: 1.5rem;
            padding-bottom: 3rem;
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ============================================================ --}}
{{-- NAVBAR PRINCIPAL                                             --}}
{{-- ============================================================ --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid px-4">

        {{-- Logo / Nome do sistema --}}
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="bi bi-buildings-fill me-1"></i> Gestão Imóveis
        </a>

        {{-- Botão hambúrguer (mobile) --}}
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false"
                aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">

            {{-- ---- MENU ESQUERDO ---- --}}
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth

                    {{-- Dashboard --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>

                    {{-- Pessoas --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pessoas.*') ? 'active' : '' }}"
                           href="{{ route('pessoas.index') }}">
                            <i class="bi bi-people me-1"></i> Pessoas
                        </a>
                    </li>

                    {{-- Imóveis --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('imoveis.*') ? 'active' : '' }}"
                           href="{{ route('imoveis.index') }}">
                            <i class="bi bi-house-door me-1"></i> Imóveis
                        </a>
                    </li>

                    {{-- Contratos --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contratos.*') ? 'active' : '' }}"
                           href="{{ route('contratos.index') }}">
                            <i class="bi bi-file-earmark-text me-1"></i> Contratos
                        </a>
                    </li>

                    {{-- Financeiro --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('financeiro.*') ? 'active' : '' }}"
                           href="{{ route('financeiro.index') }}">
                            <i class="bi bi-currency-dollar me-1"></i> Financeiro
                        </a>
                    </li>

                    {{-- Alertas --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('alertas.*') ? 'active' : '' }}"
                           href="{{ route('alertas.index') }}">
                            <i class="bi bi-bell me-1"></i> Alertas
                        </a>
                    </li>

                    {{-- Mais (dropdown) --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle
                            {{ request()->routeIs('despesas-imovel.*', 'relatorios.*', 'renegociacoes.*', 'acoes-judiciais.*') ? 'active' : '' }}"
                           href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid me-1"></i> Mais
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">

                            {{-- Despesas --}}
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('despesas-imovel.*') ? 'active' : '' }}"
                                   href="{{ route('despesas-imovel.index') }}">
                                    <i class="bi bi-receipt me-1"></i> Despesas
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            {{-- Submenu Relatórios --}}
                            <li>
                                <span class="dropdown-item-text text-secondary small px-3">
                                    <i class="bi bi-bar-chart-line me-1"></i> Relatórios
                                </span>
                            </li>
                            <li>
                                <a class="dropdown-item ps-4 {{ request()->routeIs('relatorios.imovel') ? 'active' : '' }}"
                                   href="{{ route('relatorios.imovel') }}">
                                    <i class="bi bi-house-gear me-1"></i> Rel. Financeiro por Imóvel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item ps-4 {{ request()->routeIs('relatorios.inadimplencia') ? 'active' : '' }}"
                                   href="{{ route('relatorios.inadimplencia') }}">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Rel. Inadimplência
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            {{-- Ações Judiciais --}}
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('acoes-judiciais.*') ? 'active' : '' }}"
                                   href="{{ route('acoes-judiciais.index') }}">
                                    <i class="bi bi-bank me-1"></i> Ações Judiciais
                                </a>
                            </li>

                        </ul>
                    </li>

                    {{-- ---- MENU ADMIN (somente ADMIN vê) ---- --}}
                    @if(Auth::user()->isAdministrador())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle
                                {{ request()->routeIs('users.*', 'backups.*') ? 'active' : '' }}"
                               href="#" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-shield-lock me-1"></i> Admin
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}"
                                       href="{{ route('users.index') }}">
                                        <i class="bi bi-person-gear me-1"></i> Usuários
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('backups.*') ? 'active' : '' }}"
                                       href="{{ route('backups.index') }}">
                                        <i class="bi bi-cloud-arrow-down me-1"></i> Backups
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                @endauth
            </ul>

            {{-- ---- MENU DIREITO (usuário logado) ---- --}}
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            {{ Auth::user()->name }}
                            <span class="badge bg-secondary badge-perfil ms-1">
                                {{ Auth::user()->perfil }}
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-pencil-square me-1"></i> Meu Perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-1"></i> Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth

                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                        </a>
                    </li>
                @endguest
            </ul>

        </div>{{-- /collapse --}}
    </div>{{-- /container-fluid --}}
</nav>

{{-- ============================================================ --}}
{{-- CONTEÚDO PRINCIPAL                                           --}}
{{-- ============================================================ --}}
<main class="container">

    {{-- Mensagem de sucesso --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    {{-- Mensagem de erro --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    {{-- Erros de validação --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Corrija os erros abaixo:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @yield('content')

</main>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Scripts adicionais das views --}}
@stack('scripts')

</body>
</html>
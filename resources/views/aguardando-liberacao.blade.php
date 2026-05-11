@extends('layouts.app')

@section('title', 'Aguardando Liberação')

@section('content')
    <div class="text-center py-5">
        <h1 class="display-4">Aguardando Liberação</h1>
        <p class="lead mt-4">
            Seu cadastro foi concluído, porém ainda está pendente de aprovação pelo administrador.
            Assim que o seu perfil for liberado, você terá acesso total ao sistema.
        </p>
        <p class="mt-3">
            Caso precise de mais informações, entre em contato com o suporte ou com o responsável
            pela sua conta.
        </p>
        <a href="{{ route('logout') }}" class="btn btn-outline-secondary mt-4">Sair</a>
    </div>
@endsection
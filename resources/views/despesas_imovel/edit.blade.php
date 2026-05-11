@extends('layouts.app')

@section('title', 'Editar Despesa de Imóvel')

@section('content')
    <h1>Editar Despesa de Imóvel</h1>

    <form action="{{ route('despesas-imovel.update', $despesa_imovel->id) }}" method="POST" class="mt-3">
        @csrf
        @method('PUT')
        @include('despesas_imovel.partials._form', ['despesa_imovel' => $despesa_imovel, 'imoveis' => $imoveis, 'contratos' => $contratos, 'users' => $users, 'tiposDespesa' => $tiposDespesa, 'responsaveis' => $responsaveis, 'statusDespesa' => $statusDespesa])
        <button type="submit" class="btn btn-primary mt-3">Atualizar Despesa</button>
        <a href="{{ route('despesas-imovel.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
@endsection
@extends('layouts.app')

@section('title', 'Nova Despesa de Imóvel')

@section('content')
    <h1>Nova Despesa de Imóvel</h1>

    <form action="{{ route('despesas-imovel.store') }}" method="POST" class="mt-3">
        @csrf
        @include('despesas_imovel.partials._form', ['despesa_imovel' => new \App\Models\DespesaImovel(), 'imoveis' => $imoveis, 'contratos' => $contratos, 'users' => $users, 'tiposDespesa' => $tiposDespesa, 'responsaveis' => $responsaveis, 'statusDespesa' => $statusDespesa])
        <button type="submit" class="btn btn-primary mt-3">Salvar Despesa</button>
        <a href="{{ route('despesas-imovel.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
@endsection
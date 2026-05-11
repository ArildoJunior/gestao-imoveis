@extends('layouts.app')

@section('title', 'Editar Imóvel')

@section('content')
    <h1 class="mb-4">Editar Imóvel: {{ $imovel->descricao ?? $imovel->logradouro }}</h1>

    <form action="{{ route('imoveis.update', $imovel->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('imoveis.partials._form', [
            'imovel' => $imovel,
            'proprietarios' => $proprietarios,
            'tiposImovel' => $tiposImovel
        ])

        <button type="submit" class="btn btn-primary mt-3">Atualizar Imóvel</button>
        <a href="{{ route('imoveis.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
@endsection
@extends('layouts.app')

@section('title', 'Cadastrar Imóvel')

@section('content')
    <h1 class="mb-4">Cadastrar Imóvel</h1>

    <form action="{{ route('imoveis.store') }}" method="POST">
        @csrf

        @include('imoveis.partials._form', [
            'imovel' => new \App\Models\Imovel(), // Passa uma nova instância para o partial
            'proprietarios' => $proprietarios,
            'tiposImovel' => $tiposImovel
        ])

        <button type="submit" class="btn btn-primary mt-3">Salvar Imóvel</button>
        <a href="{{ route('imoveis.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
@endsection
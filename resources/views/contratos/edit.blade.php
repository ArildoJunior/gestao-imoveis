@extends('layouts.app')

@section('title', 'Editar Contrato')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Editar Contrato: {{ $contrato->codigo }}</h1>
        <a href="{{ route('contratos.show', $contrato->id) }}" class="btn btn-info">Ver Detalhes / Parcelas</a>
    </div>

    <form action="{{ route('contratos.update', $contrato->id) }}" method="POST" class="mt-3">
        @csrf
        @method('PUT')

        @include('contratos.partials._form', [
            'contrato' => $contrato,
            'imoveis' => $imoveis,
            'locatarios' => $locatarios,
            'proprietarios' => $proprietarios,
            'tiposContrato' => $tiposContrato,
            'indicesReajuste' => $indicesReajuste,
            'statusContrato' => $statusContrato
        ])

        <button type="submit" class="btn btn-primary mt-3">Atualizar Contrato</button>
        <a href="{{ route('contratos.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
@endsection
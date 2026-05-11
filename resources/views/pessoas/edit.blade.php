@extends('layouts.app')

@section('title', 'Editar Pessoa')

@section('content')
    <h1>Editar Pessoa</h1>

    <form action="{{ route('pessoas.update', $pessoa->id) }}" method="POST" class="mt-3">
        @csrf
        @method('PUT')

        @include('pessoas.partials.form', ['pessoa' => $pessoa])

        <button type="submit" class="btn btn-primary mt-3">Atualizar</button>
        <a href="{{ route('pessoas.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
@endsection
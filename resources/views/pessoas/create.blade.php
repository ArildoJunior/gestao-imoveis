@extends('layouts.app')

@section('title', 'Nova Pessoa')

@section('content')
    <h1>Nova Pessoa</h1>

    <form action="{{ route('pessoas.store') }}" method="POST" class="mt-3" id="pessoaForm">
        @csrf

        @include('pessoas.partials.form', ['pessoa' => null])

        <button type="submit" class="btn btn-primary mt-3">Salvar</button>
        <a href="{{ route('pessoas.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
@endsection
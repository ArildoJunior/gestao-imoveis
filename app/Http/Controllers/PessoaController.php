<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use Illuminate\Http\Request;

class PessoaController extends Controller
{
    /**
     * Lista todas as pessoas com paginação simples.
     */
    public function index()
    {
        $pessoas = Pessoa::orderBy('nome')->paginate(10);

        return view('pessoas.index', compact('pessoas'));
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        return view('pessoas.create');
    }

    /**
     * Salvar nova pessoa.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'       => 'required|string|max:255',
            'cpf_cnpj'   => 'nullable|string|max:20',
            'rg'         => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'telefone'   => 'nullable|string|max:20',
            'celular'    => 'nullable|string|max:20',
            'logradouro' => 'nullable|string|max:255',
            'numero'     => 'nullable|string|max:20',
            'complemento'=> 'nullable|string|max:255',
            'bairro'     => 'nullable|string|max:255',
            'cidade'     => 'nullable|string|max:255',
            'estado'     => 'nullable|string|max:2',
            'cep'        => 'nullable|string|max:9',
            'tipo'       => 'required|in:PROPRIETARIO,LOCATARIO,AMBOS',
            'ativo'      => 'nullable|boolean',
        ]);

        $data['ativo'] = $request->has('ativo');

        Pessoa::create($data);

        return redirect()->route('pessoas.index')
            ->with('success', 'Pessoa criada com sucesso.');
    }

    /**
     * Formulário de edição.
     */
    public function edit(Pessoa $pessoa)
    {
        return view('pessoas.edit', compact('pessoa'));
    }

    /**
     * Atualizar pessoa.
     */
    public function update(Request $request, Pessoa $pessoa)
    {
        $data = $request->validate([
            'nome'       => 'required|string|max:255',
            'cpf_cnpj'   => 'nullable|string|max:20',
            'rg'         => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'telefone'   => 'nullable|string|max:20',
            'celular'    => 'nullable|string|max:20',
            'logradouro' => 'nullable|string|max:255',
            'numero'     => 'nullable|string|max:20',
            'complemento'=> 'nullable|string|max:255',
            'bairro'     => 'nullable|string|max:255',
            'cidade'     => 'nullable|string|max:255',
            'estado'     => 'nullable|string|max:2',
            'cep'        => 'nullable|string|max:9',
            'tipo'       => 'required|in:PROPRIETARIO,LOCATARIO,AMBOS',
            'ativo'      => 'nullable|boolean',
        ]);

        $data['ativo'] = $request->has('ativo');

        $pessoa->update($data);

        return redirect()->route('pessoas.index')
            ->with('success', 'Pessoa atualizada com sucesso.');
    }

    /**
     * Remover pessoa (soft delete por enquanto via model).
     */
    public function destroy(Pessoa $pessoa)
    {
        $pessoa->delete();

        return redirect()->route('pessoas.index')
            ->with('success', 'Pessoa removida com sucesso.');
    }
}
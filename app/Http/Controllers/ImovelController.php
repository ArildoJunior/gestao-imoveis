<?php

namespace App\Http\Controllers;

use App\Models\Imovel;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ImovelController extends Controller
{
    /**
     * Exibe uma lista de imóveis.
     */
    public function index()
    {
        $imoveis = Imovel::with('proprietario')->orderBy('descricao')->paginate(10);
        return view('imoveis.index', compact('imoveis'));
    }

    /**
     * Mostra o formulário para criar um novo imóvel.
     */
    public function create()
    {
        $proprietarios = Pessoa::whereIn('tipo', ['PROPRIETARIO', 'AMBOS'])->orderBy('nome')->get();
        $tiposImovel = [
            'CASA' => 'Casa',
            'APARTAMENTO' => 'Apartamento',
            'SITIO' => 'Sítio',
            'LOJA' => 'Loja',
            'SALA_COMERCIAL' => 'Sala Comercial',
            'GALPAO' => 'Galpão',
            'TERRENO' => 'Terreno',
            'OUTRO' => 'Outro',
        ];
        return view('imoveis.create', compact('proprietarios', 'tiposImovel'));
    }

    /**
     * Armazena um imóvel recém-criado no banco de dados.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'descricao' => 'nullable|string|max:255',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'cep' => 'required|string|max:9',
            'tipo_imovel' => ['required', Rule::in(['CASA', 'APARTAMENTO', 'SITIO', 'LOJA', 'SALA_COMERCIAL', 'GALPAO', 'TERRENO', 'OUTRO'])],
            'area_m2' => 'nullable|numeric|min:0',
            'matricula' => 'nullable|string|max:255',
            'inscricao_iptu' => 'nullable|string|max:255',
            'valor_iptu_anual' => 'nullable|numeric|min:0', // Mantém nullable na validação
            'possui_condominio' => 'boolean',
            'valor_condominio_mensal' => 'nullable|numeric|min:0', // Mantém nullable na validação
            'possui_agua_incluida' => 'boolean',
            'possui_luz_incluida' => 'boolean',
            'ativo' => 'boolean',
            'proprietario_id' => 'required|exists:pessoas,id',
        ]);

        // Trata os checkboxes para garantir que sejam booleanos (0 ou 1)
        $data['possui_condominio'] = $request->has('possui_condominio');
        $data['possui_agua_incluida'] = $request->has('possui_agua_incluida');
        $data['possui_luz_incluida'] = $request->has('possui_luz_incluida');
        $data['ativo'] = $request->has('ativo');

        // Garante que campos numéricos com default 0 no banco não sejam NULL
        // Se vierem vazios do formulário, serão tratados como 0.
        $data['valor_iptu_anual'] = $data['valor_iptu_anual'] ?? 0;
        $data['valor_condominio_mensal'] = $data['valor_condominio_mensal'] ?? 0;

        Imovel::create($data);

        return redirect()->route('imoveis.index')
            ->with('success', 'Imóvel criado com sucesso.');
    }

    /**
     * Exibe os detalhes de um imóvel específico.
     */
    public function show(Imovel $imovel)
    {
        return view('imoveis.show', compact('imovel'));
    }

    /**
     * Mostra o formulário para editar um imóvel existente.
     */
    public function edit(Imovel $imovel)
    {
        $proprietarios = Pessoa::whereIn('tipo', ['PROPRIETARIO', 'AMBOS'])->orderBy('nome')->get();
        $tiposImovel = [
            'CASA' => 'Casa',
            'APARTAMENTO' => 'Apartamento',
            'SITIO' => 'Sítio',
            'LOJA' => 'Loja',
            'SALA_COMERCIAL' => 'Sala Comercial',
            'GALPAO' => 'Galpão',
            'TERRENO' => 'Terreno',
            'OUTRO' => 'Outro',
        ];
        return view('imoveis.edit', compact('imovel', 'proprietarios', 'tiposImovel'));
    }

    /**
     * Atualiza um imóvel existente no banco de dados.
     */
    public function update(Request $request, Imovel $imovel)
    {
        $data = $request->validate([
            'descricao' => 'nullable|string|max:255',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'cep' => 'required|string|max:9',
            'tipo_imovel' => ['required', Rule::in(['CASA', 'APARTAMENTO', 'SITIO', 'LOJA', 'SALA_COMERCIAL', 'GALPAO', 'TERRENO', 'OUTRO'])],
            'area_m2' => 'nullable|numeric|min:0',
            'matricula' => 'nullable|string|max:255',
            'inscricao_iptu' => 'nullable|string|max:255',
            'valor_iptu_anual' => 'nullable|numeric|min:0', // Mantém nullable na validação
            'possui_condominio' => 'boolean',
            'valor_condominio_mensal' => 'nullable|numeric|min:0', // Mantém nullable na validação
            'possui_agua_incluida' => 'boolean',
            'possui_luz_incluida' => 'boolean',
            'ativo' => 'boolean',
            'proprietario_id' => 'required|exists:pessoas,id',
        ]);

        // Trata os checkboxes para garantir que sejam booleanos (0 ou 1)
        $data['possui_condominio'] = $request->has('possui_condominio');
        $data['possui_agua_incluida'] = $request->has('possui_agua_incluida');
        $data['possui_luz_incluida'] = $request->has('possui_luz_incluida');
        $data['ativo'] = $request->has('ativo');

        // Garante que campos numéricos com default 0 no banco não sejam NULL
        $data['valor_iptu_anual'] = $data['valor_iptu_anual'] ?? 0;
        $data['valor_condominio_mensal'] = $data['valor_condominio_mensal'] ?? 0;

        $imovel->update($data);

        return redirect()->route('imoveis.index')
            ->with('success', 'Imóvel atualizado com sucesso.');
    }

    /**
     * Remove um imóvel do banco de dados (soft delete).
     */
    public function destroy(Imovel $imovel)
    {
        $imovel->delete();

        return redirect()->route('imoveis.index')
            ->with('success', 'Imóvel removido com sucesso.');
    }
}
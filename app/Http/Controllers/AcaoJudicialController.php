<?php

namespace App\Http\Controllers;

use App\Models\AcaoJudicial;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AcaoJudicialController extends Controller
{
    /**
     * Retorna as opções de dropdown para o módulo de Ações Judiciais.
     */
    private function getAcaoJudicialOptions(): array
    {
        return [
            'tiposAcao' => [
                'DESPEJO'              => 'Despejo',
                'COBRANCA'             => 'Cobrança',
                'DESPEJO_E_COBRANCA'   => 'Despejo e Cobrança',
                'REVISIONAL_ALUGUEL'   => 'Revisional de Aluguel',
                'RENOVATORIA'          => 'Renovatória',
                'CONSIGNACAO_PAGAMENTO'=> 'Consignação em Pagamento',
                'OUTRA'                => 'Outra',
            ],
            'statusAcao' => [
                'EM_ANDAMENTO'         => 'Em Andamento',
                'ACORDO_REALIZADO'     => 'Acordo Realizado',
                'ENCERRADA_SEM_ACORDO' => 'Encerrada sem Acordo',
                'SUSPENSA'             => 'Suspensa',
                'ARQUIVADA'            => 'Arquivada',
            ],
            'condicoesImovel' => [
                'BOM'     => 'Bom',
                'REGULAR' => 'Regular',
                'RUIM'    => 'Ruim',
                'NAO_APLICAVEL' => 'Não Aplicável',
            ],
        ];
    }

    /**
     * Exibe uma lista de ações judiciais.
     */
    public function index(Request $request)
    {
        // Adicionado eager loading para 'contrato.imovel' e 'contrato.locatario'
        $query = AcaoJudicial::with(['contrato.imovel', 'contrato.locatario', 'registradoPor'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('contrato_id')) {
            $query->where('contrato_id', $request->input('contrato_id'));
        }
        if ($request->filled('tipo_acao')) {
            $query->where('tipo_acao', $request->input('tipo_acao'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('numero_processo')) {
            $query->where('numero_processo', 'like', '%' . $request->input('numero_processo') . '%');
        }

        $acoesJudiciais = $query->paginate(10)->withQueryString();

        $contratos = Contrato::orderBy('codigo')->get();
        $options = $this->getAcaoJudicialOptions();

        return view('acoes_judiciais.index', compact('acoesJudiciais', 'contratos', 'options'));
    }

    /**
     * Mostra o formulário para criar uma nova ação judicial.
     */
    public function create(Request $request)
    {
        $contratos = Contrato::orderBy('codigo')->get();
        $users = User::orderBy('name')->get();
        $options = $this->getAcaoJudicialOptions();

        // Pré-selecionar contrato se passado via URL
        $contratoSelecionado = null;
        if ($request->filled('contrato_id')) {
            $contratoSelecionado = Contrato::find($request->input('contrato_id'));
        }

        return view('acoes_judiciais.create', compact('contratos', 'users', 'options', 'contratoSelecionado'));
    }

    /**
     * Armazena uma nova ação judicial no banco de dados.
     */
    public function store(Request $request)
    {
        $options = $this->getAcaoJudicialOptions();
        $data = $request->validate([
            'contrato_id'               => 'required|exists:contratos,id',
            'tipo_acao'                 => ['required', Rule::in(array_keys($options['tiposAcao']))],
            'status'                    => ['required', Rule::in(array_keys($options['statusAcao']))],
            'numero_processo'           => 'nullable|string|max:255|unique:acoes_judiciais,numero_processo',
            'vara'                      => 'nullable|string|max:255',
            'comarca'                   => 'nullable|string|max:255',
            'advogado_nome'             => 'nullable|string|max:255',
            'advogado_telefone'         => 'nullable|string|max:20',
            'valor_cobrado'             => 'required|numeric|min:0',
            'valor_recuperado'          => 'nullable|numeric|min:0',
            'custo_advocaticio'         => 'nullable|numeric|min:0',
            'imovel_devolvido'          => 'required|boolean',
            'data_entrega_chaves'       => 'nullable|date',
            'condicao_imovel_entrega'   => ['nullable', Rule::in(array_keys($options['condicoesImovel']))],
            'houve_acordo'              => 'required|boolean',
            'descricao_acordo'          => 'nullable|string',
            'valor_acordo'              => 'nullable|numeric|min:0',
            'parcelas_acordo'           => 'nullable|integer|min:1',
            'novo_contrato_apos_decisao'=> 'required|boolean',
            'data_encerramento'         => 'nullable|date',
            'observacoes'               => 'nullable|string',
        ]);

        $data['registrado_por_user_id'] = Auth::id();

        AcaoJudicial::create($data);

        return redirect()->route('acoes-judiciais.index')->with('success', 'Ação judicial registrada com sucesso!');
    }

    /**
     * Exibe os detalhes de uma ação judicial específica.
     */
    public function show(AcaoJudicial $acaoJudicial)
    {
        // Carrega as relações necessárias para a view de detalhes
        $acaoJudicial->load(['contrato.imovel', 'contrato.locatario', 'registradoPor']);
        $options = $this->getAcaoJudicialOptions();

        return view('acoes_judiciais.show', compact('acaoJudicial', 'options'));
    }

    /**
     * Mostra o formulário para editar uma ação judicial existente.
     */
    public function edit(AcaoJudicial $acaoJudicial)
    {
        $contratos = Contrato::orderBy('codigo')->get();
        $users = User::orderBy('name')->get();
        $options = $this->getAcaoJudicialOptions();

        return view('acoes_judiciais.edit', [
            'acao_judicial' => $acaoJudicial,
            'contratos'     => $contratos,
            'users'         => $users,
            'options'       => $options,
        ]);
    }

    /**
     * Atualiza uma ação judicial no banco de dados.
     */
    public function update(Request $request, AcaoJudicial $acaoJudicial)
    {
        $options = $this->getAcaoJudicialOptions();
        $data = $request->validate([
            'contrato_id'               => 'required|exists:contratos,id',
            'tipo_acao'                 => ['required', Rule::in(array_keys($options['tiposAcao']))],
            'status'                    => ['required', Rule::in(array_keys($options['statusAcao']))],
            'numero_processo'           => 'nullable|string|max:255|unique:acoes_judiciais,numero_processo,' . $acaoJudicial->id,
            'vara'                      => 'nullable|string|max:255',
            'comarca'                   => 'nullable|string|max:255',
            'advogado_nome'             => 'nullable|string|max:255',
            'advogado_telefone'         => 'nullable|string|max:20',
            'valor_cobrado'             => 'required|numeric|min:0',
            'valor_recuperado'          => 'nullable|numeric|min:0',
            'custo_advocaticio'         => 'nullable|numeric|min:0',
            'imovel_devolvido'          => 'required|boolean',
            'data_entrega_chaves'       => 'nullable|date',
            'condicao_imovel_entrega'   => ['nullable', Rule::in(array_keys($options['condicoesImovel']))],
            'houve_acordo'              => 'required|boolean',
            'descricao_acordo'          => 'nullable|string',
            'valor_acordo'              => 'nullable|numeric|min:0',
            'parcelas_acordo'           => 'nullable|integer|min:1',
            'novo_contrato_apos_decisao'=> 'required|boolean',
            'data_encerramento'         => 'nullable|date',
            'observacoes'               => 'nullable|string',
        ]);

        $acaoJudicial->update($data);

        return redirect()->route('acoes-judiciais.index')->with('success', 'Ação judicial atualizada com sucesso!');
    }

    /**
     * Remove uma ação judicial do banco de dados.
     */
    public function destroy(AcaoJudicial $acaoJudicial)
    {
        $acaoJudicial->delete();

        return redirect()->route('acoes-judiciais.index')->with('success', 'Ação judicial excluída com sucesso!');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\AcaoJudicial;
use App\Models\Contrato;
use App\Models\Imovel;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcaoJudicialController extends Controller
{
    /**
     * Opções reutilizáveis para dropdowns.
     */
    private function getAcaoJudicialOptions(): array
    {
        return [
            'tiposAcao' => [
                'DESPEJO'            => 'Despejo',
                'COBRANCA'           => 'Cobrança',
                'REVISIONAL'         => 'Revisional de Aluguel',
                'RENOVATORIA'        => 'Renovatória',
                'CONSIGNACAO'        => 'Consignação em Pagamento',
                'OUTROS'             => 'Outros',
            ],
            'statusAcao' => [
                'EM_ANDAMENTO'       => 'Em Andamento',
                'ACORDO_REALIZADO'   => 'Acordo Realizado',
                'SENTENCA_FAVORAVEL' => 'Sentença Favorável',
                'SENTENCA_CONTRARIA' => 'Sentença Contrária',
                'RECURSO'            => 'Em Recurso',
                'ENCERRADA'          => 'Encerrada',
                'ARQUIVADA'          => 'Arquivada',
            ],
            'condicoesImovel' => [
                'OTIMO'      => 'Ótimo',
                'BOM'        => 'Bom',
                'REGULAR'    => 'Regular',
                'RUIM'       => 'Ruim',
                'PESSIMO'    => 'Péssimo',
            ],
        ];
    }

    /**
     * Lista ações judiciais — só carrega registros quando há filtros ativos.
     */
    public function index(Request $request)
    {
        // Listas para os selects de filtro
        $imoveis      = Imovel::orderBy('descricao')->get();
        $proprietarios = Pessoa::whereHas('imoveisProprietario')
                               ->orderBy('nome')
                               ->get();

        $filtroAtivo = $request->hasAny([
            'imovel_id',
            'proprietario_id',
            'tipo_acao',
            'status',
            'busca',
        ]);

        $acoes = collect(); // vazio por padrão

        if ($filtroAtivo) {
            $query = AcaoJudicial::query()
                ->with(['contrato.locatario', 'contrato.imovel.proprietario', 'registradoPor']);

            // Filtro por imóvel
            if ($request->filled('imovel_id')) {
                $query->whereHas('contrato', function ($q) use ($request) {
                    $q->where('imovel_id', $request->imovel_id);
                });
            }

            // Filtro por proprietário
            if ($request->filled('proprietario_id')) {
                $query->whereHas('contrato.imovel', function ($q) use ($request) {
                    $q->where('proprietario_id', $request->proprietario_id);
                });
            }

            // Filtro por tipo de ação
            if ($request->filled('tipo_acao')) {
                $query->where('tipo_acao', $request->tipo_acao);
            }

            // Filtro por status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Busca por locatário ou número do processo
            if ($request->filled('busca')) {
                $busca = $request->busca;
                $query->where(function ($q) use ($busca) {
                    $q->where('numero_processo', 'like', "%{$busca}%")
                      ->orWhereHas('contrato.locatario', function ($q2) use ($busca) {
                          $q2->where('nome', 'like', "%{$busca}%");
                      });
                });
            }

            $acoes = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        }

        $options = $this->getAcaoJudicialOptions();

        return view('acoes_judiciais.index', compact(
            'acoes',
            'imoveis',
            'proprietarios',
            'options',
            'filtroAtivo'
        ));
    }

    /**
     * Exibe o formulário de criação.
     */
    public function create(Request $request)
    {
        $contratos = Contrato::with(['locatario', 'imovel'])
            ->orderBy('codigo')
            ->get();

        $contratoSelecionado = $request->filled('contrato_id')
            ? Contrato::find($request->contrato_id)
            : null;

        $options = $this->getAcaoJudicialOptions();

        return view('acoes_judiciais.create', compact(
            'contratos',
            'contratoSelecionado',
            'options'
        ));
    }

    /**
     * Salva uma nova ação judicial.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contrato_id'               => 'required|exists:contratos,id',
            'tipo_acao'                 => 'required|string',
            'status'                    => 'required|string',
            'numero_processo'           => 'nullable|string|max:100',
            'vara'                      => 'nullable|string|max:150',
            'comarca'                   => 'nullable|string|max:150',
            'advogado_nome'             => 'nullable|string|max:200',
            'advogado_telefone'         => 'nullable|string|max:30',
            'valor_cobrado'             => 'required|numeric|min:0',
            'valor_recuperado'          => 'nullable|numeric|min:0',
            'custo_advocaticio'         => 'nullable|numeric|min:0',
            'imovel_devolvido'          => 'required|boolean',
            'data_entrega_chaves'       => 'nullable|date',
            'condicao_imovel_entrega'   => 'nullable|string',
            'houve_acordo'              => 'required|boolean',
            'descricao_acordo'          => 'nullable|string',
            'valor_acordo'              => 'nullable|numeric|min:0',
            'parcelas_acordo'           => 'nullable|integer|min:1',
            'novo_contrato_apos_decisao'=> 'required|boolean',
            'data_encerramento'         => 'nullable|date',
            'observacoes'               => 'nullable|string',
        ]);

        $validated['registrado_por'] = Auth::id();

        AcaoJudicial::create($validated);

        return redirect()
            ->route('acoes-judiciais.index')
            ->with('success', 'Ação judicial registrada com sucesso!');
    }

    /**
     * Exibe os detalhes de uma ação judicial.
     */
    public function show(AcaoJudicial $acaoJudicial)
    {
        $acaoJudicial->load([
            'contrato.locatario',
            'contrato.imovel.proprietario',
            'registradoPor',
        ]);

        $options = $this->getAcaoJudicialOptions();

        return view('acoes_judiciais.show', compact('acaoJudicial', 'options'));
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(AcaoJudicial $acao_judicial)
    {
        $contratos = Contrato::with(['locatario', 'imovel'])
            ->orderBy('codigo')
            ->get();

        $options = $this->getAcaoJudicialOptions();

        return view('acoes_judiciais.edit', compact('acao_judicial', 'contratos', 'options'));
    }

    /**
     * Atualiza uma ação judicial existente.
     */
    public function update(Request $request, AcaoJudicial $acao_judicial)
    {
        $validated = $request->validate([
            'contrato_id'              => 'required|exists:contratos,id',
            'tipo_acao'                => 'required|string|max:50',
            'status'                   => 'required|string|max:50',
            'numero_processo'          => 'nullable|string|max:100',
            'vara'                     => 'nullable|string|max:100',
            'comarca'                  => 'nullable|string|max:100',
            'advogado_nome'            => 'nullable|string|max:150',
            'advogado_telefone'        => 'nullable|string|max:30',
            'valor_cobrado'            => 'nullable|numeric|min:0',
            'valor_recuperado'         => 'nullable|numeric|min:0',
            'custo_advocaticio'        => 'nullable|numeric|min:0',
            'imovel_devolvido'         => 'required|boolean',
            'data_entrega_chaves'      => 'nullable|date',
            'condicao_imovel_entrega'  => 'nullable|string|max:100',
            'houve_acordo'             => 'required|boolean',
            'descricao_acordo'         => 'nullable|string',
            'valor_acordo'             => 'nullable|numeric|min:0',
            'parcelas_acordo'          => 'nullable|integer|min:1',
            'novo_contrato_apos_decisao' => 'required|boolean',
            'data_encerramento'        => 'nullable|date',
            'observacoes'              => 'nullable|string',
        ]);

        $acao_judicial->update($validated);

        return redirect()
            ->route('acoes-judiciais.show', $acao_judicial)
            ->with('success', 'Ação judicial atualizada com sucesso!');
    }

    /**
     * Remove uma ação judicial.
     */
    public function destroy(AcaoJudicial $acao_judicial)
    {
        $acao_judicial->delete();

        return redirect()
            ->route('acoes-judiciais.index')
            ->with('success', 'Ação judicial removida com sucesso!');
    }

}
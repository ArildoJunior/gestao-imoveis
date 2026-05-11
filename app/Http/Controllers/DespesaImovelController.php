<?php

namespace App\Http\Controllers;

use App\Models\DespesaImovel;
use App\Models\Imovel;
use App\Models\Contrato;
use App\Models\User; // Assumindo que você tem um Model User
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // Para pegar o usuário logado

class DespesaImovelController extends Controller
{
    /**
     * Exibe uma lista de despesas de imóvel.
     */
    public function index()
    {
        $despesas = DespesaImovel::with(['imovel', 'contrato', 'registradoPor'])
            ->orderBy('data_despesa', 'desc')
            ->paginate(10);

        return view('despesas_imovel.index', compact('despesas'));
    }

    /**
     * Mostra o formulário para criar uma nova despesa.
     */
    public function create()
    {
        $imoveis = Imovel::where('ativo', true)->orderBy('descricao')->get();
        $contratos = Contrato::where('status', 'ATIVO')->orderBy('codigo')->get(); // Apenas contratos ativos
        $users = User::orderBy('name')->get(); // Lista de usuários para 'registrado por'

        $tiposDespesa = [
            'MANUTENCAO' => 'Manutenção',
            'REFORMA' => 'Reforma',
            'IPTU' => 'IPTU',
            'CONDOMINIO' => 'Condomínio',
            'AGUA' => 'Água',
            'LUZ' => 'Luz',
            'SEGURO' => 'Seguro',
            'HONORARIO_ADVOCATICIO' => 'Honorário Advocatício',
            'OUTROS' => 'Outros',
        ];

        $responsaveis = [
            'PROPRIETARIO' => 'Proprietário',
            'LOCATARIO' => 'Locatário',
            'A_DEFINIR' => 'A Definir',
        ];

        $statusDespesa = [
            'PENDENTE' => 'Pendente',
            'PAGA' => 'Paga',
            'REEMBOLSADA' => 'Reembolsada',
            'CANCELADA' => 'Cancelada',
        ];

        return view('despesas_imovel.create', compact(
            'imoveis',
            'contratos',
            'users',
            'tiposDespesa',
            'responsaveis',
            'statusDespesa'
        ));
    }

    /**
     * Armazena uma nova despesa no banco de dados.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'imovel_id' => 'required|exists:imoveis,id',
            'contrato_id' => 'nullable|exists:contratos,id',
            'registrado_por_user_id' => 'required|exists:users,id',
            'data_despesa' => 'required|date',
            'tipo_despesa' => ['required', Rule::in(array_keys($this->getTiposDespesa()))],
            'descricao' => 'nullable|string|max:500',
            'valor' => 'required|numeric|min:0',
            'responsavel' => ['required', Rule::in(array_keys($this->getResponsaveis()))],
            'status' => ['required', Rule::in(array_keys($this->getStatusDespesa()))],
            'fornecedor' => 'nullable|string|max:255',
            'numero_nota_fiscal' => 'nullable|string|max:255',
            'data_reembolso' => 'nullable|date|after_or_equal:data_despesa',
            'valor_reembolso' => 'nullable|numeric|min:0',
        ]);

        DespesaImovel::create($data);

        return redirect()->route('despesas-imovel.index')
            ->with('success', 'Despesa de imóvel criada com sucesso.');
    }

    /**
     * Mostra o formulário para editar uma despesa existente.
     */
    public function edit(DespesaImovel $despesa_imovel)
    {
        $imoveis = Imovel::where('ativo', true)->orderBy('descricao')->get();
        $contratos = Contrato::where('status', 'ATIVO')->orderBy('codigo')->get();
        $users = User::orderBy('name')->get();

        $tiposDespesa = $this->getTiposDespesa();
        $responsaveis = $this->getResponsaveis();
        $statusDespesa = $this->getStatusDespesa();

        return view('despesas_imovel.edit', compact(
            'despesa_imovel',
            'imoveis',
            'contratos',
            'users',
            'tiposDespesa',
            'responsaveis',
            'statusDespesa'
        ));
    }

    /**
     * Atualiza uma despesa existente no banco de dados.
     */
    public function update(Request $request, DespesaImovel $despesa_imovel)
    {
        $data = $request->validate([
            'imovel_id' => 'required|exists:imoveis,id',
            'contrato_id' => 'nullable|exists:contratos,id',
            'registrado_por_user_id' => 'required|exists:users,id',
            'data_despesa' => 'required|date',
            'tipo_despesa' => ['required', Rule::in(array_keys($this->getTiposDespesa()))],
            'descricao' => 'nullable|string|max:500',
            'valor' => 'required|numeric|min:0',
            'responsavel' => ['required', Rule::in(array_keys($this->getResponsaveis()))],
            'status' => ['required', Rule::in(array_keys($this->getStatusDespesa()))],
            'fornecedor' => 'nullable|string|max:255',
            'numero_nota_fiscal' => 'nullable|string|max:255',
            'data_reembolso' => 'nullable|date|after_or_equal:data_despesa',
            'valor_reembolso' => 'nullable|numeric|min:0',
        ]);

        $despesa_imovel->update($data);

        return redirect()->route('despesas-imovel.index')
            ->with('success', 'Despesa de imóvel atualizada com sucesso.');
    }

    /**
     * Remove uma despesa do banco de dados.
     */
    public function destroy(DespesaImovel $despesa_imovel)
    {
        $despesa_imovel->delete();

        return redirect()->route('despesas-imovel.index')
            ->with('success', 'Despesa de imóvel removida com sucesso.');
    }

    // Métodos auxiliares para os ENUMs
    private function getTiposDespesa(): array
    {
        return [
            'MANUTENCAO' => 'Manutenção',
            'REFORMA' => 'Reforma',
            'IPTU' => 'IPTU',
            'CONDOMINIO' => 'Condomínio',
            'AGUA' => 'Água',
            'LUZ' => 'Luz',
            'SEGURO' => 'Seguro',
            'HONORARIO_ADVOCATICIO' => 'Honorário Advocatício',
            'OUTROS' => 'Outros',
        ];
    }

    private function getResponsaveis(): array
    {
        return [
            'PROPRIETARIO' => 'Proprietário',
            'LOCATARIO' => 'Locatário',
            'A_DEFINIR' => 'A Definir',
        ];
    }

    private function getStatusDespesa(): array
    {
        return [
            'PENDENTE' => 'Pendente',
            'PAGA' => 'Paga',
            'REEMBOLSADA' => 'Reembolsada',
            'CANCELADA' => 'Cancelada',
        ];
    }
}
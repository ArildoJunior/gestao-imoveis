<?php

namespace App\Http\Controllers;

use App\Models\ParcelaAluguel;
use App\Models\Imovel;
use App\Models\Contrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para pegar o ID do usuário logado

class FinanceiroController extends Controller
{
    /**
     * Tela de controle financeiro (parcelas de aluguel).
     */
    public function index(Request $request)
    {
        $query = ParcelaAluguel::with(['contrato.imovel', 'contrato.locatario'])
            ->orderBy('data_vencimento');

        // Filtros
        if ($request->filled('imovel_id')) {
            $imovelId = $request->input('imovel_id');
            $query->whereHas('contrato', function ($q) use ($imovelId) {
                $q->where('imovel_id', $imovelId);
            });
        }

        if ($request->filled('contrato_id')) {
            $query->where('contrato_id', $request->input('contrato_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('competencia')) {
            // formato esperado: YYYY-MM
            $query->where('competencia', $request->input('competencia'));
        }

        // Período por data de vencimento
        if ($request->filled('vencimento_de')) {
            $query->whereDate('data_vencimento', '>=', $request->input('vencimento_de'));
        }

        if ($request->filled('vencimento_ate')) {
            $query->whereDate('data_vencimento', '<=', $request->input('vencimento_ate'));
        }

        $parcelas = $query->paginate(15)->withQueryString();

        // Dados para filtros
        $imoveis   = Imovel::orderBy('descricao')->get();
        $contratos = Contrato::orderBy('codigo')->get();

        // Status possíveis (incluindo os novos)
        $statusPossiveis = [
            'ABERTA'             => 'Aberta',
            'EM_ATRASO'          => 'Em atraso',
            'PAGA_PARCIALMENTE'  => 'Paga parcialmente',
            'PAGA'               => 'Paga',
            'EM_ACORDO'          => 'Em Acordo',
            'CANCELADA'          => 'Cancelada',
            'PERDIDA'            => 'Perdida',
            'JURIDICO'           => 'Jurídico',
        ];

        return view('financeiro.index', compact(
            'parcelas',
            'imoveis',
            'contratos',
            'statusPossiveis'
        ));
    }

    /**
     * Marca uma parcela como "Em Acordo".
     */
    public function marcarComoAcordo(Request $request, ParcelaAluguel $parcela)
    {
        $parcela->update([
            'status' => 'EM_ACORDO',
            // Você pode adicionar campos para registrar detalhes do acordo aqui
            // 'observacoes_acordo' => $request->input('observacoes_acordo'),
            // 'data_acordo' => now(),
        ]);

        return redirect()->back()->with('success', 'Parcela marcada como "Em Acordo" com sucesso!');
    }

    /**
     * Cancela uma parcela.
     */
    public function cancelarParcela(Request $request, ParcelaAluguel $parcela)
    {
        $parcela->update([
            'status' => 'CANCELADA',
            // 'motivo_cancelamento' => $request->input('motivo_cancelamento'),
        ]);

        return redirect()->back()->with('success', 'Parcela cancelada com sucesso!');
    }

    /**
     * Marca uma parcela como "Perdida".
     */
    public function marcarComoPerdida(Request $request, ParcelaAluguel $parcela)
    {
        $parcela->update([
            'status' => 'PERDIDA',
            // 'motivo_perda' => $request->input('motivo_perda'),
        ]);

        return redirect()->back()->with('success', 'Parcela marcada como "Perdida" com sucesso!');
    }

    /**
     * Envia uma parcela para o setor Jurídico.
     */
    public function enviarParaJuridico(Request $request, ParcelaAluguel $parcela)
    {
        $parcela->update([
            'status' => 'JURIDICO',
            // 'data_envio_juridico' => now(),
        ]);

        return redirect()->back()->with('success', 'Parcela enviada para o Jurídico com sucesso!');
    }
}
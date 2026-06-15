<?php

namespace App\Http\Controllers;

use App\Models\ParcelaAluguel;
use App\Models\Contrato;
use App\Models\Imovel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // Adicione esta linha

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        // ... (código existente do método index) ...

        $imoveis = Imovel::orderBy('descricao')->get();
        $contratos = Contrato::orderBy('codigo')->get();

        $statusPossiveis = [
            'ABERTA',
            'PAGA',
            'PAGA_PARCIALMENTE',
            'EM_ATRASO',
            'RENEGOCIADA',
            'CANCELADA',
            'EM_ACORDO',
            'JURIDICO',
            'PERDIDA',
        ];

        $query = ParcelaAluguel::with(['contrato.imovel', 'contrato.locatario'])
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('competencia', 'asc');

        // Filtros
        if ($request->filled('imovel_id')) {
            $query->whereHas('contrato', function ($q) use ($request) {
                $q->where('imovel_id', $request->imovel_id);
            });
        }

        if ($request->filled('contrato_id')) {
            $query->where('contrato_id', $request->contrato_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('competencia')) {
            $query->where('competencia', $request->competencia);
        }

        if ($request->filled('vencimento_de')) {
            $query->whereDate('data_vencimento', '>=', $request->vencimento_de);
        }

        if ($request->filled('vencimento_ate')) {
            $query->whereDate('data_vencimento', '<=', $request->vencimento_ate);
        }

        $parcelas = $query->paginate(10);

        // Aplica multa e juros em memória para exibição
        $parcelas->each(function ($parcela) {
            if ($parcela->status === 'ABERTA' || $parcela->status === 'EM_ATRASO' || $parcela->status === 'PAGA_PARCIALMENTE') {
                $parcela->aplicarMultaEJurosSeAtraso();
            }
        });

        return view('financeiro.index', compact(
            'parcelas',
            'imoveis',
            'contratos',
            'statusPossiveis'
        ));
    }

    public function marcarComoAcordo(ParcelaAluguel $parcela)
    {
        // Autoriza a ação usando a política
        $this->authorize('updateStatus', $parcela);

        if ($parcela->status === 'PAGA' || $parcela->status === 'CANCELADA') {
            return redirect()
                ->back()
                ->with('error', 'Não é possível marcar uma parcela paga ou cancelada como "Em Acordo".');
        }

        $parcela->status = 'EM_ACORDO';
        $parcela->save();

        return redirect()
            ->back()
            ->with('success', 'Parcela marcada como "Em Acordo" com sucesso!');
    }

    public function cancelarParcela(ParcelaAluguel $parcela)
    {
        // Autoriza a ação usando a política
        $this->authorize('updateStatus', $parcela);

        if ($parcela->status === 'PAGA') {
            return redirect()
                ->back()
                ->with('error', 'Não é possível cancelar uma parcela que já foi paga.');
        }

        $parcela->status = 'CANCELADA';
        $parcela->save();

        return redirect()
            ->back()
            ->with('success', 'Parcela cancelada com sucesso!');
    }

    public function marcarComoPerdida(ParcelaAluguel $parcela)
    {
        // Autoriza a ação usando a política
        $this->authorize('updateStatus', $parcela);

        if ($parcela->status === 'PAGA' || $parcela->status === 'CANCELADA') {
            return redirect()
                ->back()
                ->with('error', 'Não é possível marcar uma parcela paga ou cancelada como "Perdida".');
        }

        $parcela->status = 'PERDIDA';
        $parcela->save();

        return redirect()
            ->back()
            ->with('success', 'Parcela marcada como "Perdida" com sucesso!');
    }

    public function enviarParaJuridico(ParcelaAluguel $parcela)
    {
        // Autoriza a ação usando a política
        $this->authorize('updateStatus', $parcela);

        if ($parcela->status === 'PAGA' || $parcela->status === 'CANCELADA') {
            return redirect()
                ->back()
                ->with('error', 'Não é possível enviar uma parcela paga ou cancelada para o jurídico.');
        }

        $parcela->status = 'JURIDICO';
        $parcela->save();

        return redirect()
            ->back()
            ->with('success', 'Parcela enviada para o jurídico com sucesso!');
    }

    /**
     * Remove a ParcelaAluguel especificada do armazenamento (soft delete).
     *
     * @param  \App\Models\ParcelaAluguel  $parcela
     * @return \Illuminate\Http\Response
     */
    public function destroy(ParcelaAluguel $parcela)
    {
        // Autoriza a ação usando a política ParcelaAluguelPolicy
        $this->authorize('delete', $parcela);

        // Verifica se a parcela possui pagamentos associados
        if ($parcela->pagamentos()->exists()) {
            // Se houver pagamentos, não permite a exclusão direta para evitar perda de histórico financeiro.
            // Em vez disso, sugere o cancelamento ou a marcação como perdida.
            return redirect()
                ->back()
                ->with('error', 'Não é possível excluir uma parcela que possui pagamentos registrados. Considere cancelá-la ou marcá-la como "Perdida" se necessário.');
        }

        // Realiza o soft delete da parcela
        $parcela->delete();

        return redirect()
            ->route('financeiro.index')
            ->with('success', 'Parcela excluída com sucesso (soft delete)!');
    }
}
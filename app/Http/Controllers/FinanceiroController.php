<?php

namespace App\Http\Controllers;

use App\Models\ParcelaAluguel;
use App\Models\Contrato;
use App\Models\Imovel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $imoveis = Imovel::orderBy('descricao')->get();
        $contratos = Contrato::orderBy('codigo')->get();

        // Definindo os status possíveis para o filtro na view
        // É uma boa prática ter esses valores em um Enum ou constante para evitar duplicação
        $statusPossiveis = [
            'ABERTA'            => 'Aberta',
            'PAGA'              => 'Paga',
            'PAGA_PARCIALMENTE' => 'Paga Parcialmente',
            'EM_ATRASO'         => 'Em Atraso',
            'RENEGOCIADA'       => 'Renegociada',
            'CANCELADA'         => 'Cancelada',
            'EM_ACORDO'         => 'Em Acordo',
            'JURIDICO'          => 'Jurídico',
            'PERDIDA'           => 'Perdida',
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

        // CORREÇÃO AQUI: Adicionado && $request->status !== '0'
        // E também ajustei o array $statusPossiveis para ter chaves e valores
        if ($request->filled('status') && $request->status !== '0') {
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

    public function destroy(ParcelaAluguel $parcela)
    {
        $this->authorize('delete', $parcela);

        if ($parcela->pagamentos()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Não é possível excluir uma parcela que possui pagamentos registrados. Considere cancelá-la ou marcá-la como "Perdida" se necessário.');
        }

        $parcela->delete();

        return redirect()
            ->route('financeiro.index')
            ->with('success', 'Parcela excluída com sucesso (soft delete)!');
    }
}
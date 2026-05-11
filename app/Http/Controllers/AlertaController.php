<?php // app/Http/Controllers/AlertaController.php

namespace App\Http\Controllers;

use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AlertaController extends Controller
{
    public function index(Request $request)
    {
        $query = Alerta::with(['contrato.imovel', 'parcela', 'despesaImovel', 'acaoJudicial'])
            ->orderByDesc('data_alerta');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('tipo_alerta')) {
            $query->where('tipo_alerta', $request->input('tipo_alerta'));
        }

        if ($request->filled('data_de')) {
            $query->whereDate('data_alerta', '>=', $request->input('data_de'));
        }

        if ($request->filled('data_ate')) {
            $query->whereDate('data_alerta', '<=', $request->input('data_ate'));
        }

        $alertas = $query->paginate(20)->withQueryString();

        $statusPossiveis = [
            'PENDENTE'    => 'Pendente',
            'VISUALIZADO' => 'Visualizado',
            'RESOLVIDO'   => 'Resolvido',
            'IGNORADO'    => 'Ignorado',
        ];

        // Ajustado para corresponder aos tipos da migration e do GerarAlertas.php
        $tiposPossiveis = [
            'PARCELA_ATRASO'             => 'Parcela em atraso',
            'PARCELA_A_VENCER'           => 'Parcela a vencer (7 dias)',
            'CONTRATO_VENCENDO'          => 'Contrato vencendo',
            'CAUCAO_PENDENTE'            => 'Caução pendente',
            'REAJUSTE_PREVISTO'          => 'Reajuste previsto',
            'DESPESA_PENDENTE'           => 'Despesa pendente',
            'PAGAMENTO_TEMPORADA_PENDENTE' => 'Temporada: Pagamento pendente (30 dias)',
            'ACAO_JUDICIAL'              => 'Ação Judicial', // <--- NOME DO TIPO DE ALERTA ALTERADO AQUI
        ];

        return view('alertas.index', compact(
            'alertas',
            'statusPossiveis',
            'tiposPossiveis'
        ));
    }

    public function marcarComoVisualizado(Alerta $alerta)
    {
        if ($alerta->status === 'PENDENTE') {
            $alerta->status         = 'VISUALIZADO';
            $alerta->visualizado_em = now();
            $alerta->save();
        }

        return back()->with('success', 'Alerta marcado como visualizado.');
    }

    public function marcarComoResolvido(Alerta $alerta)
    {
        if (in_array($alerta->status, ['PENDENTE', 'VISUALIZADO'])) {
            $alerta->status       = 'RESOLVIDO';
            $alerta->resolvido_em = now();
            $alerta->save();
        }

        return back()->with('success', 'Alerta marcado como resolvido.');
    }

    public function marcarComoIgnorado(Alerta $alerta)
    {
        if ($alerta->status === 'PENDENTE') {
            $alerta->status = 'IGNORADO';
            $alerta->save();
        }

        return back()->with('success', 'Alerta ignorado.');
    }
}
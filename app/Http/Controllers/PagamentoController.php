<?php

namespace App\Http\Controllers;

use App\Models\ParcelaAluguel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class PagamentoController extends Controller
{
    public function create(ParcelaAluguel $parcela)
    {
        $formasPagamento = [
            'PIX'       => 'PIX',
            'DINHEIRO'  => 'Dinheiro',
            'TED_DOC'   => 'TED/DOC',
            'BOLETO'    => 'Boleto',
            'CARTAO'    => 'Cartão',
            'OUTRO'     => 'Outro',
        ];

        // Carrega contrato e pagamentos
        $parcela->load(['contrato', 'pagamentos']);

        $resultadoCalculo = $parcela->calcularMultaJurosEmMemoria(now());
        $multaAtual       = $resultadoCalculo['multa'];
        $jurosAtuais      = $resultadoCalculo['juros'];
        $valorDevidoAtual = $resultadoCalculo['valor_devido_calculado'];

        $valorRestante = max(0, $valorDevidoAtual - $parcela->valor_pago);

        return view('pagamentos.create', compact(
            'parcela',
            'formasPagamento',
            'valorRestante',
            'multaAtual',
            'jurosAtuais',
            'valorDevidoAtual'
        ));
    }

    public function showParcelPayments(ParcelaAluguel $parcela)
    {
        // Carrega contrato, imóvel, locatário e pagamentos
        $parcela->load(['contrato.imovel', 'contrato.locatario', 'pagamentos']);

        return view('pagamentos.show', compact('parcela'));
    }

    public function store(Request $request, ParcelaAluguel $parcela)
    {
        // Recalcula para garantir coerência com o momento do pagamento
        $resultadoCalculo = $parcela->calcularMultaJurosEmMemoria(now());
        $valorDevidoAtual = $resultadoCalculo['valor_devido_calculado'];
        $valorRestante    = max(0, $valorDevidoAtual - $parcela->valor_pago);

        $request->validate([
            'valor_pago' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . ($valorRestante + 0.01),
            ],
            'desconto' => [
                'nullable',
                'numeric',
                'min:0',
                'max:' . ($valorRestante + 0.01),
            ],
            'forma_pagamento' => [
                'required',
                Rule::in(['PIX', 'DINHEIRO', 'TED_DOC', 'BOLETO', 'CARTAO', 'OUTRO']),
            ],
            'numero_comprovante' => 'nullable|string|max:255',
            'observacoes'        => 'nullable|string|max:500',
        ]);

        $valorPago = (float) $request->input('valor_pago');
        $desconto  = (float) $request->input('desconto', 0);

        $registradoPorUserId = Auth::id();

        try {
            // Aplica desconto (perdão) na parcela antes de pagar
            if ($desconto > 0) {
                $parcela->desconto_aplicado = bcadd($parcela->desconto_aplicado, $desconto, 2);
                $parcela->aplicarMultaEJurosSeAtraso(now());
                // Save virá dentro de registrarPagamento()
            }

            $parcela->registrarPagamento(
                $valorPago,
                $request->input('forma_pagamento'),
                $request->input('numero_comprovante'),
                $request->input('observacoes'),
                $registradoPorUserId
            );

            return redirect()
                ->route('contratos.show', $parcela->contrato_id)
                ->with('success', 'Pagamento registrado com sucesso! Status da parcela atualizado para ' . $parcela->status . '.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao registrar pagamento: ' . $e->getMessage());
        }
    }
}
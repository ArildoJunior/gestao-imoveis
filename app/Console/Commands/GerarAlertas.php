<?php

namespace App\Console\Commands;

use App\Models\Alerta;
use App\Models\AcaoJudicial;
use App\Models\Contrato;
use App\Models\DespesaImovel;
use App\Models\ParcelaAluguel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GerarAlertas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sistema:gerar-alertas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera alertas automáticos para o sistema.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando a geração de alertas...');
        Log::info('Iniciando a geração de alertas automáticos.');

        // --- Alertas de Parcelas em Atraso ---
        $parcelasAtraso = ParcelaAluguel::where('status', 'ABERTA')
            ->where('data_vencimento', '<', Carbon::today())
            ->get();

        foreach ($parcelasAtraso as $parcela) {
            Alerta::updateOrCreate(
                [
                    'tipo_alerta' => 'PARCELA_ATRASO',
                    'parcela_id'  => $parcela->id,
                ],
                [
                    'titulo'      => 'Parcela em Atraso',
                    'descricao'   => "A parcela de {$parcela->competencia} do contrato {$parcela->contrato->codigo} está em atraso desde " . $parcela->data_vencimento->format('d/m/Y') . ". Valor devido: R$ " . number_format($parcela->valor_devido_atual, 2, ',', '.'),
                    'data_alerta' => Carbon::now(),
                    'status'      => 'PENDENTE',
                    'contrato_id' => $parcela->contrato_id,
                    'imovel_id'   => $parcela->contrato->imovel_id,
                ]
            );
        }
        $this->info("Gerados " . $parcelasAtraso->count() . " alertas de parcelas em atraso.");

        // --- Alertas de Parcelas a Vencer (próximos 7 dias) ---
        $parcelasAVencer = ParcelaAluguel::whereIn('status', ['ABERTA', 'PAGA_PARCIALMENTE'])
            ->whereBetween('data_vencimento', [Carbon::today(), Carbon::today()->addDays(7)])
            ->get();

        foreach ($parcelasAVencer as $parcela) {
            Alerta::updateOrCreate(
                [
                    'tipo_alerta' => 'PARCELA_A_VENCER',
                    'parcela_id'  => $parcela->id,
                ],
                [
                    'titulo'      => 'Parcela a Vencer',
                    'descricao'   => "A parcela de {$parcela->competencia} do contrato {$parcela->contrato->codigo} vence em " . $parcela->data_vencimento->format('d/m/Y') . ". Valor devido: R$ " . number_format($parcela->valor_devido_atual, 2, ',', '.'),
                    'data_alerta' => Carbon::now(),
                    'status'      => 'PENDENTE',
                    'contrato_id' => $parcela->contrato_id,
                    'imovel_id'   => $parcela->contrato->imovel_id,
                ]
            );
        }
        $this->info("Gerados " . $parcelasAVencer->count() . " alertas de parcelas a vencer.");

        // --- Alertas de Contratos Próximos do Vencimento (próximos 60 dias) ---
        $contratosVencendo = Contrato::where('status', 'ATIVO')
            ->whereNotNull('data_fim_prevista')
            ->whereBetween('data_fim_prevista', [Carbon::today(), Carbon::today()->addDays(60)])
            ->get();

        foreach ($contratosVencendo as $contrato) {
            Alerta::updateOrCreate(
                [
                    'tipo_alerta' => 'CONTRATO_VENCENDO',
                    'contrato_id' => $contrato->id,
                ],
                [
                    'titulo'      => 'Contrato Próximo do Vencimento',
                    'descricao'   => "O contrato {$contrato->codigo} com {$contrato->locatario->nome} vence em " . $contrato->data_fim_prevista->format('d/m/Y') . ".",
                    'data_alerta' => Carbon::now(),
                    'status'      => 'PENDENTE',
                    'imovel_id'   => $contrato->imovel_id,
                ]
            );
        }
        $this->info("Gerados " . $contratosVencendo->count() . " alertas de contratos vencendo.");

        // --- Alertas de Reajuste Previsto (próximos 30 dias) ---
        $contratosReajuste = Contrato::where('status', 'ATIVO')
            ->whereNotNull('mes_reajuste')
            ->where('indice_reajuste', '!=', 'SEM_REAJUSTE')
            ->get()
            ->filter(function ($contrato) {
                $proximaDataReajuste = $contrato->proxima_data_reajuste;
                return $proximaDataReajuste && $proximaDataReajuste->between(Carbon::today(), Carbon::today()->addDays(30));
            });

        foreach ($contratosReajuste as $contrato) {
            Alerta::updateOrCreate(
                [
                    'tipo_alerta' => 'REAJUSTE_PREVISTO',
                    'contrato_id' => $contrato->id,
                ],
                [
                    'titulo'      => 'Reajuste de Contrato Previsto',
                    'descricao'   => "O contrato {$contrato->codigo} com {$contrato->locatario->nome} tem reajuste previsto para " . $contrato->proxima_data_reajuste->format('d/m/Y') . " pelo índice {$contrato->indice_reajuste}.",
                    'data_alerta' => Carbon::now(),
                    'status'      => 'PENDENTE',
                    'imovel_id'   => $contrato->imovel_id,
                ]
            );
        }
        $this->info("Gerados " . $contratosReajuste->count() . " alertas de reajuste previsto.");

        // --- Alertas de Caução Pendente ---
        $contratosCaucaoPendente = Contrato::where('possui_caucao', true)
            ->where('caucao_paga_integralmente', false)
            ->where('status', 'ATIVO')
            ->get();

        foreach ($contratosCaucaoPendente as $contrato) {
            Alerta::updateOrCreate(
                [
                    'tipo_alerta' => 'CAUCAO_PENDENTE',
                    'contrato_id' => $contrato->id,
                ],
                [
                    'titulo'      => 'Caução Pendente',
                    'descricao'   => "A caução do contrato {$contrato->codigo} com {$contrato->locatario->nome} ainda não foi paga integralmente.",
                    'data_alerta' => Carbon::now(),
                    'status'      => 'PENDENTE',
                    'imovel_id'   => $contrato->imovel_id,
                ]
            );
        }
        $this->info("Gerados " . $contratosCaucaoPendente->count() . " alertas de caução pendente.");

        // --- Alertas de Despesas Pendentes ---
        $despesasPendentes = DespesaImovel::where('status', 'PENDENTE')
            ->where('data_despesa', '<=', Carbon::today()) // Considera despesas pendentes até hoje
            ->get();

        foreach ($despesasPendentes as $despesa) {
            Alerta::updateOrCreate(
                [
                    'tipo_alerta'       => 'DESPESA_PENDENTE',
                    'despesa_imovel_id' => $despesa->id,
                ],
                [
                    'titulo'      => 'Despesa Pendente',
                    'descricao'   => "A despesa de {$despesa->tipo_despesa} no valor de R$ " . number_format($despesa->valor, 2, ',', '.') . " para o imóvel {$despesa->imovel->descricao} está pendente desde " . $despesa->data_despesa->format('d/m/Y') . ".",
                    'data_alerta' => Carbon::now(),
                    'status'      => 'PENDENTE',
                    'imovel_id'   => $despesa->imovel_id,
                    'contrato_id' => $despesa->contrato_id, // Pode ser nulo
                ]
            );
        }
        $this->info("Gerados " . $despesasPendentes->count() . " alertas de despesas pendentes.");

        // --- Alertas de Ação Judicial ---
        // Alerta para qualquer ação judicial em andamento
        $acoesJudiciaisEmAndamento = AcaoJudicial::where('status', 'EM_ANDAMENTO')->get();

        foreach ($acoesJudiciaisEmAndamento as $acao) {
            Alerta::updateOrCreate(
                [
                    'tipo_alerta'      => 'ACAO_JUDICIAL', // Nome do tipo de alerta ajustado
                    'acao_judicial_id' => $acao->id,
                ],
                [
                    'titulo'      => 'Ação Judicial em Andamento', // Título mais genérico
                    'descricao'   => "A ação judicial #{$acao->id} do contrato {$acao->contrato->codigo} está em andamento. Status atual: {$acao->status}.",
                    'data_alerta' => Carbon::now(),
                    'status'      => 'PENDENTE',
                    'contrato_id' => $acao->contrato_id,
                    'imovel_id'   => $acao->contrato->imovel_id,
                ]
            );
        }
        $this->info("Gerados " . $acoesJudiciaisEmAndamento->count() . " alertas de ação judicial.");

        // --- Alertas de Pagamento de Temporada Pendente (30 dias antes da entrada) ---
        $contratosTemporadaPendentes = Contrato::where('tipo_contrato', 'TEMPORADA')
            ->where('status', 'ATIVO')
            ->whereNotNull('data_entrada_prevista')
            ->get()
            ->filter(function ($contrato) {
                // Filtra contratos onde a parcela de temporada não está paga integralmente
                if ($contrato->temporada_paga_integralmente) {
                    return false;
                }

                $dataEntrada = Carbon::parse($contrato->data_entrada_prevista)->startOfDay();
                $hoje = Carbon::today();
                $diasAteEntrada = $hoje->diffInDays($dataEntrada, false);

                // Gera alerta se faltam entre 1 e 30 dias para a entrada e não está pago
                return $diasAteEntrada > 0 && $diasAteEntrada <= 30;
            });

        foreach ($contratosTemporadaPendentes as $contrato) {
            Alerta::updateOrCreate(
                [
                    'tipo_alerta' => 'PAGAMENTO_TEMPORADA_PENDENTE',
                    'contrato_id' => $contrato->id,
                ],
                [
                    'titulo'      => 'Pagamento de Temporada Pendente',
                    'descricao'   => "O pagamento da temporada do contrato {$contrato->codigo} para o imóvel {$contrato->imovel->descricao} está pendente. Faltam {$contrato->data_entrada_prevista->diffInDays(Carbon::now())} dias para a entrada.",
                    'data_alerta' => Carbon::now(),
                    'status'      => 'PENDENTE',
                    'imovel_id'   => $contrato->imovel_id,
                ]
            );
        }
        $this->info("Gerados " . $contratosTemporadaPendentes->count() . " alertas de pagamento de temporada pendente.");

        $this->info('Geração de alertas concluída!');
        Log::info('Geração de alertas automáticos concluída.');
    }
}
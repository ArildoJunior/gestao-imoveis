@extends('layouts.app')

@section('title', 'Detalhes do Contrato')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Detalhes do Contrato: {{ $contrato->codigo }}</h1>

        <div class="btn-group">
            <a href="{{ route('contratos.edit', $contrato->id) }}" class="btn btn-warning">
                Editar Contrato
            </a>

            @if($contrato->status === 'ATIVO' && $contrato->tipo_contrato !== 'TEMPORADA')
                <a href="{{ route('reajustes.create', $contrato->id) }}"
                   class="btn btn-outline-primary">
                    Reajustar Aluguel
                </a>
            @endif

            @if($contrato->status === 'ATIVO')
                <a href="{{ route('contratos.encerrar.form', $contrato->id) }}"
                   class="btn btn-outline-danger">
                    Encerrar / Rescindir
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- Coluna esquerda: dados + parcelas --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    Informações do Contrato
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Imóvel:</strong> {{ $contrato->imovel->descricao ?? 'N/A' }}</p>
                            <p><strong>Locatário:</strong> {{ $contrato->locatario->nome ?? 'N/A' }}</p>
                            <p><strong>Proprietário:</strong> {{ $contrato->proprietario->nome ?? 'N/A' }}</p>
                            <p><strong>Tipo:</strong> {{ $contrato->tipo_contrato }}</p>
                            <p><strong>Status:</strong> {{ $contrato->status }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Data Início:</strong> {{ $contrato->data_inicio?->format('d/m/Y') }}</p>
                            <p><strong>Data Fim Prevista:</strong> {{ $contrato->data_fim_prevista?->format('d/m/Y') }}</p>

                            @if($contrato->tipo_contrato !== 'TEMPORADA')
                                <p>
                                    <strong>Valor Aluguel Atual:</strong>
                                    R$ {{ number_format($contrato->valor_aluguel_atual, 2, ',', '.') }}
                                </p>
                                <p><strong>Dia Vencimento:</strong> {{ $contrato->dia_vencimento }}</p>
                                <p><strong>Índice Reajuste:</strong> {{ $contrato->indice_reajuste ?? 'N/A' }}</p>
                                <p>
                                    <strong>Mês Reajuste:</strong>
                                    {{ $contrato->mes_reajuste ? $contrato->mes_reajuste : 'N/A' }}
                                </p>
                                <p>
                                    <strong>Percentual Reajuste Padrão:</strong>
                                    {{ $contrato->percentual_reajuste_padrao
                                        ? number_format($contrato->percentual_reajuste_padrao, 2, ',', '.') . '%'
                                        : 'Não definido' }}
                                </p>
                            @else
                                {{-- Contrato de TEMPORADA: mostrar dados específicos --}}
                                <p>
                                    <strong>Valor Total Temporada:</strong>
                                    R$ {{ number_format($contrato->valor_total_temporada, 2, ',', '.') }}
                                </p>
                                <p>
                                    <strong>Entrada Prevista:</strong>
                                    {{ $contrato->data_entrada_prevista?->format('d/m/Y') }}
                                    @if($contrato->hora_entrada)
                                        às {{ substr($contrato->hora_entrada, 0, 5) }}
                                    @endif
                                </p>
                                <p>
                                    <strong>Saída Prevista:</strong>
                                    {{ $contrato->data_saida_prevista?->format('d/m/Y') }}
                                    @if($contrato->hora_saida)
                                        às {{ substr($contrato->hora_saida, 0, 5) }}
                                    @endif
                                </p>
                                <p>
                                    <strong>Nº Hóspedes:</strong>
                                    {{ $contrato->numero_hospedes ?? '-' }}
                                </p>
                                <p>
                                    <strong>Prazo Máx. Pagamento:</strong>
                                    {{ $contrato->prazo_maximo_pagamento_dias ?? 30 }} dias antes da entrada
                                </p>
                            @endif
                        </div>
                    </div>

                    @if($contrato->tipo_contrato === 'TEMPORADA')
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Regras Especiais:</strong><br>
                                    {!! nl2br(e($contrato->regras_especiais ?? 'Não informado')) !!}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Restrições:</strong><br>
                                    {!! nl2br(e($contrato->restricoes ?? 'Não informado')) !!}
                                </p>
                            </div>
                        </div>

                        {{-- Situação da temporada + alertas --}}
                        @if(isset($dadosTemporada))
                            @php
                                $faltando = $dadosTemporada['falta_pagar'];
                                $diasAte  = $dadosTemporada['dias_ate_entrada'];
                            @endphp

                            <hr>
                            <div class="mt-2">
                                <h5>Situação da Temporada</h5>
                                <p>
                                    <strong>Parcela TEMPORADA:</strong>
                                    Vencimento em
                                    {{ $dadosTemporada['parcela']->data_vencimento?->format('d/m/Y') ?? 'N/A' }}
                                </p>
                                <p>
                                    <strong>Valor Total:</strong>
                                    R$ {{ number_format($dadosTemporada['valor_total'], 2, ',', '.') }}<br>
                                    <strong>Valor Pago:</strong>
                                    R$ {{ number_format($dadosTemporada['valor_pago'], 2, ',', '.') }}<br>
                                    <strong>Falta Pagar:</strong>
                                    R$ {{ number_format($faltando, 2, ',', '.') }}
                                </p>

                                @if($dadosTemporada['pago_integral'])
                                    <div class="alert alert-success mb-0">
                                        Valor da temporada <strong>totalmente quitado</strong>.
                                    </div>
                                @else
                                    @if($dadosTemporada['alerta_7_dias'])
                                        <div class="alert alert-danger mb-0">
                                            <strong>Atenção:</strong> faltam
                                            <strong>{{ $diasAte }}</strong> dia(s) para a entrada
                                            e ainda há valor em aberto da temporada.
                                        </div>
                                    @elseif($dadosTemporada['alerta_30_dias'])
                                        <div class="alert alert-warning mb-0">
                                            <strong>Alerta:</strong> faltam
                                            <strong>{{ $diasAte }}</strong> dia(s) para a entrada
                                            e o valor da temporada ainda não está totalmente pago.
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Parcelas --}}
            <div class="card mb-4">
                <div class="card-header">
                    Parcelas (Aluguel / Caução / Temporada / Outras)
                </div>
                <div class="card-body p-0">
                    @if($contrato->parcelas->isEmpty())
                        <p class="p-3 mb-0">Nenhuma parcela gerada.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Competência</th>
                                        <th>Vencimento</th>
                                        <th>Valor Original</th>
                                        <th>Valor Devido</th>
                                        <th>Valor Pago</th>
                                        <th>Status</th>
                                        <th>Origem</th>
                                        <th style="width: 230px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contrato->parcelas as $parcela)
                                        @php
                                            $rowClass = $parcela->status === 'EM_ATRASO' ? 'table-danger' : '';

                                            $origemLabel = match($parcela->tipo_origem) {
                                                'ALUGUEL_NORMAL'  => 'Aluguel',
                                                'CAUCAO'          => 'Caução',
                                                'ACORDO_ATRASO',
                                                'EM_ACORDO'       => 'Acordo',
                                                'MULTA_RESCISAO'  => 'Multa Rescisão',
                                                'TAXA_EXTRA'      => 'Taxa Extra',
                                                'TEMPORADA'       => 'Temporada',
                                                default           => $parcela->tipo_origem,
                                            };

                                            $badgeClass = '';
                                            switch ($parcela->status) {
                                                case 'ABERTA':            $badgeClass = 'bg-info'; break;
                                                case 'PAGA':              $badgeClass = 'bg-success'; break;
                                                case 'PAGA_PARCIALMENTE': $badgeClass = 'bg-warning text-dark'; break;
                                                case 'EM_ATRASO':         $badgeClass = 'bg-danger'; break;
                                                case 'RENEGOCIADA':       $badgeClass = 'bg-primary'; break;
                                                case 'CANCELADA':         $badgeClass = 'bg-secondary'; break;
                                                case 'EM_ACORDO':         $badgeClass = 'bg-primary'; break;
                                                case 'PERDIDA':           $badgeClass = 'bg-secondary'; break;
                                                case 'JURIDICO':          $badgeClass = 'bg-danger'; break;
                                            }
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td>{{ $parcela->competencia }}</td>
                                            <td>{{ $parcela->data_vencimento?->format('d/m/Y') }}</td>
                                            <td>R$ {{ number_format($parcela->valor_original, 2, ',', '.') }}</td>
                                            <td>R$ {{ number_format($parcela->valor_devido_atual, 2, ',', '.') }}</td>
                                            <td>R$ {{ number_format($parcela->valor_pago, 2, ',', '.') }}</td>
                                            <td>
                                                <span class="badge {{ $badgeClass }}">{{ $parcela->status }}</span>
                                            </td>
                                            <td>{{ $origemLabel }}</td>
                                            <td>
                                                {{-- Ver histórico de pagamentos sempre disponível --}}
                                                <a href="{{ route('pagamentos.parcela.show', $parcela->id) }}"
                                                   class="btn btn-sm btn-outline-primary mb-1">
                                                    Ver Pagamentos
                                                </a>

                                                @if(!in_array($parcela->status, ['PAGA', 'CANCELADA', 'PERDIDA']))
                                                    <a href="{{ route('pagamentos.create', $parcela->id) }}"
                                                       class="btn btn-sm btn-success mb-1">
                                                        Registrar Pagamento
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary mb-1" disabled>
                                                        Pagamento Registrado
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @php
                            $totalDevido = $contrato->parcelas->sum('valor_devido');
                            $totalPago   = $contrato->parcelas->sum('valor_pago');
                            $totalAberto = $totalDevido - $totalPago;
                        @endphp

                        <div class="p-3 border-top bg-light">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Total Devido (base cadastral):</strong><br>
                                    R$ {{ number_format($totalDevido, 2, ',', '.') }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Pago:</strong><br>
                                    R$ {{ number_format($totalPago, 2, ',', '.') }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Total em Aberto (base cadastral):</strong><br>
                                    R$ {{ number_format($totalAberto, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Coluna direita: Situação da Caução e Ações Judiciais --}}
        <div class="col-lg-4">
            {{-- Situação da Caução --}}
            <div class="card mb-4">
                <div class="card-header">
                    Situação da Caução
                </div>
                <div class="card-body">
                    @if(!$contrato->possui_caucao)
                        <p class="mb-0 text-muted">
                            Este contrato <strong>não possui caução</strong>.
                        </p>
                    @else
                        <p>
                            <strong>Valor da Caução:</strong><br>
                            R$ {{ number_format($contrato->valor_caucao ?? 0, 2, ',', '.') }}
                        </p>

                        <p>
                            <strong>Total pago em parcelas de caução:</strong><br>
                            R$ {{ number_format($contrato->total_caucao_pago ?? 0, 2, ',', '.') }}
                        </p>

                        <p>
                            <strong>Caução paga integralmente?</strong><br>
                            @if($contrato->caucao_paga_integralmente)
                                <span class="badge bg-success">Sim</span>
                                @if($contrato->data_pagamento_total_caucao)
                                    <br>
                                    <small class="text-muted">
                                        Quitada em {{ $contrato->data_pagamento_total_caucao?->format('d/m/Y') }}
                                    </small>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark">Ainda não</span>
                            @endif
                        </p>

                        <p>
                            <strong>Caução devolvida?</strong><br>
                            @if($contrato->caucao_devolvida)
                                <span class="badge bg-success">Sim</span>
                                @if($contrato->data_devolucao_caucao)
                                    <br>
                                    <small class="text-muted">
                                        Devolvida em {{ $contrato->data_devolucao_caucao?->format('d/m/Y') }}
                                    </small>
                                @endif
                            @else
                                <span class="badge bg-secondary">Ainda não</span>
                            @endif
                        </p>

                        @if($contrato->motivo_nao_devolucao_caucao)
                            <p>
                                <strong>Motivo / Observações:</strong><br>
                                {!! nl2br(e($contrato->motivo_nao_devolucao_caucao)) !!}
                            </p>
                        @endif

                        @if($contrato->possui_caucao
                            && $contrato->caucao_paga_integralmente
                            && !$contrato->caucao_devolvida)
                            <hr>
                            <a href="{{ route('contratos.caucao.devolucao.form', $contrato->id) }}"
                               class="btn btn-outline-primary w-100">
                                Registrar devolução da caução
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Ações Judiciais --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Ações Judiciais
                    <a href="{{ route('acoes-judiciais.create', ['contrato_id' => $contrato->id]) }}"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Nova Ação
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($contrato->acoesJudiciais->isEmpty())
                        <p class="p-3 mb-0 text-muted">
                            Nenhuma ação judicial registrada para este contrato.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Status</th>
                                        <th>Processo</th>
                                        <th style="width: 120px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contrato->acoesJudiciais as $acao)
                                        @php
                                            $badgeClass = match($acao->status) {
                                                'EM_ANDAMENTO'         => 'bg-warning text-dark',
                                                'ACORDO_REALIZADO'     => 'bg-success',
                                                'ENCERRADA_SEM_ACORDO' => 'bg-danger',
                                                'SUSPENSA'             => 'bg-info',
                                                'ARQUIVADA'            => 'bg-secondary',
                                                default                => 'bg-secondary',
                                            };
                                        @endphp
                                        <tr>
                                            <td>{{ $acao->tipo_acao }}</td>
                                            <td>
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ $acao->status }}
                                                </span>
                                            </td>
                                            <td>{{ $acao->numero_processo ?? 'N/A' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('acoes-judiciais.show', $acao->id) }}"
                                                       class="btn btn-sm btn-outline-info" title="Ver Detalhes">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('acoes-judiciais.edit', $acao->id) }}"
                                                       class="btn btn-sm btn-outline-warning" title="Editar Ação">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
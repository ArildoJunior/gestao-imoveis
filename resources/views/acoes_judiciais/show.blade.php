@extends('layouts.app')

@section('title', 'Detalhes da Ação Judicial')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Detalhes da Ação Judicial #{{ $acaoJudicial->id }}</h1>
        <div>
            <a href="{{ route('acoes-judiciais.edit', $acaoJudicial->id) }}" class="btn btn-warning me-2">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <form action="{{ route('acoes-judiciais.destroy', $acaoJudicial->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Tem certeza que deseja excluir esta ação judicial? Esta ação é irreversível.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash me-1"></i> Excluir
                </button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Informações da Ação Judicial
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Contrato:</strong>
                        @if($acaoJudicial->contrato)
                            <a href="{{ route('contratos.show', $acaoJudicial->contrato->id) }}" class="text-decoration-none">
                                {{ $acaoJudicial->contrato->codigo }} - {{ $acaoJudicial->contrato->locatario->nome ?? 'N/A' }}
                            </a>
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Tipo de Ação:</strong> {{ $options['tiposAcao'][$acaoJudicial->tipo_acao] ?? $acaoJudicial->tipo_acao }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    @php
                        $badgeClass = match($acaoJudicial->status) {
                            'EM_ANDAMENTO'         => 'bg-warning text-dark',
                            'ACORDO_REALIZADO'     => 'bg-success',
                            'ENCERRADA_SEM_ACORDO' => 'bg-danger',
                            'SUSPENSA'             => 'bg-info',
                            default                => 'bg-secondary',
                        };
                    @endphp
                    <p class="mb-1"><strong>Status:</strong> <span class="badge {{ $badgeClass }}">{{ $options['statusAcao'][$acaoJudicial->status] ?? $acaoJudicial->status }}</span></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Número do Processo:</strong> {{ $acaoJudicial->numero_processo ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Vara:</strong> {{ $acaoJudicial->vara ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Comarca:</strong> {{ $acaoJudicial->comarca ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Advogado:</strong> {{ $acaoJudicial->advogado_nome ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <p class="mb-1"><strong>Telefone Advogado:</strong> {{ $acaoJudicial->advogado_telefone ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Valor Cobrado:</strong> R$ {{ number_format($acaoJudicial->valor_cobrado ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Valor Recuperado:</strong> R$ {{ number_format($acaoJudicial->valor_recuperado ?? 0, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <p class="mb-1"><strong>Custo Advocatício:</strong> R$ {{ number_format($acaoJudicial->custo_advocaticio ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Imóvel Devolvido:</strong> {{ $acaoJudicial->imovel_devolvido ? 'Sim' : 'Não' }}</p>
                </div>
                @if($acaoJudicial->imovel_devolvido)
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Data Entrega Chaves:</strong> {{ $acaoJudicial->data_entrega_chaves?->format('d/m/Y') ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Condição Imóvel:</strong> {{ $options['condicoesImovel'][$acaoJudicial->condicao_imovel_entrega] ?? 'N/A' }}</p>
                    </div>
                @endif
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <p class="mb-1"><strong>Houve Acordo:</strong> {{ $acaoJudicial->houve_acordo ? 'Sim' : 'Não' }}</p>
                </div>
                @if($acaoJudicial->houve_acordo)
                    <div class="col-md-8">
                        <p class="mb-1"><strong>Descrição Acordo:</strong> {{ $acaoJudicial->descricao_acordo ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Valor Acordo:</strong> R$ {{ number_format($acaoJudicial->valor_acordo ?? 0, 2, ',', '.') }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Parcelas Acordo:</strong> {{ $acaoJudicial->parcelas_acordo ?? 'N/A' }}</p>
                    </div>
                @endif
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <p class="mb-1"><strong>Novo Contrato Após Decisão:</strong> {{ $acaoJudicial->novo_contrato_apos_decisao ? 'Sim' : 'Não' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Data Encerramento:</strong> {{ $acaoJudicial->data_encerramento?->format('d/m/Y') ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <p class="mb-1"><strong>Observações:</strong> {{ $acaoJudicial->observacoes ?? 'N/A' }}</p>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4">
                    <p class="mb-1 text-muted"><strong>Registrado Por:</strong> {{ $acaoJudicial->registradoPor->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-muted"><strong>Criado Em:</strong> {{ $acaoJudicial->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-muted"><strong>Última Atualização:</strong> {{ $acaoJudicial->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('acoes-judiciais.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Lista
    </a>
@endsection
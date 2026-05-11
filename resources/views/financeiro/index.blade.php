@extends('layouts.app')

@section('title', 'Controle Financeiro - Parcelas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Controle Financeiro - Parcelas de Aluguel</h1>
    </div>

    {{-- Formulário de Filtros --}}
    <div class="card mb-4">
        <div class="card-header">Filtros</div>
        <div class="card-body">
            <form action="{{ route('financeiro.index') }}" method="GET" id="formFiltros">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="imovel_id" class="form-label">Imóvel</label>
                        <select name="imovel_id" id="imovel_id" class="form-select">
                            <option value="">Todos os Imóveis</option>
                            @foreach($imoveis as $imovel)
                                <option value="{{ $imovel->id }}"
                                    {{ request('imovel_id') == $imovel->id ? 'selected' : '' }}>
                                    {{ $imovel->descricao }} ({{ $imovel->cidade }}/{{ $imovel->estado }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="contrato_id" class="form-label">Contrato</label>
                        <select name="contrato_id" id="contrato_id" class="form-select">
                            <option value="">Todos os Contratos</option>
                            @foreach($contratos as $contrato)
                                <option value="{{ $contrato->id }}"
                                    {{ request('contrato_id') == $contrato->id ? 'selected' : '' }}>
                                    {{ $contrato->codigo }} ({{ $contrato->locatario->nome ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Todos os Status</option>
                            @foreach($statusPossiveis as $key => $label)
                                <option value="{{ $key }}"
                                    {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="competencia" class="form-label">Competência (AAAA-MM)</label>
                        <input type="month" name="competencia" id="competencia"
                               class="form-control" value="{{ request('competencia') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="vencimento_de" class="form-label">Vencimento De</label>
                        <input type="date" name="vencimento_de" id="vencimento_de"
                               class="form-control" value="{{ request('vencimento_de') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="vencimento_ate" class="form-label">Vencimento Até</label>
                        <input type="date" name="vencimento_ate" id="vencimento_ate"
                               class="form-control" value="{{ request('vencimento_ate') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                        <a href="{{ route('financeiro.index') }}" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($parcelas->isEmpty())
        <div class="alert alert-info">Nenhuma parcela encontrada com os filtros aplicados.</div>
    @else

        {{-- Formulário de Renegociação (GET para renegociacoes.create) --}}
        <form method="GET" action="{{ route('renegociacoes.create') }}" id="formRenegociacao">

            {{-- Barra de ação de renegociação --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="text-muted small">
                        Selecione parcelas com status <strong>Aberta</strong> ou
                        <strong>Em Atraso</strong> para renegociar.
                    </span>
                </div>
                <button type="submit"
                        class="btn btn-warning"
                        id="btnIniciarRenegociacao"
                        disabled
                        title="Selecione ao menos uma parcela elegível">
                    <i class="bi bi-arrow-repeat"></i>
                    Iniciar Renegociação com Selecionadas
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            {{-- Checkbox "Selecionar Todos" --}}
                            <th style="width: 40px;">
                                <input type="checkbox"
                                       id="selecionarTodos"
                                       class="form-check-input"
                                       title="Selecionar todas as parcelas elegíveis">
                            </th>
                            <th>ID</th>
                            <th>Imóvel</th>
                            <th>Contrato</th>
                            <th>Locatário</th>
                            <th>Competência</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th>Origem</th>
                            <th>Valor Original</th>
                            <th>Devido</th>
                            <th>Pago</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($parcelas as $parcela)
                            @php
                                $elegivel = in_array($parcela->status, ['ABERTA', 'EM_ATRASO']);
                                $badge = 'secondary';
                                switch ($parcela->status) {
                                    case 'ABERTA':            $badge = 'warning';   break;
                                    case 'EM_ATRASO':         $badge = 'danger';    break;
                                    case 'PAGA_PARCIALMENTE': $badge = 'info';      break;
                                    case 'PAGA':              $badge = 'success';   break;
                                    case 'RENEGOCIADA':       $badge = 'primary';   break;
                                    case 'EM_ACORDO':         $badge = 'primary';   break;
                                    case 'CANCELADA':         $badge = 'dark';      break;
                                    case 'PERDIDA':           $badge = 'secondary'; break;
                                    case 'JURIDICO':          $badge = 'danger';    break;
                                }
                                $rowClass = $parcela->status === 'EM_ATRASO' ? 'table-danger' : '';

                                // Label amigável da origem
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
                            @endphp
                            <tr class="{{ $rowClass }}">
                                {{-- Checkbox por linha --}}
                                <td>
                                    <input type="checkbox"
                                           name="parcelas_ids[]"
                                           value="{{ $parcela->id }}"
                                           class="form-check-input checkbox-parcela"
                                           data-contrato-id="{{ $parcela->contrato_id }}"
                                           {{ $elegivel ? '' : 'disabled' }}
                                           title="{{ $elegivel ? 'Selecionar para renegociação' : 'Status ' . $parcela->status . ' não permite renegociação' }}">
                                </td>
                                <td>{{ $parcela->id }}</td>
                                <td>
                                    {{ $parcela->contrato->imovel->descricao ?? 'N/A' }}<br>
                                    <small>
                                        {{ $parcela->contrato->imovel->cidade ?? '' }}/{{ $parcela->contrato->imovel->estado ?? '' }}
                                    </small>
                                </td>
                                <td>{{ $parcela->contrato->codigo ?? 'N/A' }}</td>
                                <td>{{ $parcela->contrato->locatario->nome ?? 'N/A' }}</td>
                                <td>{{ $parcela->competencia }}</td>
                                <td>{{ $parcela->data_vencimento?->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $badge }}">
                                        {{ $parcela->status }}
                                    </span>
                                </td>
                                <td>{{ $origemLabel }}</td>
                                <td>R$ {{ number_format($parcela->valor_original, 2, ',', '.') }}</td>
                                {{-- Devido agora usa valor_devido_atual --}}
                                <td>R$ {{ number_format($parcela->valor_devido_atual, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($parcela->valor_pago, 2, ',', '.') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle"
                                                type="button"
                                                id="dropdownMenuButton{{ $parcela->id }}"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            Ações
                                        </button>
                                        <ul class="dropdown-menu"
                                            aria-labelledby="dropdownMenuButton{{ $parcela->id }}">
                                            <li>
                                                <a href="{{ route('contratos.show', $parcela->contrato_id) }}"
                                                   class="dropdown-item">
                                                    Ver Contrato
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('pagamentos.parcela.show', $parcela->id) }}"
                                                   class="dropdown-item">
                                                    Ver Pagamentos
                                                </a>
                                            </li>

                                            @if(in_array($parcela->status, ['ABERTA', 'EM_ATRASO', 'PAGA_PARCIALMENTE']))
                                                <li>
                                                    <a href="{{ route('pagamentos.create', $parcela->id) }}"
                                                       class="dropdown-item">
                                                        Registrar Pagamento
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('financeiro.parcelas.acordo', $parcela->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Confirmar que esta parcela está em acordo?');">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            Marcar como Acordo
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('financeiro.parcelas.juridico', $parcela->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Confirmar envio para o Jurídico?');">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            Enviar para Jurídico
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('financeiro.parcelas.perdida', $parcela->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Confirmar que esta parcela está perdida?');">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            Marcar como Perdida
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('financeiro.parcelas.cancelar', $parcela->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Confirmar cancelamento desta parcela?');">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            Cancelar Parcela
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </form>

        {{-- Contador de selecionadas e aviso de contrato misto --}}
        <div class="d-flex justify-content-between align-items-center mt-1 mb-3">
            <small class="text-muted" id="contadorSelecionadas">
                0 parcela(s) selecionada(s)
            </small>
            <div id="avisoContratoMisto" class="alert alert-warning py-1 px-2 mb-0 small d-none">
                ⚠️ Você selecionou parcelas de contratos diferentes.
                A renegociação só permite parcelas de <strong>um único contrato</strong>.
            </div>
        </div>

        {{ $parcelas->links() }}

    @endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const formRenegociacao    = document.getElementById('formRenegociacao');
    const btnIniciar          = document.getElementById('btnIniciarRenegociacao');
    const chkTodos            = document.getElementById('selecionarTodos');
    const contador            = document.getElementById('contadorSelecionadas');
    const avisoMisto          = document.getElementById('avisoContratoMisto');

    function getCheckboxes() {
        return document.querySelectorAll('.checkbox-parcela:not([disabled])');
    }

    function getMarcados() {
        return document.querySelectorAll('.checkbox-parcela:not([disabled]):checked');
    }

    function atualizarEstado() {
        const marcados = getMarcados();
        const qtd = marcados.length;

        contador.textContent = qtd + ' parcela(s) selecionada(s)';

        const contratosIds = new Set();
        marcados.forEach(function(chk) {
            const contratoId = chk.dataset.contratoId;
            if (contratoId) contratosIds.add(contratoId);
        });

        const contratoMisto = contratosIds.size > 1;

        if (avisoMisto) {
            avisoMisto.classList.toggle('d-none', !contratoMisto);
        }

        btnIniciar.disabled = (qtd === 0 || contratoMisto);

        const todos = getCheckboxes();
        if (todos.length > 0) {
            chkTodos.indeterminate = (qtd > 0 && qtd < todos.length);
            chkTodos.checked       = (qtd === todos.length);
        }
    }

    if (chkTodos) {
        chkTodos.addEventListener('change', function () {
            getCheckboxes().forEach(function(chk) {
                chk.checked = chkTodos.checked;
            });
            atualizarEstado();
        });
    }

    document.querySelectorAll('.checkbox-parcela:not([disabled])').forEach(function(chk) {
        chk.addEventListener('change', atualizarEstado);
    });

    if (formRenegociacao) {
        formRenegociacao.addEventListener('submit', function(e) {
            const marcados = getMarcados();
            if (marcados.length === 0) {
                e.preventDefault();
                alert('Selecione ao menos uma parcela para iniciar a renegociação.');
                return false;
            }
        });
    }

    atualizarEstado();
});
</script>
@endpush
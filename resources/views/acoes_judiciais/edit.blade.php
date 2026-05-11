<?php // resources/views/acoes_judiciais/edit.blade.php ?>

@extends('layouts.app')

@section('title', 'Editar Ação Judicial')

@section('content')
    <h2 class="mb-4">Editar Ação Judicial</h2>

    <div class="card mb-4">
        <div class="card-body">

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('acoes-judiciais.update', $acao_judicial) }}">
                @csrf
                @method('PUT')

                {{-- Contrato --}}
                <div class="mb-3">
                    <label for="contrato_id" class="form-label">Contrato</label>
                    <select id="contrato_id" name="contrato_id" class="form-select" required>
                        <option value="">Selecione um contrato</option>
                        @foreach($contratos as $contrato)
                            <option value="{{ $contrato->id }}"
                                {{ old('contrato_id', $acao_judicial->contrato_id) == $contrato->id ? 'selected' : '' }}>
                                {{ $contrato->codigo }} - {{ $contrato->locatario->nome ?? 'N/A' }} ({{ $contrato->imovel->descricao ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('contrato_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tipo de Ação --}}
                <div class="mb-3">
                    <label for="tipo_acao" class="form-label">Tipo de Ação</label>
                    <select id="tipo_acao" name="tipo_acao" class="form-select" required>
                        <option value="">Selecione o tipo</option>
                        @foreach($options['tiposAcao'] as $key => $label)
                            <option value="{{ $key }}"
                                {{ old('tipo_acao', $acao_judicial->tipo_acao) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_acao')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="">Selecione o status</option>
                        @foreach($options['statusAcao'] as $key => $label)
                            <option value="{{ $key }}"
                                {{ old('status', $acao_judicial->status) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Número do Processo --}}
                <div class="mb-3">
                    <label for="numero_processo" class="form-label">Número do Processo</label>
                    <input id="numero_processo" name="numero_processo" type="text"
                                  class="form-control"
                                  value="{{ old('numero_processo', $acao_judicial->numero_processo) }}" />
                    @error('numero_processo')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Vara e Comarca --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vara" class="form-label">Vara</label>
                        <input id="vara" name="vara" type="text"
                                      class="form-control"
                                      value="{{ old('vara', $acao_judicial->vara) }}" />
                        @error('vara')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="comarca" class="form-label">Comarca</label>
                        <input id="comarca" name="comarca" type="text"
                                      class="form-control"
                                      value="{{ old('comarca', $acao_judicial->comarca) }}" />
                        @error('comarca')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Advogado --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="advogado_nome" class="form-label">Nome do Advogado</label>
                        <input id="advogado_nome" name="advogado_nome" type="text"
                                      class="form-control"
                                      value="{{ old('advogado_nome', $acao_judicial->advogado_nome) }}" />
                        @error('advogado_nome')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="advogado_telefone" class="form-label">Telefone do Advogado</label>
                        <input id="advogado_telefone" name="advogado_telefone" type="text"
                                      class="form-control"
                                      value="{{ old('advogado_telefone', $acao_judicial->advogado_telefone) }}" />
                        @error('advogado_telefone')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Valores --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="valor_cobrado" class="form-label">Valor Cobrado (R$)</label>
                        <input id="valor_cobrado" name="valor_cobrado" type="number"
                                      step="0.01" class="form-control" required
                                      value="{{ old('valor_cobrado', $acao_judicial->valor_cobrado) }}" />
                        @error('valor_cobrado')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="valor_recuperado" class="form-label">Valor Recuperado (R$)</label>
                        <input id="valor_recuperado" name="valor_recuperado" type="number"
                                      step="0.01" class="form-control"
                                      value="{{ old('valor_recuperado', $acao_judicial->valor_recuperado) }}" />
                        @error('valor_recuperado')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="custo_advocaticio" class="form-label">Custo Advocatício (R$)</label>
                        <input id="custo_advocaticio" name="custo_advocaticio" type="number"
                                      step="0.01" class="form-control"
                                      value="{{ old('custo_advocaticio', $acao_judicial->custo_advocaticio) }}" />
                        @error('custo_advocaticio')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Imóvel Devolvido? (Select Sim/Não) --}}
                <div class="mb-3">
                    <label for="imovel_devolvido" class="form-label">Imóvel Devolvido?</label>
                    <select id="imovel_devolvido" name="imovel_devolvido" class="form-select" required>
                        <option value="0" {{ old('imovel_devolvido', $acao_judicial->imovel_devolvido) == '0' ? 'selected' : '' }}>Não</option>
                        <option value="1" {{ old('imovel_devolvido', $acao_judicial->imovel_devolvido) == '1' ? 'selected' : '' }}>Sim</option>
                    </select>
                    @error('imovel_devolvido')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Bloco de Devolução de Imóvel (condicional) --}}
                <div id="bloco_devolucao_imovel" class="mb-3 p-3 border rounded" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="data_entrega_chaves" class="form-label">Data de Entrega das Chaves</label>
                            <input id="data_entrega_chaves" name="data_entrega_chaves" type="date"
                                          class="form-control"
                                          value="{{ old('data_entrega_chaves', $acao_judicial->data_entrega_chaves?->format('Y-m-d')) }}" />
                            @error('data_entrega_chaves')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="condicao_imovel_entrega" class="form-label">Condição do Imóvel na Entrega</label>
                            <select id="condicao_imovel_entrega" name="condicao_imovel_entrega" class="form-select">
                                <option value="">Selecione a condição</option>
                                @foreach($options['condicoesImovel'] as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('condicao_imovel_entrega', $acao_judicial->condicao_imovel_entrega) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('condicao_imovel_entrega')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Houve Acordo? (Select Sim/Não) --}}
                <div class="mb-3">
                    <label for="houve_acordo" class="form-label">Houve Acordo?</label>
                    <select id="houve_acordo" name="houve_acordo" class="form-select" required>
                        <option value="0" {{ old('houve_acordo', $acao_judicial->houve_acordo) == '0' ? 'selected' : '' }}>Não</option>
                        <option value="1" {{ old('houve_acordo', $acao_judicial->houve_acordo) == '1' ? 'selected' : '' }}>Sim</option>
                    </select>
                    @error('houve_acordo')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Bloco de Acordo (condicional) --}}
                <div id="bloco_acordo" class="mb-3 p-3 border rounded" style="display: none;">
                    <div class="mb-3">
                        <label for="descricao_acordo" class="form-label">Descrição do Acordo</label>
                        <textarea id="descricao_acordo" name="descricao_acordo" class="form-control">{{ old('descricao_acordo', $acao_judicial->descricao_acordo) }}</textarea>
                        @error('descricao_acordo')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="valor_acordo" class="form-label">Valor do Acordo (R$)</label>
                            <input id="valor_acordo" name="valor_acordo" type="number"
                                          step="0.01" class="form-control"
                                          value="{{ old('valor_acordo', $acao_judicial->valor_acordo) }}" />
                            @error('valor_acordo')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="parcelas_acordo" class="form-label">Número de Parcelas do Acordo</label>
                            <input id="parcelas_acordo" name="parcelas_acordo" type="number"
                                          class="form-control"
                                          value="{{ old('parcelas_acordo', $acao_judicial->parcelas_acordo) }}" />
                            @error('parcelas_acordo')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Novo Contrato Após Decisão? (Select Sim/Não) --}}
                <div class="mb-3">
                    <label for="novo_contrato_apos_decisao" class="form-label">Novo Contrato Após Decisão?</label>
                    <select id="novo_contrato_apos_decisao" name="novo_contrato_apos_decisao" class="form-select" required>
                        <option value="0" {{ old('novo_contrato_apos_decisao', $acao_judicial->novo_contrato_apos_decisao) == '0' ? 'selected' : '' }}>Não</option>
                        <option value="1" {{ old('novo_contrato_apos_decisao', $acao_judicial->novo_contrato_apos_decisao) == '1' ? 'selected' : '' }}>Sim</option>
                    </select>
                    @error('novo_contrato_apos_decisao')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Data de Encerramento --}}
                <div class="mb-3">
                    <label for="data_encerramento" class="form-label">Data de Encerramento da Ação</label>
                    <input id="data_encerramento" name="data_encerramento" type="date"
                                  class="form-control"
                                  value="{{ old('data_encerramento', $acao_judicial->data_encerramento?->format('Y-m-d')) }}" />
                    @error('data_encerramento')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Observações --}}
                <div class="mb-3">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea id="observacoes" name="observacoes" class="form-control">{{ old('observacoes', $acao_judicial->observacoes) }}</textarea>
                    @error('observacoes')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('acoes-judiciais.index') }}" class="btn btn-secondary me-2">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Atualizar Ação Judicial
                    </button>
                </div>
            </form>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const imovelDevolvidoSelect = document.getElementById('imovel_devolvido');
                const blocoDevolucaoImovel = document.getElementById('bloco_devolucao_imovel');
                const houveAcordoSelect = document.getElementById('houve_acordo');
                const blocoAcordo = document.getElementById('bloco_acordo');

                function toggleBlocoDevolucao() {
                    if (imovelDevolvidoSelect.value === '1') { // '1' para Sim
                        blocoDevolucaoImovel.style.display = 'block';
                    } else {
                        blocoDevolucaoImovel.style.display = 'none';
                    }
                }

                function toggleBlocoAcordo() {
                    if (houveAcordoSelect.value === '1') { // '1' para Sim
                        blocoAcordo.style.display = 'block';
                    } else {
                        blocoAcordo.style.display = 'none';
                    }
                }

                imovelDevolvidoSelect.addEventListener('change', toggleBlocoDevolucao);
                houveAcordoSelect.addEventListener('change', toggleBlocoAcordo);

                // Chama as funções no carregamento inicial para exibir/ocultar com base nos valores existentes
                toggleBlocoDevolucao();
                toggleBlocoAcordo();
            });
        </script>
    @endpush
@endsection
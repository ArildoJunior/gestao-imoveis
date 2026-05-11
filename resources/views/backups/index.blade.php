{{-- resources/views/backups/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Histórico de Backups') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensagens de sucesso/erro --}}
            @if (session('success'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Cabeçalho e botão --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6 text-gray-900 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium">Backups do sistema</h3>
                        <p class="text-sm text-gray-600">
                            Histórico de backups executados (automáticos e manuais).
                        </p>
                    </div>
                    <form method="POST" action="{{ route('backups.store') }}">
                        @csrf
                        <x-primary-button>
                            Executar backup manual agora
                        </x-primary-button>
                    </form>
                </div>
            </div>

            {{-- Tabela de backups --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-3 py-2 text-left">Data</th>
                                <th class="px-3 py-2 text-left">Tipo</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-left">Usuário</th>
                                <th class="px-3 py-2 text-left">Caminho</th>
                                <th class="px-3 py-2 text-left">Tamanho</th>
                                <th class="px-3 py-2 text-left">Erro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($backups as $backup)
                                <tr class="border-b">
                                    <td class="px-3 py-2">
                                        {{ optional($backup->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-1 rounded text-xs
                                            @if($backup->tipo === 'AUTOMATICO') bg-blue-100 text-blue-800
                                            @elseif($backup->tipo === 'MANUAL') bg-gray-100 text-gray-800
                                            @else bg-yellow-100 text-yellow-800
                                            @endif
                                        ">
                                            {{ $backup->tipo }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-1 rounded text-xs
                                            @if($backup->status === 'SUCESSO') bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800
                                            @endif
                                        ">
                                            {{ $backup->status }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ optional($backup->usuario)->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $backup->caminho_arquivo ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @if($backup->tamanho_bytes)
                                            {{ number_format($backup->tamanho_bytes / 1024 / 1024, 2, ',', '.') }} MB
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs text-red-700">
                                        @if($backup->mensagem_erro)
                                            {{ strlen($backup->mensagem_erro) > 80
                                                ? substr($backup->mensagem_erro, 0, 77) . '...'
                                                : $backup->mensagem_erro }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-4 text-center text-gray-500">
                                        Nenhum backup registrado até o momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $backups->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
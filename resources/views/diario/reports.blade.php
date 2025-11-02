{{-- resources/views/diario/reports.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Relatórios de obra') }}
            </h2>
            <a href="{{ route('diario.index') }}"
               class="text-sm text-indigo-600 hover:text-indigo-800">
                ← Voltar a gravar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- alertas de sessão (sucesso/erro ao atualizar) --}}
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            {{-- filtros --}}
            <form method="GET" class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Obra</label>
                    <select name="obra" class="rounded-md border-gray-300 text-sm">
                        <option value="">Todas</option>
                        @foreach ($availableSites as $siteName)
                            <option value="{{ $siteName }}" @selected(request('obra') == $siteName)>
                                {{ $siteName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">De</label>
                    <input type="date" name="de" value="{{ request('de') }}" class="rounded-md border-gray-300 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Até</label>
                    <input type="date" name="ate" value="{{ request('ate') }}" class="rounded-md border-gray-300 text-sm">
                </div>

                <div class="flex items-center gap-2">
                    <button class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm rounded-md">
                        Filtrar
                    </button>
                    @if (request()->hasAny(['obra','de','ate']))
                        <a href="{{ route('diario.reports') }}" class="inline-flex items-center px-3 py-2 border text-sm rounded-md">
                            Limpar
                        </a>
                    @endif
                </div>
            </form>

            {{-- cards rápidos --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <p class="text-xs text-gray-500 uppercase">Total de registos</p>
                    <p class="text-2xl font-semibold mt-1">
                        {{ $entries->total() }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <p class="text-xs text-gray-500 uppercase">Último registo</p>
                    <p class="text-sm mt-1">
                        @if ($entries->count())
                            {{ $entries->first()->entry_date?->format('d/m/Y') ?? '—' }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <p class="text-xs text-gray-500 uppercase">Obras diferentes</p>
                    <p class="text-2xl font-semibold mt-1">
                        {{ $availableSites->count() }}
                    </p>
                </div>
            </div>

            {{-- tabela --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Obra</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Utilizador</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Trabalhos</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ocorrências</th>
                                {{-- ✨ nova coluna --}}
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>

                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($entries as $entry)
                                @php
                                    $p = $entry->payload ?? [];
                                    $trabs = $p['trabalhos_executados'] ?? [];
                                    $ocors = $p['ocorrencias'] ?? [];
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ $entry->entry_date?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $entry->site_name ?: '—' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $entry->user?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($trabs)
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach($trabs as $t)
                                                    <li>{{ $t }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($ocors)
                                            <ul class="list-disc list-inside space-y-1 text-rose-600">
                                                @foreach($ocors as $o)
                                                    <li>{{ $o }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    {{-- ✨ ações --}}
                                 <td class="px-4 py-2">
    <div class="flex items-center gap-2">
        <a href="{{ route('diario.reports.edit', $entry) }}"
           class="inline-flex items-center px-2.5 py-1.5 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-500">
            Editar
        </a>

        <form method="POST" action="{{ route('diario.reports.destroy', $entry) }}"
              onsubmit="return confirm('Apagar este diário? Esta ação é irreversível.');">
            @csrf
            @method('DELETE')
            <button class="inline-flex items-center px-2.5 py-1.5 text-xs bg-rose-600 text-white rounded hover:bg-rose-500">
                Apagar
            </button>
        </form>
    </div>
</td>


                                
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">
                                        Ainda não há diários guardados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3">
                    {{ $entries->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

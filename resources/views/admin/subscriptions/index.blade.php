<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Gestão de subscrições
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- MÉTRICAS --}}
            <div class="grid gap-4 md:grid-cols-4">
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Total</p>
                    <p class="text-2xl font-bold">{{ $metrics['total'] }}</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Ativas</p>
                    <p class="text-2xl font-bold text-green-600">{{ $metrics['active'] }}</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Trial</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ $metrics['trial'] }}</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Expiradas/Canceladas</p>
                    <p class="text-2xl font-bold text-red-500">{{ $metrics['expired'] }}</p>
                </div>
            </div>

            {{-- FILTROS --}}
            <div class="bg-white rounded-lg p-4 shadow">
                <form method="GET" class="grid gap-4 md:grid-cols-4 items-end">
                    {{-- Estado --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" class="w-full border rounded px-3 py-2">
                            <option value="">(todos)</option>
                            <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Ativos</option>
                            <option value="trial" {{ ($filters['status'] ?? '') === 'trial' ? 'selected' : '' }}>Só trial</option>
                            <option value="expired" {{ ($filters['status'] ?? '') === 'expired' ? 'selected' : '' }}>Expirados</option>
                            <option value="canceled" {{ ($filters['status'] ?? '') === 'canceled' ? 'selected' : '' }}>Cancelados</option>
                        </select>
                    </div>

                    {{-- Plano --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plano</label>
                        <select name="plan" class="w-full border rounded px-3 py-2">
                            <option value="">(todos)</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" {{ ($filters['plan'] ?? '') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Renova até --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Renova até</label>
                        <input type="date" name="renews_to"
                               value="{{ $filters['renews_to'] ?? '' }}"
                               class="w-full border rounded px-3 py-2">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                            Filtrar
                        </button>
                        <a href="{{ route('admin.subscriptions') }}"
                           class="px-4 py-2 border rounded text-gray-700">
                            Limpar
                        </a>
                    </div>
                </form>
            </div>

            {{-- TABELA --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-2 px-3 border-b">#</th>
                            <th class="py-2 px-3 border-b">Utilizador</th>
                            <th class="py-2 px-3 border-b">Empresa</th>
                            <th class="py-2 px-3 border-b">Plano</th>
                            <th class="py-2 px-3 border-b">Estado</th>
                            <th class="py-2 px-3 border-b">Criado</th>
                            <th class="py-2 px-3 border-b">Renova</th>
                            <th class="py-2 px-3 border-b"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subs as $sub)
                            @php
                                $renova = $sub->renews_at;
                                $faltamDias = $renova ? now()->diffInDays($renova, false) : null;

                                $estado = $sub->status;
                                $estadoClass = match (true) {
                                    $estado === 'active' && (!$renova || $renova->isFuture()) => 'bg-green-100 text-green-800',
                                    $sub->plan && $sub->plan->slug === 'trial' => 'bg-indigo-100 text-indigo-800',
                                    $estado === 'canceled' || ($renova && $renova->isPast()) => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3 border-b">{{ $sub->id }}</td>
                                <td class="py-2 px-3 border-b">
                                    {{ $sub->company?->owner?->name ?? '—' }} <br>
                                    <span class="text-xs text-gray-500">{{ $sub->company?->owner?->email ?? '' }}</span>
                                </td>
                                <td class="py-2 px-3 border-b">
                                    {{ $sub->company?->name ?? '—' }}
                                </td>
                                <td class="py-2 px-3 border-b">
                                    {{ $sub->plan?->name ?? '—' }}
                                </td>
                                <td class="py-2 px-3 border-b">
                                    <span class="inline-block px-2 py-1 rounded text-xs {{ $estadoClass }}">
                                        @if ($sub->plan && $sub->plan->slug === 'trial')
                                            trial
                                        @else
                                            {{ $estado }}
                                        @endif
                                    </span>
                                    @if ($faltamDias !== null)
                                        <div class="text-xs text-gray-500 mt-1">
                                            @if ($faltamDias > 0)
                                                {{ $faltamDias }} dia(s) restantes
                                            @elseif ($faltamDias === 0)
                                                termina hoje
                                            @else
                                                expirou há {{ abs($faltamDias) }} dia(s)
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="py-2 px-3 border-b">
                                    {{ $sub->created_at?->format('Y-m-d H:i') }}
                                </td>
                                <td class="py-2 px-3 border-b">
                                    {{ $renova?->format('Y-m-d H:i') ?? '—' }}
                                </td>
                                <td class="py-2 px-3 border-b text-right">
                                    @if ($sub->company?->owner)
                                        <a href="{{ route('admin.users.edit', $sub->company->owner) }}"
                                           class="text-indigo-600 text-xs underline">
                                            Ver utilizador
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-4 text-center text-gray-400">
                                    Nenhum resultado com estes filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- paginação --}}
                <div class="p-4">
                    {{ $subs->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Área do SaaS
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-900 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-200 text-red-900 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white p-6 rounded shadow space-y-2">
                <p>Empresa: <strong>{{ $company->name }}</strong></p>
                <p>Plano atual: <strong>{{ $subscription->plan->name }}</strong></p>

                @php
                    $renewsAt = $subscription->renews_at;
                    $diasRestantes = $renewsAt ? now()->diffInDays($renewsAt, false) : null;
                @endphp

                @if ($renewsAt)
                    <p>Renova em: <strong>{{ $renewsAt->format('Y-m-d H:i') }}</strong></p>

                    @if ($diasRestantes > 0)
                        <p class="text-sm text-gray-600">
                            Faltam <strong>{{ $diasRestantes }}</strong> dia(s) do teu período atual.
                        </p>
                    @elseif ($diasRestantes === 0)
                        <p class="text-sm text-orange-600">
                            ⚠️ O teu plano/trial termina hoje.
                        </p>
                    @else
                        <p class="text-sm text-red-600">
                            ⚠️ O teu plano/trial terminou. Escolhe um plano.
                        </p>
                    @endif
                @else
                    <p class="text-sm text-gray-500">Sem data de renovação definida.</p>
                @endif
            </div>

            <div>
                <a href="{{ route('plans.index') }}"
                   class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Mudar de plano
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

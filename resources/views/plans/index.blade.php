<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Escolhe o teu plano
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <p class="text-gray-600 mb-6">
                Escolhe um plano para poderes continuar a usar a aplicação.
            </p>

            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($plans as $plan)
                    <div class="bg-white rounded-xl shadow p-6 flex flex-col">
                        <h3 class="text-lg font-semibold mb-2">{{ $plan->name }}</h3>
                        <p class="text-sm text-gray-500 mb-4 capitalize">
                            @if ($plan->slug === 'trial')
                                Período experimental (7 dias)
                            @else
                                Faturação {{ $plan->interval }}
                            @endif
                        </p>

                        <div class="text-3xl font-bold mb-4">
                            @if ($plan->price_cents == 0)
                                €0
                            @else
                                €{{ number_format($plan->price_cents / 100, 2, ',', '.') }}
                                <span class="text-sm text-gray-400">/mês</span>
                            @endif
                        </div>

                        @if (is_array($plan->features))
                            <ul class="mb-6 space-y-1 text-sm text-gray-600">
                                @foreach ($plan->features as $feat)
                                    <li>• {{ $feat }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mb-6 text-sm text-gray-500">
                                Acesso às funcionalidades base.
                            </p>
                        @endif

                        <form action="{{ route('plans.choose', $plan) }}" method="POST" class="mt-auto">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 rounded-lg
                                           @if ($plan->price_cents == 0)
                                                bg-gray-900 text-white hover:bg-gray-800
                                           @else
                                                bg-indigo-600 text-white hover:bg-indigo-700
                                           @endif
                                           transition">
                                Escolher
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>

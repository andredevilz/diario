<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Utilizadores
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th class="py-2 border-b">ID</th>
                            <th class="py-2 border-b">Nome</th>
                            <th class="py-2 border-b">Email</th>
                            <th class="py-2 border-b">Telemóvel</th>
                            <th class="py-2 border-b">Empresa</th>
                            <th class="py-2 border-b">Plano</th>
                            <th class="py-2 border-b">Renova em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="py-1 border-b">{{ $user->id }}</td>
                                <td class="py-1 border-b">{{ $user->name }}</td>
                                <td class="py-1 border-b">{{ $user->email }}</td>
                                <td class="py-1 border-b">{{ $user->phone ?? '—' }}</td>
                                <td class="py-1 border-b">{{ $user->company?->name ?? '—' }}</td>
                                <td class="py-1 border-b">
                                    {{ $user->company?->currentSubscription?->plan?->name ?? '—' }}
                                </td>
                                <td class="py-1 border-b">
                                    {{ optional($user->company?->currentSubscription?->renews_at)->format('Y-m-d') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 text-center text-gray-500">
                                    Ainda não há utilizadores.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

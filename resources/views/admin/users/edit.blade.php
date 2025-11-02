<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Editar utilizador
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block mb-1" for="name">Nome</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                               class="w-full border rounded px-3 py-2">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1" for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                               class="w-full border rounded px-3 py-2">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1" for="phone">Telemóvel</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                               class="w-full border rounded px-3 py-2">
                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1" for="role">Tipo</label>
                        <select id="role" name="role" class="w-full border rounded px-3 py-2">
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1" for="password">Password (deixa vazio p/ não mudar)</label>
                        <input id="password" name="password" type="password"
                               class="w-full border rounded px-3 py-2">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1" for="password_confirmation">Confirmar password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               class="w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="block mb-1" for="plan_id">Plano</label>
                        <select id="plan_id" name="plan_id" class="w-full border rounded px-3 py-2">
                            <option value="">(não mudar plano)</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->name }}
                                    @if ($plan->price_cents == 0)
                                        — grátis
                                    @else
                                        — €{{ number_format($plan->price_cents / 100, 2, ',', '.') }}/{{ $plan->interval }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.users') }}" class="px-4 py-2 border rounded">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">
                            Guardar
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-sm text-gray-500">
                    @if ($user->company && $user->company->currentSubscription)
                        <p>Subscrição atual: {{ $user->company->currentSubscription->plan->name }}
                            (renova em {{ $user->company->currentSubscription->renews_at?->format('Y-m-d') }})
                        </p>
                    @else
                        <p>Este utilizador não tem subscrição ativa.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Criar empresa
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <form action="{{ route('company.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block mb-1" for="name">Nome da empresa</label>
                        <input id="name" name="name" type="text" class="w-full border rounded px-3 py-2"
                            value="{{ old('name', auth()->user()->name . ' — Empresa') }}">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">
                        Criar
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

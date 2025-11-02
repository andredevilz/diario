{{-- resources/views/diario/reports_edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar diário
            </h2>
            <a href="{{ route('diario.reports') }}"
               class="text-sm text-indigo-600 hover:text-indigo-800">← Voltar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 bg-red-50 text-red-800 border border-red-200 rounded p-3 text-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('diario.reports.update', $entry) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data</label>
                        <input type="date" name="entry_date" value="{{ old('entry_date', $form['entry_date']) }}"
                               class="mt-1 block w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Obra</label>
                        <input type="text" name="site_name" value="{{ old('site_name', $form['site_name']) }}"
                               class="mt-1 block w-full border rounded px-3 py-2" placeholder="Ex.: Edifício A">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Transcrição (opcional)</label>
                        <textarea name="transcription" rows="4" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Texto livre...">{{ old('transcription', $form['transcription']) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Equipa presente (1 por linha)</label>
                            <textarea name="equipa_presente" rows="6" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Ex.: Pedro Silva&#10;Ana Ramos">{{ old('equipa_presente', $form['equipa_presente']) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Trabalhos executados (1 por linha)</label>
                            <textarea name="trabalhos_executados" rows="6" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Ex.: Betonagem laje piso 1&#10;Assentamento de alvenaria">{{ old('trabalhos_executados', $form['trabalhos_executados']) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Materiais recebidos (1 por linha)</label>
                            <textarea name="materiais_recebidos" rows="6" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Ex.: 20 sacos cimento&#10;Aço A500 12mm">{{ old('materiais_recebidos', $form['materiais_recebidos']) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ocorrências / Condições (1 por linha)</label>
                            <textarea name="ocorrencias" rows="6" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Ex.: Chuva fraca das 10h às 12h">{{ old('ocorrencias', $form['ocorrencias']) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Plano para o dia seguinte (1 por linha)</label>
                            <textarea name="plano_seguinte" rows="6" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Ex.: Montagem de cofragem p/ vigas P2">{{ old('plano_seguinte', $form['plano_seguinte']) }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('diario.reports') }}"
                           class="px-4 py-2 text-sm border rounded">Cancelar</a>
                        <button class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-500">
                            Guardar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

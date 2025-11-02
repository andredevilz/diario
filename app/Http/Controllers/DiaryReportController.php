<?php

namespace App\Http\Controllers;

use App\Models\DiaryEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DiaryReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = DiaryEntry::query()
            ->with('user')
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc');

        if ($user->company_id) {
            $query->where('company_id', $user->company_id);
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('obra')) {
            $query->where('site_name', 'like', '%'.$request->obra.'%');
        }
        if ($request->filled('de')) {
            $query->whereDate('entry_date', '>=', $request->de);
        }
        if ($request->filled('ate')) {
            $query->whereDate('entry_date', '<=', $request->ate);
        }

        $entries = $query->paginate(15)->withQueryString();

        $availableSites = DiaryEntry::query()
            ->select('site_name')
            ->whereNotNull('site_name')
            ->when($user->company_id, fn ($q) => $q->where('company_id', $user->company_id))
            ->groupBy('site_name')
            ->orderBy('site_name')
            ->pluck('site_name');

        return view('diario.reports', [
            'entries'        => $entries,
            'availableSites' => $availableSites,
        ]);
    }

    /** ✨ Mostrar formulário de edição */
    public function edit(Request $request, DiaryEntry $entry)
    {
        $user = $request->user();
        // autorização simples: da mesma empresa ou autor
        if ($user->company_id) {
            abort_if($entry->company_id !== $user->company_id, 403);
        } else {
            abort_if($entry->user_id !== $user->id, 403);
        }

        // normalizar payload para o formulário (cada array -> string linha a linha)
        $payload = $entry->payload ?? [];

        $form = [
            'entry_date'           => optional($entry->entry_date)->toDateString(),
            'site_name'            => $entry->site_name,
            'transcription'        => $entry->transcription,
            'equipa_presente'      => implode("\n", Arr::wrap($payload['equipa_presente'] ?? [])),
            'trabalhos_executados' => implode("\n", Arr::wrap($payload['trabalhos_executados'] ?? [])),
            'materiais_recebidos'  => implode("\n", Arr::wrap($payload['materiais_recebidos'] ?? [])),
            'ocorrencias'          => implode("\n", Arr::wrap($payload['ocorrencias'] ?? [])),
            'plano_seguinte'       => implode("\n", Arr::wrap($payload['plano_seguinte'] ?? [])),
        ];

        return view('diario.reports_edit', compact('entry', 'form'));
    }

    /** ✨ Guardar alterações */
    public function update(Request $request, DiaryEntry $entry)
    {
        $user = $request->user();
        if ($user->company_id) {
            abort_if($entry->company_id !== $user->company_id, 403);
        } else {
            abort_if($entry->user_id !== $user->id, 403);
        }

        $data = $request->validate([
            'entry_date'           => ['required', 'date'],
            'site_name'            => ['nullable', 'string', 'max:255'],
            'transcription'        => ['nullable', 'string'],
            // cada item por linha (opcionalmente vazio)
            'equipa_presente'      => ['nullable', 'string'],
            'trabalhos_executados' => ['nullable', 'string'],
            'materiais_recebidos'  => ['nullable', 'string'],
            'ocorrencias'          => ['nullable', 'string'],
            'plano_seguinte'       => ['nullable', 'string'],
        ]);

        // converter textareas (1 item por linha) -> arrays limpos
        $toArray = function (?string $s): array {
            if ($s === null) return [];
            $lines = preg_split('/\r\n|\r|\n/', $s);
            $clean = array_values(array_filter(array_map(fn($v) => trim($v), $lines), fn($v) => $v !== ''));
            return $clean;
        };

        $newPayload = $entry->payload ?? [];
        $newPayload['data']                 = $data['entry_date']; // mantém coerência com teu JSON
        $newPayload['obra']                 = $data['site_name'] ?? ($newPayload['obra'] ?? '');
        $newPayload['equipa_presente']      = $toArray($data['equipa_presente'] ?? null);
        $newPayload['trabalhos_executados'] = $toArray($data['trabalhos_executados'] ?? null);
        $newPayload['materiais_recebidos']  = $toArray($data['materiais_recebidos'] ?? null);
        $newPayload['ocorrencias']          = $toArray($data['ocorrencias'] ?? null);
        $newPayload['plano_seguinte']       = $toArray($data['plano_seguinte'] ?? null);

        $entry->update([
            'entry_date'    => $data['entry_date'],
            'site_name'     => $data['site_name'] ?? null,
            'transcription' => $data['transcription'] ?? null,
            'payload'       => $newPayload,
        ]);

        return redirect()
            ->route('diario.reports')
            ->with('success', 'Diário atualizado com sucesso.');
    }
    public function destroy(Request $request, \App\Models\DiaryEntry $entry)
{
    $user = $request->user();

    // autorização simples
    if ($user->company_id) {
        abort_if($entry->company_id !== $user->company_id, 403);
    } else {
        abort_if($entry->user_id !== $user->id, 403);
    }

    $entry->delete();

    return back()->with('success', 'Diário apagado.');
}

}

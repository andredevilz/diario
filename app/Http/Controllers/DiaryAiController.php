<?php

namespace App\Http\Controllers;

use App\Models\DiaryEntry;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DiaryAiController extends Controller
{
    public function store(Request $request)
    {
        // -- Auth
        $user = $request->user();
        if (!$user) {
            return response()->json(['error'=>true,'user_message'=>'Tens de fazer login primeiro.'], 401);
        }

        // -- Rate limit (se quiseres, ajusta no .env e config/diary.php)
        if (config('diary.rate_limit_enabled')) {
            if (!(app()->environment('local') && config('diary.bypass_on_local'))) {
                $whitelist = collect(config('diary.allow_user_ids', []))->map(fn($v)=>(int)$v);
                if (!$whitelist->contains($user->id)) {
                    $limit = max(0,(int)config('diary.rate_limit_per_hour',5));
                    if ($limit>0) {
                        $scope = config('diary.rate_limit_scope','ip');
                        $identifier = $scope==='user' ? 'user:'.$user->id : 'ip:'.$request->ip();
                        $bucket = now()->format('YmdH');
                        $key = "diary-ai:{$identifier}:{$bucket}";
                        $cur = cache()->get($key,0);
                        if ($cur >= $limit) {
                            $retry = now()->endOfHour()->addSecond()->diffInSeconds(now());
                            return response()->json([
                                'error'=>true,
                                'user_message'=>"Atingiste o limite de {$limit} relatórios por hora.",
                            ],429)->withHeaders([
                                'X-RateLimit-Limit'=>$limit,
                                'X-RateLimit-Remaining'=>0,
                                'Retry-After'=>$retry,
                            ]);
                        }
                        $ttl = now()->endOfHour()->addSecond()->diffInSeconds(now());
                        if (!cache()->has($key)) cache()->put($key,1,$ttl);
                        else { cache()->increment($key); cache()->put($key, cache()->get($key), $ttl); }
                        @header('X-RateLimit-Limit: '.$limit);
                        @header('X-RateLimit-Remaining: '.max(0, $limit - cache()->get($key)));
                    }
                }
            }
        }

        // -- Validação
        $request->validate([
            'audio'      => ['required','file','max:20480'], // 20MB
            'entry_date' => ['nullable','date'],
            'site_name'  => ['nullable','string','max:255'],
        ]);

        $file = $request->file('audio');
        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        $allowed = [
            'audio/webm','video/webm',
            'audio/ogg','application/ogg',
            'audio/mpeg','audio/mp3',
            'audio/wav','audio/x-wav',
            'audio/m4a','audio/x-m4a','audio/mp4','video/mp4',
            'application/octet-stream',
        ];
        if (!in_array($mime,$allowed,true)) {
            return response()->json([
                'error'=>true,
                'user_message'=>"Formato de áudio não suportado ($mime). Usa webm, mp3, wav, ogg ou m4a.",
            ],400);
        }

        // -- Guardar áudio (se existir coluna)
        $audioPath = null;
        if (Schema::hasColumn('diary_entries','audio_path')) {
            try {
                if (str_contains($mime,'webm'))       $ext='webm';
                elseif (str_contains($mime,'ogg'))    $ext='ogg';
                elseif (str_contains($mime,'mpeg')||str_contains($mime,'mp3')) $ext='mp3';
                elseif (str_contains($mime,'wav'))    $ext='wav';
                elseif (str_contains($mime,'mp4'))    $ext='m4a';
                elseif (str_contains($mime,'m4a'))    $ext='m4a';
                else                                   $ext= $file->getClientOriginalExtension() ?: 'webm';
                $filename = 'diario_'.now()->format('Ymd_His').'_'.Str::random(6).'.'.$ext;
                $audioPath = $file->storeAs('public/diarios',$filename);
            } catch (\Throwable $e) {
                $audioPath = null;
            }
        }

        // -- OpenAI
        $apiKey = config('services.openai.key') ?: env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return response()->json(['error'=>true,'user_message'=>'API key não configurada (.env: OPENAI_API_KEY).'],500);
        }

        // -- TRANSCRIÇÃO (apenas whisper-1: mais robusto)
        $transcribedText = '';
        $transcribeStatus = null;
        try {
            $filenameForAi = 'audio.' . (str_contains($mime,'mp4') ? 'm4a' : (str_contains($mime,'webm') ? 'webm' : (str_contains($mime,'wav') ? 'wav' : 'ogg')));
            $resp = Http::timeout(60)->connectTimeout(12)->retry(2, 700)
                ->withToken($apiKey)
                ->attach('file', file_get_contents($file->getRealPath()), $filenameForAi)
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model'           => 'whisper-1',
                    'response_format' => 'json',
                    'language'        => 'pt',
                    'temperature'     => 0,
                ]);

            $transcribeStatus = 'whisper:'.$resp->status();
            if ($resp->ok()) {
                $txt = trim((string)($resp->json('text') ?? ''));
                if (mb_strlen($txt) >= 6) {
                    $transcribedText = $txt;
                }
            }
        } catch (ConnectionException $ce) {
            Log::warning('OpenAI transcribe timeout/conn', ['e'=>$ce->getMessage()]);
        } catch (\Throwable $e) {
            Log::warning('OpenAI transcribe error', ['e'=>$e->getMessage()]);
        }

        if ($transcribedText === '') {
            // modo degradado com heurística mínima
            return $this->storeDegraded(
                $request,
                $user->id,
                $user->company_id,
                $audioPath,
                '(indisponível)',
                'Não consegui transcrever o áudio.',
                ['mime'=>$mime,'size_bytes'=>$file->getSize(),'status'=>$transcribeStatus]
            );
        }

        // -- FORMATAR JSON (gpt-4.1-mini). Se vier vazio, aplica heurística.
        $report = [];
        $aiOk = false;
        try {
            $promptUser =
                "Transcrição (PT-PT):\n".$transcribedText."\n\n".
                "Transforma em diário JSON com chaves: data (YYYY-MM-DD), obra (string), ".
                "equipa_presente[], trabalhos_executados[], materiais_recebidos[], ocorrencias[], plano_seguinte[]. ".
                "Não inventes nomes/obra. Se faltar info, usa arrays vazias; data=hoje. Responde só JSON.";

            $payload = [
                "model" => "gpt-4.1-mini",
                "input" => [
                    ["role"=>"system","content"=>"És um encarregado de obra. Sê conciso e não inventes factos."],
                    ["role"=>"user","content"=>$promptUser],
                ],
                "text" => [
                    "format" => [
                        "name"=>"diario_obra","type"=>"json_schema",
                        "schema" => [
                            "type"=>"object",
                            "properties"=>[
                                "data"=>["type"=>"string"],
                                "obra"=>["type"=>"string"],
                                "equipa_presente"=>["type"=>"array","items"=>["type"=>"string"]],
                                "trabalhos_executados"=>["type"=>"array","items"=>["type"=>"string"]],
                                "materiais_recebidos"=>["type"=>"array","items"=>["type"=>"string"]],
                                "ocorrencias"=>["type"=>"array","items"=>["type"=>"string"]],
                                "plano_seguinte"=>["type"=>"array","items"=>["type"=>"string"]],
                            ],
                            "required"=>["data","obra","equipa_presente","trabalhos_executados","materiais_recebidos","ocorrencias","plano_seguinte"],
                            "additionalProperties"=>false,
                        ]
                    ]
                ]
            ];

            $formatResponse = Http::timeout(45)->connectTimeout(10)->retry(2, 500)
                ->withToken($apiKey)->post('https://api.openai.com/v1/responses',$payload);

            if ($formatResponse->ok()) {
                $fd = $formatResponse->json();
                if (isset($fd['output'][0]['content'][0]['json'])) {
                    $report = $fd['output'][0]['content'][0]['json'];
                } elseif (isset($fd['output'][0]['content'][0]['text'])) {
                    $report = json_decode($fd['output'][0]['content'][0]['text'], true) ?? [];
                }
                $report = $this->normalizeReport($report);
                $aiOk = true;
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI responses error', ['e'=>$e->getMessage()]);
        }

        if ($this->isReportEmpty($report)) {
            $entryDateGuess = $request->input('entry_date')
                ? Carbon::parse($request->input('entry_date'))->toDateString()
                : now()->toDateString();
            $siteNameGuess = $request->input('site_name') ?: ($report['obra'] ?? '');
            $report = $this->heuristicReport($transcribedText, $entryDateGuess, $siteNameGuess);
        }

        // -- Data final
        $entryDate = $request->input('entry_date');
        if ($entryDate) {
            try { $entryDate = Carbon::parse($entryDate)->toDateString(); }
            catch (\Throwable $e) { $entryDate = now()->toDateString(); }
        } else {
            $entryDate = isset($report['data'])
                ? (string) Carbon::parse($report['data'])->toDateString()
                : now()->toDateString();
        }

        // -- Obra final
        $siteName = $request->input('site_name') ?: ($report['obra'] ?? null);

        // -- Guardar
        $attrs = [
            'user_id'       => $user->id,
            'company_id'    => $user->company_id,
            'entry_date'    => $entryDate,
            'site_name'     => $siteName,
            'payload'       => is_array($report)?$report:[],
            'transcription' => $transcribedText,
        ];
        if (Schema::hasColumn('diary_entries','audio_path')) {
            $attrs['audio_path'] = $audioPath ? str_replace('public/','storage/',$audioPath) : null;
        }

        $entry = DiaryEntry::create($attrs);

        // -- Resposta
        return response()->json([
            'success'       => true,
            'degraded'      => !$aiOk && $this->isReportHeuristic($report),
            'entry_id'      => $entry->id,
            'entry_date'    => optional($entry->entry_date)->toDateString(),
            'site_name'     => $entry->site_name,
            'transcription' => $transcribedText,
            'report'        => $report,
            'audio_url'     => ($attrs['audio_path'] ?? null),
            'transcription_debug' => [
                'mime'       => $mime,
                'size_bytes' => $file->getSize(),
                'status'     => $transcribeStatus,
            ],
            'user_message'  => 'Diário criado com sucesso.',
        ]);
    }

    protected function normalizeReport($r): array
    {
        $r = is_array($r)?$r:[];
        $r['data']                 = isset($r['data']) ? (string)$r['data'] : now()->toDateString();
        $r['obra']                 = isset($r['obra']) ? (string)$r['obra'] : '';
        $r['equipa_presente']      = isset($r['equipa_presente']) && is_array($r['equipa_presente']) ? $r['equipa_presente'] : [];
        $r['trabalhos_executados'] = isset($r['trabalhos_executados']) && is_array($r['trabalhos_executados']) ? $r['trabalhos_executados'] : [];
        $r['materiais_recebidos']  = isset($r['materiais_recebidos']) && is_array($r['materiais_recebidos']) ? $r['materiais_recebidos'] : [];
        $r['ocorrencias']          = isset($r['ocorrencias']) && is_array($r['ocorrencias']) ? $r['ocorrencias'] : [];
        $r['plano_seguinte']       = isset($r['plano_seguinte']) && is_array($r['plano_seguinte']) ? $r['plano_seguinte'] : [];
        return $r;
    }

    protected function isReportEmpty(array $r): bool
    {
        $r = $this->normalizeReport($r);
        return empty($r['equipa_presente'])
            && empty($r['trabalhos_executados'])
            && empty($r['materiais_recebidos'])
            && empty($r['ocorrencias'])
            && empty($r['plano_seguinte']);
    }

    protected function heuristicReport(string $text, string $dateIso, string $siteName): array
    {
        $clean = trim(preg_replace('/\s+/', ' ', (string) $text));
        $parts = preg_split('/[\r\n]+|(?<=[\.\!\?])\s+/u', $clean) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), fn($s)=>$s!==''));

        $trabs = []; $ocors = []; $plan = [];

        foreach ($parts as $p) {
            $pl = mb_strtolower($p,'UTF-8');
            if (preg_match('/amanh[ãa]|seguinte|próximo dia|proximo dia/u',$pl)) { $plan[]=$p; continue; }
            if (preg_match('/chuva|avaria|atras|atraso|falha|problema|parado|paragem|falta/u',$pl)) { $ocors[]=$p; continue; }
            if (count($trabs) < 3) { $trabs[]=$p; continue; }
        }
        if (empty($trabs) && !empty($parts)) $trabs = array_slice($parts,0,min(3,count($parts)));

        return [
            '_heuristic'           => true,
            'data'                 => $dateIso ?: now()->toDateString(),
            'obra'                 => $siteName ?: '',
            'equipa_presente'      => [],
            'trabalhos_executados' => $trabs ?: ($text ? [$text] : []),
            'materiais_recebidos'  => [],
            'ocorrencias'          => $ocors,
            'plano_seguinte'       => $plan,
        ];
    }

    protected function storeDegraded(
        Request $request,
        int $userId,
        ?int $companyId,
        ?string $audioPath,
        string $transcription,
        string $userMessage,
        array $debug = []
    ) {
        $entryDate = $request->input('entry_date');
        if ($entryDate) {
            try { $entryDate = Carbon::parse($entryDate)->toDateString(); }
            catch (\Throwable $e) { $entryDate = now()->toDateString(); }
        } else $entryDate = now()->toDateString();

        $siteName = $request->input('site_name') ?? '';

        $report = $this->heuristicReport($transcription ?: '', $entryDate, $siteName);

        $attrs = [
            'user_id'       => $userId,
            'company_id'    => $companyId,
            'entry_date'    => $entryDate,
            'site_name'     => $siteName,
            'payload'       => $report,
            'transcription' => $transcription,
        ];
        if (Schema::hasColumn('diary_entries','audio_path')) {
            $attrs['audio_path'] = $audioPath ? str_replace('public/','storage/',$audioPath) : null;
        }

        $entry = DiaryEntry::create($attrs);

        return response()->json([
            'success'       => true,
            'degraded'      => true,
            'entry_id'      => $entry->id,
            'entry_date'    => optional($entry->entry_date)->toDateString(),
            'site_name'     => $entry->site_name,
            'transcription' => $transcription,
            'report'        => $report,
            'audio_url'     => ($attrs['audio_path'] ?? null),
            'user_message'  => $userMessage,
            'debug'         => $debug,
        ], 200);
    }
}

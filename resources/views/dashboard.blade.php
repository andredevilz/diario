{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard / Diário de Obra') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('diario.reports') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    📋 Relatórios
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- CARD PRINCIPAL --}}
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <style>
                        .main-card {
                            background: #ffffff;
                            border: 1px solid rgba(15,23,42,0.06);
                            border-radius: 18px;
                            padding: 16px;
                            box-shadow: 0 10px 28px rgba(15,23,42,0.02);
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            gap: 14px;
                            position: relative;
                            overflow: hidden;
                        }
                        .hero-img { width: 100%; display: flex; justify-content: center; }
                        .hero-img img {
                            width: auto; max-width: 80%; max-height: 210px; object-fit: contain;
                            user-select: none; pointer-events: none;
                        }
                        .rec-btn {
                            width: 96px; height: 96px;
                            margin-top: -56px; border-radius: 999px;
                            background: radial-gradient(circle at 30% 30%, #ffb347, #ff7a00);
                            display: flex; align-items: center; justify-content: center;
                            cursor: pointer; border: none;
                            box-shadow: 0 10px 25px rgb(88 57 29 / 35%);
                            transition: transform .08s ease, filter .2s ease, opacity .2s ease;
                        }
                        .rec-btn:hover { transform: translateY(-1px); filter: brightness(1.02); }
                        .rec-btn:disabled { opacity: .6; cursor: not-allowed; }
                        .rec-btn.recording {
                            background: radial-gradient(circle, #ef4444, #b91c1c);
                            box-shadow: 0 10px 25px rgba(239,68,68,0.28);
                        }
                        .rec-icon { width: 60px; height: 60px; fill: #fff; }
                        .headline-small { font-size: .95rem; color: #374151; text-align: center; margin: 0; }

                        /* badge grande de countdown DURANTE a gravação (45s) */
                        .recording-countdown {
                            position: absolute; top: 12px; right: 12px;
                            background: rgba(248,113,113,0.95);
                            color: #fff; width: 68px; height: 68px;
                            border-radius: 999px; display: none;
                            align-items: center; justify-content: center;
                            font-weight: 800; font-size: 26px;
                            box-shadow: 0 6px 18px rgba(248,113,113,0.35);
                        }

                        /* Pre-countdown overlay 3-2-1 */
                        .overlay {
                            position: fixed; inset: 0;
                            display: none; align-items: center; justify-content: center;
                            background: rgba(15,23,42,.45);
                            z-index: 1000; padding: 16px;
                        }
                        .overlay.show { display: flex; }
                        .bubble {
                            background: #ff7a00; color: #fff;
                            width: min(60vw, 260px); height: min(60vw, 260px);
                            border-radius: 999px; display: flex; align-items: center; justify-content: center;
                            font-weight: 900; font-size: min(24vw, 110px);
                            box-shadow: 0 18px 40px rgba(255,122,0,.3);
                            user-select: none; text-align: center; line-height: 0.95;
                        }
                        .bubble small { display: block; font-size: .20em; font-weight: 700; opacity: .85; margin-top: .1em; }

                        /* Modal simples (obra + data) */
                        .modal { position: fixed; inset: 0; background: rgba(15,23,42,.35); display: none; z-index: 1001; align-items: center; justify-content: center; padding: 16px; }
                        .modal.show { display: flex; }
                        .modal-card {
                            background: #fff; border-radius: 18px; width: min(480px, 100%); padding: 18px;
                            box-shadow: 0 20px 40px rgba(15,23,42,.18);
                        }
                        .modal-title { font-weight: 700; font-size: 16px; margin-bottom: 6px; }
                        .modal-text { font-size: 13px; color: #64748b; margin-bottom: 14px; }
                        .modal-row { display: grid; grid-template-columns: 1fr; gap: 10px; }
                        .modal-row label { font-size: 12px; color: #6b7280; }
                        .modal-input {
                            width: 100%; border: 1px solid rgba(148,163,184,.5); border-radius: 10px; padding: 8px 10px; font-size: 14px;
                        }
                        .modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px; }
                        .btn { border: none; border-radius: 10px; padding: 9px 14px; font-weight: 700; cursor: pointer; }
                        .btn-light { background: #e5e7eb; color: #111827; }
                        .btn-primary { background: #0f766e; color: #fff; }
                    </style>

                    <div class="p-6 space-y-4">
                        <p class="headline-small">Fala o que foi feito hoje e eu guardo na tua conta.</p>

                        <div class="main-card">
                            <div id="recordingCountdown" class="recording-countdown">45</div>

                            <div class="hero-img">
                                <img id="heroImage" src="{{ asset('images/construtor1.png') }}" alt="Construtor">
                            </div>

                            <button id="recordBtn" type="button" class="rec-btn" title="Gravar / parar">
                                <svg id="recordIcon" class="rec-icon" viewBox="0 0 24 24">
                                    <path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a 3 3 0 0 0 3 3Zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 14 0h-2ZM11 18.93V22h2v-3.07A8.001 8.001 0 0 0 20 11h-2a6 6 0 0 1-12 0H4a8.001 8.001 0 0 0 7 7.93Z"/>
                                </svg>
                            </button>

                            <div id="statusLine" class="text-sm text-gray-500 text-center">Pronto para gravar.</div>

                            <div id="lastDiaryBox" class="hidden w-full">
                                <h3 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs">✓</span>
                                    Último diário guardado
                                </h3>
                                <pre id="lastDiaryContent" class="bg-gray-50 rounded-md p-3 text-xs text-gray-700 whitespace-pre-wrap"></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD LADO DIREITO --}}
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-4">
                        <h3 class="text-sm font-semibold text-gray-800">Últimos diários</h3>

                        @php $entries = $entries ?? collect(); @endphp
                        <div class="space-y-3" id="recentList">
                            @forelse ($entries as $entry)
                                <div class="border border-gray-100 rounded-md p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-medium text-gray-800">{{ $entry->site_name ?: 'Obra sem nome' }}</p>
                                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($entry->entry_date)->format('d/m/Y') }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                        @php
                                            $payload = $entry->payload ?? [];
                                            $texto = '';
                                            if (is_array($payload) && !empty($payload['trabalhos_executados'])) {
                                                $texto = implode(', ', $payload['trabalhos_executados']);
                                            } else {
                                                $texto = \Illuminate\Support\Str::limit($entry->transcription ?? '', 120);
                                            }
                                        @endphp
                                        {{ $texto }}
                                    </p>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400">Ainda não tens diários. Grava o primeiro 👆</p>
                            @endforelse
                        </div>

                        <a href="{{ route('diario.reports') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-500">
                            Ver todos os relatórios →
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- OVERLAYS / MODAIS --}}
    <div id="preCountdownOverlay" class="overlay">
        <div class="bubble" id="preCountdownBubble">
            3
            <small>prepara-te</small>
        </div>
    </div>

    <div id="saveModal" class="modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true">
            <div class="modal-title">Guardar diário</div>
            <div class="modal-text">Escolhe a <strong>obra</strong> e a <strong>data</strong> antes de enviar.</div>
            <div class="modal-row">
                <div>
                    <label for="modalSite" class="block">Obra</label>
                    <input id="modalSite" type="text" class="modal-input" placeholder="Ex.: Edifício Alfa">
                </div>
                <div>
                    <label for="modalDate" class="block">Data</label>
                    <input id="modalDate" type="date" class="modal-input">
                </div>
            </div>
            <div class="modal-actions">
                <button id="btnCancelSave" class="btn btn-light" type="button">Cancelar</button>
                <button id="btnConfirmSave" class="btn btn-primary" type="button">Guardar & Enviar</button>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
   <script>
const UPLOAD_URL = "{{ route('diario.upload') }}";
const CSRF_TOKEN = "{{ csrf_token() }}";
const RECORD_SECONDS = 45;
const PRE_COUNTDOWN = true;

const recordBtn   = document.getElementById('recordBtn');
const recordIcon  = document.getElementById('recordIcon');
const statusLine  = document.getElementById('statusLine');
const lastDiaryBox= document.getElementById('lastDiaryBox');
const lastDiaryContent = document.getElementById('lastDiaryContent');
const recentList  = document.getElementById('recentList');
const heroImage   = document.getElementById('heroImage');
const recordingCountdown = document.getElementById('recordingCountdown');

const preOverlay  = document.getElementById('preCountdownOverlay');
const preBubble   = document.getElementById('preCountdownBubble');
const saveModal   = document.getElementById('saveModal');
const modalSite   = document.getElementById('modalSite');
const modalDate   = document.getElementById('modalDate');
const btnCancelSave = document.getElementById('btnCancelSave');
const btnConfirmSave = document.getElementById('btnConfirmSave');

let mediaRecorder = null;
let chunks = [];
let isRecording = false;
let countdownInterval = null;
let secondsLeft = RECORD_SECONDS;
let pendingBlob = null;

function showMsg(m){ statusLine.textContent = m; }

function startUiRecording(){
  recordBtn.classList.add('recording');
  recordIcon.innerHTML = '<rect x="7" y="7" width="10" height="10" rx="2" ry="2"></rect>';
  heroImage.src = "{{ asset('images/construtor2.png') }}";
  showMsg('A gravar. Máx. ' + RECORD_SECONDS + 's. Toca para parar.');
  secondsLeft = RECORD_SECONDS;
  recordingCountdown.style.display = 'flex';
  recordingCountdown.textContent = secondsLeft;
  clearInterval(countdownInterval);
  countdownInterval = setInterval(()=>{
    secondsLeft--;
    if (secondsLeft <= 0) { stopRecording(); }
    else { recordingCountdown.textContent = secondsLeft; }
  }, 1000);
}
function stopUiRecording(processing=true){
  recordBtn.classList.remove('recording');
  recordIcon.innerHTML = '<path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3Zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 14 0h-2ZM11 18.93V22h2v-3.07A8.001 8.001 0 0 0 20 11h-2a6 6 0 0 1-12 0H4a8.001 8.001 0 0 0 7 7.93Z"/>';
  clearInterval(countdownInterval);
  recordingCountdown.style.display = 'none';
  if (processing){ heroImage.src = "{{ asset('images/construtor3.png') }}"; showMsg('A preparar áudio...'); }
  else { heroImage.src = "{{ asset('images/construtor1.png') }}"; }
}

/* countdown 3-2-1 */
function showPreCountdown(){
  if (!PRE_COUNTDOWN) { startRecording(); return; }
  preBubble.innerHTML = '3<small>prepara-te</small>';
  preOverlay.classList.add('show');
  let n=3;
  const iv = setInterval(()=>{
    n--;
    if (n===2) preBubble.innerHTML = '2<small>prepara-te</small>';
    if (n===1) preBubble.innerHTML = '1<small>prepara-te</small>';
    if (n<=0){ clearInterval(iv); preOverlay.classList.remove('show'); startRecording(); }
  }, 900);
}

/* gravação */
async function startRecording(){
  try{
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    chunks = [];

    // Tipos preferidos; usa timeslice p/ garantir eventos regulares
    let options = {};
    try{
      const prefer = ['audio/webm;codecs=opus','audio/webm','audio/mp4'];
      for (const mt of prefer) {
        if (window.MediaRecorder && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(mt)) {
          options.mimeType = mt; break;
        }
      }
    }catch(_){}

    mediaRecorder = new MediaRecorder(stream, options);
    mediaRecorder.ondataavailable = (e)=>{ if (e.data && e.data.size>0) chunks.push(e.data); };
    mediaRecorder.onstop = handleAfterStop;

    // timeslice => cria chunks periódicos (alguns browsers precisam disto)
    mediaRecorder.start(1000);
    isRecording = true;

    startUiRecording();
  }catch(err){
    console.error(err);
    if (err.name === 'NotAllowedError' || err.name === 'SecurityError'){
      showMsg('O browser bloqueou o micro. Dá permissão ao microfone.');
    }else if (err.name === 'NotFoundError'){
      showMsg('Não encontrei dispositivo de áudio.');
    }else{
      showMsg('Erro a aceder ao micro: ' + (err.message || err.name));
    }
    heroImage.src = "{{ asset('images/construtor1.png') }}";
  }
}

function stopRecording(){
  if (!isRecording) return;
  isRecording = false;
  try { mediaRecorder.requestData?.(); } catch(_){}
  stopUiRecording(true);
  try { mediaRecorder.stop(); } catch(_){}
}

function openSaveModalWith(blob, ext){
  pendingBlob = { blob, ext };
  const d=new Date(); d.setMinutes(d.getMinutes()-d.getTimezoneOffset());
  modalDate.value = d.toISOString().slice(0,10);
  modalSite.value = '';
  saveModal.classList.add('show');
  showMsg('Escolhe a obra e a data.');
}

async function handleAfterStop(){
  const first = chunks[0];
  const mime = (first && first.type) ? first.type : 'audio/webm;codecs=opus';
  let ext = 'webm';
  if (mime.includes('mp4')) ext = 'm4a';
  if (mime.includes('ogg')) ext = 'ogg';
  if (mime.includes('wav')) ext = 'wav';

  const blob = new Blob(chunks, { type: mime });

  // Bloquear silêncio claro (< 6KB é quase sempre silêncio em 2–3s)
  if (!blob || blob.size < 6000){
    showMsg('Não captei som suficiente. Fala 3–5 segundos e tenta outra vez.');
    heroImage.src = "{{ asset('images/construtor1.png') }}";
    return;
  }
  openSaveModalWith(blob, ext);
}

/* envio com timeout + debug */
async function sendToServer(pending, siteName, dateIso){
  showMsg('A enviar áudio...');

  const ctrl = new AbortController();
  const t = setTimeout(() => ctrl.abort('timeout'), 60000);

  try {
    const formData = new FormData();
    formData.append('audio', pending.blob, `diario.${pending.ext}`);
    if (siteName) formData.append('site_name', siteName);
    if (dateIso)  formData.append('entry_date', dateIso);

    const resp = await fetch(UPLOAD_URL, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
      body: formData,
      cache: 'no-store',
      signal: ctrl.signal
    });

    const raw = await resp.text();
    let data = null;
    try { data = raw ? JSON.parse(raw) : null; } catch(_) {}

    if (!resp.ok || (data && data.error)) {
      const snippet = (raw && !data) ? (' · ' + raw.slice(0,160)) : '';
      const msg =
        (data && (data.user_message || data.message)) ||
        (resp.status===429 ? 'Limite de relatórios atingido. Tenta mais tarde.' :
         resp.status===419 ? 'Sessão expirada (419). Faz login outra vez.' :
         resp.status===401 ? 'Não autenticado (401). Entra na conta.' :
         `Erro ao processar áudio (HTTP ${resp.status})${snippet}`);
      showMsg(msg);
      heroImage.src = "{{ asset('images/construtor1.png') }}";
      return null;
    }

    return data;

  } catch (err) {
    if (err && err.name === 'AbortError') {
      showMsg('O servidor demorou demasiado (timeout). Tenta de novo.');
    } else {
      console.error(err);
      showMsg('Falha de rede ao enviar o áudio.');
    }
    heroImage.src = "{{ asset('images/construtor1.png') }}";
    return null;
  } finally {
    clearTimeout(t);
  }
}

/* modal */
btnCancelSave.addEventListener('click', ()=>{
  saveModal.classList.remove('show');
  pendingBlob = null;
  heroImage.src = "{{ asset('images/construtor1.png') }}";
  showMsg('Cancelado. Pronto para gravar.');
});

btnConfirmSave.addEventListener('click', async ()=>{
  if (!pendingBlob) return;
  const site = modalSite.value.trim();
  const date = modalDate.value;
  saveModal.classList.remove('show');

  const data = await sendToServer(pendingBlob, site, date);
  pendingBlob = null;

  if (!data) return;

  showMsg(data.degraded ? 'Diário criado (modo degradado).' : 'Diário gravado com sucesso.');
  heroImage.src = "{{ asset('images/construtor1.png') }}";

  // MOSTRAR DEBUG quando degradado (para veres imediatamente o motivo)
  if (data.degraded && (data.transcription_debug || data.debug)) {
    lastDiaryBox.classList.remove('hidden');
    lastDiaryContent.textContent =
      'DEBUG:\n' + JSON.stringify((data.transcription_debug || data.debug), null, 2) +
      '\n\nREPORT:\n' + JSON.stringify(data.report, null, 2);
  } else {
    lastDiaryBox.classList.remove('hidden');
    lastDiaryContent.textContent = JSON.stringify(data.report, null, 2);
  }

  const div = document.createElement('div');
  div.className = 'border border-gray-100 rounded-md p-3';
  div.innerHTML = `
    <div class="flex items-center justify-between gap-3">
      <p class="text-sm font-medium text-gray-800">${data.site_name || 'Obra'}</p>
      <p class="text-xs text-gray-400">${data.entry_date || ''}</p>
    </div>
    <p class="text-xs text-gray-500 mt-1 line-clamp-2">
      ${(data.report && data.report.trabalhos_executados && data.report.trabalhos_executados.length)
        ? data.report.trabalhos_executados.join(', ')
        : (data.transcription || '')
      }
    </p>`;
  recentList.prepend(div);
});

/* botão */
recordBtn.addEventListener('click', ()=>{
  if (isRecording) { stopRecording(); return; }
  showPreCountdown();
});
</script>


</x-app-layout>

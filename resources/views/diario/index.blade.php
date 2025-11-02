<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Diário de obras (voz → diário)
        </h2>
    </x-slot>

    {{-- isto é praticamente o teu HTML antigo, só adaptado para Blade/Laravel --}}
    <div class="py-4">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <style>
                    /* --- estilos do teu antigo index.php --- */
                    * { box-sizing: border-box; }
                    .diario-body {
                        background: #ffffff;
                        font-family: system-ui, -apple-system, "SF Pro Rounded", "Segoe UI Rounded", "Segoe UI", sans-serif;
                        color: #111827;
                    }
                    .top-nav-diario {
                        background: #0f172a;
                        color: #fff;
                        display: flex;
                        gap: 12px;
                        align-items: center;
                        padding: 10px 16px;
                    }
                    .top-nav-diario .brand { font-weight: 700; font-size: .95rem; }
                    .top-nav-diario a {
                        color: rgba(255,255,255,.8);
                        text-decoration: none;
                        font-size: .8rem;
                        padding: 4px 9px;
                        border-radius: 999px;
                    }
                    .top-nav-diario a.active {
                        background: rgba(255,255,255,.18);
                        color: #fff;
                    }

                    .page-diario {
                        max-width: 540px;
                        margin: 0 auto;
                        padding: 16px 16px 70px;
                        display: flex;
                        flex-direction: column;
                        gap: 18px;
                        text-align: center;
                    }

                    .headline-diario { font-weight: 700; font-size: 1.35rem; color: #0f172a; margin-top: 4px; }
                    .subhead-diario { font-size: .9rem; color: #374151; }

                    .obra-bar {
                        background: #f8fafc;
                        border: 1px solid rgba(15,23,42,.05);
                        border-radius: 14px;
                        padding: 8px 10px;
                        display: flex;
                        gap: 8px;
                        align-items: center;
                        justify-content: space-between;
                    }
                    .obra-bar-left {
                        display: flex;
                        gap: 6px;
                        align-items: center;
                    }
                    .obra-label { font-size: .7rem; color: #64748b; text-transform: uppercase; }
                    .obra-select {
                        border: 1px solid rgba(148,163,184,.4);
                        border-radius: 999px;
                        padding: 4px 8px;
                        font-size: .8rem;
                    }
                    .btn-obras {
                        background: rgba(15,23,42,.04);
                        border: 1px solid rgba(15,23,42,.08);
                        border-radius: 999px;
                        padding: 5px 10px;
                        font-size: .7rem;
                        cursor: pointer;
                    }

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
                    }
                    .hero-img { width: 100%; display: flex; justify-content: center; }
                    .hero-img img {
                        width: auto;
                        max-width: 80%;
                        max-height: 210px;
                        object-fit: contain;
                    }
                    .rec-btn {
                        width: 88px;
                        height: 88px;
                        margin-top: -50px;
                        border-radius: 999px;
                        background: radial-gradient(circle at 30% 30%, #ffb347, #ff7a00);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        box-shadow: 0 10px 25px rgb(88 57 29 / 35%);
                        border: none;
                    }
                    .rec-btn.recording {
                        background: radial-gradient(circle, #ef4444, #b91c1c);
                        box-shadow: 0 10px 25px rgba(239,68,68,0.28);
                    }
                    .rec-icon { width: 56px; height: 56px; fill: #fff; }
                    .status-line { font-size: 12px; color: #4b5563; text-align: center; }

                    .recording-countdown {
                        position: absolute;
                        top: 10px;
                        right: 10px;
                        background: rgba(248,113,113,0.9);
                        color: #fff;
                        width: 42px;
                        height: 42px;
                        border-radius: 999px;
                        display: none;
                        align-items: center;
                        justify-content: center;
                        font-weight: 700;
                        font-size: 14px;
                        box-shadow: 0 4px 12px rgba(248,113,113,0.35);
                    }

                    .history-section { text-align: left; }
                    .section-title-left { font-weight: 600; font-size: 14px; margin-bottom: 6px; }
                    .history-list { display: flex; flex-direction: column; gap: 8px; }

                    .accordion-item {
                        border: 1px solid rgba(15,23,42,0.06);
                        border-radius: 12px;
                        background: #fff;
                        overflow: hidden;
                    }
                    .accordion-summary {
                        width: 100%;
                        background: #f8fafc;
                        border: none;
                        padding: 10px 12px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        cursor: pointer;
                        font-size: 14px;
                    }
                    .accordion-summary .meta {
                        display: flex; gap: 10px; align-items: center; color: #475569;
                        flex-wrap: wrap;
                    }
                    .date-pill {
                        background: #eef2ff;
                        color: #3730a3;
                        border-radius: 999px;
                        padding: 2px 8px;
                        font-size: 11px;
                        font-weight: 700;
                    }
                    .obra-pill {
                        background: rgba(14,116,144,.08);
                        color: #0f172a;
                        border-radius: 999px;
                        padding: 2px 6px;
                        font-size: 10px;
                    }
                    .accordion-chevron { transition: transform .15s ease; }
                    .accordion-item.open .accordion-chevron { transform: rotate(90deg); }
                    .accordion-content { display: none; padding: 10px 12px 12px; }
                    .accordion-item.open .accordion-content { display: block; }

                    .field-label {
                        display: block;
                        font-size: 11px;
                        text-transform: uppercase;
                        color: #94a3b8;
                        margin-top: 6px;
                        margin-bottom: 3px;
                    }
                    .field-input, .field-textarea, .field-select {
                        width: 100%;
                        border: 1px solid rgba(148,163,184,0.5);
                        border-radius: 10px;
                        padding: 6px 8px;
                        font-size: 13px;
                    }
                    .field-textarea { min-height: 55px; resize: vertical; }
                    .inline-actions {
                        display: flex;
                        gap: 6px;
                        margin-top: 10px;
                        justify-content: flex-end;
                    }
                    .btn-save-line {
                        background: #0f766e;
                        color: #fff;
                        border: none;
                        border-radius: 999px;
                        padding: 5px 12px;
                        font-size: 12px;
                        cursor: pointer;
                    }
                    .btn-delete-line {
                        background: rgba(248,113,113,0.1);
                        color: #b91c1c;
                        border: none;
                        border-radius: 999px;
                        padding: 5px 12px;
                        font-size: 12px;
                        cursor: pointer;
                    }
                    .empty-state {
                        background: #ffffff;
                        border: 1px dashed rgba(15,23,42,0.1);
                        border-radius: 14px;
                        padding: 12px;
                        font-size: .8rem;
                        color: #94a3b8;
                    }

                    /* modais */
                    .modal-overlay {
                        position: fixed;
                        inset: 0;
                        background: rgba(15,23,42,0.28);
                        display: none;
                        align-items: center;
                        justify-content: center;
                        z-index: 200;
                        padding: 16px;
                    }
                    .modal-overlay.show { display: flex; }
                    .modal-card {
                        background: #fff;
                        border-radius: 18px;
                        width: min(420px, 100%);
                        padding: 16px 18px 14px;
                        box-shadow: 0 20px 40px rgba(15,23,42,.12);
                        text-align: left;
                    }
                    .modal-title { font-weight: 600; margin-bottom: 6px; font-size: 15px; }
                    .modal-text { font-size: 13px; color: #64748b; margin-bottom: 10px; }
                    .modal-input, .modal-select {
                        width: 100%;
                        padding: 8px 10px;
                        border: 1px solid rgba(148,163,184,0.5);
                        border-radius: 10px;
                        font-size: 13px;
                        margin-bottom: 12px;
                    }
                    .modal-actions { display: flex; gap: 8px; justify-content: flex-end; }
                    .btn-secondary {
                        background: #e2e8f0;
                        color: #0f172a;
                        border: none;
                        border-radius: 999px;
                        padding: 7px 14px;
                        font-weight: 600;
                        cursor: pointer;
                    }
                    .btn-primary {
                        background: #ff7a00;
                        color: #fff;
                        border: none;
                        border-radius: 999px;
                        padding: 7px 14px;
                        font-weight: 600;
                        cursor: pointer;
                    }

                    .countdown-overlay {
                        position: fixed;
                        inset: 0;
                        background: rgba(0,0,0,0.35);
                        display: none;
                        align-items: center;
                        justify-content: center;
                        z-index: 99;
                    }
                    .countdown-bubble {
                        width: 130px;
                        height: 130px;
                        border-radius: 999px;
                        background: #ff7a00;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #fff;
                        font-size: 54px;
                        font-weight: 700;
                    }

                    .obra-modal {
                        position: fixed;
                        inset: 0;
                        background: rgba(15,23,42,.3);
                        display: none;
                        align-items: center;
                        justify-content: center;
                        z-index: 222;
                        padding: 16px;
                    }
                    .obra-modal.show { display: flex; }
                    .obra-card {
                        background: #fff;
                        border-radius: 18px;
                        width: min(380px, 100%);
                        padding: 16px 18px 14px;
                        box-shadow: 0 20px 40px rgba(15,23,42,.12);
                        text-align: left;
                    }
                    .obra-row {
                        display: flex;
                        gap: 6px;
                        margin-bottom: 6px;
                    }
                    .obra-row input {
                        flex: 1;
                        padding: 6px 8px;
                        border-radius: 8px;
                        border: 1px solid rgba(148,163,184,.5);
                    }
                    .obra-row button {
                        border: none;
                        background: rgba(248,113,113,.12);
                        color: #b91c1c;
                        border-radius: 8px;
                        padding: 4px 8px;
                        font-size: .7rem;
                        cursor: pointer;
                    }
                    .btn-add-obra {
                        background: rgba(15,118,110,.1);
                        color: #0f766e;
                        border: none;
                        border-radius: 999px;
                        padding: 5px 12px;
                        font-size: .7rem;
                        cursor: pointer;
                        margin-bottom: 8px;
                    }
                    .obra-actions { display: flex; gap: 6px; justify-content: flex-end; }

                </style>

                <div class="diario-body">
                    <nav class="top-nav-diario">
                        <span class="brand">📋 Diário de Obras</span>
                        <a href="{{ route('diario.index') }}" class="active">Gravar</a>
                        {{-- no Laravel vamos fazer esta página de relatórios depois --}}
                        <a href="#" onclick="alert('Vamos pôr esta página no Laravel a seguir 👍'); return false;">Relatórios</a>
                    </nav>

                    <div class="page-diario">
                        <header>
                            <p class="headline-diario">Regista as tuas obras em 30 segundos.</p>
                            <p class="subhead-diario">Falas → escolhes o dia e a obra → fica no histórico (no browser).</p>
                        </header>

                        <div class="obra-bar">
                            <div class="obra-bar-left">
                                <span class="obra-label">Obra atual</span>
                                <select id="obraAtualSelect" class="obra-select"></select>
                            </div>
                            <button type="button" class="btn-obras" id="btnGerirObras">Gerir obras</button>
                        </div>

                        <div class="main-card">
                            <div class="hero-img">
                                <img src="{{ asset('images/construtor1.png') }}" alt="Construtor" id="heroImage">
                            </div>
                            <div class="recording-countdown" id="recordingCountdown">30</div>
                            <button class="rec-btn" id="micButton" title="Gravar / parar">
                                <svg class="rec-icon" viewBox="0 0 24 24" id="recIcon">
                                    <path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3Zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 14 0h-2ZM11 18.93V22h2v-3.07A8.001 8.001 0 0 0 20 11h-2a6 6 0 0 1-12 0H4a8.001 8.001 0 0 0 7 7.93Z"/>
                                </svg>
                            </button>
                            <div class="status-line" id="statusLine">Pronto para gravar.</div>
                        </div>

                        <div class="history-section">
                            <div class="section-title-left">Histórico de diários (guarda no teu browser)</div>
                            <div id="historyList" class="history-list"></div>
                        </div>

                        <footer style="font-size:11px;color:#94a3b8;text-align:center;">
                            Usamos o teu áudio apenas para gerar o relatório desta sessão.
                        </footer>
                    </div>
                </div>

                {{-- MODAIS --}}
                <div class="modal-overlay" id="saveModal">
                    <div class="modal-card">
                        <div class="modal-title">Guardar diário?</div>
                        <div class="modal-text">Muda o dia se isto foi de outro dia e escolhe a obra.</div>
                        <input type="date" id="modalDateInput" class="modal-input">
                        <select id="modalObraSelect" class="modal-select"></select>
                        <div class="modal-actions">
                            <button type="button" class="btn-secondary" id="btnModalAgain">Gravar outra vez</button>
                            <button type="button" class="btn-primary" id="btnModalSave">Guardar</button>
                        </div>
                    </div>
                </div>

                <div class="obra-modal" id="obraModal">
                    <div class="obra-card">
                        <div class="modal-title">Obras a decorrer</div>
                        <p style="font-size:.7rem;color:#94a3b8;margin-top:-4px;margin-bottom:6px;">Edita o nome ou adiciona novas. A obra de exemplo não se apaga.</p>
                        <div id="obraListWrap"></div>
                        <button type="button" class="btn-add-obra" id="btnAddObra">+ Adicionar obra</button>
                        <div class="obra-actions">
                            <button type="button" class="btn-secondary" id="btnObraCancel">Fechar</button>
                            <button type="button" class="btn-primary" id="btnObraSave">Guardar</button>
                        </div>
                    </div>
                </div>

                <div class="countdown-overlay" id="countdownOverlay">
                    <div class="countdown-bubble" id="countdownNumber">3</div>
                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPT (adaptado): agora chama a rota do Laravel e envia CSRF --}}
    <script>
        const diaryEndpoint = "{{ route('ai.diary') }}";
        const csrfToken = "{{ csrf_token() }}";

        const DIARIOS_KEY = 'diarios_obra_v5';
        const OBRAS_KEY   = 'diarios_obras_v2';
        const DEMO_OBRA_ID = 'obra_demo_cascais';
        const DEMO_OBRA_NAME = 'Obra Cascais (exemplo)';

        // refs iguais às do teu código
        const micButton = document.getElementById('micButton');
        const recIcon   = document.getElementById('recIcon');
        const statusLine= document.getElementById('statusLine');
        const heroImage = document.getElementById('heroImage');
        const recordingCountdown = document.getElementById('recordingCountdown');
        const countdownOverlay = document.getElementById('countdownOverlay');
        const countdownNumber  = document.getElementById('countdownNumber');

        const historyList = document.getElementById('historyList');

        const saveModal = document.getElementById('saveModal');
        const modalDateInput = document.getElementById('modalDateInput');
        const modalObraSelect = document.getElementById('modalObraSelect');
        const btnModalSave = document.getElementById('btnModalSave');
        const btnModalAgain = document.getElementById('btnModalAgain');

        const obraAtualSelect = document.getElementById('obraAtualSelect');
        const btnGerirObras = document.getElementById('btnGerirObras');
        const obraModal = document.getElementById('obraModal');
        const obraListWrap = document.getElementById('obraListWrap');
        const btnAddObra = document.getElementById('btnAddObra');
        const btnObraCancel = document.getElementById('btnObraCancel');
        const btnObraSave = document.getElementById('btnObraSave');

        let mediaRecorder = null;
        let chunks = [];
        let state = 'idle';
        let stopTimeout = null;
        const RECORDING_MAX_SECONDS = 30;
        let recordingInterval = null;
        let recordingSecondsLeft = RECORDING_MAX_SECONDS;

        let lastReport = null;
        let lastTranscription = '';

        // --- funções auxiliares (iguais às tuas) ---
        function loadObras() {
            try {
                const arr = JSON.parse(localStorage.getItem(OBRAS_KEY) || '[]');
                const hasDemo = arr.some(o => o.id === DEMO_OBRA_ID);
                if (!hasDemo) {
                    arr.unshift({id: DEMO_OBRA_ID, name: DEMO_OBRA_NAME});
                    localStorage.setItem(OBRAS_KEY, JSON.stringify(arr));
                }
                return arr;
            } catch {
                const def = [{id: DEMO_OBRA_ID, name: DEMO_OBRA_NAME}];
                localStorage.setItem(OBRAS_KEY, JSON.stringify(def));
                return def;
            }
        }
        function saveObras(arr) {
            const cleaned = arr.filter(o => o.id !== DEMO_OBRA_ID);
            cleaned.unshift({id: DEMO_OBRA_ID, name: DEMO_OBRA_NAME});
            localStorage.setItem(OBRAS_KEY, JSON.stringify(cleaned));
        }
        function loadDiarios() {
            try { return JSON.parse(localStorage.getItem(DIARIOS_KEY) || '[]'); }
            catch { return []; }
        }
        function saveDiarios(arr) {
            localStorage.setItem(DIARIOS_KEY, JSON.stringify(arr));
        }
        function toISODate(d) {
            if (d instanceof Date) return d.toISOString().slice(0,10);
            if (typeof d === 'string' && d.length >= 10) return d.slice(0,10);
            return (new Date()).toISOString().slice(0,10);
        }
        function shortDatePt(iso) {
            const d = new Date(iso);
            if (!isNaN(d)) return d.toLocaleDateString('pt-PT', {day:'2-digit', month:'short', year:'numeric'});
            return iso;
        }
        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g,"&amp;")
                .replace(/</g,"&lt;")
                .replace(/>/g,"&gt;");
        }

        function renderObraSelects() {
            const obras = loadObras();
            obraAtualSelect.innerHTML = '';
            obras.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.id;
                opt.textContent = o.name;
                obraAtualSelect.appendChild(opt);
            });
            modalObraSelect.innerHTML = '';
            obras.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.id;
                opt.textContent = o.name;
                modalObraSelect.appendChild(opt);
            });
        }

        function renderHistory() {
            const diarios = loadDiarios();
            diarios.sort((a,b) => b.date.localeCompare(a.date));
            const obras = loadObras();
            const obraById = {};
            obras.forEach(o => obraById[o.id] = o.name);

            historyList.innerHTML = '';
            if (!diarios.length) {
                historyList.innerHTML = '<div class="empty-state">Ainda não tens diários guardados. Grava em cima, escolhe o dia e a obra, e eles aparecem aqui.</div>';
                return;
            }

            diarios.forEach((item, idx) => {
                const r = item.report || {};
                const wrapper = document.createElement('div');
                wrapper.className = 'accordion-item' + (idx === 0 ? ' open' : '');
                wrapper.dataset.id = item.id;

                const obraNome = item.obra_id ? (obraById[item.obra_id] || 'Obra') : (item.obra_nome || r.obra || 'Obra');
                const titulo = item.titulo && item.titulo.trim()
                    ? item.titulo.trim()
                    : (r.trabalhos_executados && r.trabalhos_executados.length ? r.trabalhos_executados[0] : 'Diário de obra');

                const summary = document.createElement('button');
                summary.type = 'button';
                summary.className = 'accordion-summary';
                summary.setAttribute('aria-expanded', idx === 0 ? 'true' : 'false');
                summary.innerHTML = `
                    <span class="meta">
                        <span class="date-pill">${shortDatePt(item.date)}</span>
                        <span>${escapeHtml(titulo)}</span>
                        <span class="obra-pill">${escapeHtml(obraNome)}</span>
                    </span>
                    <svg class="accordion-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                `;

                const content = document.createElement('div');
                content.className = 'accordion-content';

                const dateInput = document.createElement('input');
                dateInput.type = 'date';
                dateInput.className = 'field-input';
                dateInput.value = item.date;

                const titInput = document.createElement('input');
                titInput.type = 'text';
                titInput.className = 'field-input';
                titInput.value = titulo;

                const obraSelect = document.createElement('select');
                obraSelect.className = 'field-select';
                obras.forEach(o => {
                    const opt = document.createElement('option');
                    opt.value = o.id;
                    opt.textContent = o.name;
                    if (item.obra_id === o.id) opt.selected = true;
                    obraSelect.appendChild(opt);
                });

                const equipaArea = document.createElement('textarea');
                equipeArea = equipaArea;
                equipaArea.className = 'field-textarea';
                equipaArea.value = (r.equipa_presente && r.equipa_presente.length) ? r.equipa_presente.join('\n') : '';

                const trabArea = document.createElement('textarea');
                trabArea.className = 'field-textarea';
                trabArea.value = (r.trabalhos_executados && r.trabalhos_executados.length) ? r.trabalhos_executados.join('\n') : '';

                const ocorArea = document.createElement('textarea');
                ocorArea.className = 'field-textarea';
                ocorArea.value = (r.ocorrencias && r.ocorrencias.length) ? r.ocorrencias.join('\n') : '';

                const planoArea = document.createElement('textarea');
                planoArea.className = 'field-textarea';
                planoArea.value = (r.plano_seguinte && r.plano_seguinte.length) ? r.plano_seguinte.join('\n') : '';

                const transArea = document.createElement('textarea');
                transArea.className = 'field-textarea';
                transArea.value = item.transcription || '';

                const actions = document.createElement('div');
                actions.className = 'inline-actions';
                const btnDel = document.createElement('button');
                btnDel.type = 'button';
                btnDel.className = 'btn-delete-line';
                btnDel.textContent = 'Apagar';
                const btnSave = document.createElement('button');
                btnSave.type = 'button';
                btnSave.className = 'btn-save-line';
                btnSave.textContent = 'Guardar alterações';
                actions.appendChild(btnDel);
                actions.appendChild(btnSave);

                content.innerHTML = `<label class="field-label">Data</label>`;
                content.appendChild(dateInput);

                const labTit = document.createElement('label');
                labTit.className = 'field-label';
                labTit.textContent = 'Título / resumo';
                content.appendChild(labTit);
                content.appendChild(titInput);

                const labObra = document.createElement('label');
                labObra.className = 'field-label';
                labObra.textContent = 'Obra';
                content.appendChild(labObra);
                content.appendChild(obraSelect);

                const labEq = document.createElement('label');
                labEq.className = 'field-label';
                labEq.textContent = 'Equipa presente';
                content.appendChild(labEq);
                content.appendChild(equipaArea);

                const labTr = document.createElement('label');
                labTr.className = 'field-label';
                labTr.textContent = 'Trabalhos executados';
                content.appendChild(labTr);
                content.appendChild(trabArea);

                const labOc = document.createElement('label');
                labOc.className = 'field-label';
                labOc.textContent = 'Ocorrências';
                content.appendChild(labOc);
                content.appendChild(ocorArea);

                const labPl = document.createElement('label');
                labPl.className = 'field-label';
                labPl.textContent = 'Plano seguinte';
                content.appendChild(labPl);
                content.appendChild(planoArea);

                const labTrs = document.createElement('label');
                labTrs.className = 'field-label';
                labTrs.textContent = 'Transcrição original';
                content.appendChild(labTrs);
                content.appendChild(transArea);

                content.appendChild(actions);

                summary.addEventListener('click', () => {
                    const isOpen = wrapper.classList.contains('open');
                    wrapper.classList.toggle('open', !isOpen);
                    summary.setAttribute('aria-expanded', !isOpen);
                });

                btnDel.addEventListener('click', () => {
                    const all = loadDiarios().filter(d => d.id !== item.id);
                    saveDiarios(all);
                    renderHistory();
                });

                btnSave.addEventListener('click', () => {
                    const all = loadDiarios();
                    const ix = all.findIndex(d => d.id === item.id);
                    if (ix === -1) return;
                    const newDate = dateInput.value ? toISODate(dateInput.value) : toISODate(new Date());
                    const newObraId = obraSelect.value;
                    const obrasNow = loadObras();
                    const newObraName = (obrasNow.find(o => o.id === newObraId) || {}).name || 'Obra';

                    all[ix] = {
                        id: item.id,
                        date: newDate,
                        obra_id: newObraId,
                        obra_nome: newObraName,
                        titulo: titInput.value.trim() || 'Diário de obra',
                        report: {
                            data: newDate,
                            obra: newObraName,
                            equipa_presente: equipaArea.value.split('\n').map(s=>s.trim()).filter(Boolean),
                            trabalhos_executados: trabArea.value.split('\n').map(s=>s.trim()).filter(Boolean),
                            materiais_recebidos: [],
                            ocorrencias: ocorArea.value.split('\n').map(s=>s.trim()).filter(Boolean),
                            plano_seguinte: planoArea.value.split('\n').map(s=>s.trim()).filter(Boolean),
                        },
                        transcription: transArea.value
                    };
                    all.sort((a,b) => b.date.localeCompare(a.date));
                    saveDiarios(all);
                    renderHistory();
                });

                wrapper.appendChild(summary);
                wrapper.appendChild(content);
                historyList.appendChild(wrapper);
            });
        }

        function openSaveModal(defaultDateIso) {
            modalDateInput.value = defaultDateIso;
            renderObraSelects();
            modalObraSelect.value = obraAtualSelect.value;
            saveModal.classList.add('show');
        }
        function closeSaveModal() { saveModal.classList.remove('show'); }

        btnModalSave.addEventListener('click', () => {
            const pickedDate = modalDateInput.value ? toISODate(modalDateInput.value) : toISODate(new Date());
            const pickedObra = modalObraSelect.value;
            if (lastReport) {
                lastReport.data = pickedDate;
                saveCurrentDiary(pickedDate, pickedObra);
            }
            closeSaveModal();
            lastReport = null;
            lastTranscription = '';
        });
        btnModalAgain.addEventListener('click', () => {
            closeSaveModal();
        });

        function saveCurrentDiary(selectedDate, obraId) {
            const all = loadDiarios();
            const obras = loadObras();
            const obraObj = obras.find(o => o.id === obraId) || {id: DEMO_OBRA_ID, name: DEMO_OBRA_NAME};
            const autoTitle = (lastReport && lastReport.trabalhos_executados && lastReport.trabalhos_executados.length)
                ? lastReport.trabalhos_executados[0]
                : 'Diário de obra';
            const item = {
                id: 'r_' + Date.now(),
                date: selectedDate,
                obra_id: obraObj.id,
                obra_nome: obraObj.name,
                titulo: autoTitle,
                report: {
                    data: selectedDate,
                    obra: obraObj.name,
                    equipa_presente: Array.isArray(lastReport.equipa_presente) ? lastReport.equipa_presente : [],
                    trabalhos_executados: Array.isArray(lastReport.trabalhos_executados) ? lastReport.trabalhos_executados : [],
                    materiais_recebidos: Array.isArray(lastReport.materiais_recebidos) ? lastReport.materiais_recebidos : [],
                    ocorrencias: Array.isArray(lastReport.ocorrencias) ? lastReport.ocorrencias : [],
                    plano_seguinte: Array.isArray(lastReport.plano_seguinte) ? lastReport.plano_seguinte : [],
                },
                transcription: lastTranscription || ''
            };
            all.push(item);
            all.sort((a,b) => b.date.localeCompare(a.date));
            saveDiarios(all);
            renderHistory();
        }

        // MODAL OBRAS
        function openObraModal() {
            const obras = loadObras();
            obraListWrap.innerHTML = '';
            obras.forEach(o => {
                const row = document.createElement('div');
                row.className = 'obra-row';
                row.dataset.id = o.id;
                row.innerHTML = `
                    <input type="text" value="${escapeHtml(o.name)}" ${o.id===DEMO_OBRA_ID ? 'readonly' : ''}>
                    ${o.id===DEMO_OBRA_ID ? '' : '<button type="button" class="del-obra">x</button>'}
                `;
                obraListWrap.appendChild(row);
            });
            obraModal.classList.add('show');
        }
        function closeObraModal() { obraModal.classList.remove('show'); }

        btnGerirObras.addEventListener('click', openObraModal);
        btnObraCancel.addEventListener('click', closeObraModal);

        btnAddObra.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'obra-row';
            const newId = 'obra_' + Date.now();
            row.dataset.id = newId;
            row.innerHTML = `
                <input type="text" value="Nova obra">
                <button type="button" class="del-obra">x</button>
            `;
            obraListWrap.appendChild(row);
        });
        obraListWrap.addEventListener('click', (e) => {
            if (e.target.classList.contains('del-obra')) {
                const row = e.target.closest('.obra-row');
                if (row && row.dataset.id !== DEMO_OBRA_ID) {
                    row.remove();
                }
            }
        });
        btnObraSave.addEventListener('click', () => {
            const rows = obraListWrap.querySelectorAll('.obra-row');
            const arr = [];
            rows.forEach(r => {
                const id = r.dataset.id;
                const name = r.querySelector('input').value.trim() || 'Obra sem nome';
                arr.push({id, name});
            });
            saveObras(arr);
            closeObraModal();
            renderObraSelects();
            renderHistory();
        });

        // GRAVAÇÃO
        micButton.addEventListener('click', async () => {
            if (state === 'idle') {
                startCountdown();
            } else if (state === 'recording') {
                stopRecording();
            }
        });

        function startCountdown() {
            state = 'countdown';
            countdownNumber.textContent = '3';
            countdownOverlay.style.display = 'flex';
            statusLine.textContent = "A iniciar…";
            let count = 3;
            const interval = setInterval(async () => {
                count--;
                if (count > 0) {
                    countdownNumber.textContent = count;
                } else {
                    clearInterval(interval);
                    countdownOverlay.style.display = 'none';
                    await startRecording();
                }
            }, 850);
        }

        async function startRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                chunks = [];
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.start();
                state = 'recording';
                micButton.classList.add('recording');
                statusLine.textContent = "A gravar… tens 30s. Toca para parar.";
                heroImage.src = "{{ asset('images/construtor2.png') }}";
                recIcon.innerHTML = '<rect x="7" y="7" width="10" height="10" rx="2" ry="2"></rect>';

                recordingSecondsLeft = RECORDING_MAX_SECONDS;
                recordingCountdown.style.display = 'flex';
                recordingCountdown.textContent = recordingSecondsLeft;

                recordingInterval = setInterval(() => {
                    recordingSecondsLeft--;
                    if (recordingSecondsLeft <= 0) {
                        clearInterval(recordingInterval);
                        recordingCountdown.style.display = 'none';
                        stopRecording();
                    } else {
                        recordingCountdown.textContent = recordingSecondsLeft;
                    }
                }, 1000);

                mediaRecorder.ondataavailable = (e) => { chunks.push(e.data); };
                mediaRecorder.onstop = async () => {
                    const blob = new Blob(chunks, { type: 'audio/webm' });
                    stream.getTracks().forEach(t => t.stop());
                    await sendAudio(blob);
                };

                stopTimeout = setTimeout(() => {
                    if (state === 'recording' && mediaRecorder && mediaRecorder.state !== 'inactive') {
                        stopRecording();
                    }
                }, (RECORDING_MAX_SECONDS + 1) * 1000);
            } catch (e) {
                console.error(e);
                statusLine.textContent = "Erro ao aceder ao microfone.";
                state = 'idle';
                heroImage.src = "{{ asset('images/construtor1.png') }}";
            }
        }

        function stopRecording() {
            if (recordingInterval) {
                clearInterval(recordingInterval);
                recordingInterval = null;
            }
            recordingCountdown.style.display = 'none';

            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
            if (stopTimeout) clearTimeout(stopTimeout);
            state = 'processing';
            micButton.classList.remove('recording');
            micButton.disabled = true;
            statusLine.textContent = "A processar…";
            heroImage.src = "{{ asset('images/construtor3.png') }}";
            recIcon.innerHTML = '<circle cx="12" cy="12" r="5" stroke-width="2" stroke="#fff" fill="none" stroke-dasharray="31.4" stroke-linecap="round"></circle>';
        }

        async function sendAudio(blob) {
            const formData = new FormData();
            formData.append('audio', blob, 'diario.webm');

            try {
                const resp = await fetch(diaryEndpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });
                const data = await resp.json();

                micButton.disabled = false;
                recIcon.innerHTML = '<path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3Zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 14 0h-2ZM11 18.93V22h2v-3.07A8.001 8.001 0 0 0 20 11h-2a6 6 0 0 1-12 0H4a8.001 8.001 0 0 0 7 7.93Z"/>';
                state = 'idle';

                if (data.error) {
                    statusLine.textContent = data.user_message || "Erro ao processar.";
                    heroImage.src = "{{ asset('images/construtor1.png') }}";
                    return;
                }

                statusLine.textContent = "Feito ✅";

                lastReport = data.report || null;
                lastTranscription = data.transcription || '';

                const iso = toISODate((lastReport && lastReport.data) ? lastReport.data : new Date());
                openSaveModal(iso);

                setTimeout(() => {
                    if (state === 'idle') heroImage.src = "{{ asset('images/construtor1.png') }}";
                }, 2000);

            } catch (e) {
                console.error(e);
                statusLine.textContent = "Erro ao enviar/processar.";
                micButton.disabled = false;
                recIcon.innerHTML = '<path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3Zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 14 0h-2ZM11 18.93V22h2v-3.07A8.001 8.001 0 0 0 20 11h-2ZM11 18.93V22h2v-3.07A8.001 8.001 0 0 0 20 11h-2Z"/>';
                state = 'idle';
                heroImage.src = "{{ asset('images/construtor1.png') }}";
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderObraSelects();
            renderHistory();
        });
    </script>
</x-app-layout>

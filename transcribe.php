<?php
require_once __DIR__ . '/config.php';
$config = get_app_config();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Transcribe (Speech-to-Text)</title>
    <style>
        :root {
            --text: #1e293b; --muted: #475569; --brand: #1f2937; --brand-2: #8B9DC3;
            --ring: rgba(148, 163, 184, .3); --surface: rgba(255, 255, 255, 0.98);
            --background-gradient: linear-gradient(to bottom, #ffffff, #f8fafc, #e2e8f0);
            --radius-lg: 16px; --radius-md: 12px; --radius-sm: 8px;
            --shadow-primary: 0 10px 30px rgba(0, 0, 0, .07); --gap: 24px;
            --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Roboto, system-ui, sans-serif;
            --h2-size: clamp(22px, 3.6vw, 34px); --kicker-size: 12px;
            --error-bg: #fef2f2; --error-border: #fca5a5; --error-text: #991b1b;
            --warn-bg: #fffbeb; --warn-border: #fcd34d; --warn-text: #92400e;
            --ok-bg:   #f0fdf4; --ok-border:   #86efac; --ok-text:   #166534;
        }
        body { font-family: var(--font-family); margin: 0; background: var(--background-gradient);
               color: var(--text); padding: var(--gap); min-height: 100vh; }
        .wrapper { max-width: 1200px; margin: 0 auto; }
        h1 { font-size: clamp(34px, 6.2vw, 64px); font-weight: 700;
             padding-top: 24px; margin-bottom: 32px; text-align: center; }
        h2 { font-size: var(--h2-size); font-weight: 600; margin-bottom: var(--gap);
             border-bottom: 1px solid var(--ring); padding-bottom: 16px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); }
        .card { background: var(--surface); border: 1px solid var(--ring);
                border-radius: var(--radius-lg); padding: var(--gap);
                box-shadow: var(--shadow-primary); }
        input[type="file"], select, input[type="text"] {
            width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring);
            border-radius: var(--radius-sm); box-sizing: border-box; background-color: #f8fafc;
            font-family: var(--font-family); font-size: 14px;
        }
        input[type="file"]:focus, select:focus, input[type="text"]:focus {
            border-color: var(--brand-2); outline: none;
            box-shadow: 0 0 0 3px rgba(139, 157, 195, 0.1);
        }
        label { display: block; margin-bottom: 8px; font-weight: 600;
                color: var(--text); font-size: var(--kicker-size);
                text-transform: uppercase; letter-spacing: .08em; }
        button { padding: 12px 20px; border: none; border-radius: var(--radius-sm);
                 cursor: pointer; background-color: var(--brand-2); color: white;
                 margin-right: 8px; margin-bottom: 8px; font-weight: 600;
                 transition: all 0.2s ease; font-size: 14px; }
        button:hover { background-color: var(--brand); transform: translateY(-1px); }
        button:disabled { background-color: #a0aec0; cursor: not-allowed; transform: none; }
        .response-area { margin-top: var(--gap); padding: var(--gap); background: #f8fafc;
                         border: 1px solid var(--ring); border-radius: var(--radius-sm);
                         white-space: pre-wrap; font-family: 'SF Mono', Menlo, monospace;
                         min-height: 100px; max-height: 600px; overflow-y: auto; font-size: 13px; }
        .hint { font-size: 12px; color: var(--muted); margin-top: -8px; margin-bottom: 16px; line-height: 1.5; }
        .status { padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 16px; font-size: 14px; }
        .status.ok    { background: var(--ok-bg);    border: 1px solid var(--ok-border);    color: var(--ok-text); }
        .status.err   { background: var(--error-bg); border: 1px solid var(--error-border); color: var(--error-text); }
        .status.warn  { background: var(--warn-bg);  border: 1px solid var(--warn-border);  color: var(--warn-text); }
        .status.info  { background: #f1f5f9; border: 1px solid var(--ring); color: var(--muted); }
        .segment-list { display: flex; flex-direction: column; gap: 6px; }
        .segment { display: grid; grid-template-columns: 80px 60px 1fr; gap: 12px;
                   align-items: start; padding: 8px 12px; border-radius: var(--radius-sm); }
        .segment .time { font-family: 'SF Mono', Menlo, monospace; font-size: 12px; color: var(--muted); }
        .segment .speaker { font-weight: 700; }
        .segment .text { line-height: 1.5; }
        /* Speaker color palette — pastel, consistent across runs */
        .sp-A { background: #eff6ff; } .sp-A .speaker { color: #1d4ed8; }
        .sp-B { background: #f0fdf4; } .sp-B .speaker { color: #15803d; }
        .sp-C { background: #fdf4ff; } .sp-C .speaker { color: #a21caf; }
        .sp-D { background: #fff7ed; } .sp-D .speaker { color: #c2410c; }
        .sp-E { background: #fef2f2; } .sp-E .speaker { color: #b91c1c; }
        .sp-F { background: #f0fdfa; } .sp-F .speaker { color: #0f766e; }
        .meta { display: flex; flex-wrap: wrap; gap: 8px; font-size: 12px; color: var(--muted); margin-bottom: 12px; }
        .meta span { background: #f1f5f9; padding: 4px 10px; border-radius: 999px; }
        .copy-btn { background: transparent; color: var(--brand-2); border: 1px solid var(--brand-2);
                    padding: 6px 12px; font-size: 12px; margin-top: 8px; }
        .copy-btn:hover { background: var(--brand-2); color: white; }

        @media (max-width: 768px) {
            body { padding: 16px; }
            .container { grid-template-columns: 1fr; gap: 20px; }
            .card { padding: 20px; }
            button { width: 100%; min-height: 44px; padding: 14px 20px; font-size: 16px; }
            .segment { grid-template-columns: 70px 50px 1fr; gap: 8px; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <h1>Speech-to-Text</h1>

    <div class="container">
        <!-- LEFT: Input form -->
        <div class="card">
            <h2>Audio &amp; Optionen</h2>

            <label for="audio-file">Audio-Datei</label>
            <input type="file" id="audio-file" accept="audio/*">
            <div class="hint">
                Erlaubt: mp3, m4a, wav, webm, ogg, flac.
                OpenAI-Modelle: max 25&nbsp;MB pro Datei.
                Diarisierungs-Modell zusätzlich max&nbsp;23&nbsp;min (1400&nbsp;s) Audio.
                Gemini hat keine harten Größenlimits.
            </div>

            <label for="model">Modell</label>
            <select id="model">
                <optgroup label="OpenAI (kostet API-Credits)">
                    <option value="whisper-1" selected>whisper-1 — Klassiker, günstig, kein Speaker</option>
                    <option value="gpt-4o-transcribe">gpt-4o-transcribe — neuer, bessere Qualität</option>
                    <option value="gpt-4o-mini-transcribe">gpt-4o-mini-transcribe — günstiger als gpt-4o</option>
                    <option value="gpt-4o-transcribe-diarize">gpt-4o-transcribe-diarize — MIT Speaker A/B/C…</option>
                </optgroup>
                <optgroup label="Google Gemini (Free-Tier, kostenlos)">
                    <option value="gemini-2.5-flash">gemini-2.5-flash — current default, kein Speaker</option>
                    <option value="gemini-2.5-flash-lite">gemini-2.5-flash-lite — am schnellsten/günstigsten</option>
                    <option value="gemini-2.5-pro">gemini-2.5-pro — beste Qualität, langsamer</option>
                    <option value="gemini-2.0-flash">gemini-2.0-flash — älteres Modell</option>
                </optgroup>
            </select>
            <div class="hint" id="model-hint">
                whisper-1: klassisches Whisper-Modell, sehr robust.
            </div>

            <label for="language">Sprache</label>
            <select id="language">
                <option value="" selected>auto-detect</option>
                <option value="de">de — Deutsch</option>
                <option value="en">en — English</option>
                <option value="it">it — Italiano</option>
                <option value="sl">sl — Slovenščina</option>
                <option value="fr">fr — Français</option>
                <option value="es">es — Español</option>
                <option value="zh">zh — 中文</option>
            </select>
            <div class="hint">
                Nur für OpenAI-Modelle. Gemini ignoriert das Feld, erkennt selbst.
                Bei bekannter Sprache klar bessere Accuracy.
            </div>

            <label for="response-format">Response-Format</label>
            <select id="response-format">
                <option value="" selected>text (Default — nur Volltext)</option>
                <option value="json">json — mit Metadaten</option>
                <option value="diarized_json">diarized_json — Speaker-Segmente (nur Diarize-Modell)</option>
            </select>
            <div class="hint">
                <code>diarized_json</code> wird automatisch aktiviert wenn Diarize-Modell gewählt ist.
            </div>

            <label for="prompt-hint">Kontext-Hint (optional)</label>
            <input type="text" id="prompt-hint" placeholder="z.B. Eigennamen, Fachbegriffe">
            <div class="hint">
                Nur OpenAI. Hilft beim Erkennen schwieriger Begriffe (z.B. "Tscheppaschlucht, Karawanken").
            </div>

            <div class="btn-group">
                <button id="transcribe-btn" onclick="runTranscribe()">Transkribieren</button>
                <button id="clear-btn" onclick="clearResult()" style="background:#94a3b8;">Zurücksetzen</button>
            </div>

            <div id="status-area"></div>
        </div>

        <!-- RIGHT: Result -->
        <div class="card">
            <h2>Ergebnis</h2>
            <div id="result-meta" class="meta" style="display:none;"></div>
            <div id="result-area" class="response-area">Noch keine Transkription gestartet.</div>
            <button class="copy-btn" id="copy-btn" onclick="copyResult()" style="display:none;">Text in Zwischenablage kopieren</button>
        </div>
    </div>
</div>

<script>
const API_BASE_URL = '<?= js_config('api_ai_base_url'); ?>';
const API_KEY = 'Inetpass1';

const MODEL_HINTS = {
    'whisper-1': 'whisper-1: klassisches Whisper-Modell, sehr robust.',
    'gpt-4o-transcribe': 'gpt-4o-transcribe: neuer, akkurater bei Deutsch/Dialekten. Teurer als whisper-1.',
    'gpt-4o-mini-transcribe': 'gpt-4o-mini-transcribe: ähnliche Qualität wie gpt-4o, günstiger.',
    'gpt-4o-transcribe-diarize': 'gpt-4o-transcribe-diarize: liefert Speaker-Labels A/B/C/D. ⚠️ Max 23 Min Audio. Setze Response-Format auf diarized_json.',
    'gemini-2.5-flash': 'gemini-2.5-flash: kostenlos via Google Free-Tier. Kein Speaker-Label, ignoriert language-Feld.',
    'gemini-2.5-flash-lite': 'gemini-2.5-flash-lite: schnellste + günstigste Variante, kostenlos.',
    'gemini-2.5-pro': 'gemini-2.5-pro: kostenlos, beste Qualität, etwas langsamer.',
    'gemini-2.0-flash': 'gemini-2.0-flash: ältere Version, kostenlos. Nur als Fallback.',
};

const modelEl = document.getElementById('model');
const formatEl = document.getElementById('response-format');
const hintEl = document.getElementById('model-hint');

modelEl.addEventListener('change', () => {
    const m = modelEl.value;
    hintEl.textContent = MODEL_HINTS[m] || '';
    if (m.includes('diarize')) {
        formatEl.value = 'diarized_json';
    } else if (formatEl.value === 'diarized_json') {
        formatEl.value = '';  // reset wenn nicht-diarize gewählt
    }
});

function fmtTime(s) {
    const m = Math.floor(s / 60), sec = Math.floor(s % 60);
    return `${m.toString().padStart(2,'0')}:${sec.toString().padStart(2,'0')}`;
}
function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]);
}

function setStatus(kind, text) {
    const el = document.getElementById('status-area');
    el.innerHTML = `<div class="status ${kind}">${escapeHtml(text)}</div>`;
}
function clearStatus() { document.getElementById('status-area').innerHTML = ''; }

function clearResult() {
    document.getElementById('result-area').textContent = 'Noch keine Transkription gestartet.';
    document.getElementById('result-meta').style.display = 'none';
    document.getElementById('copy-btn').style.display = 'none';
    clearStatus();
}

let lastPlainText = '';

function copyResult() {
    if (!lastPlainText) return;
    navigator.clipboard.writeText(lastPlainText).then(() => {
        const btn = document.getElementById('copy-btn');
        const orig = btn.textContent;
        btn.textContent = '✓ kopiert';
        setTimeout(() => { btn.textContent = orig; }, 1500);
    });
}

function renderResult(data) {
    const metaEl = document.getElementById('result-meta');
    const area = document.getElementById('result-area');
    const meta = [];
    if (data.model) meta.push(`Modell: ${data.model}`);
    if (data.language) meta.push(`Sprache: ${data.language}`);
    if (data.filename) meta.push(`Datei: ${data.filename}`);
    if (data.response_format) meta.push(`Format: ${data.response_format}`);
    metaEl.innerHTML = meta.map(s => `<span>${escapeHtml(s)}</span>`).join('');
    metaEl.style.display = meta.length ? 'flex' : 'none';

    if (data.diarized && Array.isArray(data.diarized.segments)) {
        const segs = data.diarized.segments;
        const speakers = new Set(segs.map(s => s.speaker).filter(Boolean));
        metaEl.innerHTML += `<span>Segmente: ${segs.length}</span><span>Sprecher: ${[...speakers].join(', ')}</span>`;
        if (data.diarized.duration) metaEl.innerHTML += `<span>Dauer: ${fmtTime(data.diarized.duration)}</span>`;

        // Collapse consecutive same-speaker segments for readability
        const turns = [];
        for (const seg of segs) {
            const last = turns[turns.length - 1];
            if (last && last.speaker === seg.speaker) {
                last.text += ' ' + (seg.text || '').trim();
                last.end = seg.end;
            } else {
                turns.push({ speaker: seg.speaker || '?', start: seg.start, end: seg.end, text: (seg.text || '').trim() });
            }
        }

        let html = '<div class="segment-list">';
        for (const t of turns) {
            const cls = 'sp-' + (t.speaker.match(/[A-Z]/) || ['A'])[0];
            html += `<div class="segment ${cls}">
                       <div class="time">${fmtTime(t.start)}</div>
                       <div class="speaker">${escapeHtml(t.speaker)}</div>
                       <div class="text">${escapeHtml(t.text)}</div>
                     </div>`;
        }
        html += '</div>';
        area.innerHTML = html;
        lastPlainText = turns.map(t => `[${fmtTime(t.start)}] ${t.speaker}: ${t.text}`).join('\n');
    } else if (data.text) {
        area.textContent = data.text;
        lastPlainText = data.text;
    } else {
        area.textContent = JSON.stringify(data, null, 2);
        lastPlainText = area.textContent;
    }
    document.getElementById('copy-btn').style.display = lastPlainText ? 'inline-block' : 'none';
}

async function runTranscribe() {
    clearStatus();
    const fileInput = document.getElementById('audio-file');
    if (!fileInput.files.length) {
        setStatus('warn', 'Bitte zuerst eine Audio-Datei auswählen.');
        return;
    }
    const file = fileInput.files[0];
    const model = modelEl.value;
    const language = document.getElementById('language').value;
    const responseFormat = formatEl.value;
    const prompt = document.getElementById('prompt-hint').value.trim();

    const form = new FormData();
    form.append('file', file);
    form.append('model', model);
    if (language) form.append('language', language);
    if (responseFormat) form.append('response_format', responseFormat);
    if (prompt) form.append('prompt', prompt);

    const btn = document.getElementById('transcribe-btn');
    btn.disabled = true;
    const sizeMb = (file.size / 1024 / 1024).toFixed(1);
    setStatus('info', `Sende ${file.name} (${sizeMb} MB) an ${model}… kann je nach Audiolänge ein paar Minuten dauern.`);
    document.getElementById('result-area').textContent = '⏳ Transkription läuft…';

    const started = Date.now();
    try {
        const response = await fetch(`${API_BASE_URL}/ai/transcribe`, {
            method: 'POST',
            headers: { 'X-API-KEY': API_KEY },
            body: form,
        });
        const elapsed = ((Date.now() - started) / 1000).toFixed(1);
        const data = await response.json().catch(() => null);

        if (!response.ok) {
            // Handle our actionable error-codes (429 quota, 400 audio-invalid, etc.)
            let msg = `HTTP ${response.status}`;
            if (data && data.detail) {
                if (typeof data.detail === 'object') {
                    if (data.detail.code === 'openai_quota_exhausted') {
                        msg = '⚠️ OpenAI-Quota erschöpft — Budget aufladen ODER Modell auf gemini-2.0-flash-exp wechseln (kostenlos, ohne Speaker-Label).';
                    } else if (data.detail.code === 'audio_invalid_for_model') {
                        msg = '⚠️ Audio nicht kompatibel — vermutlich zu lang für Diarize (max 23 min) oder falsches Format.';
                    } else {
                        msg = data.detail.error || JSON.stringify(data.detail);
                    }
                    if (data.detail.upstream) msg += `\n\nUpstream: ${data.detail.upstream}`;
                } else {
                    msg = String(data.detail);
                }
            }
            setStatus('err', `${msg} (${elapsed}s)`);
            document.getElementById('result-area').textContent = JSON.stringify(data, null, 2);
            return;
        }

        setStatus('ok', `✓ Fertig in ${elapsed}s.`);
        renderResult(data);
    } catch (e) {
        setStatus('err', `Netzwerk-/Parse-Fehler: ${e.message}`);
        document.getElementById('result-area').textContent = String(e);
    } finally {
        btn.disabled = false;
    }
}
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dialog Builder</title>
    <style>
        :root { --ring: rgba(148,163,184,.3); --surface: rgba(255,255,255,.98); --text:#1e293b; --muted:#475569; --brand:#1f2937; --brand-2:#8B9DC3; --radius:12px; }
        body { font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Roboto,system-ui,sans-serif; margin:0; background: linear-gradient(to bottom,#ffffff,#f8fafc,#e2e8f0); color:var(--text); padding:24px; }
        .wrapper { max-width:1200px; margin:0 auto; }
        h1 { font-size: clamp(28px,4vw,48px); font-weight:700; margin:32px 0; text-align:center; }
        .card { background:var(--surface); border:1px solid var(--ring); border-radius:16px; padding:20px; box-shadow: 0 10px 30px rgba(0,0,0,.07); }
        .row { display:grid; grid-template-columns: 1fr 1fr; gap:24px; }
        @media (max-width: 900px){ .row { grid-template-columns: 1fr; } }
        label { display:block; font-size:12px; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); margin:10px 0 6px; font-weight:600; }
        textarea, select, input[type="text"] { width:100%; box-sizing:border-box; border:1px solid var(--ring); border-radius:8px; background:#f8fafc; padding:12px; }
        .btn { padding:10px 16px; border:none; border-radius:8px; background:var(--brand-2); color:#fff; font-weight:600; cursor:pointer; margin-right:8px; }
        .btn:disabled { background:#a0aec0; cursor:not-allowed; }
        .btn.secondary { background:#1f2937; }
        .btn.link { background:transparent; color:var(--brand); }
        .response { white-space:pre-wrap; background:#f8fafc; border:1px solid var(--ring); border-radius:8px; padding:12px; min-height:120px; }
        .stepper { display:flex; gap:8px; justify-content:center; margin: 8px 0 20px; flex-wrap:wrap; }
        .step { padding:8px 12px; border-radius:999px; border:1px solid var(--ring); background: rgba(248,250,252,.8); font-size:13px; }
        .step.active { background:var(--brand-2); color:#fff; border-color:transparent; }
        .toolbar { display:flex; gap:8px; justify-content:flex-end; margin-top:12px; }
        .inline { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
        .muted { color: var(--muted); font-size:12px; }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include 'menu.php'; ?>
    <h1>Dialog Builder</h1>

    <div class="stepper">
        <div id="step-1-pill" class="step active">1 • Analyze</div>
        <div id="step-2-pill" class="step">2 • Generate</div>
        <div id="step-3-pill" class="step">3 • Mix</div>
    </div>

    <div id="panel-1" class="card">
        <div class="row">
            <div>
                <label for="dlg-text">Dialog Text</label>
                <textarea id="dlg-text" rows="8" placeholder="Person A: ...\nPerson B: ..."></textarea>
                <label for="dlg-language">Language</label>
                <select id="dlg-language">
                    <option value="de-DE" selected>German (de-DE)</option>
                    <option value="en-US">English (en-US)</option>
                    <option value="fr-FR">French (fr-FR)</option>
                    <option value="it-IT">Italian (it-IT)</option>
                    <option value="es-ES">Spanish (es-ES)</option>
                </select>
                <label for="dlg-voice-map">Voice Mapping (JSON)</label>
                <textarea id="dlg-voice-map" rows="4" placeholder='{"Narrator":"shimmer"}'></textarea>
                <label for="dlg-analysis-hint">Analysis Hint (optional)</label>
                <textarea id="dlg-analysis-hint" rows="3" placeholder="Optional guidance for the director AI (keeps fixed JSON schema)"></textarea>
                <div class="inline">
                    <label>Music</label>
                    <label class="inline"><input type="radio" name="music-mode" value="none" checked> None</label>
                    <label class="inline"><input type="radio" name="music-mode" value="generate"> Generate</label>
                    <label class="inline"><input type="radio" name="music-mode" value="existing"> Use existing</label>
                    <input type="text" id="dlg-music-id" placeholder="Storage ID" style="max-width:220px" disabled>
                </div>
                <!-- Narrator gender now follows AI analysis by default; control removed per request -->
                <div class="inline">
                    <label class="inline"><input type="checkbox" id="dlg-add-sfx"> Add SFX</label>
                    <label class="inline"><input type="checkbox" id="dlg-title-image"> Title Image</label>
                    <label class="inline"><input type="checkbox" id="dlg-ai-male"> AI voice male</label>
                </div>
                <div class="inline">
                    <label>Music behavior</label>
                    <label class="inline"><input type="checkbox" id="dlg-music-loop" checked> Loop if too short</label>
                    <label class="inline"><input type="checkbox" id="dlg-music-stop"> Stop at dialog end</label>
                </div>
            </div>
            <div>
                <label>Analysis Result</label>
                <div id="analysis-json" class="response"></div>
                <div class="muted" id="status-text"></div>
            </div>
        </div>
        <div class="toolbar">
            <button class="btn" id="btn-analyze">Analyze</button>
        </div>
    </div>

    <div id="panel-2" class="card" style="display:none;">
        <div class="row">
            <div>
                <label>Items</label>
                <div id="gen-items" class="response" style="min-height:160px;"></div>
            </div>
            <div>
                <label>Progress</label>
                <div id="gen-progress" class="response"></div>
                <label style="margin-top:12px;">Live Status</label>
                <div id="gen-status" class="response" style="min-height:80px;"></div>
                <label style="margin-top:12px;">Event Log</label>
                <div id="gen-log" class="response" style="min-height:80px; max-height:200px; overflow:auto;"></div>
                <label style="margin-top:12px;">Timeline</label>
                <div id="gen-timeline" class="response" style="min-height:120px;"></div>
                <label style="margin-top:12px;">Title Image</label>
                <img id="gen-image" src="" alt="Title Image" style="display:none; max-width:100%; border-radius:8px; margin-top:6px;">
            </div>
        </div>
        <div class="toolbar">
            <button class="btn link" id="btn-back-2">← Back</button>
            <button class="btn" id="btn-generate">Generate</button>
            <button class="btn link" id="btn-cancel-2" style="display:none;">Cancel</button>
            <button class="btn secondary" id="btn-next-2" disabled>Next →</button>
        </div>
    </div>

    <div id="panel-3" class="card" style="display:none;">
        <div class="row">
            <div>
                <label>Final Mix</label>
                <div id="final-json" class="response"></div>
            </div>
            <div>
                <label>Preview</label>
                <audio id="final-audio" controls style="width:100%; display:none;"></audio>
                <div class="inline" style="margin-top:8px;">
                    <a id="final-download" class="btn" href="#" download style="display:none;">Download</a>
                </div>
            </div>
        </div>
        <div class="toolbar">
            <button class="btn link" id="btn-back-3">← Back</button>
        </div>
    </div>

</div>

<script>
const API_BASE_URL = 'https://api.arkturian.com';
const API_KEY = 'Inetpass1';

const state = {
  requestId: null,
  payload: null,
  analysis: null,
  result: null
};

function setActive(step){
  ['step-1-pill','step-2-pill','step-3-pill'].forEach((id,i)=>{
    document.getElementById(id).classList.toggle('active', i===step-1);
  });
  document.getElementById('panel-1').style.display = step===1?'block':'none';
  document.getElementById('panel-2').style.display = step===2?'block':'none';
  document.getElementById('panel-3').style.display = step===3?'block':'none';
}

// Music mode interactions
document.querySelectorAll('input[name="music-mode"]').forEach(r=>{
  r.addEventListener('change',()=>{
    const mode = document.querySelector('input[name="music-mode"]:checked').value;
    document.getElementById('dlg-music-id').disabled = (mode !== 'existing');
  });
});

function buildPayload(analyzeOnly){
  const mode = document.querySelector('input[name="music-mode"]:checked').value;
  const manualId = document.getElementById('dlg-music-id').value.trim();
  const voiceMapText = (document.getElementById('dlg-voice-map').value || '').trim();
  let voiceMap = {};
  try { if (voiceMapText) voiceMap = JSON.parse(voiceMapText); } catch(e) { /* ignore - validated on analyze */ }
  const id = `dialog-${Date.now()}`;
  state.requestId = id;
  const cfg = {
    provider: 'openai',
    output_format: 'mp3',
    dialog_mode: true,
    voice_mapping: voiceMap,
    ai_gender: document.getElementById('dlg-ai-male').checked ? 'male':'female',
    narrator_gender: (document.querySelector('input[name="narrator-gender"]:checked')?.value)||null,
    analysis_user_hint: (document.getElementById('dlg-analysis-hint').value||null),
    generate_title_image: document.getElementById('dlg-title-image').checked,
    add_sfx: document.getElementById('dlg-add-sfx').checked,
    add_music: mode !== 'none',
    manual_music_storage_id: (mode==='existing' && manualId) ? manualId : null,
    music_loop: document.getElementById('dlg-music-loop').checked,
    music_stop_at_end: document.getElementById('dlg-music-stop').checked,
    analyze_only: !!analyzeOnly
  };
  const payload = {
    id,
    timestamp: new Date().toISOString(),
    content: { text: document.getElementById('dlg-text').value, language: document.getElementById('dlg-language').value, speed: 1.0 },
    config: cfg,
    collection_id: 'ai_hörbuch',
    save_options: { is_public: true, owner_email: 'apopovic.aut@gmail.com' }
  };
  state.payload = payload;
  return payload;
}

// Analyze
document.getElementById('btn-analyze').addEventListener('click', async (ev)=>{
  // If we already analyzed, treat this as Next →
  if (state.analysis) { setActive(2); doGenerate(); return; }
  const text = document.getElementById('dlg-text').value.trim();
  const status = document.getElementById('status-text');
  if(!text){ alert('Please enter dialog text.'); return; }
  // Validate voice map JSON
  try { const vm = document.getElementById('dlg-voice-map').value.trim(); if(vm) JSON.parse(vm); } catch(e){ alert('Voice Mapping JSON invalid.'); return; }
  const btn = ev.currentTarget; btn.disabled = true; btn.textContent = 'Analyzing…';
  const payload = buildPayload(true);
  document.getElementById('analysis-json').textContent = '';
  status.textContent = 'Analyzing…';
  const started = Date.now();
  const timer = setInterval(()=>{
    const s = Math.floor((Date.now()-started)/1000); const mm = String(Math.floor(s/60)).padStart(2,'0'); const ss = String(s%60).padStart(2,'0');
    status.textContent = `Analyzing… elapsed ${mm}:${ss}`;
  },1000);
  try{
    const resp = await fetch(`${API_BASE_URL}/ai/generate_speech`, {
      method:'POST', headers:{ 'Content-Type':'application/json','X-API-KEY':API_KEY }, body: JSON.stringify(payload)
    });
    const text = await resp.text();
    clearInterval(timer);
    let data = null;
    try { data = JSON.parse(text); } catch(parseErr){
      const snippet = (text || '').slice(0,200);
      status.textContent = `Analyze failed (HTTP ${resp.status}). ${snippet}`;
      btn.textContent = 'Analyze'; btn.disabled = false;
      return;
    }
    if(!resp.ok){
      const detail = (data && data.detail) ? data.detail : (text || `HTTP ${resp.status}`);
      status.textContent = `Analyze failed: ${typeof detail === 'string' ? detail : JSON.stringify(detail).slice(0,200)}`;
      btn.textContent = 'Analyze'; btn.disabled = false;
      return;
    }
    state.analysis = data.analysis_result || null;
    document.getElementById('analysis-json').textContent = JSON.stringify(state.analysis, null, 2);
    status.textContent = state.analysis ? 'Analysis ready. Review and click Next →' : 'No analysis returned';
    // persist chosen music mode into generation defaults
    const chosenMode = document.querySelector('input[name="music-mode"]:checked')?.value || 'none';
    const gm = document.getElementById('gen-music'); if (gm) gm.checked = (chosenMode !== 'none');
    // morph button to Next but do NOT auto-advance
    btn.textContent = 'Next →'; btn.disabled = false;
  }catch(err){ clearInterval(timer); status.textContent = `Error: ${err.message}`; btn.textContent = 'Analyze'; btn.disabled = false; }
});

// Reset analysis state when inputs change
['dlg-text','dlg-voice-map','dlg-language','dlg-music-id'].forEach(id=>{
  const el = document.getElementById(id);
  if (el){
    const handler = ()=>{ state.analysis = null; const b = document.getElementById('btn-analyze'); if (b) { b.textContent='Analyze'; b.disabled=false; } };
    el.addEventListener('input', handler);
    el.addEventListener('change', handler);
  }
});


// Back from step 2
document.getElementById('btn-back-2').addEventListener('click', ()=>{ setActive(1); });

// Generate
async function doGenerate(){
  const progress = document.getElementById('gen-progress');
  progress.textContent = 'Generating assets…';
  const genBtn = document.getElementById('btn-generate');
  const nextBtn = document.getElementById('btn-next-2');
  const cancelBtn = document.getElementById('btn-cancel-2');
  // Prevent duplicate clicks during generation
  if (genBtn) genBtn.disabled = true;
  if (nextBtn) nextBtn.disabled = true;
  if (cancelBtn) { cancelBtn.style.display = 'inline-block'; cancelBtn.disabled = false; }
  const payload = buildPayload(false); // same fields, analyze_only=false
  // If user edited the analysis JSON, send it as override
  try {
    const edited = document.getElementById('analysis-json').textContent;
    if (edited && edited.trim()) {
      const parsed = JSON.parse(edited);
      payload.config.analysis_override = parsed;
    }
  } catch(e) { /* ignore parse errors, fall back to server analysis */ }
  try{
    // Start background job
    const startResp = await fetch(`${API_BASE_URL}/ai/dialog/start`, { method:'POST', headers:{ 'Content-Type':'application/json','X-API-KEY':API_KEY }, body: JSON.stringify(payload) });
    const startData = await startResp.json();
    if(!startResp.ok){ throw new Error(startData.detail || `HTTP ${startResp.status}`); }
    const jobId = startData.job_id || state.payload.id;

    // Poll status until done
    let running = true; let lastPhase = '';
    let failCount = 0;
    const backoff = (n)=> Math.min(5000, 1200 * Math.pow(1.5, Math.max(0, n-1)));
    async function fetchStatus(url, timeoutMs){
      const ctrl = new AbortController();
      const t = setTimeout(()=> ctrl.abort(), timeoutMs);
      try{
        const resp = await fetch(url, { signal: ctrl.signal });
        const text = await resp.text();
        if (!resp.ok){ throw new Error(`HTTP ${resp.status}: ${text.slice(0,120)}`); }
        try { return JSON.parse(text); } catch(e){ throw new Error(`Bad JSON: ${text.slice(0,120)}`); }
      } finally { clearTimeout(t); }
    }
    const poll = async ()=>{
      if(!running) return;
      let st = null;
      try{
        st = await fetchStatus(`${API_BASE_URL}/ai/dialog/status?id=${encodeURIComponent(jobId)}`, 8000);
        failCount = 0;
      }catch(err){
        progress.textContent = `Status error: ${err.message}`;
        if (failCount++ >= 3){ running = false; if (cancelBtn) cancelBtn.style.display='none'; return; }
        return setTimeout(poll, backoff(failCount));
      }
      const p = st.phase || st.status || 'unknown';
      const sp = st.subphase ? ` • ${st.subphase}` : '';
      progress.textContent = `Generating assets… ${p}${sp}`;
      const statusEl = document.getElementById('gen-status');
      const logEl = document.getElementById('gen-log');
      if (statusEl){
        const lines = [];
        lines.push(`phase: ${p}`);
        if (st.subphase) lines.push(`subphase: ${st.subphase}`);
        if (st.total_cues !== undefined) lines.push(`cues: ${st.total_cues}`);
        if (st.storage_id) lines.push(`music storage_id: ${st.storage_id}`);
        if (st.error) lines.push(`error: ${st.error}`);
        // Friendly hints
        if (st.subphase === 'music_manual') lines.push('Using provided music (no provider calls).');
        if (st.subphase === 'music_provider_eleven_start') lines.push('Music: ElevenLabs generating…');
        if (st.subphase === 'music_provider_aiml_start') lines.push('Music: AIMLAPI generating…');
        if (st.subphase === 'music_provider_fallback_start') lines.push('Music: Sourcing fallback track…');
        if (st.subphase && st.subphase.endsWith('_error')) lines.push('Hint: Try Music=None or Use existing to avoid provider waits.');
        statusEl.textContent = lines.join('\n');
      }
      if (logEl){
        const hist = Array.isArray(st.history) ? st.history : [];
        const fmt = (t)=> new Date((t||0)*1000).toLocaleTimeString();
        const rows = hist.map(ev=>{
          const parts = [];
          if (ev.phase) parts.push(`phase=${ev.phase}`);
          if (ev.subphase) parts.push(`sub=${ev.subphase}`);
          if (ev.description) parts.push(`desc=${ev.description}`);
          if (ev.sfx_id) parts.push(`sfx=${ev.sfx_id}`);
          if (ev.storage_id) parts.push(`music=${ev.storage_id}`);
          if (ev.error) parts.push(`error=${ev.error}`);
          if (ev.duration_ms!==undefined) parts.push(`ms=${ev.duration_ms}`);
          return `[${fmt(ev.ts)}] ${parts.join(' | ')}`;
        });
        logEl.textContent = rows.join('\n');
        logEl.scrollTop = logEl.scrollHeight;
      }
      if (p === 'done' && st.result){
        running = false;
        // Use server-provided final result compatible with /ai/generate_speech
        state.result = st.result;
        progress.textContent = 'Assets generated.';
        document.getElementById('btn-next-2').disabled = false;
        if (cancelBtn) cancelBtn.style.display = 'none';
        // Render UI from the same code path used before
        renderItemsAndTimeline(state.result);
        return;
      }
      if (p === 'error'){
        running = false; progress.textContent = `Error: ${st.error || 'unknown error'}`;
        if (cancelBtn) cancelBtn.style.display = 'none';
        return;
      }
      setTimeout(poll, backoff(0));
    };

    // Hook cancel
    if (cancelBtn){
      cancelBtn.onclick = async ()=>{
        cancelBtn.disabled = true;
        try{ await fetch(`${API_BASE_URL}/ai/dialog/cancel?id=${encodeURIComponent(jobId)}`, { method:'POST', headers:{ 'X-API-KEY': API_KEY } });
          progress.textContent = 'Cancelled.';
        }catch(_){ progress.textContent = 'Cancel requested.'; }
      };
    }

    poll();
  }catch(err){ progress.textContent = `Error: ${err.message}`; }
  finally {
    if (genBtn) genBtn.disabled = false;
  }
}

function renderItemsAndTimeline(data){
  const itemsEl = document.getElementById('gen-items');
  const tlEl = document.getElementById('gen-timeline');
  const imgEl = document.getElementById('gen-image');
  itemsEl.textContent = ''; tlEl.textContent='';
  const analysis = (data && data.analysis_result) ? data.analysis_result : null;
  const timeline = analysis && analysis.mix_timeline ? analysis.mix_timeline : [];
  const genImg = data && data.generated_image && data.generated_image.file_url ? data.generated_image.file_url : null;
  if (genImg) { imgEl.src = genImg; imgEl.style.display = 'block'; } else { imgEl.style.display = 'none'; }

  const fmt = (s)=>{ const mm = String(Math.floor(s/60)).padStart(2,'0'); const ss = String(Math.floor(s%60)).padStart(2,'0'); const ms = String(Math.round((s%1)*1000)).padStart(3,'0'); return `${mm}:${ss}.${ms}`; };

  const table = document.createElement('table'); table.style.width='100%'; table.style.fontSize='13px';
  const head = document.createElement('thead'); head.innerHTML = '<tr><th align="left">#</th><th align="left">Kind</th><th align="left">Label</th><th align="left">Start</th><th align="left">Dur</th><th align="left">Actions</th></tr>';
  table.appendChild(head);
  const body = document.createElement('tbody');
  timeline.forEach((it, idx)=>{
    if(it.kind === 'music') return;
    const tr = document.createElement('tr');
    const label = it.kind==='dialog' ? `${it.speaker||''} ${it.voice_style? '('+it.voice_style+')':''}` : (it.kind==='sfx'? (it.description||'SFX') : it.type);
    tr.innerHTML = `<td>${idx+1}</td><td>${it.kind}</td><td>${label}</td><td>${fmt(it.start_s||0)}</td><td>${fmt(it.duration_s||0)}</td><td></td>`;
    const actions = tr.lastChild;
    const playBtn = document.createElement('button'); playBtn.className='btn'; playBtn.textContent='Play'; playBtn.style.padding='6px 10px';
    const removeBtn = document.createElement('button'); removeBtn.className='btn link'; removeBtn.textContent='Remove';
    const regenBtn = document.createElement('button'); regenBtn.className='btn link'; regenBtn.textContent='Regenerate';
    actions.appendChild(playBtn); actions.appendChild(removeBtn); actions.appendChild(regenBtn);

    playBtn.addEventListener('click', async ()=>{
      try{
        const url = `${API_BASE_URL}/ai/dialog/tempchunk?id=${encodeURIComponent(state.payload.id)}&index=${encodeURIComponent(it.index)}`;
        const r = await fetch(url);
        if(!r.ok){ const t = await r.text(); throw new Error(`HTTP ${r.status}: ${t}`); }
        const blob = await r.blob(); const src = URL.createObjectURL(blob);
        let audioEl = document.getElementById('preview-audio');
        if(!audioEl){ audioEl = document.createElement('audio'); audioEl.id='preview-audio'; audioEl.controls=true; audioEl.style.width='100%'; audioEl.style.display='none'; itemsEl.appendChild(audioEl); }
        audioEl.src = src; audioEl.style.display='block'; await audioEl.play();
      }catch(err){ alert('Play failed: '+err.message); }
    });
    removeBtn.addEventListener('click', ()=>{ tr.remove(); });
    regenBtn.addEventListener('click', ()=>{ alert('Regenerate coming soon.'); });
    body.appendChild(tr);
  });
  table.appendChild(body); itemsEl.appendChild(table);

  // SVG timeline
  const total = timeline.reduce((m,it)=> Math.max(m, (it.start_s||0)+(it.duration_s||0)), 0) || 1;
  const width = tlEl.clientWidth || 600; const height = 90; const px = (s)=> (s/total)*width;
  const svgNS = 'http://www.w3.org/2000/svg'; const svg = document.createElementNS(svgNS, 'svg'); svg.setAttribute('width', width); svg.setAttribute('height', height);
  const lanes = { music:10, sfx:40, dialog:70 };
  timeline.forEach(it=>{ const x = px(it.start_s||0); const w = Math.max(2, px(it.duration_s||0)); const y = it.kind==='music'?lanes.music : (it.kind==='sfx'? lanes.sfx : lanes.dialog); const rect = document.createElementNS(svgNS,'rect'); rect.setAttribute('x', x); rect.setAttribute('y', y); rect.setAttribute('width', w); rect.setAttribute('height', 12); rect.setAttribute('rx', 3); rect.setAttribute('fill', it.kind==='music'? '#94a3b8' : it.kind==='sfx'? '#60a5fa' : '#22c55e'); svg.appendChild(rect); });
  tlEl.innerHTML=''; tlEl.appendChild(svg);
}

document.getElementById('btn-generate').addEventListener('click', ()=>{ doGenerate(); });

document.getElementById('btn-next-2').addEventListener('click', ()=>{
  // Populate final panel
  document.getElementById('final-json').textContent = JSON.stringify(state.result || {}, null, 2);
  const audio = document.getElementById('final-audio');
  const dl = document.getElementById('final-download');
  if(state.result && state.result.file_url){
    audio.src = state.result.file_url; audio.style.display='block';
    dl.href = state.result.file_url; dl.style.display='inline-block';
  }
  setActive(3);
});

document.getElementById('btn-back-3').addEventListener('click', ()=>{ setActive(2); });

</script>
<script>
// Prefill support: read ?prefill= (JSON) or #prefill= (JSON) or ?text=
(function prefillFromURL(){
  try {
    const txtEl = document.getElementById('dlg-text');
    const langEl = document.getElementById('dlg-language');
    if (!txtEl) return;
    const u = new URL(window.location.href);
    let payload = null;
    const q = u.searchParams.get('prefill');
    if (q) {
      try { payload = JSON.parse(decodeURIComponent(q)); } catch(_) { payload = { text: q }; }
    } else if (window.location.hash && window.location.hash.startsWith('#prefill=')) {
      const raw = decodeURIComponent(window.location.hash.slice('#prefill='.length));
      try { payload = JSON.parse(raw); } catch(_) { payload = { text: raw }; }
    } else {
      const t = u.searchParams.get('text') || u.searchParams.get('dlg_text');
      if (t) payload = { text: t };
    }
    if (payload) {
      if (typeof payload.text === 'string') {
        txtEl.value = payload.text;
      }
      if (payload.language && langEl) {
        try { langEl.value = payload.language; } catch(_) {}
      }
    }
  } catch(e) { /* ignore */ }
})();
</script>

</body>
</html>


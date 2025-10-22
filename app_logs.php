<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Logs</title>
    <style>
        :root { --bg-color:#f8f9fa; --card-bg:#ffffff; --text-color:#212529; --primary-color:#007bff; --primary-hover:#0056b3; --secondary-color:#6c757d; --secondary-hover:#5a6268; --danger-color:#dc3545; --danger-hover:#c82333; --log-bg:#fdfdff; --border-color:#dee2e6; --font-monospace:"SF Mono","Consolas","Menlo",monospace; --log-bg-old:#f1f3f5; }
        body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; line-height:1.6; background-color:var(--bg-color); color:var(--text-color); margin:0; }
        .container { padding:20px; min-height:100vh; box-sizing:border-box; }
        h1 { border-bottom:1px solid var(--border-color); padding-bottom:15px; margin-top:0; }
        .controls { margin-bottom:20px; display:flex; flex-direction:column; gap:15px; }
        .controls-row { display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
        .controls label { font-weight:600; }
        .controls select, .controls input, .controls button { padding:10px 15px; border-radius:8px; border:1px solid var(--border-color); font-size:16px; background-color:var(--card-bg); }
        .controls button { background-color:var(--primary-color); color:white; border-color:var(--primary-color); cursor:pointer; transition:background-color .2s ease; }
        .controls button:hover { background-color:var(--primary-hover); }
        .btn-secondary { background-color:var(--secondary-color); border-color:var(--secondary-color); }
        .btn-secondary:hover { background-color:var(--secondary-hover); }
        #log-container { background-color:var(--log-bg); border:1px solid var(--border-color); border-radius:8px; height:70vh; overflow-y:auto; padding:10px; }
        .status { margin-top:15px; font-style:italic; color:#6c757d; }
        .log-entry { display:flex; flex-direction:column; padding:8px 12px; border-bottom:1px solid var(--border-color); font-family:var(--font-monospace); font-size:14px; transition:background-color .5s ease; }
        .log-entry:last-child { border-bottom:none; }
        .log-entry-new { background-color:var(--card-bg); }
        .log-entry-old { background-color:var(--log-bg-old); }
        .log-meta { display:flex; align-items:center; gap:12px; margin-bottom:4px; }
        .log-timestamp { font-weight:600; color:#343a40; }
        .log-level { font-size:12px; font-weight:700; padding:2px 8px; border-radius:12px; color:#fff; text-transform:uppercase; }
        .log-level-error { background-color:#dc3545; }
        .log-level-warning { background-color:#ffc107; color:#212529; }
        .log-level-info { background-color:#17a2b8; }
        .log-level-debug, .log-level-default { background-color:#6c757d; }
        .log-extra { font-size:12px; color:#6c757d; }
        .log-message { white-space:pre-wrap; word-break:break-all; color:#495057; }
        @media (min-width:768px){ .controls { flex-direction:row; justify-content:space-between; } .controls-row { flex-wrap:nowrap; } }
    </style>
    <script>
        function escapeHtml(unsafe) { return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;"); }
    </script>
</head>
<body>
    <div class="container">
        <?php include 'menu.php'; ?>
        <h1>App Log Viewer</h1>
        <div class="controls">
            <div class="controls-row">
                <label for="app-name">App:</label>
                <input id="app-name" placeholder="Unity-AR, Tscheppaschlucht-AR, ..." />

                <label for="level">Level:</label>
                <select id="level">
                    <option value="">Alle</option>
                    <option>debug</option>
                    <option selected>info</option>
                    <option>warning</option>
                    <option>error</option>
                    <option>critical</option>
                </select>

                <label for="limit">Limit:</label>
                <select id="limit">
                    <option>50</option>
                    <option selected>100</option>
                    <option>200</option>
                    <option>500</option>
                </select>

                <label><input type="checkbox" id="user-only"> Nur meine</label>

                <button id="fetch-btn">Refresh</button>
                <button id="clear-view-btn" class="btn-secondary">Ansicht leeren</button>
            </div>
        </div>

        <div id="log-container"></div>
        <div class="status" id="status-line">Logs werden geladen...</div>
    </div>

    <script>
        const appNameInput = document.getElementById('app-name');
        const levelSelect = document.getElementById('level');
        const limitSelect = document.getElementById('limit');
        const userOnlyCheckbox = document.getElementById('user-only');
        const fetchBtn = document.getElementById('fetch-btn');
        const clearViewBtn = document.getElementById('clear-view-btn');
        const logContainer = document.getElementById('log-container');
        const statusLine = document.getElementById('status-line');
        let autoRefreshInterval;
        let knownIds = new Set();

        function levelClass(level){
            const l = (level||'').toLowerCase();
            if(l==='error') return 'log-level-error';
            if(l==='warning') return 'log-level-warning';
            if(l==='info') return 'log-level-info';
            return 'log-level-debug';
        }

        function renderLogs(items){
            const dateOpts={ day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' };
            const html = items.map(x=>{
                const isNew = !knownIds.has(x.id);
                knownIds.add(x.id);
                const entryClass = isNew ? 'log-entry-new' : 'log-entry-old';
                const ts = new Date(x.created_at).toLocaleString('de-DE', dateOpts);
                const meta = [x.app_name||'-', x.platform||'-', x.build||'-', x.device_id||'-', x.request_id||''].filter(Boolean).join(' · ');
                const tags = (x.tags||[]).join(', ');
                const extra = [meta, tags].filter(Boolean).join(' | ');
                return `<div class="log-entry ${entryClass}">
                    <div class="log-meta">
                        <span class="log-timestamp">${ts}</span>
                        <span class="log-level ${levelClass(x.level)}">${x.level}</span>
                        <span class="log-extra">${escapeHtml(extra)}</span>
                    </div>
                    <div class="log-message">${escapeHtml(x.message)}</div>
                </div>`;
            }).join('');
            logContainer.innerHTML = html || '<div class="log-entry"><div class="log-message">Keine Logs gefunden.</div></div>';
        }

        async function fetchLogs(){
            statusLine.textContent = 'Lade App Logs...';
            const params = new URLSearchParams();
            const level = levelSelect.value.trim();
            const appName = appNameInput.value.trim();
            const limit = limitSelect.value.trim();
            const userOnly = userOnlyCheckbox.checked;
            if(level) params.set('level', level);
            if(appName) params.set('app_name', appName);
            if(limit) params.set('limit', limit);
            if(userOnly) params.set('user_only', '1');
            try{
                const resp = await fetch('app_logs_api.php?' + params.toString());
                if(!resp.ok) throw new Error(resp.status + ' ' + resp.statusText);
                const data = await resp.json();
                renderLogs(data);
                statusLine.textContent = `Aktualisiert am: ${new Date().toLocaleTimeString()}`;
            }catch(e){
                logContainer.innerHTML = `<div class="log-entry"><div class="log-message log-level-error">Fehler: ${e.message}</div></div>`;
                statusLine.textContent = 'Fehler beim Laden';
            }
        }

        clearViewBtn.addEventListener('click', ()=>{ logContainer.innerHTML=''; knownIds.clear(); statusLine.textContent='Ansicht geleert.'; });
        fetchBtn.addEventListener('click', ()=>{ knownIds.clear(); fetchLogs(); });
        document.addEventListener('DOMContentLoaded', ()=>{ fetchLogs(); });
    </script>
</body>
</html>


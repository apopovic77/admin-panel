<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Logs</title>
    <style>
        :root {
            --text: #1e293b; --muted: #475569; --brand: #1f2937; --brand-2: #8B9DC3;
            --ring: rgba(148, 163, 184, .3); --surface: rgba(255, 255, 255, 0.98);
            --background-gradient: linear-gradient(to bottom, #ffffff, #f8fafc, #e2e8f0);
            --radius-lg: 16px; --radius-md: 12px; --radius-sm: 8px;
            --shadow-primary: 0 10px 30px rgba(0, 0, 0, .07); --gap: 24px;
            --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Roboto, system-ui, sans-serif;
            --h2-size: clamp(22px, 3.6vw, 34px); --kicker-size: 12px;
            --success-color: #d4edda; --error-color: #f8d7da;
            --font-monospace: "SF Mono", "Consolas", "Menlo", monospace;
        }
        body { 
            font-family: var(--font-family); margin: 0; background: var(--background-gradient);
            color: var(--text); padding-bottom: 64px; line-height: 1.6;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: var(--gap); min-height: 100vh; box-sizing: border-box; }
        h1 {
            font-size: clamp(34px, 6.2vw, 76px); font-weight: 700;
            padding-top: 32px; margin-bottom: 64px; text-align: center;
            border-bottom: 1px solid var(--ring); padding-bottom: 32px;
        }
        .controls { 
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            margin-bottom: 32px; box-shadow: var(--shadow-primary);
            display: flex; flex-direction: column; gap: 15px;
        }
        .controls-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .controls label { 
            font-weight: 600; color: var(--text);
            font-size: var(--kicker-size); text-transform: uppercase; letter-spacing: .08em;
        }
        .controls select, .controls button { 
            padding: 10px 15px; border-radius: var(--radius-sm); border: 1px solid var(--ring); 
            font-size: 16px; background-color: #f8fafc; cursor: pointer; transition: all 0.2s ease;
        }
        .controls button { 
            background-color: var(--brand-2); color: white; border-color: var(--brand-2); 
            font-weight: 600;
        }
        .controls button:hover { background-color: var(--brand); transform: translateY(-1px); }
        .btn-secondary { background-color: var(--muted); border-color: var(--muted); }
        .btn-secondary:hover { background-color: var(--text); }
        .btn-danger { background-color: #dc3545; border-color: #dc3545; }
        .btn-danger:hover { background-color: #c82333; }
        #log-container { 
            background: var(--surface); border: 1px solid var(--ring); 
            border-radius: var(--radius-lg); height: 70vh; overflow-y: auto; 
            padding: var(--gap); box-shadow: var(--shadow-primary);
        }
        .status { 
            margin-top: 15px; font-style: italic; color: var(--muted); 
            font-size: var(--kicker-size); text-transform: uppercase; letter-spacing: .08em;
        }
        
        .log-entry { 
            display: flex; flex-direction: column; padding: 12px 16px; 
            border-bottom: 1px solid var(--ring); font-family: var(--font-monospace); 
            font-size: 14px; transition: background-color 0.5s ease;
            border-radius: var(--radius-sm); margin-bottom: 8px;
        }
        .log-entry-new { background-color: var(--surface); border: 1px solid var(--brand-2); }
        .log-entry-old { background-color: #f8fafc; }

        .log-entry:last-child { border-bottom: none; }
        .log-meta { display: flex; align-items: center; gap: 12px; margin-bottom: 4px; }
        .log-timestamp { font-weight: 600; color: var(--text); }
        .log-level { 
            font-size: var(--kicker-size); font-weight: 700; padding: 4px 8px; 
            border-radius: var(--radius-sm); color: #fff; text-transform: uppercase; 
        }
        .log-level-error { background-color: #dc3545; }
        .log-level-warning { background-color: #ffc107; color: var(--text); }
        .log-level-info { background-color: var(--brand-2); }
        .log-level-success { background-color: #28a745; }
        .log-level-debug, .log-level-default { background-color: var(--muted); }
        .log-extra { font-size: var(--kicker-size); color: var(--muted); }
        .log-message { white-space: pre-wrap; word-break: break-all; color: var(--text); }
        /* Enhanced Mobile Responsive Design */
        @media (max-width: 768px) {
            .container { 
                padding: 16px; 
                max-width: 100%; 
            }
            
            h1 { 
                font-size: clamp(24px, 8vw, 32px); 
                padding-top: 16px; 
                margin-bottom: 32px; 
            }
            
            .controls { 
                padding: 20px; 
                gap: 12px; 
            }
            
            .controls-row { 
                flex-direction: column; 
                align-items: stretch; 
                gap: 12px; 
            }
            
            .controls select, .controls button { 
                padding: 12px; 
                font-size: 16px; 
                width: 100%; 
                min-height: 44px; 
            }
            
            .controls label { 
                font-size: 11px; 
                margin-bottom: 4px; 
            }
            
            #log-container { 
                height: 60vh; 
                padding: 16px; 
                font-size: 12px; 
            }
            
            .log-entry { 
                padding: 10px 12px; 
                margin-bottom: 6px; 
                font-size: 12px; 
            }
            
            .log-meta { 
                gap: 8px; 
                flex-wrap: wrap; 
            }
            
            .log-timestamp { 
                font-size: 11px; 
            }
            
            .log-level { 
                font-size: 10px; 
                padding: 2px 6px; 
            }
            
            .log-extra { 
                font-size: 10px; 
            }
            
            .log-message { 
                font-size: 11px; 
                line-height: 1.4; 
                word-break: break-word; 
            }
            
            .status { 
                font-size: 11px; 
                margin-top: 12px; 
            }
        }
        
        @media (max-width: 480px) {
            .container { 
                padding: 12px; 
            }
            
            h1 { 
                font-size: clamp(20px, 10vw, 28px); 
            }
            
            .controls { 
                padding: 16px; 
            }
            
            .controls select, .controls button { 
                padding: 10px; 
                font-size: 14px; 
            }
            
            #log-container { 
                height: 50vh; 
                padding: 12px; 
            }
            
            .log-entry { 
                padding: 8px 10px; 
                font-size: 11px; 
            }
            
            .log-meta { 
                gap: 6px; 
            }
            
            .log-timestamp { 
                font-size: 10px; 
            }
            
            .log-level { 
                font-size: 9px; 
                padding: 1px 4px; 
            }
            
            .log-message { 
                font-size: 10px; 
            }
        }
        
        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            .controls button { 
                min-height: 44px; 
                padding: 12px 20px; 
            }
            
            .controls select { 
                min-height: 44px; 
            }
            
            .log-entry:hover { 
                background-color: inherit; 
            }
        }
        
        /* Landscape orientation on mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            #log-container { 
                height: 40vh; 
            }
        }
        
        @media (min-width: 769px) { 
            .controls { flex-direction: row; justify-content: space-between; } 
            .controls-row { flex-wrap: nowrap; } 
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'menu.php'; ?>
        <h1>Server Log Viewer</h1>
        <div class="controls">
            <div class="controls-row">
                <label for="log-select">Log:</label>
                <select id="log-select">
                    <option value="api" selected>api-arkturian.service</option>
                    <option value="nginx_access">Nginx Access</option>
                    <option value="nginx_error">Nginx Error</option>
                    <option value="syslog">System Log</option>
                </select>
                <button id="fetch-btn">Refresh</button>
                <button id="clear-view-btn" class="btn-secondary">Ansicht leeren</button>
                <button id="truncate-log-btn" class="btn-danger">Log-Datei leeren</button>
            </div>
            <div class="controls-row">
                <label><input type="checkbox" id="auto-refresh" checked> Auto-Refresh (5s)</label>
            </div>
        </div>
        
        <div id="log-container"></div>
        <div class="status" id="status-line">Logs werden geladen...</div>
    </div>

    <script>
        const logSelect = document.getElementById('log-select');
        const fetchBtn = document.getElementById('fetch-btn');
        const clearViewBtn = document.getElementById('clear-view-btn');
        const truncateLogBtn = document.getElementById('truncate-log-btn');
        const logContainer = document.getElementById('log-container');
        const statusLine = document.getElementById('status-line');
        const autoRefreshCheckbox = document.getElementById('auto-refresh');
        let autoRefreshInterval;
        let knownLogLines = new Set();

        const truncatableLogs = ['nginx_access', 'nginx_error', 'syslog'];

        function parseAndFormatLogs(text) {
            const lines = text.split('\n').reverse();
            const dateTimeOptions = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            
            const newHtml = lines.map(line => {
                if (!line.trim()) return '';
                
                const isNewLine = !knownLogLines.has(line);
                const entryClass = isNewLine ? 'log-entry-new' : 'log-entry-old';

                let match;
                // Corrected Regex for journalctl/syslog, handles optional "LEVEL:" prefix
                match = line.match(/^(?<month>\w{3})\s+(?<day>\d{1,2})\s+(?<time>\d{2}:\d{2}:\d{2})\s+(?<host>[\w.-]+)\s+(?<process>[\w.-]+)(?:\[(?<pid>\d+)\])?:\s+(?:(?<level>\w+):\s+)?(?<message>.*)$/);
                if (match) {
                    const { month, day, time, process, pid, level: detectedLevel, message } = match.groups;
                    const date = new Date(`${month} ${day}, ${new Date().getFullYear()} ${time}`);
                    const timestamp = date.toLocaleString('de-DE', dateTimeOptions);
                    let level = 'default';
                    const lowerMessage = message.toLowerCase();
                    const lowerLevel = (detectedLevel || '').toLowerCase();
                    if (lowerLevel === 'error' || lowerMessage.includes('error') || lowerMessage.includes('failed')) level = 'error';
                    else if (lowerLevel === 'warning' || lowerMessage.includes('warning')) level = 'warning';
                    else if (lowerLevel === 'info') level = 'info';
                    return `<div class="log-entry ${entryClass}"><div class="log-meta"><span class="log-timestamp">${timestamp}</span><span class="log-level log-level-${level}">${level}</span><span class="log-extra">${process}${pid ? `[${pid}]` : ''}</span></div><div class="log-message">${escapeHtml(message)}</div></div>`;
                }
                // Nginx error
                match = line.match(/^(?<datetime>\d{4}\/\d{2}\/\d{2}\s\d{2}:\d{2}:\d{2})\s+\[(?<level>\w+)\]\s+.*:\s+(?<message>.*)$/);
                if (match) {
                    const { datetime, level, message } = match.groups;
                    const date = new Date(datetime.replace(/\//g, '-'));
                    const timestamp = date.toLocaleString('de-DE', dateTimeOptions);
                    const cleanLevel = level.toLowerCase();
                    return `<div class="log-entry ${entryClass}"><div class="log-meta"><span class="log-timestamp">${timestamp}</span><span class="log-level log-level-${cleanLevel}">${cleanLevel}</span></div><div class="log-message">${escapeHtml(message)}</div></div>`;
                }
                return `<div class="log-entry ${entryClass}"><div class="log-message">${escapeHtml(line)}</div></div>`;
            }).join('');

            return newHtml;
        }
        
        function escapeHtml(unsafe) { return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;"); }

        async function fetchLogs() {
            // Mark all existing entries as old before fetching new ones
            document.querySelectorAll('.log-entry').forEach(el => {
                el.classList.remove('log-entry-new');
                el.classList.add('log-entry-old');
            });

            const selectedLog = logSelect.value;
            statusLine.textContent = `Lade ${selectedLog} Log...`;
            try {
                const response = await fetch(`log_api.php?log=${selectedLog}`);
                if (response.ok) {
                    const data = await response.text();
                    
                    // Add new lines to the known set before rendering
                    const lines = data.split('\n');
                    const newLines = lines.filter(line => line.trim() && !knownLogLines.has(line));
                    newLines.forEach(line => knownLogLines.add(line));

                    logContainer.innerHTML = parseAndFormatLogs(data) || '<div class="log-entry"><div class="log-message">Log ist leer.</div></div>';
                    statusLine.textContent = `Aktualisiert am: ${new Date().toLocaleTimeString()}`;
                } else {
                    logContainer.innerHTML = `<div class="log-entry"><div class="log-message log-level-error">Fehler: ${response.status} ${response.statusText}</div></div>`;
                }
            } catch (error) {
                logContainer.innerHTML = `<div class="log-entry"><div class="log-message log-level-error">Netzwerkfehler: ${error.message}</div></div>`;
            }
        }

        function setupAutoRefresh() {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            if (autoRefreshCheckbox.checked) autoRefreshInterval = setInterval(fetchLogs, 5000);
        }

        function toggleTruncateButton() {
            truncateLogBtn.style.display = truncatableLogs.includes(logSelect.value) ? 'inline-block' : 'none';
        }

        clearViewBtn.addEventListener('click', () => {
            logContainer.innerHTML = '';
            knownLogLines.clear();
            statusLine.textContent = 'Ansicht geleert. Refresh, um neue Logs zu laden.';
        });

        truncateLogBtn.addEventListener('click', async () => {
            const selectedLog = logSelect.value;
            if (!confirm(`WARNUNG!\n\nSie sind dabei, den Inhalt der Log-Datei "${selectedLog}" permanent zu löschen.\n\nDiese Aktion kann nicht rückgängig gemacht werden. Fortfahren?`)) return;
            
            statusLine.textContent = `Leere Log-Datei ${selectedLog}...`;
            try {
                const response = await fetch(`admin_actions.php?action=truncate_log&log=${selectedLog}`, { method: 'POST' });
                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    statusLine.textContent = 'Log-Datei erfolgreich geleert.';
                    knownLogLines.clear();
                    await fetchLogs();
                } else {
                    throw new Error(result.message || 'Unbekannter Fehler');
                }
            } catch (error) {
                statusLine.textContent = `Fehler beim Leeren: ${error.message}`;
            }
        });

        fetchBtn.addEventListener('click', () => {
            knownLogLines.clear();
            fetchLogs();
        });
        autoRefreshCheckbox.addEventListener('change', setupAutoRefresh);
        logSelect.addEventListener('change', () => {
            knownLogLines.clear();
            fetchLogs();
            toggleTruncateButton();
        });
        
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const logFromUrl = urlParams.get('log');
            if (logFromUrl) logSelect.value = logFromUrl;
            fetchLogs();
            setupAutoRefresh();
            toggleTruncateButton();
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Admin</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #212529; margin: 0; }
        .container { padding: 20px; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); max-width: 600px; margin-bottom: 20px; }
        h1, h2 { border-bottom: 1px solid #dee2e6; padding-bottom: 15px; }
        button { font-size: 16px; padding: 12px 20px; border-radius: 8px; border: none; cursor: pointer; color: #fff; transition: background-color 0.2s ease; }
        .btn-danger { background-color: #dc3545; }
        .btn-danger:hover { background-color: #c82333; }
        .btn-warning { background-color: #ffc107; color: #212529; }
        .btn-warning:hover { background-color: #e0a800; }
        button:disabled { background-color: #6c757d; cursor: not-allowed; }
        #status-message { margin-top: 20px; padding: 15px; border-radius: 8px; display: none; }
        .status-success { background-color: #d4edda; color: #155724; }
        .status-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'menu.php'; ?>
        <h1>Server Administration</h1>
        
        <div class="card">
            <h2>API Service</h2>
            <p>Startet den <code>api-arkturian.service</code> neu. Sie werden danach automatisch zur Log-Ansicht weitergeleitet.</p>
            <button id="restart-api-btn" class="btn-danger">API Service jetzt neu starten</button>
        </div>

        <div class="card">
            <h2>System Journal (API Log)</h2>
            <p>Reduziert die Größe der gesamten System-Log-Datenbank auf 10MB. Dies entfernt alte Einträge von allen Diensten, um eine saubere Ansicht zu erhalten.</p>
            <button id="vacuum-journal-btn" class="btn-warning">System Journal jetzt aufräumen</button>
        </div>

        <div id="status-message"></div>
    </div>

    <script>
        const restartBtn = document.getElementById('restart-api-btn');
        const vacuumBtn = document.getElementById('vacuum-journal-btn');
        const statusMessage = document.getElementById('status-message');

        async function handleAction(button, action, confirmMessage, successMessage, redirectUrl) {
            if (!confirm(confirmMessage)) return;

            button.disabled = true;
            button.textContent = 'Befehl wird ausgeführt...';
            statusMessage.style.display = 'none';

            try {
                const response = await fetch(`admin_actions.php?action=${action}`, { method: 'POST' });
                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    statusMessage.className = 'status-success';
                    statusMessage.textContent = `${successMessage} Leite weiter...`;
                    statusMessage.style.display = 'block';
                    
                    if (redirectUrl) {
                        setTimeout(() => { window.location.href = redirectUrl; }, 2000);
                    } else {
                         button.disabled = false;
                         button.textContent = button.dataset.originalText;
                    }
                } else {
                    throw new Error(result.message || 'Ein unbekannter Fehler ist aufgetreten.');
                }
            } catch (error) {
                statusMessage.className = 'status-error';
                statusMessage.textContent = `Fehler: ${error.message}`;
                statusMessage.style.display = 'block';
                button.disabled = false;
                button.textContent = button.dataset.originalText;
            }
        }
        
        restartBtn.dataset.originalText = restartBtn.textContent;
        vacuumBtn.dataset.originalText = vacuumBtn.textContent;

        restartBtn.addEventListener('click', () => handleAction(
            restartBtn,
            'restart_api',
            'Sind Sie sicher, dass Sie den API Service neu starten möchten?',
            'Erfolgreich!',
            'logs.php?log=api'
        ));

        vacuumBtn.addEventListener('click', () => handleAction(
            vacuumBtn,
            'vacuum_journal',
            'WARNUNG! Sie sind dabei, alte Log-Einträge permanent zu löschen. Diese Aktion kann nicht rückgängig gemacht werden. Fortfahren?',
            'System Journal erfolgreich aufgeräumt!',
            'logs.php?log=api'
        ));
    </script>
</body>
</html>
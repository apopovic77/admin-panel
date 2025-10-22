<?php
// Einbinden des Menüs und anderer gemeinsamer Elemente
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Server Status</title>
    <style>
        /* Simple styling for the status display */
        body { font-family: sans-serif; background-color: #f4f4f9; color: #333; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .status-card { border: 1px solid #ddd; padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; }
        .status-card h2 { margin-top: 0; font-size: 1.5em; color: #555; }
        .status-count { font-size: 4em; font-weight: bold; margin: 10px 0; }
        .status-text { font-size: 1.2em; font-style: italic; }
        .status-idle { color: #28a745; } /* Green for idle */
        .status-connected { color: #007bff; } /* Blue for connected */
        .error-message { color: #dc3545; font-weight: bold; margin-top: 15px; }
        .last-updated { font-size: 0.9em; color: #888; margin-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h1>API Server Status</h1>
    <div class="status-card">
        <h2>Aktive Client-Verbindungen</h2>
        <div id="connection-count" class="status-count">...</div>
        <div id="connection-status" class="status-text">Lade Daten...</div>
        <div id="error-message" class="error-message" style="display: none;"></div>
        <div class="last-updated">Zuletzt aktualisiert: <span id="last-updated-time">nie</span></div>
    </div>
</div>

<script>
    const apiUrl = 'https://api.arkturian.com/status'; // Die URL zu Ihrem API-Endpunkt
    const apiKey = 'Inetpass1'; // API-Schlüssel wie in storage.php

    const countElement = document.getElementById('connection-count');
    const statusElement = document.getElementById('connection-status');
    const errorElement = document.getElementById('error-message');
    const timeElement = document.getElementById('last-updated-time');

    async function fetchStatus() {
        try {
            const response = await fetch(apiUrl, {
                headers: {
                    'X-API-KEY': apiKey
                }
            });

            if (!response.ok) {
                let errorText = `Fehler: ${response.status} ${response.statusText}`;
                if (response.status === 403) {
                    errorText = 'Zugriff verweigert. Stellen Sie sicher, dass Sie als Admin angemeldet sind.';
                } else if (response.status === 404) {
                    errorText = 'API-Endpunkt nicht gefunden. Wurde /status implementiert?';
                }
                throw new Error(errorText);
            }

            const data = await response.json();

            // UI aktualisieren
            countElement.textContent = data.activeConnections;
            statusElement.textContent = data.activeConnections > 0 ? 'Clients sind verbunden' : 'API wartet (idle)';
            timeElement.textContent = new Date().toLocaleTimeString();

            // Farbliche Anpassung
            if (data.activeConnections > 0) {
                countElement.className = 'status-count status-connected';
            } else {
                countElement.className = 'status-count status-idle';
            }

            errorElement.style.display = 'none'; // Fehler ausblenden bei Erfolg

        } catch (error) {
            console.error('Fehler beim Abrufen des Status:', error);
            countElement.textContent = '-';
            statusElement.textContent = 'Konnte Status nicht laden.';
            errorElement.textContent = error.message;
            errorElement.style.display = 'block';
        }
    }

    // Status sofort laden und dann alle 5 Sekunden aktualisieren
    fetchStatus();
    setInterval(fetchStatus, 5000);
</script>

</body>
</html>

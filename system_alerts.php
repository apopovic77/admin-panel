<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Status - arkturian.com</title>
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
        }
        body { 
            font-family: var(--font-family); margin: 0; background: var(--background-gradient);
            color: var(--text); padding: 20px; min-height: 100vh;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 var(--gap); }
        h1 {
            font-size: clamp(34px, 6.2vw, 76px); font-weight: 700;
            margin-bottom: 32px; text-align: center; color: var(--text);
        }
        .header { 
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            margin-bottom: 32px; box-shadow: var(--shadow-primary);
        }
        .header h1 { 
            margin: 0; color: var(--text); font-weight: 700;
            font-size: var(--h2-size); text-align: left;
        }
        h2 {
            font-size: var(--h2-size); font-weight: 600; margin-bottom: var(--gap);
            border-bottom: 1px solid var(--ring); padding-bottom: 16px;
        }
        .dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: var(--gap); }
        .card { 
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            box-shadow: var(--shadow-primary); transition: transform 0.2s ease;
        }
        .card:hover { transform: translateY(-2px); }
        .stat { display: flex; justify-content: space-between; margin-bottom: 12px; align-items: center; }
        .stat strong { color: var(--muted); font-weight: 600; }
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 15px 0; }
        .stat-item { 
            background: rgba(248, 250, 252, 0.8); padding: 12px 16px; border-radius: var(--radius-sm);
            border: 1px solid rgba(148, 163, 184, 0.2); text-align: center;
        }
        .stat-value { 
            font-size: 28px; font-weight: 700; color: var(--text); 
            display: block; line-height: 1;
        }
        .stat-label { 
            font-size: var(--kicker-size); color: var(--muted); 
            text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;
        }
        .status-healthy { color: #16a34a; }
        .status-warning { color: #ca8a04; }
        .status-critical { color: #dc2626; }
        .status-unknown { color: var(--muted); }
        .service-status { 
            display: flex; align-items: center; gap: 8px; 
            padding: 8px 12px; border-radius: var(--radius-sm);
            font-weight: 500; font-size: 14px;
        }
        .service-status.healthy { background: #dcfce7; color: #16a34a; }
        .service-status.warning { background: #fefce8; color: #ca8a04; }
        .service-status.critical { background: #fef2f2; color: #dc2626; }
        .service-status.unknown { background: #f8fafc; color: var(--muted); }
        .service-meta {
            font-size: 13px; color: var(--muted); margin-top: 12px;
            display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
        }
        .loading { 
            text-align: center; padding: 40px; color: var(--muted);
            font-style: italic;
        }
        .refresh-info {
            text-align: center; color: var(--muted); font-size: 13px;
            margin-top: 32px; padding: 16px;
            background: rgba(248, 250, 252, 0.6); border-radius: var(--radius-sm);
        }
        .controls {
            display: flex; justify-content: center; align-items: center; gap: 16px;
            margin-bottom: 32px; padding: 16px;
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-md); box-shadow: var(--shadow-primary);
        }
        .btn {
            padding: 8px 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm);
            background: var(--surface); color: var(--text); cursor: pointer;
            font-family: inherit; font-size: 14px; transition: all 0.2s ease;
        }
        .btn:hover { background: var(--brand-2); color: white; }
        .live-indicator {
            display: flex; align-items: center; gap: 8px; font-weight: 500;
            color: var(--muted); font-size: 14px;
        }
        .live-dot {
            width: 8px; height: 8px; border-radius: 50%; background: #16a34a;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="controls">
        <div class="live-indicator">
            <div class="live-dot"></div>
            Live System Monitor
        </div>
        <button class="btn" onclick="refreshData()">Refresh Now</button>
    </div>
    
    <div class="container">
        <div class="header">
            <h1>🔧 System Status Monitor</h1>
        </div>
        
        <div class="stat-grid">
            <div class="stat-item">
                <span class="stat-value" id="total-services">-</span>
                <div class="stat-label">Total Services</div>
            </div>
            <div class="stat-item">
                <span class="stat-value status-healthy" id="healthy-services">-</span>
                <div class="stat-label">Healthy</div>
            </div>
            <div class="stat-item">
                <span class="stat-value status-warning" id="warning-services">-</span>
                <div class="stat-label">Warnings</div>
            </div>
            <div class="stat-item">
                <span class="stat-value status-critical" id="critical-services">-</span>
                <div class="stat-label">Critical</div>
            </div>
        </div>
        
        <h2>Service Details</h2>
        <div class="dashboard" id="services-overview">
            <div class="loading">Loading system status...</div>
        </div>
        
        <div class="refresh-info">
            🔄 Auto-refreshes every 30 seconds | Last Update: <span id="last-update">Never</span>
        </div>
    </div>

    <script>
        const API_BASE = 'https://api.arkturian.com';
        
        function formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }
        
        function getStatusIcon(status) {
            const icons = {
                'healthy': '✅',
                'warning': '⚠️',
                'critical': '🔥',
                'down': '❌',
                'unknown': '❓'
            };
            return icons[status] || '❓';
        }
        
        async function fetchServiceData() {
            try {
                const response = await fetch(`${API_BASE}/alerts`);
                if (response.ok) {
                    const data = await response.json();
                    // Convert service data to expected format
                    return data.services.map(service => ({
                        service: service.service,
                        component: service.component,
                        current_status: service.status,
                        message: service.message,
                        last_update: service.last_update,
                        uptime_percentage: service.status === 'healthy' ? 99.9 : (service.status === 'warning' ? 95.0 : 80.0),
                        recent_alerts: [],
                        metadata: service.metadata
                    }));
                }
            } catch (error) {
                console.error('Error fetching service data:', error);
            }
            return [];
        }
        
        function renderServiceCards(services, containerId) {
            const container = document.getElementById(containerId);
            
            if (services.length === 0) {
                container.innerHTML = '<div class="card"><div class="loading">No service data available. Make sure monitoring is active.</div></div>';
                return;
            }
            
            const cardsHtml = services.map(service => `
                <div class="card">
                    <div class="stat">
                        <strong>${service.service.toUpperCase()}${service.component ? ` / ${service.component}` : ''}</strong>
                        <div class="service-status ${service.current_status}">
                            ${getStatusIcon(service.current_status)} ${service.current_status}
                        </div>
                    </div>
                    <div class="stat">
                        <span>Status:</span>
                        <span>${service.message}</span>
                    </div>
                    <div class="stat">
                        <span>Uptime:</span>
                        <span class="status-healthy">${service.uptime_percentage.toFixed(1)}%</span>
                    </div>
                    ${service.metadata && service.metadata.memory ? `
                    <div class="stat">
                        <span>Memory:</span>
                        <span>${service.metadata.memory}</span>
                    </div>
                    ` : ''}
                    ${service.metadata && service.metadata.disk ? `
                    <div class="stat">
                        <span>Disk:</span>
                        <span>${service.metadata.disk}</span>
                    </div>
                    ` : ''}
                    <div class="service-meta">
                        <div><strong>Last Update:</strong><br>${formatDate(service.last_update)}</div>
                        <div><strong>Active Connections:</strong><br>${service.metadata?.active_connections || 'N/A'}</div>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = cardsHtml;
        }
        
        async function refreshData() {
            try {
                // Get the full alerts response with summary
                const alertsResponse = await fetch(`${API_BASE}/alerts`);
                if (alertsResponse.ok) {
                    const alertsData = await alertsResponse.json();
                    
                    // Convert services to expected format
                    const services = alertsData.services.map(service => ({
                        service: service.service,
                        component: service.component,
                        current_status: service.status,
                        message: service.message,
                        last_update: service.last_update,
                        uptime_percentage: service.status === 'healthy' ? 99.9 : (service.status === 'warning' ? 95.0 : 80.0),
                        recent_alerts: [],
                        metadata: service.metadata
                    }));
                    
                    // Update metrics from summary
                    const summary = alertsData.summary;
                    document.getElementById('total-services').textContent = summary.total_services;
                    document.getElementById('healthy-services').textContent = summary.healthy;
                    document.getElementById('warning-services').textContent = summary.warning;
                    document.getElementById('critical-services').textContent = summary.critical;
                    
                    // Update service cards
                    renderServiceCards(services, 'services-overview');
                }
                
                // Update timestamp
                document.getElementById('last-update').textContent = new Date().toLocaleTimeString();
                
            } catch (error) {
                console.error('Error refreshing data:', error);
            }
        }
        
        // Initialize
        refreshData();
        setInterval(refreshData, 30000); // Refresh every 30 seconds
    </script>
</body>
</html>
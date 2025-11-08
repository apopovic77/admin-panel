<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Key Management</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Roboto, system-ui, sans-serif; margin: 0; padding: 24px; background: #f8fafc; color: #1e293b; }
        h1 { margin: 0 0 16px 0; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.04); margin-bottom: 24px; }
        .row { display: grid; grid-template-columns: 1fr 1fr 120px; gap: 12px; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
        .row.header { font-weight: 600; color: #475569; }
        .row:last-child { border-bottom: none; }
        input { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; }
        button { padding: 10px 12px; border: 1px solid #e2e8f0; background: #eef2ff; color: #3730a3; border-radius: 8px; cursor: pointer; }
        .danger { background: #fee2e2; color: #991b1b; }
        .muted { color: #64748b; font-size: 12px; }
        .grid { margin-top: 12px; }
        .form { display: grid; grid-template-columns: 1fr 1fr 120px; gap: 12px; margin-top: 12px; }
        .actions { display: flex; gap: 8px; }
        .note { margin-top: 12px; font-size: 12px; color: #475569; }
        .status-grid { margin-top: 12px; display: grid; gap: 12px; }
        .status-row { display: grid; grid-template-columns: 220px repeat(3, 1fr); gap: 12px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; }
        .status-header { background: #eef2ff; color: #1e293b; font-weight: 600; }
        .badge { display: inline-block; padding: 2px 8px; background: #e0f2fe; color: #0369a1; border-radius: 999px; font-size: 12px; }
        .muted-small { color: #94a3b8; font-size: 12px; }
        .status-summary { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 12px; color: #475569; }
    </style>
    <script>
    const API_BASE_URL = 'https://api.arkturian.com';
    const API_KEY = 'Inetpass1'; // admin key with admin user

    async function fetchKeys(){
        const res = await fetch(`${API_BASE_URL}/tenants/keys`, { headers: { 'X-API-KEY': API_KEY } });
        if(!res.ok){ throw new Error(await res.text()); }
        return await res.json();
    }
    async function upsertKey(apiKey, tenantId){
        const res = await fetch(`${API_BASE_URL}/tenants/keys`, {
            method: 'POST',
            headers: { 'X-API-KEY': API_KEY, 'Content-Type': 'application/json' },
            body: JSON.stringify({ api_key: apiKey, tenant_id: tenantId })
        });
        if(!res.ok){ throw new Error(await res.text()); }
        return await res.json();
    }
    async function deleteKey(apiKey){
        const res = await fetch(`${API_BASE_URL}/tenants/keys/${encodeURIComponent(apiKey)}`, {
            method: 'DELETE', headers: { 'X-API-KEY': API_KEY }
        });
        if(!res.ok){ throw new Error(await res.text()); }
        return await res.json();
    }
    async function fetchStatus(){
        const res = await fetch(`${API_BASE_URL}/tenants/status`, { headers: { 'X-API-KEY': API_KEY } });
        if(!res.ok){ throw new Error(await res.text()); }
        return await res.json();
    }
    function formatBytes(bytes){
        if(bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        const value = bytes / Math.pow(k, i);
        return `${value.toFixed(value >= 10 || i === 0 ? 0 : 1)} ${sizes[i]}`;
    }

    async function refreshGrid(){
        const grid = document.getElementById('grid');
        grid.innerHTML = '';
        try{
            const data = await fetchKeys();
            const keys = Object.keys(data).sort();
            if(keys.length === 0){
                grid.innerHTML = '<div class="muted">No tenant keys configured.</div>';
                return;
            }
            const header = document.createElement('div');
            header.className = 'row header';
            header.innerHTML = '<div>API Key</div><div>Tenant ID</div><div>Actions</div>';
            grid.appendChild(header);
            keys.forEach(k => {
                const row = document.createElement('div');
                row.className = 'row';
                const t = data[k] || '';
                row.innerHTML = `
                    <div><code>${k}</code></div>
                    <div>${t}</div>
                    <div class="actions">
                        <button class="danger" onclick="onDeleteKey('${encodeURIComponent(k)}')">Delete</button>
                    </div>
                `;
                grid.appendChild(row);
            });
        }catch(e){
            grid.innerHTML = `<div class="muted">Error: ${e.message}</div>`;
        }
    }

    async function onCreate(){
        const apiKey = document.getElementById('new-api-key').value.trim();
        const tenantId = document.getElementById('new-tenant-id').value.trim();
        if(!apiKey || !tenantId){ alert('Provide both API Key and Tenant ID'); return; }
        try{
            await upsertKey(apiKey, tenantId);
            document.getElementById('new-api-key').value = '';
            document.getElementById('new-tenant-id').value = '';
            await refreshGrid();
        }catch(e){ alert(e.message); }
    }
    async function onDeleteKey(encodedKey){
        const key = decodeURIComponent(encodedKey);
        if(!confirm(`Delete key ${key}?`)) return;
        try{
            await deleteKey(key);
            await refreshGrid();
        }catch(e){ alert(e.message); }
    }

    async function refreshStatus(){
        const container = document.getElementById('status-grid');
        const summary = document.getElementById('status-summary');
        container.innerHTML = '';
        summary.innerHTML = '';
        try{
            const data = await fetchStatus();
            if(!data.tenants || data.tenants.length === 0){
                container.innerHTML = '<div class="muted">No tenant usage data available.</div>';
                return;
            }
            const header = document.createElement('div');
            header.className = 'status-row status-header';
            header.innerHTML = '<div>Tenant</div><div>Total Objects</div><div>Storage Used</div><div>Last Upload</div>';
            container.appendChild(header);
            data.tenants.forEach(t => {
                const row = document.createElement('div');
                row.className = 'status-row';
                const last = t.last_object_created_at ? new Date(t.last_object_created_at).toLocaleString() : '<span class="muted-small">n/a</span>';
                row.innerHTML = `
                    <div><strong>${t.tenant_id}</strong></div>
                    <div>${t.object_count.toLocaleString()}</div>
                    <div>${formatBytes(t.total_bytes)}</div>
                    <div>${last}</div>
                `;
                container.appendChild(row);
            });
            summary.innerHTML = `
                <div><strong>Total objects:</strong> ${data.total_objects.toLocaleString()}</div>
                <div><strong>Total storage:</strong> ${formatBytes(data.total_bytes)}</div>
            `;
        }catch(e){
            container.innerHTML = `<div class="muted">Error loading tenant status: ${e.message}</div>`;
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        refreshGrid();
        refreshStatus();
    });
    </script>
</head>
<body>
    <?php include 'menu.php'; ?>
    <h1>Tenant Key Management</h1>
    <div class="card">
        <h2 style="margin-top:0;">Tenant Usage Overview</h2>
        <div class="status-summary" id="status-summary"></div>
        <div class="status-grid" id="status-grid"></div>
    </div>
    <div class="card">
        <div class="form">
            <input id="new-api-key" placeholder="API Key (e.g., ClientASecretKey)" />
            <input id="new-tenant-id" placeholder="Tenant ID (e.g., client-a)" />
            <button onclick="onCreate()">Add / Update</button>
        </div>
        <div class="note">Keys map to tenant identifiers. Non-admin calls are tenant-scoped by their key.</div>
        <div id="grid" class="grid"></div>
    </div>
</body>
</html>



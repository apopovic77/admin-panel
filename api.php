<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - API Inspector</title>
    <style>
        :root {
            --text: #1e293b; --muted: #475569; --brand: #1f2937; --brand-2: #8B9DC3;
            --ring: rgba(148, 163, 184, .3); --surface: rgba(255, 255, 255, 0.98);
            --background-gradient: linear-gradient(to bottom, #ffffff, #f8fafc, #e2e8f0);
            --radius-lg: 16px; --radius-md: 12px; --radius-sm: 8px;
            --shadow-primary: 0 10px 30px rgba(0, 0, 0, .07); --gap: 24px;
            --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Roboto, system-ui, sans-serif;
            --h2-size: clamp(22px, 3.6vw, 34px); --kicker-size: 12px;
        }
        body { 
            font-family: var(--font-family); margin: 0; background: var(--background-gradient);
            color: var(--text); padding: var(--gap); min-height: 100vh;
        }
        .wrapper { max-width: 1200px; margin: 0 auto; }
        h1 { font-size: clamp(34px, 6.2vw, 76px); font-weight: 700; padding-top: 32px; margin-bottom: 48px; text-align: center; }
        .card { background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-lg); padding: var(--gap); box-shadow: var(--shadow-primary); }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text); font-size: var(--kicker-size); text-transform: uppercase; letter-spacing: .08em; }
        input { width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm); }
        button { padding: 12px 20px; border: none; border-radius: var(--radius-sm); cursor: pointer; background-color: var(--brand-2); color: white; font-weight: 600; }
        .response-area { margin-top: var(--gap); padding: var(--gap); background: #f8fafc; border: 1px solid var(--ring); border-radius: var(--radius-sm); white-space: pre-wrap; font-family: 'SF Mono', monospace; min-height: 120px; max-height: 500px; overflow-y: auto; }
        img { max-width: 100%; border-radius: var(--radius-sm); margin-top: 16px; display:none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'menu.php'; ?>
        <h1>API Inspector</h1>

        <div class="card">
            <label for="object-id-input">Storage Object ID</label>
            <input type="number" id="object-id-input" placeholder="e.g. 12345">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button id="fetch-object-btn" onclick="fetchObject()">Fetch Object</button>
                <button id="fetch-preview-btn" onclick="showPreview()">Show Preview</button>
            </div>
            <div id="api-response" class="response-area"></div>
            <img id="api-preview" alt="Preview">
        </div>

        <div class="card" style="margin-top: 24px;">
            <h2 style="margin-top:0;">Storage List</h2>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:12px;">
                <div>
                    <label for="list-mine">Mine</label>
                    <input type="checkbox" id="list-mine" checked>
                </div>
                <div>
                    <label for="list-context">Context</label>
                    <input type="text" id="list-context" placeholder="e.g. tts-generation">
                </div>
                <div>
                    <label for="list-collection">Collection ID</label>
                    <input type="text" id="list-collection" placeholder="e.g. ai-php-tests">
                </div>
                <div>
                    <label for="list-link">Link ID</label>
                    <input type="text" id="list-link" placeholder="optional link id">
                </div>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top: 8px;">
                <button onclick="fetchList()">Fetch List</button>
            </div>
            <div id="list-response" class="response-area"></div>
            <div id="list-items" style="margin-top:12px;"></div>
        </div>
    </div>

<script>
const API_BASE_URL = 'https://api.arkturian.com';
const API_KEY = 'Inetpass1';

async function fetchObject() {
    const idInput = document.getElementById('object-id-input');
    const out = document.getElementById('api-response');
    const img = document.getElementById('api-preview');
    img.style.display = 'none';
    out.textContent = 'Loading...';
    const id = idInput.value.trim();
    if (!id) { out.textContent = 'Please enter a valid object id.'; return; }
    try {
        const res = await fetch(`${API_BASE_URL}/storage/objects/${id}`, {
            headers: { 'X-API-KEY': API_KEY }
        });
        const text = await res.text();
        if (!res.ok) {
            out.textContent = `Error (${res.status}):\n${text}`;
            return;
        }
        out.textContent = JSON.stringify(JSON.parse(text), null, 2);
    } catch (e) {
        out.textContent = `Request failed: ${e.message}`;
    }
}

function showPreview() {
    const idInput = document.getElementById('object-id-input');
    const img = document.getElementById('api-preview');
    const id = idInput.value.trim();
    if (!id) { return; }
    img.src = `${API_BASE_URL}/imgpreview?id=${id}`;
    img.style.display = 'block';
}

async function fetchList() {
    const out = document.getElementById('list-response');
    const items = document.getElementById('list-items');
    items.innerHTML = '';
    out.textContent = 'Loading...';
    const mine = document.getElementById('list-mine').checked;
    const ctx = document.getElementById('list-context').value.trim();
    const coll = document.getElementById('list-collection').value.trim();
    const link = document.getElementById('list-link').value.trim();
    const params = new URLSearchParams();
    params.set('mine', mine ? 'true' : 'false');
    if (ctx) params.set('context', ctx);
    if (coll) params.set('collection_id', coll);
    if (link) params.set('link_id', link);
    try {
        const res = await fetch(`${API_BASE_URL}/storage/list?${params.toString()}`, {
            headers: { 'X-API-KEY': API_KEY }
        });
        const text = await res.text();
        if (!res.ok) { out.textContent = `Error (${res.status}):\n${text}`; return; }
        const data = JSON.parse(text);
        out.textContent = JSON.stringify(data, null, 2);
        // Render a compact list of items with clickable IDs
        if (data && Array.isArray(data.items)) {
            const frag = document.createDocumentFragment();
            data.items.slice(0, 50).forEach(obj => {
                const a = document.createElement('a');
                a.href = '#';
                a.textContent = `#${obj.id} ${obj.original_filename}`;
                a.style.display = 'block';
                a.style.margin = '4px 0';
                a.onclick = (e) => { e.preventDefault(); document.getElementById('object-id-input').value = obj.id; };
                frag.appendChild(a);
            });
            items.appendChild(frag);
        }
    } catch (e) {
        out.textContent = `Request failed: ${e.message}`;
    }
}
</script>

</body>
</html>


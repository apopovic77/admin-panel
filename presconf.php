<?php
// presconf.php
// Build a presentation config from a storage collection
// Input: ?collection_id=NAME (or ?collection=NAME)
// Output: If collection_id is provided → application/json with sections and concatenated template text
//         If not provided → HTML UI with combobox to select/filter collections

$collectionId = isset($_GET['collection_id']) ? $_GET['collection_id'] : (isset($_GET['collection']) ? $_GET['collection'] : '');
$ownerEmail  = isset($_GET['owner_email']) ? $_GET['owner_email'] : (isset($_GET['owner']) ? $_GET['owner'] : '');

$API_BASE = 'https://api.arkturian.com';
$API_KEY  = 'Inetpass1';

function looks_like_image_url($url) {
    if (!$url || !is_string($url)) return false;
    return (bool)preg_match('/\.(png|jpe?g|webp|gif|bmp|tiff|heic|heif)(\?.*)?$/i', $url);
}

function pick_title($it) {
    $title = '';
    if (!empty($it['ai_title'])) $title = $it['ai_title'];
    else if (!empty($it['title'])) $title = $it['title'];
    else if (!empty($it['original_filename'])) {
        $title = preg_replace('/\.[^.]+$/', '', $it['original_filename']);
    }
    return $title;
}

function pick_subtitle($it) {
    if (!empty($it['ai_subtitle'])) return $it['ai_subtitle'];
    if (!empty($it['description'])) return $it['description'];
    return '';
}

$url = $collectionId ? ($API_BASE . '/storage/list?collection_id=' . urlencode($collectionId) . '&mine=false&limit=500') : '';
$isHtmlUi = $collectionId === '';

if ($isHtmlUi) {
    // Simple HTML UI with combobox (datalist) to select a collection and preview JSON
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Presentation Config Builder</title>
        <style>
            :root{ --gap:12px; --bg:#0f172a; --card:#111827; --text:#e5e7eb; --muted:#9ca3af; --ring:rgba(148,163,184,.25); }
            body{ margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Roboto,system-ui,sans-serif; background:linear-gradient(135deg,#0b1220,#000); color:var(--text); }
            header{ padding:16px 20px; position:sticky; top:0; background:rgba(15,23,42,.85); backdrop-filter:blur(8px); border-bottom:1px solid var(--ring); }
            main{ padding:16px 20px; }
            .row{ display:flex; flex-wrap:wrap; gap:var(--gap); align-items:center; }
            .card{ background:var(--card); border:1px solid var(--ring); border-radius:10px; padding:12px; }
            label{ font-size:12px; color:var(--muted); display:block; margin-bottom:6px; }
            input[type="text"], select{ padding:10px 12px; border-radius:8px; border:1px solid var(--ring); background:#0b1220; color:var(--text); min-width:300px; }
            button{ padding:10px 14px; border-radius:8px; border:1px solid var(--ring); background:#1f2937; color:#fff; cursor:pointer; }
            button:hover{ background:#243147; }
            pre{ white-space:pre-wrap; word-break:break-word; margin:0; }
            .grid{ display:grid; grid-template-columns:1fr; gap:var(--gap); }
            textarea{ width:100%; min-height:280px; border-radius:10px; border:1px solid var(--ring); background:#0b1220; color:var(--text); padding:12px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
            .small{ font-size:12px; color:var(--muted); }
            .items-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; }
            .item-tile{ position:relative; border:1px solid var(--ring); border-radius:10px; overflow:hidden; background:#0b1220; }
            .item-tile img{ width:100%; height:140px; object-fit:cover; display:block; }
            .item-name{ padding:8px 10px; font-size:12px; color:#e5e7eb; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; border-top:1px solid var(--ring); }
            .del-btn{ position:absolute; top:8px; right:8px; width:28px; height:28px; border-radius:14px; border:1px solid rgba(148,163,184,.35); background:rgba(220,53,69,.85); color:#fff; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; }
            .del-btn:hover{ background:rgba(220,53,69,.95); }
        </style>
    </head>
    <body>
        <header>
            <h2 style="margin:0">Presentation Config Builder</h2>
        </header>
        <main class="grid">
            <section class="card">
                <div class="row" style="gap:20px; align-items:flex-end">
                    <div>
                        <label for="email-select">Owner Email</label>
                        <input id="email-select" list="emails" type="text" placeholder="Type or choose owner" autocomplete="off" style="min-width:320px" />
                        <datalist id="emails"></datalist>
                    </div>
                    <div>
                        <label for="collection">Collection</label>
                        <input id="collection" list="collections" type="text" placeholder="Search or choose collection" autocomplete="off" />
                        <datalist id="collections"></datalist>
                    </div>
                    <div>
                        <label for="presname">Presentation Name</label>
                        <input id="presname" type="text" placeholder="my-presentation" autocomplete="off" />
                    </div>
                    <div>
                        <button id="generate">Build Text</button>
                        <button id="upload">Upload to Storage</button>
                    </div>
                </div>
                <p id="status" style="color:var(--muted);margin-top:8px">Loading collections…</p>
            </section>
            <section class="card" id="collections-card">
                <label>Available Collections</label>
                <div id="collections-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;margin-top:10px"></div>
            </section>
            <section class="card" id="items-card" style="display:none;">
                <label>Collection Items</label>
                <div id="items-grid" class="items-grid"></div>
            </section>
            <section class="card">
                <label>Presentation Text (editable)</label>
                <textarea id="doc" placeholder="Generated presentation text will appear here. You can edit before uploading."></textarea>
            </section>
            <section class="card">
                <label>Source JSON (reference)</label>
                <pre id="output" class="small">{}</pre>
                <div id="links" class="small" style="margin-top:8px"></div>
            </section>
        </main>
        <script>
        (async function(){
            const API_BASE = '<?= $API_BASE ?>';
            const API_KEY  = '<?= $API_KEY ?>';
            const listBox  = document.getElementById('collections');
            const input    = document.getElementById('collection');
            const statusEl = document.getElementById('status');
            const output   = document.getElementById('output');
            const docEl    = document.getElementById('doc');
            const linksEl  = document.getElementById('links');
            const btn      = document.getElementById('generate');
            const btnUp    = document.getElementById('upload');
            const emailSel = document.getElementById('email-select');
            const emailList = document.getElementById('emails');
            const presName = document.getElementById('presname');
            const grid     = document.getElementById('collections-grid');
            const GLOBAL_HEADER = `---GLOBAL---
backgroundGradientColor1: #1a2a6c
backgroundGradientColor2: #00c4f5

processNumberLineOffset: 0.2

showLabel: true
labelColor: #FFFFFF
labelBackgroundColor: #1a2a6c
labelOrientationMode: static
labelParentMode: poishape
labelDistance: 0.05
fadeLabelOnActive: false
labelOrientationMode: static
labelDistance: 0.2
labelLongitude: 10
labelLatitude: -45
labelScale: 0.001
labelMaxWidth: 500
labelRenderMode: html
labelScreenPosition: bottom-center
labelBackgroundMode: line
labelBackgroundGradient: linear-gradient(45deg, #ff6b6b, #4ecdc4)


color: #FF00FF
shape: tile
depth: 0.1
cornerRadius: 0.05
edgeRadius: 0.15

videoAutoplay: true

layouter: line
---`;
            const itemsCard = document.getElementById('items-card');
            const itemsGrid = document.getElementById('items-grid');
            const editable = new URLSearchParams(location.search).get('editable') === 'true';

            async function fetchEmails(){
                // Seed like collections.php does
                emailList.innerHTML = '';
                emailList.insertAdjacentHTML('beforeend', `<option value="public">`);
                try {
                    const response = await fetch(`${API_BASE}/storage/admin/users-with-collections`, { headers:{ 'X-API-KEY': API_KEY }});
                    if (!response.ok) throw new Error('Failed to fetch users with collections');
                    const emailsWithCollections = await response.json();
                    emailsWithCollections.forEach(user => {
                        emailList.insertAdjacentHTML('beforeend', `<option value="${user.email}">`);
                    });
                } catch (e) {
                    // keep base options
                }
            }

            function renderCollectionsList(collections, owner){
                if(!Array.isArray(collections) || collections.length === 0){
                    grid.innerHTML = '<div style="color:#9ca3af">No collections found.</div>';
                    return;
                }
                grid.innerHTML = collections.map(c => {
                    const name = c.name || c.id;
                    const count = c.item_count ?? '';
                    return `<div class="collection-card" style="background:#0b1220;border:1px solid rgba(148,163,184,.25);border-radius:10px;padding:12px;cursor:pointer" data-name="${String(name).replace(/"/g,'&quot;')}">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                            <div style="font-weight:600;color:#e5e7eb;word-break:break-word">${name}</div>
                            <div style="background:#8B9DC3;color:white;padding:2px 8px;border-radius:999px;font-size:12px">${count}</div>
                        </div>
                        <div style="margin-top:6px;color:#9ca3af;font-size:12px">ID: ${c.id}</div>
                    </div>`;
                }).join('');
                // click to populate input
                grid.querySelectorAll('.collection-card').forEach(card => {
                    card.addEventListener('click', () => {
                        input.value = card.getAttribute('data-name') || '';
                        // Load items preview if editable mode is on
                        if(input.value){ fetchItems(input.value, emailSel.value || ''); }
                    });
                });
            }

            async function fetchItems(collectionName, owner){
                try{
                    itemsCard.style.display = 'block';
                    itemsGrid.innerHTML = '<div class="small">Loading items…</div>';
                    let url = `${API_BASE}/storage/list?collection_id=${encodeURIComponent(collectionName)}&mine=false`;
                    const res = await fetch(url, { headers:{ 'X-API-KEY': API_KEY }});
                    if(!res.ok) throw new Error('list failed');
                    const data = await res.json();
                    let items = data.items || [];
                    if(owner){
                        if(owner === 'public'){ items = items.filter(it => !it.owner_email); }
                        else { items = items.filter(it => (it.owner_email||'').toLowerCase() === owner.toLowerCase()); }
                    }
                    renderItems(items);
                }catch(err){
                    itemsGrid.innerHTML = `<div class="small">Failed to load items: ${err.message}</div>`;
                }
            }

            function renderItems(items){
                if(!Array.isArray(items) || items.length === 0){
                    itemsGrid.innerHTML = '<div class="small">No items.</div>';
                    return;
                }
                itemsGrid.innerHTML = '';
                for(const it of items){
                    const tile = document.createElement('div');
                    tile.className = 'item-tile';
                    const img = document.createElement('img');
                    img.loading = 'lazy';
                    img.src = it.thumbnail_url || it.file_url || '';
                    img.alt = it.title || it.original_filename || '';
                    tile.appendChild(img);
                    if(editable){
                        const del = document.createElement('button');
                        del.className = 'del-btn';
                        del.textContent = '×';
                        del.title = 'Delete item';
                        del.addEventListener('click', async (e) => {
                            e.stopPropagation();
                            if(!confirm(`Delete this item permanently?\n${it.title || it.original_filename || ''}`)) return;
                            try{
                                const r = await fetch(`${API_BASE}/storage/${it.id}`, { method:'DELETE', headers:{ 'X-API-KEY': API_KEY }});
                                if(!r.ok) throw new Error('delete failed');
                                tile.remove();
                            }catch(err){ alert('Delete failed: ' + err.message); }
                        });
                        tile.appendChild(del);
                    }
                    const cap = document.createElement('div');
                    cap.className = 'item-name';
                    cap.textContent = it.title || it.original_filename || '';
                    tile.appendChild(cap);
                    itemsGrid.appendChild(tile);
                }
            }

            async function fetchCollections(){
                statusEl.textContent = 'Loading collections…';
                const owner = emailSel.value || '';
                try{
                    let endpoint = API_BASE + '/storage/admin/collections';
                    if(owner === 'public') endpoint += '?public_only=true';
                    else if(owner) endpoint += '?user_email=' + encodeURIComponent(owner);
                    const res = await fetch(endpoint, { headers:{ 'X-API-KEY': API_KEY }});
                    if(!res.ok) throw new Error('collections failed');
                    const data = await res.json();
                    const list = Array.isArray(data) ? data : [];
                    // datalist entries
                    const names = list.map(x=>x.name||x.id).filter(Boolean);
                    listBox.innerHTML = names.map(n => `<option value="${String(n).replace(/"/g,'&quot;')}"></option>`).join('');
                    statusEl.textContent = `Loaded ${names.length} collections${owner? ' for '+owner : ''}.`;
                    // render grid
                    renderCollectionsList(list, owner);
                }catch(err){
                    statusEl.textContent = 'Could not load collections.';
                    grid.innerHTML = '<div style="color:#9ca3af">Failed to load collections.</div>';
                }
            }

            async function generate(){
                const name = input.value.trim();
                if(!name){ output.textContent = '{ "error": "Please select a collection" }'; return; }
                statusEl.textContent = 'Building…';
                try{
                    const owner = emailSel.value || '';
                    const qp = 'presconf.php?collection_id=' + encodeURIComponent(name) + (owner? ('&owner_email=' + encodeURIComponent(owner)) : '');
                    const res = await fetch(qp);
                    const json = await res.json();
                    const docText = (json.document || '');
                    // normalize: replace literal \n sequences with real line breaks, just in case
                    const normalized = docText.replace(/\\n/g, '\n');
                    docEl.value = GLOBAL_HEADER + '\n\n' + normalized;
                    output.textContent = JSON.stringify(json, null, 2);
                    statusEl.textContent = `Done. ${json.count||0} items.`;
                    linksEl.textContent = '';
                    // If editable mode and a collection is selected, show items list
                    if(editable){ fetchItems(name, owner); }
                }catch(err){
                    output.textContent = '{ "error": "Failed to generate." }';
                    statusEl.textContent = 'Failed.';
                }
            }

            async function upload(){
                const coll = input.value.trim();
                const owner = emailSel.value || '';
                if(!coll){ output.textContent = '{ "error": "Please select a collection" }'; return; }
                statusEl.textContent = 'Preparing markdown…';
                try{
                    let md = (docEl.value || '').trim();
                    if(!md){
                        // generate on the fly if editor is empty
                        const qp = 'presconf.php?collection_id=' + encodeURIComponent(coll) + (owner? ('&owner_email=' + encodeURIComponent(owner)) : '');
                        const res = await fetch(qp);
                        const json = await res.json();
                        if(!json || !json.document){ throw new Error('No document generated'); }
                        md = (json.document || '').replace(/\\n/g,'\n');
                        docEl.value = md;
                    }
                    // ensure global header is present
                    if(!/^---GLOBAL---/.test(md)){
                        md = GLOBAL_HEADER + '\n\n' + md;
                    }
                    if(!md.endsWith('\n')) md += '\n';

                    // Filename
                    let name = (presName.value || '').trim();
                    if(!name){ name = 'presentation-' + new Date().toISOString().replace(/[:.]/g,'-'); }
                    if(!name.endsWith('.md')) name += '.md';

                    // Build multipart form
                    const fd = new FormData();
                    fd.append('file', new Blob([md], { type:'text/markdown' }), name);
                    fd.append('context', 'presentation');
                    // Put into selected collection
                    fd.append('collection_id', coll);
                    if(owner === 'public'){ fd.append('is_public', 'true'); } else if(owner){ fd.append('owner_email', owner); }
                    fd.append('skip_ai_safety', 'true');

                    statusEl.textContent = 'Uploading…';
                    const up = await fetch(API_BASE + '/storage/upload', { method:'POST', headers:{ 'X-API-KEY': API_KEY }, body: fd });
                    if(!up.ok){ throw new Error(await up.text() || 'upload failed'); }
                    const saved = await up.json();
                    statusEl.textContent = `Uploaded: id=${saved.id} (${saved.original_filename || name})`;
                    output.textContent = JSON.stringify(saved, null, 2);
                    const storageUrl = `https://arkturian.com/?storageid=${encodeURIComponent(saved.id)}`;
                    linksEl.innerHTML = `<a href="${storageUrl}" target="_blank" rel="noopener">Open on arkturian.com</a>`;
                }catch(err){
                    statusEl.textContent = 'Upload failed.';
                    output.textContent = '{ "error": "' + (err && err.message ? err.message : 'Upload error') + '" }';
                    linksEl.textContent = '';
                }
            }

            btn.addEventListener('click', generate);
            input.addEventListener('keydown', (e)=>{ if(e.key === 'Enter') generate(); });
            emailSel.addEventListener('change', fetchCollections);
            btnUp.addEventListener('click', upload);
            await fetchEmails();
            fetchCollections();
        })();
        </script>
    </body>
    </html>
    <?php
    exit;
}

// JSON mode for a specific collection
header('Content-Type: application/json; charset=utf-8');
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'X-API-KEY: ' . $API_KEY ]);
curl_setopt($ch, CURLOPT_TIMEOUT, 6);
$resp = curl_exec($ch);
$curlErr = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

if ($resp === false) {
    http_response_code(502);
    echo json_encode([ 'error' => 'api_request_failed', 'detail' => $curlErr ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($resp, true);
if (!is_array($data)) {
    http_response_code(502);
    echo json_encode([ 'error' => 'invalid_api_response', 'curl' => $curlInfo, 'body' => $resp ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
// Optional server-side owner filter for completeness
if ($ownerEmail) {
    $items = array_values(array_filter($items, function($it) use ($ownerEmail){
        $email = $it['owner_email'] ?? '';
        if ($ownerEmail === 'public') return empty($email);
        return strcasecmp($email, $ownerEmail) === 0;
    }));
}

$sections = [];
$blocks   = [];

foreach ($items as $it) {
    if (!$it) continue;
    $mime = isset($it['mime_type']) ? $it['mime_type'] : '';
    $isImage = ($mime && strpos($mime, 'image/') === 0) || looks_like_image_url($it['file_url'] ?? '') || looks_like_image_url($it['thumbnail_url'] ?? '');
    $isVideo = !empty($it['hls_url']);
    if (!$isImage && !$isVideo) continue;

    $uri = $isVideo ? $it['hls_url'] : ($it['file_url'] ?? $it['thumbnail_url'] ?? '');
    if (!$uri) continue;

    $title = pick_title($it);
    $subtitle = pick_subtitle($it);

    $sections[] = [
        'id' => $it['id'] ?? null,
        'type' => $isVideo ? 'video' : 'image',
        'uri' => $uri,
        'title' => $title,
        'subtitle' => $subtitle,
    ];

    // Build block in requested template format
    // <--\nuri: <link>\n>\n# Title\nSubtitle\n---
    $block = "<!--\nuri: {$uri}\n-->\n# " . ($title !== '' ? $title : '') . "\n" . ($subtitle !== '' ? $subtitle : '') . "\n---";
    $blocks[] = $block;
}

echo json_encode([
    'collection_id' => $collectionId,
    'count' => count($sections),
    'sections' => $sections,
    'document' => implode("\n\n", $blocks),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);


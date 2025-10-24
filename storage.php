<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arkturian Storage</title>
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
            color: var(--text); padding-bottom: 64px;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 var(--gap); }
        h1 {
            font-size: clamp(34px, 6.2vw, 76px); font-weight: 700;
            padding-top: 32px; margin-bottom: 64px; text-align: center;
        }
        h2 {
            font-size: var(--h2-size); font-weight: 600; margin-bottom: var(--gap);
            border-bottom: 1px solid var(--ring); padding-bottom: 16px;
        }
        .upload-section {
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            margin-bottom: 64px; box-shadow: var(--shadow-primary);
        }
        .control-group { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; }
        .control-group label { font-weight: 500; color: var(--muted); }
        .control-group input {
            border: 1px solid var(--ring); border-radius: var(--radius-sm);
            padding: 10px 14px; font-size: 1em; background-color: #f8fafc;
        }
        #drop-zone {
            border: 2px dashed var(--ring); border-radius: var(--radius-md); padding: 40px;
            text-align: center; margin-top: var(--gap); transition: all 0.2s;
        }
        #drop-zone.dragover { border-color: var(--brand-2); background-color: #eef5ff; }
        .progress-bar { height: 5px; background-color: var(--brand-2); transition: width 0.3s; border-radius: 5px; margin-top: 1em; }
        .file-list { display: grid; gap: 16px; }
        .upload-status-list { display: grid; gap: 8px; margin-top: 12px; }
        .upload-item { background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 12px; box-shadow: var(--shadow-primary); }
        .upload-item .name { font-weight: 600; font-size: 0.95em; }
        .upload-item .meta { color: var(--muted); font-size: 12px; margin-top: 4px; }
        .upload-item .bar { width: 100%; height: 6px; background: #e5e7eb; border-radius: 4px; overflow: hidden; margin-top: 8px; }
        .upload-item .bar > div { height: 100%; width: 0%; background: var(--brand-2); transition: width 0.2s ease; }
        .upload-item.error { border-color: #e53935; }
        .upload-item .error-text { color: #e53935; font-size: 12px; margin-top: 6px; white-space: pre-wrap; }
        .file-card {
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-md); padding: 16px;
            display: grid; grid-template-columns: 60px 1fr; gap: 16px; align-items: center;
        }
        .file-card .thumbnail { 
            width: 60px; height: 60px; border-radius: var(--radius-sm); 
            object-fit: cover; background-color: #f8fafc; 
            display: flex; align-items: center; justify-content: center;
            border: 1px solid var(--ring);
        }
        .file-icon {
            font-size: 24px; color: var(--brand-2); 
            display: flex; align-items: center; justify-content: center;
            width: 100%; height: 100%;
        }
        .file-card .info { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px 24px; align-items: center; }
        .info-group { font-size: 0.9em; }
        .info-group .label {
            font-size: var(--kicker-size); text-transform: uppercase; letter-spacing: .08em;
            color: var(--muted); margin-bottom: 4px; display: block;
        }
        .info-group .value, .info-group a { font-weight: 500; color: var(--brand); text-decoration: none; }
        .filename-link { display: inline-block; max-width: 360px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: bottom; }
        .info-group a:hover { text-decoration: underline; }
        .info-group input, .info-group textarea {
            width: 100%; border: 1px solid transparent; padding: 6px; box-sizing: border-box;
            border-radius: var(--radius-sm); background-color: #f8fafc; transition: all 0.2s;
        }
        .info-group input:focus, .info-group textarea:focus { outline: none; border-color: var(--brand-2); background-color: #fff; }
        .actions button, .actions a { font-weight: 500; cursor: pointer; background: none; border: none; padding: 0; margin-right: 16px; font-size: 1em; }
        .play-link { color: var(--brand-2); }
        .delete-btn { color: #e53935; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; }
        .modal-buttons button { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 0 10px; }
        #modal-confirm { background-color: #dc3545; color: white; }
        #modal-cancel { background-color: #6c757d; color: white; }
        
        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            .container { 
                padding: 0 16px; 
                max-width: 100%; 
            }
            
            h1 { 
                font-size: clamp(24px, 8vw, 32px); 
                padding-top: 16px; 
                margin-bottom: 32px; 
            }
            
            h2 { 
                font-size: clamp(18px, 5vw, 24px); 
                margin-bottom: 16px; 
            }
            
            .upload-section { 
                padding: 20px; 
                margin-bottom: 32px; 
            }
            
            .control-group { 
                flex-direction: column; 
                align-items: stretch; 
                gap: 8px; 
                margin-bottom: 12px; 
            }
            
            .control-group label { 
                font-size: var(--kicker-size); 
                margin-bottom: 4px; 
            }
            
            .control-group input { 
                padding: 12px; 
                font-size: 16px; 
                width: 100%; 
                box-sizing: border-box; 
            }
            
            #drop-zone { 
                padding: 30px 20px; 
                font-size: 14px; 
            }
            
            .file-list { 
                gap: 12px; 
            }
            
            .file-card { 
                grid-template-columns: 1fr; 
                gap: 12px; 
                padding: 16px; 
                text-align: center; 
            }
            
            .file-card .thumbnail { 
                width: 80px; 
                height: 80px; 
                margin: 0 auto; 
            }
            
            .file-card .info { 
                grid-template-columns: 1fr; 
                gap: 12px; 
                text-align: left; 
            }
            
            .info-group { 
                font-size: 0.85em; 
            }
            
            .info-group .label { 
                font-size: 10px; 
            }
            
            .info-group input, .info-group textarea { 
                padding: 8px; 
                font-size: 14px; 
            }
            
            .info-group textarea { 
                min-height: 60px; 
            }
            .filename-link { max-width: 60vw; }
            
            .actions button, .actions a { 
                font-size: 14px; 
                margin-right: 12px; 
                margin-bottom: 8px; 
            }
            
            .modal-content { 
                margin: 20px; 
                padding: 20px; 
                max-width: calc(100vw - 40px); 
            }
            
            .modal-buttons { 
                flex-direction: column; 
                gap: 10px; 
            }
            
            .modal-buttons button { 
                width: 100%; 
                padding: 12px; 
                margin: 0; 
                min-height: 44px; 
            }
        }
        
        @media (max-width: 480px) {
            .container { 
                padding: 0 12px; 
            }
            
            h1 { 
                font-size: clamp(20px, 10vw, 28px); 
            }
            
            .upload-section { 
                padding: 16px; 
            }
            
            .control-group input { 
                padding: 10px; 
                font-size: 14px; 
            }
            
            #drop-zone { 
                padding: 20px 15px; 
                font-size: 13px; 
            }
            
            .file-card { 
                padding: 12px; 
            }
            
            .file-card .thumbnail { 
                width: 60px; 
                height: 60px; 
            }
            
            .info-group { 
                font-size: 0.8em; 
            }
            
            .info-group .label { 
                font-size: 9px; 
            }
            
            .info-group input, .info-group textarea { 
                padding: 6px; 
                font-size: 13px; 
            }
            
            .actions button, .actions a { 
                font-size: 13px; 
            }
        }
        
        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            .control-group input { 
                min-height: 44px; 
                padding: 12px; 
            }
            
            .info-group input, .info-group textarea { 
                min-height: 44px; 
            }
            
            .info-group input:focus, .info-group textarea:focus { 
                font-size: 16px; /* Prevent zoom on iOS */ 
            }
            
            .actions button, .actions a { 
                min-height: 44px; 
                padding: 12px 16px; 
                display: inline-flex; 
                align-items: center; 
            }
            
            .file-card:hover { 
                transform: none; 
            }
        }
        
        /* Landscape orientation adjustments */
        @media (max-width: 768px) and (orientation: landscape) {
            .file-card { 
                grid-template-columns: 80px 1fr; 
                text-align: left; 
            }
            
            .file-card .thumbnail { 
                margin: 0; 
            }
            
            .file-card .info { 
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); 
            }
        }

        /* Ensure editor textarea is scrollable and touch-friendly */
        #editor-text {
            overflow: auto;
            -webkit-overflow-scrolling: touch;
            resize: vertical;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container">
        <h1>Storage</h1>
        <div class="upload-section">
            <h2>Upload Files</h2>
            <div class="control-group">
                <label for="owner-email-input">Owner Email</label>
                <input type="email" id="owner-email-input" placeholder="user@example.com" value="apopovic.aut@gmail.com" />
            </div>
            <div class="control-group">
                <label for="collection-id-input">Collection ID</label>
                <input type="text" id="collection-id-input" placeholder="project_alpha" />
            </div>
            <div class="control-group">
                <label for="link-id-input">Link ID (for linking related files)</label>
                <input type="text" id="link-id-input" placeholder="auto-generated or custom" />
            </div>
            <div class="control-group">
                <label for="skip-ai-safety-input">Skip AI Safety Check</label>
                <input type="checkbox" id="skip-ai-safety-input" />
            </div>

            <!-- Advanced Upload Options -->
            <details style="margin: 16px 0; border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 12px;">
                <summary style="cursor: pointer; font-weight: 600; color: var(--brand);">⚙️ Advanced Options</summary>

                <div class="control-group" style="margin-top: 16px;">
                    <label for="storage-mode-select">Storage Mode</label>
                    <select id="storage-mode-select" style="border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 10px 14px; font-size: 1em; background-color: #f8fafc;">
                        <option value="copy" selected>Copy (Download & Store)</option>
                        <option value="reference">Reference (Local Filesystem)</option>
                        <option value="external">External (Web URI - Proxied)</option>
                    </select>
                </div>

                <div class="control-group" id="reference-path-group" style="display: none;">
                    <label for="reference-path-input">Reference Path</label>
                    <input type="text" id="reference-path-input" placeholder="/mnt/data/images/product.jpg" />
                </div>

                <div class="control-group" id="external-uri-group" style="display: none;">
                    <label for="external-uri-input">External URI</label>
                    <input type="text" id="external-uri-input" placeholder="https://example.com/images/product.jpg" />
                </div>

                <div class="control-group">
                    <label for="analyze-toggle">Run AI Analysis</label>
                    <input type="checkbox" id="analyze-toggle" checked />
                </div>

                <div class="control-group">
                    <label for="ai-context-text-input">AI Context Text</label>
                    <textarea id="ai-context-text-input" placeholder="Product catalog from O'Neal 2026..." style="border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 10px 14px; font-size: 1em; background-color: #f8fafc; width: 100%; min-height: 60px; resize: vertical;"></textarea>
                </div>

                <div class="control-group">
                    <label for="ai-file-path-input">AI File Path</label>
                    <input type="text" id="ai-file-path-input" placeholder="/OnEal/2026/Helmets/Airframe.jpg" />
                </div>

                <div class="control-group">
                    <label for="ai-metadata-input">AI Metadata (JSON)</label>
                    <textarea id="ai-metadata-input" placeholder='{"brand": "O&#39;Neal", "year": "2026"}' style="border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 10px 14px; font-size: 1em; background-color: #f8fafc; width: 100%; min-height: 60px; resize: vertical;"></textarea>
                </div>
            </details>

            <div class="control-group">
                <label for="search-name-input">Search filename</label>
                <input type="text" id="search-name-input" placeholder="type to filter by filename" />
            </div>
            <div class="control-group">
                <label for="search-collection-input">Search collection</label>
                <input type="text" id="search-collection-input" placeholder="type to filter by collection name" />
            </div>
            <div id="drop-zone">
                <p>Drag & drop files here to upload</p>
                <div id="upload-progress" class="progress-bar"></div>
                <div id="upload-progress-text" style="margin-top: 8px; font-size: 12px; color: var(--muted);"></div>
            </div>
            <div class="control-group">
                <button id="new-md-btn" style="background: var(--brand-2); color: #fff; border: none; padding: 10px 14px; border-radius: 8px; cursor:pointer;">New Text</button>
                <span style="color: var(--muted); font-size: 12px;">Compose a new text file (choose the filename & extension)</span>
            </div>
            <div id="upload-status-list" class="upload-status-list"></div>
        </div>
        <div class="file-list" id="file-list"></div>
    </div>

    <div id="delete-modal" class="modal-overlay">
        <div class="modal-content">
            <p>Are you sure you want to permanently delete this file?</p>
            <div class="modal-buttons">
                <button id="modal-cancel">No</button>
                <button id="modal-confirm">Yes, Delete</button>
            </div>
        </div>
    </div>
    <div id="error-modal" class="modal-overlay">
        <div class="modal-content" style="text-align: left; max-width: 80%;">
            <h3>Transcoding Error</h3>
            <pre id="error-log-content" style="white-space: pre-wrap; background-color: #eee; padding: 1em; border-radius: 5px; max-height: 60vh; overflow-y: auto;"></pre>
            <div class="modal-buttons" style="text-align: right;">
                <button id="error-modal-close">Close</button>
            </div>
        </div>
    </div>

    <div id="editor-modal" class="modal-overlay">
        <div class="modal-content" style="text-align: left; max-width: 80%; width: 80%; max-height: 80vh; display: grid; grid-template-rows: auto 1fr auto; gap: 12px;">
            <h3 id="editor-title" style="margin: 0;">Edit File</h3>
            <textarea id="editor-text" style="width: 100%; height: 50vh; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-size: 14px; padding: 12px; border: 1px solid var(--ring); border-radius: 8px; background: #f8fafc; overflow: auto; -webkit-overflow-scrolling: touch;"></textarea>
            <div class="modal-buttons" style="text-align: right;">
                <button id="editor-cancel">Cancel</button>
                <button id="editor-save" style="background-color: var(--brand-2); color: white;">Save</button>
            </div>
        </div>
    </div>

<script>
    const API_BASE_URL = 'https://api-storage.arkturian.com';
    const API_KEY = 'Inetpass1';

    const dropZone = document.getElementById('drop-zone');
    const fileListContainer = document.getElementById('file-list');
    const uploadProgress = document.getElementById('upload-progress');
    const uploadStatusList = document.getElementById('upload-status-list');
    const uploadProgressText = document.getElementById('upload-progress-text');
    const ownerEmailInput = document.getElementById('owner-email-input');
    const searchNameInput = document.getElementById('search-name-input');
    const searchCollectionInput = document.getElementById('search-collection-input');
    
    // --- Modals ---
    const deleteModal = document.getElementById('delete-modal');
    const modalConfirmBtn = document.getElementById('modal-confirm');
    const modalCancelBtn = document.getElementById('modal-cancel');
    const errorModal = document.getElementById('error-modal');
    const errorLogContent = document.getElementById('error-log-content');
    const errorModalCloseBtn = document.getElementById('error-modal-close');
    const editorModal = document.getElementById('editor-modal');
    const editorText = document.getElementById('editor-text');
    const editorTitle = document.getElementById('editor-title');
    const editorCancel = document.getElementById('editor-cancel');
    const editorSave = document.getElementById('editor-save');
    const newMdBtn = document.getElementById('new-md-btn');
    let editTargetId = null;
    let fileToDeleteId = null;

    function showDeleteModal(fileId) { fileToDeleteId = fileId; deleteModal.style.display = 'flex'; }
    function hideDeleteModal() { fileToDeleteId = null; deleteModal.style.display = 'none'; }
    function showErrorModal(base64Error) { 
        try { errorLogContent.textContent = atob(base64Error); } catch (e) { errorLogContent.textContent = "Error decoding log."; }
        errorModal.style.display = 'flex'; 
    }
    function hideErrorModal() { errorModal.style.display = 'none'; }

    function showEditor(id, name, url) {
        editTargetId = id;
        editorTitle.textContent = `Edit ${name}`;
        editorText.value = '';
        editorModal.style.display = 'flex';
        // Always fetch fresh with cache-busting
        const fetchUrl = url.includes('?') ? `${url}&_=${Date.now()}` : `${url}?_=${Date.now()}`;
        // Fetch static file directly without custom headers to avoid CORS preflight
        fetch(fetchUrl)
            .then(r => r.text())
            .then(t => { editorText.value = t; })
            .catch(() => { editorText.value = ''; });
    }
    function hideEditor() { editTargetId = null; editorModal.style.display = 'none'; }

    modalConfirmBtn.addEventListener('click', () => { if (fileToDeleteId) { deleteFile(fileToDeleteId); } });
    modalCancelBtn.addEventListener('click', hideDeleteModal);
    errorModalCloseBtn.addEventListener('click', hideErrorModal);

    // --- Core Functions ---
    function debounce(fn, delay = 300) {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn.apply(null, args), delay); };
    }
    async function fetchFiles() {
        try {
            const nameQuery = (searchNameInput?.value || '').trim();
            const collectionQuery = (searchCollectionInput?.value || '').trim();
            const limit = 100;
            const url = new URL(`${API_BASE_URL}/storage/list`);
            url.searchParams.set('mine', 'false');
            url.searchParams.set('_t', String(Date.now()));
            url.searchParams.set('limit', String(limit));
            
            const response = await fetch(url.toString(), { headers: { 'X-API-KEY': API_KEY } });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            
            fileListContainer.innerHTML = '';
            let items = data.items || [];

            // Client-side filter for filename OR collection
            if (nameQuery || collectionQuery) {
                const nameQueryLower = nameQuery.toLowerCase();
                const collectionQueryLower = collectionQuery.toLowerCase();
                items = items.filter(f => {
                    const filename = (f.original_filename || '').toLowerCase();
                    const collection = (f.collection_id || '').toLowerCase();
                    const nameMatches = nameQuery ? filename.includes(nameQueryLower) : false;
                    const collectionMatches = collectionQuery ? collection.includes(collectionQueryLower) : false;
                    
                    if (nameQuery && collectionQuery) {
                        return nameMatches || collectionMatches;
                    } else if (nameQuery) {
                        return nameMatches;
                    } else {
                        return collectionMatches;
                    }
                });
            }

            // Limit on client as well (defensive)
            items.slice(0, limit).forEach(file => {
                const card = document.createElement('div');
                card.className = 'file-card';

                // Function to get file icon based on MIME type
                function getFileIcon(mimeType, filename) {
                    if (!mimeType) return '📄';
                    
                    const mime = mimeType.toLowerCase();
                    const ext = filename ? filename.split('.').pop().toLowerCase() : '';
                    
                    // Audio files
                    if (mime.startsWith('audio/')) return '🎵';
                    
                    // Video files (fallback if no thumbnail)
                    if (mime.startsWith('video/')) return '🎬';
                    
                    // Image files (fallback if no thumbnail)
                    if (mime.startsWith('image/')) return '🖼️';
                    
                    // Text files
                    if (mime.startsWith('text/') || 
                        ['txt', 'log', 'md', 'markdown', 'mdx', 'mdown', 'mkdn', 'mkd', 'json', 'xml', 'csv'].includes(ext)) return '📝';
                    
                    // Archive files
                    if (mime.includes('zip') || mime.includes('rar') || 
                        mime.includes('tar') || mime.includes('compress') ||
                        ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'].includes(ext)) return '🗜️';
                    
                    // PDF files
                    if (mime.includes('pdf')) return '📋';
                    
                    // Office documents
                    if (mime.includes('word') || mime.includes('document') || 
                        ['doc', 'docx'].includes(ext)) return '📄';
                    if (mime.includes('sheet') || mime.includes('excel') || 
                        ['xls', 'xlsx'].includes(ext)) return '📊';
                    if (mime.includes('presentation') || mime.includes('powerpoint') || 
                        ['ppt', 'pptx'].includes(ext)) return '📽️';
                    
                    // Code files
                    if (['js', 'html', 'css', 'php', 'py', 'java', 'cpp', 'c'].includes(ext)) return '💻';
                    
                    // Default file icon
                    return '📄';
                }

                const thumbnail = file.thumbnail_url 
                    ? `<img src="${file.thumbnail_url}" alt="thumbnail" class="thumbnail">`
                    : `<div class="thumbnail"><div class="file-icon">${getFileIcon(file.mime_type, file.original_filename)}</div></div>`;

                // Stream/Play Status
                let streamContent = 'N/A';
                const isTextLike = (file.mime_type && (file.mime_type.startsWith('text/') || file.mime_type.includes('json') || file.mime_type.includes('markdown'))) ||
                                   ['txt','log','md','markdown','mdx','mdown','mkdn','mkd','json','xml','csv'].includes((file.original_filename || '').split('.').pop().toLowerCase());
                if (file.hls_url) {
                    let playerUrl = `https://share.arkturian.com?current_id=${file.id}`;
                    if (file.collection_id) {
                        playerUrl += `&collection_id=${encodeURIComponent(file.collection_id)}`;
                    }
                    const links = [
                        `<a href="${playerUrl}" target="_blank" class="play-link">▶ Play VOD</a>`
                    ];
                    // Prefer API domain for direct HLS master link
                    if (file.object_key) {
                        const baseName = String(file.object_key).replace(/\.[^./]+$/, '');
                        const hlsApiUrl = `${API_BASE_URL}/uploads/storage/media/${baseName}/master.m3u8`;
                        links.push(`<a href="${hlsApiUrl}" target="_blank">🔗 HLS</a>`);
                        links.push(`<a href="#" onclick="navigator.clipboard && navigator.clipboard.writeText('${hlsApiUrl}'); return false;">📋 Copy HLS</a>`);
                    }
                    if (file.file_url) {
                        links.push(`<a href="${file.file_url}" target="_blank">⬇️ Download</a>`);
                    }
                    streamContent = links.join(' | ');
                } else if (file.mime_type && file.mime_type.startsWith('video/')) {
                    streamContent = `<i style="color: #6c757d;">Processing...</i>`;
                } else if (file.mime_type === 'application/zip' && file.original_filename && file.original_filename.endsWith('.zip')) {
                    streamContent = `<i style="color: #6c757d;">Pre-transcoded video processing...</i>`;
                } else if (file.mime_type && file.mime_type.startsWith('image/')) {
                    // For images, show Webview link only if a preview exists, plus Download
                    let imageLinks = [];
                    if (file.webview_url) {
                        const previewUrl = `${API_BASE_URL}/imgpreview?id=${file.id}`;
                        imageLinks.push(`<a href="${previewUrl}" target="_blank" class="play-link">🖼️ Webview</a>`);
                    }
                    imageLinks.push(`<a href="${file.file_url}" target="_blank">⬇️ Download</a>`);
                    streamContent = imageLinks.join(' | ');
                } else {
                    streamContent = `<a href="${file.file_url}" target="_blank">Download</a>`;
                }

                // Transcoding Status (separate field for videos)
                let transcodingContent = 'N/A';
                if (file.mime_type && file.mime_type.startsWith('video/')) {
                    if (file.transcoding_status === 'completed') {
                        transcodingContent = `<span style="color: #28a745; font-weight: bold;">✓ COMPLETED</span>`;
                    } else if (file.transcoding_status === 'failed') {
                        const errorMsg = btoa(file.transcoding_error || 'Unknown transcoding error');
                        transcodingContent = `<strong style="color:#d8000c; cursor:pointer;" onclick="showErrorModal('${errorMsg}')">✗ FAILED</strong>`;
                    } else if (file.transcoding_status === 'processing') {
                        const progress = file.transcoding_progress !== null ? ` (${file.transcoding_progress}%)` : '';
                        transcodingContent = `<i style="color: #007bff;">🔄 Processing${progress}...</i>`;
                    } else if (file.transcoding_status === 'queued') {
                        transcodingContent = `<i style="color: #6c757d;">⏳ Queued...</i>`;
                    }
                } else if (file.mime_type === 'application/zip' && file.original_filename && file.original_filename.endsWith('.zip')) {
                    // Pre-transcoded video ZIP files
                    if (file.hls_url) {
                        transcodingContent = `<span style="color: #28a745; font-weight: bold;">✓ PRE-TRANSCODED</span>`;
                    } else {
                        transcodingContent = `<i style="color: #007bff;">🔄 Processing ZIP...</i>`;
                    }
                }


                // AI Safety Status
                let aiSafetyContent = 'N/A';
                if (file.safety_info) {
                    try {
                        const safetyInfo = typeof file.safety_info === 'string' ? JSON.parse(file.safety_info) : file.safety_info;
                        if (safetyInfo.isSafe !== undefined) {
                            const safetyColor = safetyInfo.isSafe ? '#28a745' : '#dc3545';
                            const confidence = safetyInfo.confidence ? ` (${Math.round(safetyInfo.confidence * 100)}%)` : '';
                            aiSafetyContent = `<span style="color: ${safetyColor}; font-weight: bold;">${safetyInfo.isSafe ? 'SAFE' : 'UNSAFE'}${confidence}</span>`;
                            if (safetyInfo.reasoning) {
                                aiSafetyContent += `<br><small style="color: #6c757d;">${safetyInfo.reasoning}</small>`;
                            }
                        }
                    } catch (e) {
                        aiSafetyContent = `<span style="color: #ffc107;">Parse Error</span>`;
                    }
                } else if (file.ai_safety_rating) {
                    const ratingColor = file.ai_safety_rating === 'safe' ? '#28a745' : 
                                      file.ai_safety_rating === 'borderline' ? '#ffc107' : '#dc3545';
                    aiSafetyContent = `<span style="color: ${ratingColor}; font-weight: bold;">${file.ai_safety_rating.toUpperCase()}</span>`;
                } else if (file.ai_safety_status === 'failed') {
                    const aiErrorMsg = btoa(file.ai_safety_error || 'Unknown AI safety error');
                    aiSafetyContent = `<strong style="color:#d8000c; cursor:pointer;" onclick="showErrorModal('${aiErrorMsg}')">Failed</strong>`;
                } else if (file.ai_safety_status && file.ai_safety_status !== 'n/a') {
                    aiSafetyContent = `<i style="color: #6c757d;">${file.ai_safety_status}...</i>`;
                }

                // AI Generated Content
                let aiTitleContent = file.ai_title || '<span style="color:#6c757d">Not generated</span>';
                let aiSubtitleContent = file.ai_subtitle || '<span style="color:#6c757d">Not generated</span>';
                
                let aiTagsContent = 'None';
                if (file.ai_tags) {
                    try {
                        const tags = typeof file.ai_tags === 'string' ? JSON.parse(file.ai_tags) : file.ai_tags;
                        if (Array.isArray(tags) && tags.length > 0) {
                            aiTagsContent = tags.map(tag => `<span style="background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 4px; font-size: 0.85em;">${tag}</span>`).join(' ');
                        }
                    } catch (e) {
                        aiTagsContent = '<span style="color: #ffc107;">Parse Error</span>';
                    }
                }
                
                let aiCollectionsContent = 'None';
                if (file.ai_collections) {
                    try {
                        const collections = typeof file.ai_collections === 'string' ? JSON.parse(file.ai_collections) : file.ai_collections;
                        if (Array.isArray(collections) && collections.length > 0) {
                            aiCollectionsContent = collections.map(col => `<span style="background: #f3e5f5; color: #7b1fa2; padding: 2px 6px; border-radius: 4px; font-size: 0.85em;">${col}</span>`).join(' ');
                        }
                    } catch (e) {
                        aiCollectionsContent = '<span style="color: #ffc107;">Parse Error</span>';
                    }
                }

                // Enhanced details display for all media types
                let detailsContent = '';
                
                // File size (always show)
                const fileSizeMB = (file.file_size_bytes / (1024 * 1024)).toFixed(2);
                detailsContent += `${fileSizeMB} MB`;
                
                // Transcoded size for videos/pre-transcoded content
                if (file.transcoded_file_size_bytes) {
                    const transcodedSizeMB = (file.transcoded_file_size_bytes / (1024 * 1024)).toFixed(2);
                    detailsContent += ` | VOD: ${transcodedSizeMB} MB`;
                }
                
                // Resolution
                if (file.width && file.height) {
                    detailsContent += ` | ${file.width}×${file.height}`;
                }
                
                // Duration
                if (file.duration_seconds) {
                    const minutes = Math.floor(file.duration_seconds / 60);
                    const seconds = Math.round(file.duration_seconds % 60);
                    detailsContent += ` | ${minutes}:${seconds.toString().padStart(2, '0')}`;
                }
                
                // Bitrate
                if (file.bit_rate) {
                    const bitrateDisplay = file.bit_rate > 1000000 
                        ? `${(file.bit_rate / 1000000).toFixed(1)} Mbps`
                        : `${Math.round(file.bit_rate / 1000)} kbps`;
                    detailsContent += ` | ${bitrateDisplay}`;
                }
                
                // MIME type for context
                if (file.mime_type) {
                    const mimeShort = file.mime_type.split('/')[1]?.toUpperCase() || file.mime_type;
                    detailsContent += ` | ${mimeShort}`;
                }
                
                const ownerEmail = file.owner_email || '';

                const lat = file.geo_position?.latitude ?? file.latitude;
                const lon = file.geo_position?.longitude ?? file.longitude;

                card.innerHTML = `
                    ${thumbnail}
                    <div class="info">
                        <div class="info-group">
                            <div class="label">ID</div>
                            <div class="value">${file.id}</div>
                        </div>
                        <div class="info-group">
                            <div class="label">Filename</div>
                            <div class="value"><a class="filename-link" href="${file.file_url}" target="_blank" title="${file.original_filename}">${file.original_filename}</a></div>
                        </div>
                        <div class="info-group">
                            <div class="label">Owner</div>
                            <div class="value"><input type="email" class="owner-input" data-id="${file.id}" data-link-id="${file.link_id || ''}" value="${ownerEmail || ''}" placeholder="owner@example.com"></div>
                        </div>
                        <div class="info-group">
                            <div class="label">Title</div>
                            <div class="value"><input type="text" class="metadata-input" data-field="title" data-id="${file.id}" value="${file.title || ''}" placeholder="Add title"></div>
                        </div>
                        <div class="info-group">
                            <div class="label">Collection</div>
                            <div class="value"><input type="text" class="metadata-input" data-field="collection_id" data-id="${file.id}" value="${file.collection_id || ''}"></div>
                        </div>
                        <div class="info-group">
                            <div class="label">Link ID</div>
                            <div class="value"><input type="text" class="metadata-input" data-field="link_id" data-id="${file.id}" value="${file.link_id || ''}" placeholder="Link ID"></div>
                        </div>
                        <div class="info-group">
                            <div class="label">Description</div>
                            <div class="value"><textarea class="metadata-input" data-field="description" data-id="${file.id}" placeholder="Add description">${file.description || ''}</textarea></div>
                        </div>
                        <div class="info-group">
                            <div class="label">Details</div>
                            <div class="value"><small>${detailsContent.startsWith(' | ') ? detailsContent.substring(3) : detailsContent}</small></div>
                        </div>
                        <div class="info-group">
                            <div class="label">Created</div>
                            <div class="value"><small>${new Date(file.created_at).toLocaleString()}</small></div>
                        </div>
                        <div class="info-group">
                            <div class="label">Updated</div>
                            <div class="value"><small>${new Date(file.updated_at).toLocaleString()}</small></div>
                        </div>
                        <div class="info-group">
                            <div class="label">Likes</div>
                            <div class="value">${file.likes}</div>
                        </div>
                        ${lat && lon ? `
                        <div class="info-group">
                            <div class="label">GPS Location</div>
                            <div class="value"><a href="https://www.google.com/maps?q=${lat},${lon}" target="_blank">${parseFloat(lat).toFixed(5)}, ${parseFloat(lon).toFixed(5)}</a></div>
                        </div>
                        ` : ''}
                        <div class="info-group">
                            <div class="label">AI Safety</div>
                            <div class="value">${aiSafetyContent}</div>
                        </div>
                        <div class="info-group">
                            <div class="label">AI Title</div>
                            <div class="value">${aiTitleContent}</div>
                        </div>
                        <div class="info-group">
                            <div class="label">AI Subtitle</div>
                            <div class="value">${aiSubtitleContent}</div>
                        </div>
                        <div class="info-group">
                            <div class="label">AI Tags</div>
                            <div class="value">${aiTagsContent}</div>
                        </div>
                        <div class="info-group">
                            <div class="label">AI Collections</div>
                            <div class="value">${aiCollectionsContent}</div>
                        </div>
                        <div class="info-group">
                            <div class="label">Transcoding</div>
                            <div class="value">${transcodingContent}</div>
                        </div>
                        <div class="info-group">
                            <div class="label">Stream/Download</div>
                            <div class="value actions">${streamContent}</div>
                        </div>
                        <div class="info-group">
                            <div class="label">Actions</div>
                            <div class="value actions">
                                ${isTextLike ? `<button class="edit-text-btn" data-id="${file.id}" data-name="${file.original_filename}" data-url="${file.file_url}">Edit</button>` : ''}
                                <button class="delete-btn" data-id="${file.id}">Delete</button>
                            </div>
                        </div>
                    </div>
                `;
                fileListContainer.appendChild(card);
            });
        } catch (error) {
            console.error('Error fetching files:', error);
            fileListContainer.innerHTML = '<p>Error loading files.</p>';
        }
    }

    async function updateMetadata(fileId, payload, inputEl) {
        try {
            const response = await fetch(`${API_BASE_URL}/storage/objects/${fileId}`, {
                method: 'PATCH',
                headers: { 'X-API-KEY': API_KEY, 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            inputEl.style.backgroundColor = 'var(--success-color)';
            setTimeout(() => { inputEl.style.backgroundColor = ''; }, 1500);
        } catch (error) {
            console.error('Error updating metadata:', error);
            inputEl.style.backgroundColor = 'var(--error-color)';
        }
    }

    async function uploadFile(file, linkId = null, index = null, total = null, aggregate = null) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('is_public', true);
        const ownerEmail = ownerEmailInput.value.trim();
        if (ownerEmail) { formData.append('owner_email', ownerEmail); }
        const collectionId = document.getElementById('collection-id-input').value.trim();
        if (collectionId) { formData.append('collection_id', collectionId); }
        const linkIdValue = linkId || document.getElementById('link-id-input').value.trim();
        if (linkIdValue) { formData.append('link_id', linkIdValue); }
        const skipSafety = document.getElementById('skip-ai-safety-input')?.checked;
        if (skipSafety) { formData.append('skip_ai_safety', 'true'); }

        // Add new advanced options
        const storageModeSelect = document.getElementById('storage-mode-select');
        const storageMode = storageModeSelect ? storageModeSelect.value : 'copy';
        if (storageMode && storageMode !== 'copy') {
            formData.append('storage_mode', storageMode);
        }

        if (storageMode === 'reference') {
            const refPath = document.getElementById('reference-path-input')?.value?.trim();
            if (refPath) formData.append('reference_path', refPath);
        }

        if (storageMode === 'external') {
            const extUri = document.getElementById('external-uri-input')?.value?.trim();
            if (extUri) formData.append('external_uri', extUri);
        }

        const analyzeToggle = document.getElementById('analyze-toggle');
        const runAnalyze = analyzeToggle ? analyzeToggle.checked : true;
        formData.append('analyze', runAnalyze ? 'true' : 'false');

        const aiContextText = document.getElementById('ai-context-text-input')?.value?.trim();
        if (aiContextText) formData.append('ai_context_text', aiContextText);

        const aiFilePath = document.getElementById('ai-file-path-input')?.value?.trim();
        if (aiFilePath) formData.append('ai_file_path', aiFilePath);

        const aiMetadata = document.getElementById('ai-metadata-input')?.value?.trim();
        if (aiMetadata) formData.append('ai_metadata', aiMetadata);

        // UI: create or get upload item row
        const itemId = `up_${Date.now()}_${Math.random().toString(36).slice(2,8)}`;
        const row = document.createElement('div');
        row.className = 'upload-item';
        row.id = itemId;
        const ordinal = (index !== null && total !== null) ? ` (${index + 1}/${total})` : '';
        row.innerHTML = `
            <div class="name">${file.name}${ordinal}</div>
            <div class="meta">${(file.size / (1024*1024)).toFixed(2)} MB • Starting...</div>
            <div class="bar"><div></div></div>
            <div class="error-text" style="display:none;"></div>
        `;
        uploadStatusList.prepend(row);
        const bar = row.querySelector('.bar > div');
        const meta = row.querySelector('.meta');
        const errBox = row.querySelector('.error-text');

        try {
            // Use XHR to get progress
            await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', `${API_BASE_URL}/storage/upload`, true);
                xhr.setRequestHeader('X-API-KEY', API_KEY);
                const startedAt = Date.now();
                function formatSpeed(bps) {
                    if (!isFinite(bps) || bps <= 0) return '';
                    const kbps = bps / 1024;
                    if (kbps < 1024) return `${kbps.toFixed(1)} KB/s`;
                    const mbps = kbps / 1024;
                    return `${mbps.toFixed(2)} MB/s`;
                }
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        bar.style.width = pct + '%';
                        const elapsedSec = Math.max((Date.now() - startedAt) / 1000, 0.001);
                        const speed = formatSpeed(e.loaded / elapsedSec);
                        meta.textContent = `${(file.size / (1024*1024)).toFixed(2)} MB • Uploading ${pct}%${speed ? ` • ${speed}` : ''}`;
                        if (aggregate) {
                            aggregate.currentFileBytes = e.loaded;
                            const overallPct = Math.round((aggregate.completedBytes + aggregate.currentFileBytes) / aggregate.totalBytes * 100);
                            uploadProgress.style.width = overallPct + '%';
                            if (uploadProgressText) {
                                uploadProgressText.textContent = `Uploading ${aggregate.currentIndex + 1} of ${aggregate.totalFiles} • ${overallPct}%`;
                            }
                        }
                    } else {
                        meta.textContent = `${(file.size / (1024*1024)).toFixed(2)} MB • Uploading...`;
                    }
                };
                xhr.onload = async () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        bar.style.width = '100%';
                        meta.textContent = `${(file.size / (1024*1024)).toFixed(2)} MB • Processing...`;
                        if (aggregate) {
                            aggregate.completedBytes += file.size;
                            aggregate.currentFileBytes = 0;
                        }
                        resolve();
                    } else {
                        let detail = `HTTP ${xhr.status}`;
                        try { detail = JSON.parse(xhr.responseText).detail || detail; } catch (_) {}
                        reject(new Error(detail));
                    }
                };
                xhr.onerror = () => reject(new Error('Network error during upload'));
                xhr.send(formData);
            });
        } catch (error) {
            console.error('Error uploading file:', error);
            row.classList.add('error');
            errBox.style.display = 'block';
            errBox.textContent = String(error.message || error);
            meta.textContent = `${(file.size / (1024*1024)).toFixed(2)} MB • Failed`;
        }
    }

    async function transferOwnerByLink(linkId, ownerEmail, inputEl) {
        try {
            const response = await fetch(`${API_BASE_URL}/storage/admin/transfer_owner_by_link`, {
                method: 'POST',
                headers: { 'X-API-KEY': API_KEY, 'Content-Type': 'application/json' },
                body: JSON.stringify({ link_id: linkId, owner_email: ownerEmail })
            });
            const text = await response.text();
            if (!response.ok) throw new Error(text || `HTTP ${response.status}`);
            inputEl.style.backgroundColor = 'var(--success-color)';
            setTimeout(() => { inputEl.style.backgroundColor = ''; }, 1500);
        } catch (error) {
            console.error('Error transferring owner:', error);
            inputEl.style.backgroundColor = 'var(--error-color)';
        }
    }

    async function deleteFile(fileId) {
        try {
            const response = await fetch(`${API_BASE_URL}/storage/${fileId}`, { method: 'DELETE', headers: { 'X-API-KEY': API_KEY } });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            fetchFiles();
        } catch (error) {
            console.error('Error deleting file:', error);
            alert('Failed to delete file.');
        } finally {
            hideDeleteModal();
        }
    }

    // --- Event Listeners ---
    const debouncedFetch = debounce(() => fetchFiles(), 300);
    if (searchNameInput) {
        searchNameInput.addEventListener('input', debouncedFetch);
        searchNameInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); fetchFiles(); }
        });
    }
    if (searchCollectionInput) {
        searchCollectionInput.addEventListener('input', debouncedFetch);
        searchCollectionInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); fetchFiles(); }
        });
    }
    fileListContainer.addEventListener('focusout', (e) => {
        if (e.target.classList.contains('metadata-input')) {
            const fileId = e.target.dataset.id;
            const field = e.target.dataset.field;
            const value = e.target.value.trim();
            updateMetadata(fileId, { [field]: value }, e.target);
        }
        if (e.target.classList.contains('owner-input')) {
            const newEmail = e.target.value.trim();
            const linkId = e.target.dataset.linkId;
            if (!newEmail || !linkId) { return; }
            transferOwnerByLink(linkId, newEmail, e.target);
        }
    });
    // Prevent page refresh on Enter key inside editable inputs
    fileListContainer.addEventListener('keydown', (e) => {
        if ((e.target.classList.contains('metadata-input') || e.target.classList.contains('owner-input')) && e.key === 'Enter') {
            e.preventDefault();
            e.target.blur();
        }
    });
    fileListContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete-btn')) {
            showDeleteModal(e.target.dataset.id);
        }
        if (e.target.classList.contains('edit-text-btn')) {
            const id = e.target.dataset.id;
            const name = e.target.dataset.name || `file_${id}`;
            const url = e.target.dataset.url;
            showEditor(id, name, url);
        }
    });
    editorCancel.addEventListener('click', hideEditor);
    editorSave.addEventListener('click', async () => {
        if (!editTargetId) return;
        try {
            // Preserve filename and try to infer MIME from it
            const title = editorTitle.textContent || '';
            const matchName = title.replace(/^Edit\s+/, '').trim();
            const ext = (matchName.split('.').pop() || '').toLowerCase();
            const mimeByExt = {
                'txt': 'text/plain', 'log': 'text/plain',
                'md': 'text/markdown', 'markdown': 'text/markdown', 'mdx': 'text/markdown', 'mdown': 'text/markdown', 'mkdn': 'text/markdown', 'mkd': 'text/markdown',
                'json': 'application/json', 'xml': 'application/xml', 'csv': 'text/csv'
            };
            const mime = mimeByExt[ext] || 'text/plain';
            const blob = new Blob([editorText.value], { type: mime });
            const file = new File([blob], matchName || 'updated.txt', { type: mime });
            const form = new FormData();
            form.append('file', file);
            const res = await fetch(`${API_BASE_URL}/storage/files/${editTargetId}`, {
                method: 'PUT',
                headers: { 'X-API-KEY': API_KEY },
                body: form
            });
            if (!res.ok) {
                const t = await res.text();
                throw new Error(t || `HTTP ${res.status}`);
            }
            hideEditor();
            fetchFiles();
        } catch (err) {
            alert(`Save failed: ${err.message}`);
        }
    });
    newMdBtn?.addEventListener('click', () => {
        editTargetId = null; // compose mode
        editorTitle.textContent = 'New Text';
        editorText.value = '';
        editorModal.style.display = 'flex';
        const desired = prompt('Enter filename (e.g., notes.md or data.json):', 'notes.md');
        if(desired){ editorTitle.textContent = `New ${desired}`; editorText.dataset.filename = desired; }
    });
    editorSave.addEventListener('click', async () => {
        if (editTargetId) return; // handled above for updates
        try{
            const ownerEmail = ownerEmailInput.value.trim();
            const collectionId = document.getElementById('collection-id-input').value.trim();
            let name = (editorText.dataset.filename || '').trim();
            if(!name){ name = `text-${new Date().toISOString().replace(/[:.]/g,'-')}.md`; }
            const ext = name.split('.').pop().toLowerCase();
            const mimeByExt = { 'md':'text/markdown','markdown':'text/markdown','txt':'text/plain','json':'application/json','csv':'text/csv','xml':'application/xml' };
            const mime = mimeByExt[ext] || 'text/plain';
            const blob = new Blob([editorText.value], { type:mime });
            const file = new File([blob], name, { type:mime });
            const form = new FormData();
            form.append('file', file);
            form.append('context', 'presentation');
            if (ownerEmail) form.append('owner_email', ownerEmail);
            if (collectionId) form.append('collection_id', collectionId);
            form.append('skip_ai_safety', 'true');
            const up = await fetch(`${API_BASE_URL}/storage/upload`, { method:'POST', headers:{ 'X-API-KEY': API_KEY }, body: form });
            if(!up.ok){ throw new Error(await up.text() || 'upload failed'); }
            hideEditor();
            fetchFiles();
        }catch(err){ alert('Create failed: ' + err.message); }
    }, { once:false });
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('dragover'); });
    dropZone.addEventListener('drop', async (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length) {
            uploadProgress.style.width = '0%';
            if (uploadProgressText) uploadProgressText.textContent = `Preparing ${files.length} file(s)...`;
            const totalBytes = Array.from(files).reduce((sum, f) => sum + f.size, 0);
            const aggregate = { totalBytes, completedBytes: 0, currentFileBytes: 0, totalFiles: files.length, currentIndex: 0 };
            
            // Auto-generate link_id for multiple files if not specified
            let sharedLinkId = null;
            const linkIdInput = document.getElementById('link-id-input');
            
            if (files.length > 1 && !linkIdInput.value.trim()) {
                sharedLinkId = 'batch_' + Date.now() + '_' + Math.random().toString(36).substr(2, 8);
                linkIdInput.value = sharedLinkId;
                console.log(`Auto-generated link_id for ${files.length} files: ${sharedLinkId}`);
            }
            
            for (let i = 0; i < files.length; i++) {
                aggregate.currentIndex = i;
                if (uploadProgressText) uploadProgressText.textContent = `Uploading ${i + 1} of ${files.length}...`;
                await uploadFile(files[i], sharedLinkId, i, files.length, aggregate);
            }
            if (uploadProgressText) uploadProgressText.textContent = `Finalizing...`;
            setTimeout(() => {
                uploadProgress.style.width = '0%';
                if (uploadProgressText) uploadProgressText.textContent = '';
                fetchFiles();
            }, 1000);
        }
    });

    // Storage Mode Selection Handler
    const storageModeSelect = document.getElementById('storage-mode-select');
    const referencePathGroup = document.getElementById('reference-path-group');
    const externalUriGroup = document.getElementById('external-uri-group');

    if (storageModeSelect) {
        storageModeSelect.addEventListener('change', () => {
            const mode = storageModeSelect.value;
            referencePathGroup.style.display = mode === 'reference' ? 'flex' : 'none';
            externalUriGroup.style.display = mode === 'external' ? 'flex' : 'none';
        });
    }

    // Initial load
    fetchFiles();
    
    // Auto-refresh every 5 seconds to show real-time status updates
    // Disabled polling (was 5s interval). Manual refresh only via initial load or actions.
    // setInterval(() => {
    //     fetchFiles();
    // }, 5000);
</script>

</body>
</html>
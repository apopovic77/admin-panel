// storage2.js - Extracted from storage2.php

// --- Configuration (set by PHP) ---
// These are set in the HTML before this script loads:
// const API_BASE_URL = '...';
// const SHARE_BASE_URL = '...';

const DEFAULT_TENANT_FALLBACK = (window.location.hostname || '').includes('arkserver')
    ? 'arkserver'
    : 'arkturian';

let TENANTS = {};
let currentTenant = localStorage.getItem('selectedTenant') || DEFAULT_TENANT_FALLBACK;
let API_KEY = 'Inetpass1';

// --- DOM Elements ---
const tenantSelect = document.getElementById('tenant-select');
const dropZone = document.getElementById('drop-zone');
const fileListContainer = document.getElementById('file-list');
const uploadProgress = document.getElementById('upload-progress');
const uploadStatusList = document.getElementById('upload-status-list');
const uploadProgressText = document.getElementById('upload-progress-text');
const ownerEmailInput = document.getElementById('owner-email-input');
const searchNameInput = document.getElementById('search-name-input');
const searchCollectionInput = document.getElementById('search-collection-input');
const searchIdInput = document.getElementById('search-id-input');
const bulkActionsContainer = document.getElementById('bulk-actions-container');
const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

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

// --- Tenant Management ---
async function loadTenants() {
    try {
        const res = await fetch(`${API_BASE_URL}/tenants/keys`, {
            headers: { 'X-API-KEY': 'Inetpass1' }
        });
        if (!res.ok) throw new Error(await res.text());
        const data = await res.json();
        TENANTS = {};
        Object.entries(data).forEach(([key, tenantId]) => {
            if (!tenantId) return;
            if (!TENANTS[tenantId]) {
                TENANTS[tenantId] = [];
            }
            TENANTS[tenantId].push(key);
        });
    } catch (error) {
        console.error('Failed to load tenant keys:', error);
        TENANTS = {
            [DEFAULT_TENANT_FALLBACK]: ['Inetpass1']
        };
    }

    const tenantIds = Object.keys(TENANTS);
    if (!tenantIds.includes(currentTenant)) {
        currentTenant = tenantIds[0] || DEFAULT_TENANT_FALLBACK;
        localStorage.setItem('selectedTenant', currentTenant);
    }
    API_KEY = (TENANTS[currentTenant] && TENANTS[currentTenant][0]) || 'Inetpass1';

    if (tenantSelect) {
        tenantSelect.innerHTML = tenantIds
            .map(id => `<option value="${id}">${id}</option>`)
            .join('');
        tenantSelect.value = currentTenant;
    }
}

// --- Modal Functions ---
function showDeleteModal(fileId) {
    fileToDeleteId = fileId;
    deleteModal.style.display = 'flex';
}

function hideDeleteModal() {
    fileToDeleteId = null;
    deleteModal.style.display = 'none';
}

async function deleteFile(fileId) {
    try {
        const response = await fetch(`${API_BASE_URL}/storage/${fileId}`, {
            method: 'DELETE',
            headers: { 'X-API-KEY': API_KEY }
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.detail || `HTTP error! status: ${response.status}`);
        }

        hideDeleteModal();
        await fetchFiles();

    } catch (error) {
        console.error('Error deleting file:', error);
        alert(`Failed to delete file: ${error.message}`);
        hideDeleteModal();
    }
}

function showErrorModal(base64Error) {
    try {
        errorLogContent.textContent = atob(base64Error);
    } catch (e) {
        errorLogContent.textContent = "Error decoding log.";
    }
    errorModal.style.display = 'flex';
}

function hideErrorModal() {
    errorModal.style.display = 'none';
}

function showEditor(id, name, url) {
    editTargetId = id;
    editorTitle.textContent = `Edit ${name}`;
    editorText.value = '';
    editorModal.style.display = 'flex';
    const fetchUrl = url.includes('?') ? `${url}&_=${Date.now()}` : `${url}?_=${Date.now()}`;
    fetch(fetchUrl)
        .then(r => r.text())
        .then(t => { editorText.value = t; })
        .catch(() => { editorText.value = ''; });
}

function hideEditor() {
    editTargetId = null;
    editorModal.style.display = 'none';
}

// --- Utility Functions ---
function debounce(fn, delay = 300) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(null, args), delay);
    };
}

// --- AI Status Polling ---
async function pollAIStatus(objectId, metaElement, rowElement) {
    let pollCount = 0;
    const maxPolls = 300; // 10 minutes at 2s intervals

    const poll = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}/storage/objects/${objectId}/processing-status`, {
                headers: { 'X-API-KEY': API_KEY }
            });
            const status = await response.json();

            let statusText = '';
            if (status.status === 'queued') {
                statusText = `⏳ Queued${status.stage ? ` (${status.stage})` : ''}`;
            } else if (status.status === 'processing') {
                if (status.progress) {
                    statusText = `🔄 Processing chunk ${status.progress}`;
                } else {
                    statusText = `🔄 Processing${status.stage ? ` (${status.stage})` : ''}`;
                }
                if (status.elapsed_seconds) {
                    statusText += ` • ${status.elapsed_seconds}s`;
                }
            } else if (status.status === 'completed') {
                const embeddings = status.details?.embeddings_created || 0;
                const mode = status.details?.mode || '';
                statusText = `✅ Complete • ${embeddings} embeddings${mode === 'chunked' ? ' (chunked)' : ''}`;
                if (metaElement) metaElement.textContent = statusText;
                if (rowElement) rowElement.classList.add('success');
                loadFiles();
                return;
            } else if (status.status === 'failed') {
                statusText = `❌ Failed: ${status.error?.substring(0, 100) || 'Unknown error'}`;
                if (metaElement) metaElement.textContent = statusText;
                if (rowElement) rowElement.classList.add('error');
                return;
            }

            if (metaElement) metaElement.textContent = statusText;

            pollCount++;
            if (pollCount < maxPolls && status.status !== 'completed' && status.status !== 'failed') {
                setTimeout(poll, 2000);
            }
        } catch (error) {
            console.error('Status poll error:', error);
            pollCount++;
            if (pollCount < maxPolls) {
                setTimeout(poll, 2000);
            }
        }
    };

    setTimeout(poll, 2000);
}

// --- File Upload ---
async function uploadFile(file, linkId = null, index = null, total = null, aggregate = null) {
    const formData = new FormData();
    formData.append('file', file);

    const ownerEmail = ownerEmailInput?.value?.trim();
    if (ownerEmail) formData.append('owner_email', ownerEmail);

    const collectionInput = document.getElementById('collection-id-input');
    const collectionId = collectionInput ? collectionInput.value.trim() : '';
    if (collectionId) formData.append('collection_id', collectionId);

    const linkIdInput = document.getElementById('link-id-input');
    const linkIdValue = linkId || (linkIdInput ? linkIdInput.value.trim() : '');
    if (linkIdValue) formData.append('link_id', linkIdValue);

    const skipSafetyInput = document.getElementById('skip-ai-safety-input');
    const skipSafety = skipSafetyInput && skipSafetyInput.checked;
    if (skipSafety) formData.append('skip_ai_safety', 'true');

    // Advanced options
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

    const aiModeSelect = document.getElementById('ai-mode-select');
    const aiMode = aiModeSelect ? aiModeSelect.value : 'full';
    formData.append('ai_mode', aiMode);

    const aiContextText = document.getElementById('ai-context-text-input')?.value?.trim();
    if (aiContextText) formData.append('ai_context_text', aiContextText);

    const aiFilePath = document.getElementById('ai-file-path-input')?.value?.trim();
    if (aiFilePath) formData.append('ai_file_path', aiFilePath);

    const aiMetadata = document.getElementById('ai-metadata-input')?.value?.trim();
    if (aiMetadata) formData.append('ai_metadata', aiMetadata);

    // UI row per file
    const itemId = `up_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
    const row = document.createElement('div');
    row.className = 'upload-item';
    row.id = itemId;
    const ordinal = (index !== null && total !== null) ? ` (${index + 1}/${total})` : '';
    row.innerHTML = `
        <div class="name">${file.name}${ordinal}</div>
        <div class="meta">${(file.size / (1024 * 1024)).toFixed(2)} MB • Starting...</div>
        <div class="bar"><div></div></div>
        <div class="error-text" style="display:none;"></div>
    `;
    uploadStatusList?.prepend(row);
    const bar = row.querySelector('.bar > div');
    const meta = row.querySelector('.meta');
    const errBox = row.querySelector('.error-text');

    try {
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
                    if (bar) bar.style.width = pct + '%';
                    const elapsedSec = Math.max((Date.now() - startedAt) / 1000, 0.001);
                    const speed = formatSpeed(e.loaded / elapsedSec);
                    if (meta) meta.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB • Uploading ${pct}%${speed ? ` • ${speed}` : ''}`;
                    if (aggregate) {
                        aggregate.currentFileBytes = e.loaded;
                        const overallPct = Math.round((aggregate.completedBytes + aggregate.currentFileBytes) / aggregate.totalBytes * 100);
                        if (uploadProgress) uploadProgress.style.width = overallPct + '%';
                        if (uploadProgressText) uploadProgressText.textContent = `Uploading ${aggregate.currentIndex + 1} of ${aggregate.totalFiles} • ${overallPct}%`;
                    }
                } else {
                    if (meta) meta.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB • Uploading...`;
                }
            };

            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    if (bar) bar.style.width = '100%';
                    if (meta) meta.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB • Processing...`;
                    if (aggregate) {
                        aggregate.completedBytes += file.size;
                        aggregate.currentFileBytes = 0;
                    }

                    try {
                        const response = JSON.parse(xhr.responseText);
                        const objectId = response.id;
                        if (objectId && runAnalyze) {
                            pollAIStatus(objectId, meta, row);
                        }
                    } catch (e) {
                        console.error('Failed to parse upload response:', e);
                    }

                    resolve();
                } else {
                    let detail = `HTTP ${xhr.status}`;
                    try {
                        detail = JSON.parse(xhr.responseText).detail || detail;
                    } catch (_) { }
                    reject(new Error(detail));
                }
            };

            xhr.onerror = () => reject(new Error('Network error during upload'));
            xhr.send(formData);
        });
    } catch (error) {
        console.error('Error uploading file:', error);
        row.classList.add('error');
        if (errBox) {
            errBox.style.display = 'block';
            errBox.textContent = String(error.message || error);
        }
        if (meta) meta.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB • Failed`;
    }
}

// --- File Card Rendering ---
function renderFileCard(cardElement, file) {
    const isVideo = file.mime_type && file.mime_type.startsWith('video/');
    const fileSizeMB = (file.file_size_bytes / (1024 * 1024)).toFixed(2);
    const isTextLike = (file.mime_type && (file.mime_type.startsWith('text/') || file.mime_type.includes('json') || file.mime_type.includes('markdown'))) ||
        ['txt', 'log', 'md', 'markdown', 'mdx', 'mdown', 'mkdn', 'mkd', 'json', 'xml', 'csv'].includes((file.original_filename || '').split('.').pop().toLowerCase());

    let tabsHtml = '';
    const metadataTab = `<button class="tab-btn" data-tab="metadata">Metadata</button>`;
    const techTab = `<button class="tab-btn" data-tab="tech">Technical Details</button>`;
    const aiTab = `<button class="tab-btn" data-tab="ai">AI Analysis</button>`;
    const kgTab = `<button class="tab-btn" data-tab="kg">Knowledge Graph</button>`;
    const actionsTab = `<button class="tab-btn" data-tab="actions">Actions</button>`;
    const linkedTab = file.link_id ? `<button class="tab-btn" data-tab="linked">Linked Files</button>` : '';

    // Build link chain info for semicolon-separated IDs
    let linkChainHtml = '';
    if (file.link_id && file.link_id.includes(';')) {
        const linkIds = file.link_id.split(';').map(id => id.trim()).filter(id => id);
        linkChainHtml = `
            <div class="link-chain-info">
                <div class="chain-label">Link Chain (${linkIds.length} references)</div>
                <div class="chain-ids">
                    ${linkIds.map(id => `<span class="chain-id">${id}</span>`).join('')}
                </div>
            </div>
        `;
    }

    const metadataPanel = `<div class="tab-panel" data-tab-panel="metadata"><div class="info-group"><div class="id-display">ID: ${file.id}</div><div class="label">Title</div><input type="text" class="metadata-input" data-field="title" value="${file.title || ''}"></div><div class="info-group"><div class="label">Collection</div><input type="text" class="metadata-input" data-field="collection_id" value="${file.collection_id || ''}"></div><div class="info-group"><div class="label">Owner</div><input type="email" class="owner-input" data-link-id="${file.link_id || ''}" value="${file.owner_email || ''}"></div><div class="info-group"><div class="label">Link ID</div><input type="text" class="metadata-input" data-field="link_id" value="${file.link_id || ''}"></div><div class="info-group" style="grid-column: 1 / -1;"><div class="label">Description</div><textarea class="metadata-input" data-field="description">${file.description || ''}</textarea></div></div>`;

    const lat = file.geo_position?.latitude ?? file.latitude;
    const lon = file.geo_position?.longitude ?? file.longitude;
    const geoPanelHtml = (lat && lon) ? `
        <div class="info-group">
            <div class="label">Geo Position</div>
            <a href="https://www.google.com/maps?q=${lat},${lon}" target="_blank" class="value">
                ${lat}, ${lon}
            </a>
        </div>` : '';

    const techPanel = `
        <div class="tab-panel" data-tab-panel="tech">
            <div class="info-group">
                <div class="label">MIME Type</div>
                <div class="value">${file.mime_type || 'N/A'}</div>
            </div>
            <div class="info-group">
                <div class="label">File Size</div>
                <div class="value">${(file.file_size_bytes / (1024 * 1024)).toFixed(2)} MB</div>
            </div>
            ${file.width && file.height ? `
            <div class="info-group">
                <div class="label">Dimensions</div>
                <div class="value">${file.width}x${file.height}</div>
            </div>` : ''}
            ${file.duration_seconds ? `
            <div class="info-group">
                <div class="label">Duration</div>
                <div class="value">${file.duration_seconds.toFixed(2)}s</div>
            </div>` : ''}
            <div class="info-group">
                <div class="label">Created At</div>
                <div class="value">${file.created_at ? new Date(file.created_at).toLocaleString() : 'N/A'}</div>
            </div>
            <div class="info-group">
                <div class="label">Updated At</div>
                <div class="value">${file.updated_at ? new Date(file.updated_at).toLocaleString() : 'N/A'}</div>
            </div>
            <div class="info-group">
                <div class="label">Storage Provider</div>
                <div class="value">${file.storage_provider || 'N/A'}</div>
            </div>
            <div class="info-group" style="grid-column: 1 / -1;">
                <div class="label">Storage Path</div>
                <div class="value">${file.storage_path || 'N/A'}</div>
            </div>
            <div class="info-group">
                <div class="label">Transcoding Status</div>
                <div class="value">${file.transcoding_status || 'N/A'}</div>
            </div>
            ${geoPanelHtml}
        </div>
    `;

    // AI Panel
    const aiTagsFormatted = file.ai_tags ? `<pre style="background: #f1f5f9; padding: 8px; border-radius: 4px; font-size: 11px; overflow-x: auto;">${JSON.stringify(file.ai_tags, null, 2)}</pre>` : 'N/A';
    const aiContextMetadata = file.ai_context_metadata ? `<pre style="background: #f1f5f9; padding: 8px; border-radius: 4px; font-size: 11px; overflow-x: auto;">${JSON.stringify(file.ai_context_metadata, null, 2)}</pre>` : 'N/A';

    // Vision Analysis
    let visionAnalysisSection = '';
    let extractedTagsSource = file.extracted_tags;

    if (!extractedTagsSource && file.ai_context_metadata) {
        const metadata = typeof file.ai_context_metadata === 'string'
            ? JSON.parse(file.ai_context_metadata)
            : file.ai_context_metadata;
        extractedTagsSource = metadata.extracted_tags;
    }

    if (extractedTagsSource) {
        try {
            const extractedTags = typeof extractedTagsSource === 'string'
                ? JSON.parse(extractedTagsSource)
                : extractedTagsSource;

            const hasVisionData = extractedTags.colors || extractedTags.materials || extractedTags.visual_harmony_tags;

            if (hasVisionData) {
                visionAnalysisSection = `
                    <div style="margin-top: 20px; padding: 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: white;">
                        <h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                            Vision Intelligence Analysis
                        </h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            ${extractedTags.colors && extractedTags.colors.length > 0 ? `
                                <div style="background: rgba(255,255,255,0.15); padding: 10px; border-radius: 6px;">
                                    <div style="font-weight: 600; font-size: 11px; margin-bottom: 6px; opacity: 0.9;">Colors</div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                        ${extractedTags.colors.map(c => `<span style="background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 12px; font-size: 10px;">${c}</span>`).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            ${extractedTags.materials && extractedTags.materials.length > 0 ? `
                                <div style="background: rgba(255,255,255,0.15); padding: 10px; border-radius: 6px;">
                                    <div style="font-weight: 600; font-size: 11px; margin-bottom: 6px; opacity: 0.9;">Materials</div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                        ${extractedTags.materials.map(m => `<span style="background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 12px; font-size: 10px;">${m}</span>`).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            ${extractedTags.visual_harmony_tags && extractedTags.visual_harmony_tags.length > 0 ? `
                                <div style="background: rgba(255,255,255,0.15); padding: 10px; border-radius: 6px;">
                                    <div style="font-weight: 600; font-size: 11px; margin-bottom: 6px; opacity: 0.9;">Visual Harmony</div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                        ${extractedTags.visual_harmony_tags.map(t => `<span style="background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 12px; font-size: 10px;">${t}</span>`).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            ${extractedTags.keywords && extractedTags.keywords.length > 0 ? `
                                <div style="background: rgba(255,255,255,0.15); padding: 10px; border-radius: 6px; grid-column: 1 / -1;">
                                    <div style="font-weight: 600; font-size: 11px; margin-bottom: 6px; opacity: 0.9;">Semantic Keywords</div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                        ${extractedTags.keywords.slice(0, 15).map(k => `<span style="background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 12px; font-size: 10px;">${k}</span>`).join('')}
                                        ${extractedTags.keywords.length > 15 ? `<span style="opacity: 0.7; font-size: 10px; padding: 2px 8px;">+${extractedTags.keywords.length - 15} more</span>` : ''}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            }
        } catch (e) {
            console.error('Error parsing extracted_tags for vision analysis:', e);
        }
    }

    // Detailed Vision Section
    let detailedVisionSection = '';
    if (file.ai_context_metadata) {
        try {
            const metadata = typeof file.ai_context_metadata === 'string'
                ? JSON.parse(file.ai_context_metadata)
                : file.ai_context_metadata;

            const embeddingMetadata = metadata.embedding_info?.metadata || metadata;
            const productAnalysis = embeddingMetadata.product_analysis;
            const visualAnalysis = embeddingMetadata.visual_analysis;
            const layoutIntel = embeddingMetadata.layout_intelligence;

            if (productAnalysis || visualAnalysis || layoutIntel) {
                detailedVisionSection = `
                    <details style="margin-top: 16px; padding: 12px; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px;">
                        <summary style="cursor: pointer; font-weight: 600; color: #0369a1; user-select: none;">
                            Detailed Vision Analysis (Click to expand)
                        </summary>
                        <div style="margin-top: 12px; display: grid; gap: 12px;">
                            ${productAnalysis ? `
                                <div style="background: white; padding: 10px; border-radius: 4px; border-left: 3px solid #8b5cf6;">
                                    <div style="font-weight: 600; color: #6b21a8; margin-bottom: 8px;">Product Analysis</div>
                                    <pre style="background: #faf5ff; padding: 8px; border-radius: 4px; font-size: 10px; overflow-x: auto; margin: 0;">${JSON.stringify(productAnalysis, null, 2)}</pre>
                                </div>
                            ` : ''}
                            ${visualAnalysis ? `
                                <div style="background: white; padding: 10px; border-radius: 4px; border-left: 3px solid #ec4899;">
                                    <div style="font-weight: 600; color: #9f1239; margin-bottom: 8px;">Visual Analysis</div>
                                    <pre style="background: #fdf2f8; padding: 8px; border-radius: 4px; font-size: 10px; overflow-x: auto; margin: 0;">${JSON.stringify(visualAnalysis, null, 2)}</pre>
                                </div>
                            ` : ''}
                            ${layoutIntel ? `
                                <div style="background: white; padding: 10px; border-radius: 4px; border-left: 3px solid #10b981;">
                                    <div style="font-weight: 600; color: #065f46; margin-bottom: 8px;">Layout Intelligence</div>
                                    <pre style="background: #f0fdf4; padding: 8px; border-radius: 4px; font-size: 10px; overflow-x: auto; margin: 0;">${JSON.stringify(layoutIntel, null, 2)}</pre>
                                </div>
                            ` : ''}
                        </div>
                    </details>
                `;
            }
        } catch (e) {
            console.error('Error parsing vision analysis details:', e);
        }
    }

    // AI Debug Section
    let aiDebugSection = '';
    if (file.ai_context_metadata) {
        try {
            const metadata = typeof file.ai_context_metadata === 'string'
                ? JSON.parse(file.ai_context_metadata)
                : file.ai_context_metadata;

            const aiPrompt = metadata.prompt || metadata.ai_prompt || 'Not captured';
            const aiResponse = metadata.response || metadata.ai_response || metadata.raw_response || 'Not captured';

            aiDebugSection = `
                <details style="margin-top: 16px; padding: 12px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 6px;">
                    <summary style="cursor: pointer; font-weight: 600; color: #92400e; user-select: none;">
                        Debug: AI Prompt & Response (Click to expand)
                    </summary>
                    <div style="margin-top: 12px;">
                        <div style="margin-bottom: 12px;">
                            <div style="font-weight: 600; color: #92400e; margin-bottom: 4px;">Prompt Sent to AI:</div>
                            <pre style="background: white; padding: 8px; border-radius: 4px; font-size: 10px; overflow-x: auto; max-height: 300px; border: 1px solid #fbbf24;">${typeof aiPrompt === 'string' ? aiPrompt : JSON.stringify(aiPrompt, null, 2)}</pre>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: #92400e; margin-bottom: 4px;">Response from AI:</div>
                            <pre style="background: white; padding: 8px; border-radius: 4px; font-size: 10px; overflow-x: auto; max-height: 300px; border: 1px solid #fbbf24;">${typeof aiResponse === 'string' ? aiResponse : JSON.stringify(aiResponse, null, 2)}</pre>
                        </div>
                    </div>
                </details>
            `;
        } catch (e) {
            aiDebugSection = `
                <div style="margin-top: 16px; padding: 8px; background: #fee2e2; border: 1px solid #dc2626; border-radius: 6px; font-size: 11px; color: #991b1b;">
                    Error parsing AI debug info: ${e.message}
                </div>
            `;
        }
    } else {
        aiDebugSection = `
            <div style="margin-top: 16px; padding: 8px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 6px; font-size: 11px; color: #6b7280;">
                No AI analysis data available. Upload with <code>analyze=true</code> to enable AI analysis.
            </div>
        `;
    }

    const aiPanel = `
        <div class="tab-panel" data-tab-panel="ai">
            <div class="info-group">
                <div class="label">AI Category</div>
                <div class="value">${file.ai_category || 'N/A'}</div>
            </div>
            <div class="info-group">
                <div class="label">Danger Potential</div>
                <div class="value">${file.ai_danger_potential || 'N/A'}</div>
            </div>
            <div class="info-group">
                <div class="label">AI Safety Rating</div>
                <div class="value">${file.ai_safety_rating || 'N/A'}</div>
            </div>
            <div class="info-group">
                <div class="label">AI Safety Status</div>
                <div class="value">${file.ai_safety_status || 'N/A'}</div>
            </div>
            <div class="info-group" style="grid-column: 1 / -1;">
                <div class="label">AI Title</div>
                <div class="value">${file.ai_title || 'N/A'}</div>
            </div>
            <div class="info-group" style="grid-column: 1 / -1;">
                <div class="label">AI Subtitle</div>
                <div class="value">${file.ai_subtitle || 'N/A'}</div>
            </div>
            <div style="grid-column: 1 / -1;">
                ${visionAnalysisSection}
            </div>
            <div style="grid-column: 1 / -1;">
                ${detailedVisionSection}
            </div>
            <div class="info-group" style="grid-column: 1 / -1;">
                <div class="label">Extracted Tags (AI)</div>
                ${aiTagsFormatted}
            </div>
            <div class="info-group" style="grid-column: 1 / -1;">
                <div class="label">AI Context Metadata</div>
                ${aiContextMetadata}
            </div>
            <div style="grid-column: 1 / -1;">
                ${aiDebugSection}
            </div>
        </div>
    `;

    // Knowledge Graph Panel
    const kgPanel = `
        <div class="tab-panel" data-tab-panel="kg">
            <div class="info-group" style="grid-column: 1 / -1;">
                <div class="label">Embedding Status</div>
                <div class="value" style="display: flex; align-items: center; gap: 8px;">
                    <span id="kg-status-${file.id}">Checking...</span>
                    <button class="refresh-kg-btn" data-id="${file.id}" style="padding: 4px 8px; border: 1px solid var(--ring); border-radius: 4px; background: white; cursor: pointer; font-size: 11px;">Refresh</button>
                    <button class="create-kg-btn" data-id="${file.id}" style="padding: 4px 8px; border: 1px solid var(--ring); border-radius: 4px; background: #e0e7ff; color: #4338ca; cursor: pointer; font-size: 11px;">Create Embedding</button>
                </div>
            </div>

            <details style="grid-column: 1 / -1; margin-top: 12px; padding: 12px; background: #eff6ff; border: 1px solid #3b82f6; border-radius: 6px;">
                <summary style="cursor: pointer; font-weight: 600; color: #1e40af; user-select: none;">
                    Embeddings Details (Click to expand)
                </summary>
                <div id="kg-embeddings-${file.id}" style="margin-top: 12px;">
                    <button class="load-embeddings-btn" data-id="${file.id}" style="padding: 6px 12px; border: 1px solid #3b82f6; border-radius: 4px; background: white; color: #1e40af; cursor: pointer; font-size: 11px;">Load Embeddings</button>
                </div>
            </details>

            <details style="grid-column: 1 / -1; margin-top: 12px; padding: 12px; background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px;">
                <summary style="cursor: pointer; font-weight: 600; color: #15803d; user-select: none;">
                    External Objects Created (Click to expand)
                </summary>
                <div id="kg-external-${file.id}" style="margin-top: 12px;">
                    <button class="load-external-btn" data-id="${file.id}" style="padding: 6px 12px; border: 1px solid #22c55e; border-radius: 4px; background: white; color: #15803d; cursor: pointer; font-size: 11px;">Load External Objects</button>
                </div>
            </details>

            <details style="grid-column: 1 / -1; margin-top: 12px; padding: 12px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px;">
                <summary style="cursor: pointer; font-weight: 600; color: #92400e; user-select: none;">
                    Async Tasks History (Click to expand)
                </summary>
                <div id="kg-tasks-${file.id}" style="margin-top: 12px;">
                    <button class="load-tasks-btn" data-id="${file.id}" style="padding: 6px 12px; border: 1px solid #f59e0b; border-radius: 4px; background: white; color: #92400e; cursor: pointer; font-size: 11px;">Load Async Tasks</button>
                </div>
            </details>

            <div class="info-group" style="grid-column: 1 / -1; margin-top: 16px;">
                <div class="label">Similar Objects (Semantic Search)</div>
                <div id="kg-similar-${file.id}" style="margin-top: 8px;">
                    <button class="find-similar-btn" data-id="${file.id}" style="padding: 8px 12px; border: 1px solid var(--brand-2); border-radius: 6px; background: var(--brand-2); color: white; cursor: pointer; font-size: 12px;">Find Similar Objects</button>
                </div>
            </div>
        </div>
    `;

    const actionsPanel = `<div class="tab-panel" data-tab-panel="actions"><button class="delete-btn" data-id="${file.id}">Delete File</button></div>`;
    const linkedPanel = file.link_id ? `<div class="tab-panel" data-tab-panel="linked">${linkChainHtml}<div class="linked-items-container">Loading linked files...</div></div>` : '';

    if (isTextLike) {
        tabsHtml = `<nav class="details-nav"><button class="tab-btn active" data-tab="edit">Edit</button>${metadataTab}${techTab}${aiTab}${kgTab}${linkedTab}${actionsTab}</nav><div class="tab-panel active" data-tab-panel="edit"><textarea class="edit-text-area" data-url="${file.file_url}" placeholder="Loading content..."></textarea><button class="save-text-btn" data-id="${file.id}" data-name="${file.original_filename}">Save Changes</button></div>${metadataPanel}${techPanel}${aiPanel}${kgPanel}${linkedPanel}${actionsPanel}`;
    } else {
        tabsHtml = `<nav class="details-nav"><button class="tab-btn active" data-tab="metadata">Metadata</button>${techTab}${aiTab}${kgTab}${linkedTab}${actionsTab}</nav>${metadataPanel.replace('class="tab-panel"', 'class="tab-panel active"')}${techPanel}${aiPanel}${kgPanel}${linkedPanel}${actionsPanel}`;
    }

    cardElement.innerHTML = `
        <div class="file-summary" data-link-id="${file.link_id || ''}">
            <div class="thumbnail">${file.thumbnail_url ? `<img src="${file.thumbnail_url}" alt="thumbnail">` : `<div class="file-icon">FILE</div>`}</div>
            <div class="info">
                <div class="filename">${file.original_filename}</div>
                <div class="file-title">${file.title || ''}</div>
                <div class="file-link-id">ID: ${file.id}${file.link_id ? ` / Link ID: ${file.link_id}` : ''}</div>
            </div>
            <div class="meta-pills">${file.collection_id ? `<div class="pill collection">${file.collection_id}</div>` : ''}${isVideo ? `<div class="pill video">Video</div>` : ''}<div class="pill">${fileSizeMB} MB</div>${file.width && file.height ? `<div class="pill">${file.width}x${file.height}</div>` : ''}</div>
            <div class="summary-actions">
                ${file.hls_url ? `<a href="${SHARE_BASE_URL}/vod.php?current_id=${file.id}${file.collection_id ? '&collection_id=' + encodeURIComponent(file.collection_id) : ''}" target="_blank">Play</a>` : ''}
                <a href="${file.file_url}" target="_blank">Download</a>
                <a href="${SHARE_BASE_URL}/proxy.php?id=${file.id}&width=1300" target="_blank">Web Preview</a>
                <button class="delete-btn-main" data-id="${file.id}" style="color: #dc3545; background: none; border: none; cursor: pointer; font-size: 14px;">Remove</button>
            </div>
        </div>
        <div class="file-details">${tabsHtml}</div>
    `;
}

// --- File Fetching ---
async function fetchFiles() {
    try {
        const nameQuery = (searchNameInput?.value || '').trim();
        const collectionQuery = (searchCollectionInput?.value || '').trim();
        const idQuery = (searchIdInput?.value || '').trim();
        const limit = 5000;
        const url = new URL(`${API_BASE_URL}/storage/list`);
        url.searchParams.set('mine', 'false');
        url.searchParams.set('_t', String(Date.now()));
        url.searchParams.set('limit', String(limit));
        if (nameQuery) url.searchParams.set('name', nameQuery);
        if (collectionQuery) url.searchParams.set('collection_like', collectionQuery);
        if (idQuery) url.searchParams.set('id', idQuery);

        console.log('DEBUG: API_KEY =', API_KEY);
        console.log('DEBUG: currentTenant =', currentTenant);
        console.log('DEBUG: Fetching URL:', url.toString());

        if (!API_KEY || API_KEY === 'undefined') {
            throw new Error('API_KEY is not set! Please select a tenant.');
        }

        const response = await fetch(url.toString(), { headers: { 'X-API-KEY': API_KEY } });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();

        fileListContainer.innerHTML = '';
        let items = data.items || [];

        // Bulk Delete UI
        if ((nameQuery || collectionQuery || idQuery) && items.length > 0) {
            bulkActionsContainer.style.display = 'block';
            bulkDeleteBtn.textContent = `Delete ${items.length} Filtered Item(s)`;
        } else {
            bulkActionsContainer.style.display = 'none';
        }

        if (idQuery) {
            const idQueryLower = idQuery.toLowerCase();
            items = items.filter(file => String(file.id || '').toLowerCase().includes(idQueryLower));
        }

        items.forEach(file => {
            const card = document.createElement('div');
            card.className = 'file-card';
            card.dataset.fileId = file.id;
            renderFileCard(card, file);
            fileListContainer.appendChild(card);
        });
    } catch (error) {
        console.error('Error fetching files:', error);
        fileListContainer.innerHTML = '<p>Error loading files.</p>';
    }
}

// --- Bulk Delete ---
async function bulkDelete() {
    const nameQuery = (searchNameInput?.value || '').trim();
    const collectionQuery = (searchCollectionInput?.value || '').trim();
    const idQuery = (searchIdInput?.value || '').trim();

    if (!confirm(`Are you sure you want to permanently delete all items matching the current filters?`)) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/storage/bulk-delete`, {
            method: 'POST',
            headers: { 'X-API-KEY': API_KEY, 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: nameQuery, collection_like: collectionQuery, id: idQuery })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.detail || `HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        alert(`${result.deleted_count} items have been deleted.`);
        fetchFiles();

    } catch (error) {
        console.error('Error during bulk delete:', error);
        alert(`Bulk delete failed: ${error.message}`);
    }
}

// --- Metadata Update ---
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

// --- Text File Operations ---
async function saveTextFile(fileId, originalFilename, content) {
    try {
        const ext = (originalFilename.split('.').pop() || '').toLowerCase();
        const mimeByExt = {
            'txt': 'text/plain', 'log': 'text/plain',
            'md': 'text/markdown', 'markdown': 'text/markdown',
            'json': 'application/json', 'xml': 'application/xml', 'csv': 'text/csv'
        };
        const mime = mimeByExt[ext] || 'text/plain';
        const blob = new Blob([content], { type: mime });
        const file = new File([blob], originalFilename, { type: mime });

        const formData = new FormData();
        formData.append('file', file);

        const response = await fetch(`${API_BASE_URL}/storage/files/${fileId}`, {
            method: 'PUT',
            headers: { 'X-API-KEY': API_KEY },
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(errorText || `HTTP error! status: ${response.status}`);
        }

        alert('File saved successfully!');
        fetchFiles();

    } catch (error) {
        console.error('Error saving file:', error);
        alert(`Failed to save file: ${error.message}`);
    }
}

async function loadTextContent(textArea) {
    textArea.dataset.contentLoaded = 'true';
    try {
        const url = textArea.dataset.url;
        const fetchUrl = url.includes('?') ? `${url}&_=${Date.now()}` : `${url}?_=${Date.now()}`;
        const response = await fetch(fetchUrl);
        if (!response.ok) throw new Error('Failed to fetch content');
        textArea.value = await response.text();
    } catch (error) {
        textArea.value = `Error loading content: ${error.message}`;
    }
}

// --- Linked Files (UPDATED for semicolon-separated link_ids) ---
async function loadLinkedFiles(linkId, container, parentFileId) {
    container.dataset.loaded = 'true';

    try {
        // Split semicolon-separated link_ids and deduplicate
        const linkIds = linkId.split(';')
            .map(id => id.trim())
            .filter(id => id);

        // Collect all linked items from all link_ids using a Map to deduplicate
        const allLinkedItems = new Map();

        // Fetch in parallel for all link_ids
        const fetchPromises = linkIds.map(async (singleLinkId) => {
            try {
                const url = new URL(`${API_BASE_URL}/storage/list`);
                url.searchParams.set('link_id', singleLinkId);
                url.searchParams.set('limit', 50);
                url.searchParams.set('mine', 'false');

                const response = await fetch(url.toString(), { headers: { 'X-API-KEY': API_KEY } });
                if (!response.ok) return [];

                const data = await response.json();
                return data.items || [];
            } catch (e) {
                console.error(`Error fetching linked files for ${singleLinkId}:`, e);
                return [];
            }
        });

        const results = await Promise.all(fetchPromises);

        // Merge all results, deduplicate by ID, exclude parent
        results.flat().forEach(item => {
            if (String(item.id) !== String(parentFileId)) {
                allLinkedItems.set(item.id, item);
            }
        });

        const linkedItems = Array.from(allLinkedItems.values());

        if (linkedItems.length === 0) {
            container.innerHTML = 'No other linked files found.';
            return;
        }

        // Show count info if multiple link_ids were searched
        let headerHtml = '';
        if (linkIds.length > 1) {
            headerHtml = `<div style="margin-bottom: 12px; padding: 8px; background: #e0e7ff; border-radius: 6px; font-size: 12px; color: #4338ca;">
                Found ${linkedItems.length} linked file(s) across ${linkIds.length} link references
            </div>`;
        }

        container.innerHTML = headerHtml;

        linkedItems.forEach(file => {
            const card = document.createElement('div');
            card.className = 'file-card';
            card.dataset.fileId = file.id;
            renderFileCard(card, file);
            container.appendChild(card);
        });

    } catch (error) {
        container.innerHTML = `Error loading linked files: ${error.message}`;
    }
}

// --- Knowledge Graph Functions ---
async function checkEmbeddingStatus(fileId, statusEl) {
    statusEl.textContent = 'Checking...';
    try {
        const response = await fetch(`${API_BASE_URL}/storage/similar/${fileId}?limit=1`, {
            headers: { 'X-API-KEY': API_KEY }
        });

        if (response.ok) {
            const data = await response.json();
            statusEl.innerHTML = `<span style="color: #16a34a;">✓ Embedded</span> (Total: ${data.total_embeddings || 'N/A'})`;
        } else if (response.status === 404) {
            const error = await response.json();
            if (error.detail && error.detail.includes('No embedding')) {
                statusEl.innerHTML = '<span style="color: #dc2626;">✗ No embedding found</span>';
            } else {
                statusEl.innerHTML = '<span style="color: #dc2626;">✗ Not found</span>';
            }
        } else {
            const error = await response.json();
            statusEl.innerHTML = `<span style="color: #dc2626;">Error: ${error.detail || 'Unknown'}</span>`;
        }
    } catch (error) {
        statusEl.innerHTML = `<span style="color: #dc2626;">Error: ${error.message}</span>`;
    }
}

async function createEmbedding(fileId, statusEl) {
    try {
        statusEl.textContent = 'Creating embedding...';
        const response = await fetch(`${API_BASE_URL}/storage/objects/${fileId}/embed`, {
            method: 'POST',
            headers: { 'X-API-KEY': API_KEY }
        });
        if (!response.ok) {
            const err = await response.json().catch(() => ({}));
            throw new Error(err.detail || `HTTP ${response.status}`);
        }
        await checkEmbeddingStatus(fileId, statusEl);
    } catch (error) {
        statusEl.innerHTML = `<span style="color: #dc2626;">Error: ${error.message}</span>`;
    }
}

async function findSimilarObjects(fileId, containerEl) {
    containerEl.innerHTML = '<div style="padding: 8px; color: var(--muted);">Searching for similar objects...</div>';

    try {
        const response = await fetch(`${API_BASE_URL}/storage/similar/${fileId}?limit=10`, {
            headers: { 'X-API-KEY': API_KEY }
        });

        if (!response.ok) {
            const error = await response.json();
            if (error.detail && error.detail.includes('No embedding')) {
                containerEl.innerHTML = '<div style="padding: 8px; color: #dc2626;">This object does not have an embedding yet. Upload with analyze=true to create one.</div>';
            } else {
                containerEl.innerHTML = `<div style="padding: 8px; color: #dc2626;">Error: ${error.detail || 'Failed to search'}</div>`;
            }
            return;
        }

        const data = await response.json();
        const similar = data.similar_objects || [];
        const distances = data.distances || [];

        if (similar.length === 0) {
            containerEl.innerHTML = '<div style="padding: 8px; color: var(--muted);">No similar objects found within threshold.</div>';
            return;
        }

        let html = `<div style="margin-top: 12px; padding: 12px; background: #f8fafc; border-radius: 6px;">`;
        html += `<div style="font-weight: 600; margin-bottom: 8px;">Found ${similar.length} similar object(s):</div>`;

        similar.forEach((obj, i) => {
            const distance = distances[i] !== undefined ? distances[i].toFixed(3) : 'N/A';
            const similarity = distances[i] !== undefined ? ((1 - distances[i] / 2) * 100).toFixed(1) : 'N/A';

            html += `
                <div style="padding: 8px; margin-bottom: 6px; background: white; border-radius: 4px; border: 1px solid var(--ring);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 500; color: var(--brand);">${obj.original_filename || 'Unknown'}</div>
                            <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">
                                ID: ${obj.id}
                                ${obj.ai_category ? `| Category: ${obj.ai_category}` : ''}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 12px; font-weight: 600; color: #16a34a;">${similarity}% match</div>
                            <div style="font-size: 10px; color: var(--muted);">distance: ${distance}</div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `</div>`;
        html += `<button class="find-similar-btn" data-id="${fileId}" style="margin-top: 8px; padding: 6px 12px; border: 1px solid var(--ring); border-radius: 4px; background: white; cursor: pointer; font-size: 11px;">Refresh Search</button>`;

        containerEl.innerHTML = html;

    } catch (error) {
        containerEl.innerHTML = `<div style="padding: 8px; color: #dc2626;">Error: ${error.message}</div>`;
    }
}

async function loadEmbeddings(fileId, containerEl) {
    containerEl.innerHTML = '<div style="padding: 8px; color: var(--muted);">Checking embeddings...</div>';

    let similarResponse = null;

    try {
        similarResponse = await fetch(`${API_BASE_URL}/storage/similar/${fileId}?limit=1`, {
            headers: { 'X-API-KEY': API_KEY }
        });
    } catch (error) {
        console.error('Error fetching similar:', error);
        containerEl.innerHTML = `<div style="padding: 8px; color: #dc2626;">Network error: ${error.message}</div>`;
        return;
    }

    if (!similarResponse.ok) {
        if (similarResponse.status === 404) {
            containerEl.innerHTML = '<div style="padding: 8px; color: #dc2626;">No embeddings found. Upload with analyze=true.</div>';
        } else {
            containerEl.innerHTML = `<div style="padding: 8px; color: #dc2626;">Error loading embeddings (${similarResponse.status})</div>`;
        }
        return;
    }

    const data = await similarResponse.json();
    const totalEmbeddings = data.total_embeddings || 0;

    let html = `<div style="margin-top: 12px;">`;
    html += `<div style="font-weight: 600; margin-bottom: 8px; color: #1e40af;">Embeddings stored in Chroma Vector DB</div>`;
    html += `<div style="padding: 8px; background: white; border-radius: 4px; border: 1px solid #3b82f6;">`;
    html += `<div style="font-size: 11px; color: var(--muted); line-height: 1.6;">`;
    html += `<div><strong>Total Embeddings:</strong> ${totalEmbeddings}</div>`;
    html += `<div><strong>Storage:</strong> Chroma Vector Database</div>`;
    html += `<div><strong>Status:</strong> <span style="color: #16a34a;">✓ Active</span></div>`;
    html += `<div style="margin-top: 8px; font-style: italic; color: #6b7280;">Embeddings are stored in Chroma for semantic search.</div>`;
    html += `</div></div>`;

    try {
        const embeddingTextResponse = await fetch(`${API_BASE_URL}/storage/objects/${fileId}/embedding-text`, {
            headers: { 'X-API-KEY': API_KEY }
        });

        if (embeddingTextResponse.ok) {
            const embeddingData = await embeddingTextResponse.json();
            const embeddingText = embeddingData.embedding_text || '';
            const charCount = embeddingData.char_count || 0;

            html += `<div style="margin-top: 16px;">`;
            html += `<div style="font-weight: 600; margin-bottom: 8px; color: #1e40af; display: flex; justify-content: space-between;">`;
            html += `<span>Embedding Text</span>`;
            html += `<span style="font-size: 10px; font-weight: normal; color: #6b7280;">${charCount} chars</span>`;
            html += `</div>`;
            html += `<textarea id="embedding-text-${fileId}" style="width: 100%; min-height: 100px; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; font-family: inherit; resize: vertical;">${embeddingText}</textarea>`;
            html += `<div style="margin-top: 8px; display: flex; gap: 8px; align-items: center;">`;
            html += `<button onclick="updateEmbeddingText(${fileId})" style="padding: 6px 12px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;">Update & Re-embed</button>`;
            html += `<span id="embedding-update-status-${fileId}" style="font-size: 11px; color: #6b7280;"></span>`;
            html += `</div></div>`;
        }
    } catch (error) {
        console.error('Error fetching embedding text:', error);
    }

    html += `</div>`;
    containerEl.innerHTML = html;
}

async function updateEmbeddingText(fileId) {
    const textarea = document.getElementById(`embedding-text-${fileId}`);
    const statusEl = document.getElementById(`embedding-update-status-${fileId}`);
    const newText = textarea.value.trim();

    if (!newText) {
        statusEl.textContent = '❌ Text cannot be empty';
        statusEl.style.color = '#dc2626';
        return;
    }

    statusEl.textContent = '⏳ Updating...';
    statusEl.style.color = '#6b7280';

    try {
        const response = await fetch(`${API_BASE_URL}/storage/objects/${fileId}/embedding-text`, {
            method: 'PUT',
            headers: { 'X-API-KEY': API_KEY, 'Content-Type': 'application/json' },
            body: JSON.stringify({ embedding_text: newText })
        });

        if (response.ok) {
            const data = await response.json();
            statusEl.textContent = `✅ Updated (${data.new_text_length} chars) - Re-embedding in background`;
            statusEl.style.color = '#16a34a';
        } else {
            throw new Error(`HTTP ${response.status}`);
        }
    } catch (error) {
        statusEl.textContent = `❌ Error: ${error.message}`;
        statusEl.style.color = '#dc2626';
    }
}

async function loadExternalObjects(fileId, containerEl) {
    containerEl.innerHTML = '<div style="padding: 8px; color: var(--muted);">Loading external objects...</div>';

    try {
        const response = await fetch(`${API_BASE_URL}/storage/list?link_id=${fileId}&limit=100`, {
            headers: { 'X-API-KEY': API_KEY }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        const externalObjects = (data.items || []).filter(item => item.id !== parseInt(fileId));

        if (externalObjects.length === 0) {
            containerEl.innerHTML = '<div style="padding: 8px; color: var(--muted);">No external objects created from this object.</div>';
            return;
        }

        let html = `<div style="margin-top: 12px;">`;
        html += `<div style="font-weight: 600; margin-bottom: 8px; color: #15803d;">Found ${externalObjects.length} external object(s):</div>`;

        externalObjects.forEach((obj) => {
            const createdAt = obj.created_at ? new Date(obj.created_at).toLocaleString() : 'N/A';
            const sizeDisplay = obj.file_size_bytes ? `${(obj.file_size_bytes / 1024).toFixed(1)} KB` : 'N/A';
            html += `
                <div style="padding: 8px; margin-bottom: 6px; background: white; border-radius: 4px; border: 1px solid #22c55e;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <div style="font-weight: 500; color: #15803d;">${obj.original_filename || 'Unknown'}</div>
                            <div style="font-size: 10px; color: var(--muted); margin-top: 4px; line-height: 1.4;">
                                <div><strong>ID:</strong> ${obj.id}</div>
                                <div><strong>Type:</strong> ${obj.mime_type || 'N/A'}</div>
                                <div><strong>Size:</strong> ${sizeDisplay}</div>
                                ${obj.external_uri ? `<div><strong>URI:</strong> ${obj.external_uri}</div>` : ''}
                                <div><strong>Created:</strong> ${createdAt}</div>
                            </div>
                        </div>
                        <a href="${obj.file_url}" target="_blank" style="padding: 4px 8px; background: #22c55e; color: white; border-radius: 4px; text-decoration: none; font-size: 10px;">View</a>
                    </div>
                </div>
            `;
        });

        html += `</div>`;
        html += `<button class="load-external-btn" data-id="${fileId}" style="margin-top: 8px; padding: 6px 12px; border: 1px solid #22c55e; border-radius: 4px; background: white; color: #15803d; cursor: pointer; font-size: 11px;">Refresh</button>`;

        containerEl.innerHTML = html;

    } catch (error) {
        containerEl.innerHTML = `<div style="padding: 8px; color: #dc2626;">Error loading external objects: ${error.message}</div>`;
    }
}

async function loadAsyncTasks(fileId, containerEl) {
    containerEl.innerHTML = '<div style="padding: 8px; color: var(--muted);">Loading async tasks...</div>';

    try {
        const response = await fetch(`${API_BASE_URL}/storage/tasks?object_id=${fileId}`, {
            headers: { 'X-API-KEY': API_KEY }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        const tasks = data.tasks || [];

        if (tasks.length === 0) {
            containerEl.innerHTML = '<div style="padding: 8px; color: var(--muted);">No async tasks found for this object.</div>';
            return;
        }

        let html = `<div style="margin-top: 12px;">`;
        html += `<div style="font-weight: 600; margin-bottom: 8px; color: #92400e;">Found ${tasks.length} task(s):</div>`;

        tasks.forEach((task) => {
            const statusColors = {
                'queued': '#fbbf24',
                'processing': '#3b82f6',
                'completed': '#22c55e',
                'failed': '#dc2626'
            };
            const statusColor = statusColors[task.status] || '#6b7280';
            const createdAt = task.created_at ? new Date(task.created_at).toLocaleString() : 'N/A';
            const duration = task.started_at && task.completed_at ?
                ((new Date(task.completed_at) - new Date(task.started_at)) / 1000).toFixed(2) + 's' : 'N/A';

            html += `
                <div style="padding: 8px; margin-bottom: 6px; background: white; border-radius: 4px; border: 1px solid ${statusColor};">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 6px;">
                        <div>
                            <div style="font-weight: 500; color: ${statusColor};">Task ${task.task_id.substring(0, 8)}...</div>
                            <div style="font-size: 10px; color: var(--muted); margin-top: 2px;">Mode: ${task.mode}</div>
                        </div>
                        <div style="padding: 2px 6px; background: ${statusColor}; color: white; border-radius: 4px; font-size: 10px; font-weight: 500;">${task.status.toUpperCase()}</div>
                    </div>
                    <div style="font-size: 10px; color: var(--muted); line-height: 1.4;">
                        <div><strong>Phase:</strong> ${task.current_phase || 'N/A'}</div>
                        <div><strong>Progress:</strong> ${task.progress}%</div>
                        <div><strong>Duration:</strong> ${duration}</div>
                        <div><strong>Created:</strong> ${createdAt}</div>
                        ${task.error ? `<div style="color: #dc2626;"><strong>Error:</strong> ${task.error}</div>` : ''}
                        ${task.result ? `<details style="margin-top: 4px;"><summary style="cursor: pointer; color: #1e40af;">View Result</summary><pre style="background: #f1f5f9; padding: 4px; margin-top: 4px; border-radius: 2px; font-size: 9px; overflow-x: auto;">${JSON.stringify(task.result, null, 2)}</pre></details>` : ''}
                    </div>
                </div>
            `;
        });

        html += `</div>`;
        html += `<button class="load-tasks-btn" data-id="${fileId}" style="margin-top: 8px; padding: 6px 12px; border: 1px solid #f59e0b; border-radius: 4px; background: white; color: #92400e; cursor: pointer; font-size: 11px;">Refresh</button>`;

        containerEl.innerHTML = html;

    } catch (error) {
        containerEl.innerHTML = `<div style="padding: 8px; color: #dc2626;">Error loading async tasks: ${error.message}</div>`;
    }
}

// --- Event Listeners Setup ---
function initEventListeners() {
    // Modal listeners
    modalConfirmBtn.addEventListener('click', () => {
        if (fileToDeleteId) deleteFile(fileToDeleteId);
    });
    modalCancelBtn.addEventListener('click', hideDeleteModal);
    errorModalCloseBtn.addEventListener('click', hideErrorModal);

    // Storage Mode Selection Handler
    const storageModeSelect = document.getElementById('storage-mode-select');
    const referencePathGroup = document.getElementById('reference-path-group');
    const externalUriGroup = document.getElementById('external-uri-group');

    storageModeSelect?.addEventListener('change', () => {
        const mode = storageModeSelect.value;
        if (referencePathGroup) referencePathGroup.style.display = mode === 'reference' ? 'flex' : 'none';
        if (externalUriGroup) externalUriGroup.style.display = mode === 'external' ? 'flex' : 'none';
    });

    // Search handlers
    const debouncedFetch = debounce(() => fetchFiles(), 300);
    searchNameInput?.addEventListener('input', debouncedFetch);
    searchCollectionInput?.addEventListener('input', debouncedFetch);
    searchIdInput?.addEventListener('input', debouncedFetch);

    // Bulk delete
    bulkDeleteBtn?.addEventListener('click', bulkDelete);

    // Drag & Drop handlers
    dropZone?.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    dropZone?.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });
    dropZone?.addEventListener('drop', async (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (!files || !files.length) return;

        uploadProgress.style.width = '0%';
        if (uploadProgressText) uploadProgressText.textContent = `Preparing ${files.length} file(s)...`;
        const totalBytes = Array.from(files).reduce((sum, f) => sum + f.size, 0);
        const aggregate = { totalBytes, completedBytes: 0, currentFileBytes: 0, totalFiles: files.length, currentIndex: 0 };

        for (let i = 0; i < files.length; i++) {
            aggregate.currentIndex = i;
            if (uploadProgressText) uploadProgressText.textContent = `Uploading ${i + 1} of ${files.length}...`;
            await uploadFile(files[i], null, i, files.length, aggregate);
        }
        if (uploadProgressText) uploadProgressText.textContent = `Finalizing...`;
        setTimeout(() => {
            uploadProgress.style.width = '0%';
            if (uploadProgressText) uploadProgressText.textContent = '';
            fetchFiles();
        }, 800);
    });

    // File list click handler
    fileListContainer?.addEventListener('click', async (e) => {
        const card = e.target.closest('.file-card');
        if (!card) return;

        if (e.target.classList.contains('save-text-btn')) {
            const fileId = e.target.dataset.id;
            const originalFilename = e.target.dataset.name;
            const textArea = card.querySelector('.edit-text-area');
            if (textArea) await saveTextFile(fileId, originalFilename, textArea.value);
            return;
        }

        if (e.target.classList.contains('delete-btn') || e.target.classList.contains('delete-btn-main')) {
            showDeleteModal(e.target.dataset.id);
            return;
        }

        if (e.target.classList.contains('tab-btn')) {
            const tab = e.target.dataset.tab;
            card.querySelectorAll('.tab-btn, .tab-panel').forEach(el => el.classList.remove('active'));
            e.target.classList.add('active');
            card.querySelector(`.tab-panel[data-tab-panel="${tab}"]`).classList.add('active');

            if (tab === 'linked') {
                const linkedContainer = card.querySelector('.linked-items-container');
                const linkId = card.querySelector('.file-summary').dataset.linkId;
                if (linkId && linkedContainer && !linkedContainer.dataset.loaded) {
                    await loadLinkedFiles(linkId, linkedContainer, card.dataset.fileId);
                }
            }

            if (tab === 'kg') {
                const fileId = card.dataset.fileId;
                const statusEl = card.querySelector(`#kg-status-${fileId}`);
                if (statusEl && statusEl.textContent === 'Checking...') {
                    await checkEmbeddingStatus(fileId, statusEl);
                }
            }
            return;
        }

        if (e.target.classList.contains('refresh-kg-btn')) {
            const fileId = e.target.dataset.id;
            const statusEl = card.querySelector(`#kg-status-${fileId}`);
            if (statusEl) await checkEmbeddingStatus(fileId, statusEl);
            return;
        }

        if (e.target.classList.contains('create-kg-btn')) {
            const fileId = e.target.dataset.id;
            const statusEl = card.querySelector(`#kg-status-${fileId}`);
            if (statusEl) await createEmbedding(fileId, statusEl);
            return;
        }

        if (e.target.classList.contains('find-similar-btn')) {
            const fileId = e.target.dataset.id;
            const containerEl = card.querySelector(`#kg-similar-${fileId}`);
            if (containerEl) await findSimilarObjects(fileId, containerEl);
            return;
        }

        if (e.target.classList.contains('load-embeddings-btn')) {
            const fileId = e.target.dataset.id;
            const containerEl = card.querySelector(`#kg-embeddings-${fileId}`);
            if (containerEl) await loadEmbeddings(fileId, containerEl);
            return;
        }

        if (e.target.classList.contains('load-external-btn')) {
            const fileId = e.target.dataset.id;
            const containerEl = card.querySelector(`#kg-external-${fileId}`);
            if (containerEl) await loadExternalObjects(fileId, containerEl);
            return;
        }

        if (e.target.classList.contains('load-tasks-btn')) {
            const fileId = e.target.dataset.id;
            const containerEl = card.querySelector(`#kg-tasks-${fileId}`);
            if (containerEl) await loadAsyncTasks(fileId, containerEl);
            return;
        }

        if (e.target.closest('a, button, input, textarea')) {
            return;
        }

        const summary = e.target.closest('.file-summary');
        if (summary) {
            const isExpanded = card.classList.toggle('expanded');

            const textArea = card.querySelector('.edit-text-area');
            if (isExpanded && textArea && !textArea.dataset.contentLoaded) {
                await loadTextContent(textArea);
            }
        }
    });

    // Metadata focusout handler
    fileListContainer?.addEventListener('focusout', (e) => {
        if (e.target.classList.contains('metadata-input')) {
            const fileId = e.target.closest('.file-card').dataset.fileId;
            const field = e.target.dataset.field;
            const value = e.target.value.trim();
            updateMetadata(fileId, { [field]: value }, e.target);
        }
        if (e.target.classList.contains('owner-input')) {
            const fileId = e.target.closest('.file-card').dataset.fileId;
            const newEmail = e.target.value.trim();
            const linkId = e.target.dataset.linkId;
            if (!newEmail || !linkId) return;
            updateMetadata(fileId, { owner_email: newEmail }, e.target);
        }
    });

    // Tenant selection handler
    tenantSelect?.addEventListener('change', (e) => {
        const newTenant = e.target.value;
        currentTenant = newTenant;
        API_KEY = (TENANTS[newTenant] && TENANTS[newTenant][0]) || 'Inetpass1';
        localStorage.setItem('selectedTenant', newTenant);
        fetchFiles();
        console.log(`Switched to tenant: ${newTenant}`);
    });
}

// --- Initialize ---
(async () => {
    await loadTenants();
    initEventListeners();
    fetchFiles();
})();

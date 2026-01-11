// storage2-upload.js - File Upload and AI Status Polling

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
                statusText = `Queued${status.stage ? ` (${status.stage})` : ''}`;
            } else if (status.status === 'processing') {
                if (status.progress) {
                    statusText = `Processing chunk ${status.progress}`;
                } else {
                    statusText = `Processing${status.stage ? ` (${status.stage})` : ''}`;
                }
                if (status.elapsed_seconds) {
                    statusText += ` - ${status.elapsed_seconds}s`;
                }
            } else if (status.status === 'completed') {
                const embeddings = status.details?.embeddings_created || 0;
                const mode = status.details?.mode || '';
                statusText = `Complete - ${embeddings} embeddings${mode === 'chunked' ? ' (chunked)' : ''}`;
                if (metaElement) metaElement.textContent = statusText;
                if (rowElement) rowElement.classList.add('success');
                fetchFiles();
                return;
            } else if (status.status === 'failed') {
                statusText = `Failed: ${status.error?.substring(0, 100) || 'Unknown error'}`;
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
        <div class="meta">${(file.size / (1024 * 1024)).toFixed(2)} MB - Starting...</div>
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
                    if (meta) meta.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB - Uploading ${pct}%${speed ? ` - ${speed}` : ''}`;
                    if (aggregate) {
                        aggregate.currentFileBytes = e.loaded;
                        const overallPct = Math.round((aggregate.completedBytes + aggregate.currentFileBytes) / aggregate.totalBytes * 100);
                        if (uploadProgress) uploadProgress.style.width = overallPct + '%';
                        if (uploadProgressText) uploadProgressText.textContent = `Uploading ${aggregate.currentIndex + 1} of ${aggregate.totalFiles} - ${overallPct}%`;
                    }
                } else {
                    if (meta) meta.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB - Uploading...`;
                }
            };

            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    if (bar) bar.style.width = '100%';
                    if (meta) meta.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB - Processing...`;
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
        if (meta) meta.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB - Failed`;
    }
}

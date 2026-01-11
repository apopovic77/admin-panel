// storage2-api.js - API Functions (Fetch, Delete, Metadata, Text, Linked, Knowledge Graph)

// --- File Operations ---
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

// --- Linked Files (supports semicolon-separated link_ids) ---
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
            container.innerHTML = 'Keine verlinkten Dateien gefunden.';
            return;
        }

        // Clear container and add results
        container.innerHTML = '';

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
            statusEl.innerHTML = `<span style="color: #16a34a;">Embedded</span> (Total: ${data.total_embeddings || 'N/A'})`;
        } else if (response.status === 404) {
            const error = await response.json();
            if (error.detail && error.detail.includes('No embedding')) {
                statusEl.innerHTML = '<span style="color: #dc2626;">No embedding found</span>';
            } else {
                statusEl.innerHTML = '<span style="color: #dc2626;">Not found</span>';
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
                                ID: ${obj.id} ${obj.ai_category ? `| Category: ${obj.ai_category}` : ''}
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
    html += `<div><strong>Status:</strong> <span style="color: #16a34a;">Active</span></div>`;
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
        statusEl.textContent = 'Text cannot be empty';
        statusEl.style.color = '#dc2626';
        return;
    }

    statusEl.textContent = 'Updating...';
    statusEl.style.color = '#6b7280';

    try {
        const response = await fetch(`${API_BASE_URL}/storage/objects/${fileId}/embedding-text`, {
            method: 'PUT',
            headers: { 'X-API-KEY': API_KEY, 'Content-Type': 'application/json' },
            body: JSON.stringify({ embedding_text: newText })
        });

        if (response.ok) {
            const data = await response.json();
            statusEl.textContent = `Updated (${data.new_text_length} chars) - Re-embedding in background`;
            statusEl.style.color = '#16a34a';
        } else {
            throw new Error(`HTTP ${response.status}`);
        }
    } catch (error) {
        statusEl.textContent = `Error: ${error.message}`;
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

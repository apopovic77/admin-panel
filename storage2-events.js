// storage2-events.js - Event Listeners and Initialization

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

        // Save text button
        if (e.target.classList.contains('save-text-btn')) {
            const fileId = e.target.dataset.id;
            const originalFilename = e.target.dataset.name;
            const textArea = card.querySelector('.edit-text-area');
            if (textArea) await saveTextFile(fileId, originalFilename, textArea.value);
            return;
        }

        // Delete buttons
        if (e.target.classList.contains('delete-btn') || e.target.classList.contains('delete-btn-main')) {
            showDeleteModal(e.target.dataset.id);
            return;
        }

        // Tab buttons
        if (e.target.classList.contains('tab-btn')) {
            const tab = e.target.dataset.tab;
            card.querySelectorAll('.tab-btn, .tab-panel').forEach(el => el.classList.remove('active'));
            e.target.classList.add('active');
            card.querySelector(`.tab-panel[data-tab-panel="${tab}"]`).classList.add('active');

            // Load linked files on tab open
            if (tab === 'linked') {
                const linkedContainer = card.querySelector('.linked-items-container');
                const linkId = card.querySelector('.file-summary').dataset.linkId;
                if (linkId && linkedContainer && !linkedContainer.dataset.loaded) {
                    await loadLinkedFiles(linkId, linkedContainer, card.dataset.fileId);
                }
            }

            // Check embedding status on KG tab open
            if (tab === 'kg') {
                const fileId = card.dataset.fileId;
                const statusEl = card.querySelector(`#kg-status-${fileId}`);
                if (statusEl && statusEl.textContent === 'Checking...') {
                    await checkEmbeddingStatus(fileId, statusEl);
                }
            }
            return;
        }

        // Knowledge Graph buttons
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

        // Don't expand card if clicking on interactive elements
        if (e.target.closest('a, button, input, textarea')) {
            return;
        }

        // Expand/collapse card
        const summary = e.target.closest('.file-summary');
        if (summary) {
            const isExpanded = card.classList.toggle('expanded');

            // Load text content on expand
            const textArea = card.querySelector('.edit-text-area');
            if (isExpanded && textArea && !textArea.dataset.contentLoaded) {
                await loadTextContent(textArea);
            }
        }
    });

    // Metadata focusout handler (auto-save on blur)
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

// --- Initialize Application ---
(async () => {
    await loadTenants();
    initEventListeners();
    fetchFiles();
})();

// storage2-card.js - File Card Rendering

function renderFileCard(cardElement, file) {
    const isVideo = file.mime_type && file.mime_type.startsWith('video/');
    const fileSizeMB = (file.file_size_bytes / (1024 * 1024)).toFixed(2);
    const isTextLike = (file.mime_type && (file.mime_type.startsWith('text/') || file.mime_type.includes('json') || file.mime_type.includes('markdown'))) ||
        ['txt', 'log', 'md', 'markdown', 'mdx', 'mdown', 'mkdn', 'mkd', 'json', 'xml', 'csv'].includes((file.original_filename || '').split('.').pop().toLowerCase());

    // Build tabs
    const metadataTab = `<button class="tab-btn" data-tab="metadata">Metadata</button>`;
    const techTab = `<button class="tab-btn" data-tab="tech">Technical Details</button>`;
    const aiTab = `<button class="tab-btn" data-tab="ai">AI Analysis</button>`;
    const kgTab = `<button class="tab-btn" data-tab="kg">Knowledge Graph</button>`;
    const actionsTab = `<button class="tab-btn" data-tab="actions">Actions</button>`;
    const linkedTab = file.link_id ? `<button class="tab-btn" data-tab="linked">Linked Files</button>` : '';

    // No separate link chain display - linked files show as cards below

    // Metadata Panel
    const metadataPanel = `
        <div class="tab-panel" data-tab-panel="metadata">
            <div class="info-group">
                <div class="id-display">ID: ${file.id}</div>
                <div class="label">Title</div>
                <input type="text" class="metadata-input" data-field="title" value="${file.title || ''}">
            </div>
            <div class="info-group">
                <div class="label">Collection</div>
                <input type="text" class="metadata-input" data-field="collection_id" value="${file.collection_id || ''}">
            </div>
            <div class="info-group">
                <div class="label">Owner</div>
                <input type="email" class="owner-input" data-link-id="${file.link_id || ''}" value="${file.owner_email || ''}">
            </div>
            <div class="info-group">
                <div class="label">Link ID</div>
                <input type="text" class="metadata-input" data-field="link_id" value="${file.link_id || ''}">
            </div>
            <div class="info-group" style="grid-column: 1 / -1;">
                <div class="label">Description</div>
                <textarea class="metadata-input" data-field="description">${file.description || ''}</textarea>
            </div>
        </div>
    `;

    // Technical Panel
    const lat = file.geo_position?.latitude ?? file.latitude;
    const lon = file.geo_position?.longitude ?? file.longitude;
    const geoPanelHtml = (lat && lon) ? `
        <div class="info-group">
            <div class="label">Geo Position</div>
            <a href="https://www.google.com/maps?q=${lat},${lon}" target="_blank" class="value">${lat}, ${lon}</a>
        </div>` : '';

    const techPanel = `
        <div class="tab-panel" data-tab-panel="tech">
            <div class="info-group">
                <div class="label">MIME Type</div>
                <div class="value">${file.mime_type || 'N/A'}</div>
            </div>
            <div class="info-group">
                <div class="label">File Size</div>
                <div class="value">${fileSizeMB} MB</div>
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

    // AI Panel - build vision sections
    const aiPanel = buildAIPanel(file);

    // Knowledge Graph Panel
    const kgPanel = buildKGPanel(file);

    // Actions & Linked Panels
    const actionsPanel = `<div class="tab-panel" data-tab-panel="actions"><button class="delete-btn" data-id="${file.id}">Delete File</button></div>`;
    const linkedPanel = file.link_id ? `<div class="tab-panel" data-tab-panel="linked"><div class="linked-items-container">Lade verlinkte Dateien...</div></div>` : '';

    // Build final tabs HTML
    let tabsHtml = '';
    if (isTextLike) {
        tabsHtml = `
            <nav class="details-nav">
                <button class="tab-btn active" data-tab="edit">Edit</button>
                ${metadataTab}${techTab}${aiTab}${kgTab}${linkedTab}${actionsTab}
            </nav>
            <div class="tab-panel active" data-tab-panel="edit">
                <textarea class="edit-text-area" data-url="${file.file_url}" placeholder="Loading content..."></textarea>
                <button class="save-text-btn" data-id="${file.id}" data-name="${file.original_filename}">Save Changes</button>
            </div>
            ${metadataPanel}${techPanel}${aiPanel}${kgPanel}${linkedPanel}${actionsPanel}
        `;
    } else {
        tabsHtml = `
            <nav class="details-nav">
                <button class="tab-btn active" data-tab="metadata">Metadata</button>
                ${techTab}${aiTab}${kgTab}${linkedTab}${actionsTab}
            </nav>
            ${metadataPanel.replace('class="tab-panel"', 'class="tab-panel active"')}
            ${techPanel}${aiPanel}${kgPanel}${linkedPanel}${actionsPanel}
        `;
    }

    // Render card
    cardElement.innerHTML = `
        <div class="file-summary" data-link-id="${file.link_id || ''}">
            <div class="thumbnail">${file.thumbnail_url ? `<img src="${file.thumbnail_url}" alt="thumbnail">` : `<div class="file-icon">FILE</div>`}</div>
            <div class="info">
                <div class="filename">${file.original_filename}</div>
                <div class="file-title">${file.title || ''}</div>
                <div class="file-link-id">ID: ${file.id}${file.link_id ? ` / Link ID: ${file.link_id}` : ''}</div>
            </div>
            <div class="meta-pills">
                ${file.collection_id ? `<div class="pill collection">${file.collection_id}</div>` : ''}
                ${isVideo ? `<div class="pill video">Video</div>` : ''}
                <div class="pill">${fileSizeMB} MB</div>
                ${file.width && file.height ? `<div class="pill">${file.width}x${file.height}</div>` : ''}
            </div>
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

// --- AI Panel Builder ---
function buildAIPanel(file) {
    const aiTagsFormatted = file.ai_tags
        ? `<pre style="background: #f1f5f9; padding: 8px; border-radius: 4px; font-size: 11px; overflow-x: auto;">${JSON.stringify(file.ai_tags, null, 2)}</pre>`
        : 'N/A';
    const aiContextMetadata = file.ai_context_metadata
        ? `<pre style="background: #f1f5f9; padding: 8px; border-radius: 4px; font-size: 11px; overflow-x: auto;">${JSON.stringify(file.ai_context_metadata, null, 2)}</pre>`
        : 'N/A';

    // Vision Analysis Section
    let visionAnalysisSection = buildVisionSection(file);
    let detailedVisionSection = buildDetailedVisionSection(file);
    let aiDebugSection = buildAIDebugSection(file);

    return `
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
            <div style="grid-column: 1 / -1;">${visionAnalysisSection}</div>
            <div style="grid-column: 1 / -1;">${detailedVisionSection}</div>
            <div class="info-group" style="grid-column: 1 / -1;">
                <div class="label">Extracted Tags (AI)</div>
                ${aiTagsFormatted}
            </div>
            <div class="info-group" style="grid-column: 1 / -1;">
                <div class="label">AI Context Metadata</div>
                ${aiContextMetadata}
            </div>
            <div style="grid-column: 1 / -1;">${aiDebugSection}</div>
        </div>
    `;
}

function buildVisionSection(file) {
    let extractedTagsSource = file.extracted_tags;
    if (!extractedTagsSource && file.ai_context_metadata) {
        try {
            const metadata = typeof file.ai_context_metadata === 'string'
                ? JSON.parse(file.ai_context_metadata)
                : file.ai_context_metadata;
            extractedTagsSource = metadata.extracted_tags;
        } catch (e) { }
    }

    if (!extractedTagsSource) return '';

    try {
        const extractedTags = typeof extractedTagsSource === 'string'
            ? JSON.parse(extractedTagsSource)
            : extractedTagsSource;

        const hasVisionData = extractedTags.colors || extractedTags.materials || extractedTags.visual_harmony_tags;
        if (!hasVisionData) return '';

        return `
            <div style="margin-top: 20px; padding: 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: white;">
                <h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 600;">Vision Intelligence Analysis</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                    ${extractedTags.colors?.length ? `
                        <div style="background: rgba(255,255,255,0.15); padding: 10px; border-radius: 6px;">
                            <div style="font-weight: 600; font-size: 11px; margin-bottom: 6px; opacity: 0.9;">Colors</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                ${extractedTags.colors.map(c => `<span style="background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 12px; font-size: 10px;">${c}</span>`).join('')}
                            </div>
                        </div>
                    ` : ''}
                    ${extractedTags.materials?.length ? `
                        <div style="background: rgba(255,255,255,0.15); padding: 10px; border-radius: 6px;">
                            <div style="font-weight: 600; font-size: 11px; margin-bottom: 6px; opacity: 0.9;">Materials</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                ${extractedTags.materials.map(m => `<span style="background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 12px; font-size: 10px;">${m}</span>`).join('')}
                            </div>
                        </div>
                    ` : ''}
                    ${extractedTags.visual_harmony_tags?.length ? `
                        <div style="background: rgba(255,255,255,0.15); padding: 10px; border-radius: 6px;">
                            <div style="font-weight: 600; font-size: 11px; margin-bottom: 6px; opacity: 0.9;">Visual Harmony</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                ${extractedTags.visual_harmony_tags.map(t => `<span style="background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 12px; font-size: 10px;">${t}</span>`).join('')}
                            </div>
                        </div>
                    ` : ''}
                    ${extractedTags.keywords?.length ? `
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
    } catch (e) {
        console.error('Error parsing extracted_tags:', e);
        return '';
    }
}

function buildDetailedVisionSection(file) {
    if (!file.ai_context_metadata) return '';

    try {
        const metadata = typeof file.ai_context_metadata === 'string'
            ? JSON.parse(file.ai_context_metadata)
            : file.ai_context_metadata;

        const embeddingMetadata = metadata.embedding_info?.metadata || metadata;
        const productAnalysis = embeddingMetadata.product_analysis;
        const visualAnalysis = embeddingMetadata.visual_analysis;
        const layoutIntel = embeddingMetadata.layout_intelligence;

        if (!productAnalysis && !visualAnalysis && !layoutIntel) return '';

        return `
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
    } catch (e) {
        console.error('Error parsing vision analysis:', e);
        return '';
    }
}

function buildAIDebugSection(file) {
    if (!file.ai_context_metadata) {
        return `
            <div style="margin-top: 16px; padding: 8px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 6px; font-size: 11px; color: #6b7280;">
                No AI analysis data available. Upload with <code>analyze=true</code> to enable AI analysis.
            </div>
        `;
    }

    try {
        const metadata = typeof file.ai_context_metadata === 'string'
            ? JSON.parse(file.ai_context_metadata)
            : file.ai_context_metadata;

        const aiPrompt = metadata.prompt || metadata.ai_prompt || 'Not captured';
        const aiResponse = metadata.response || metadata.ai_response || metadata.raw_response || 'Not captured';

        return `
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
        return `
            <div style="margin-top: 16px; padding: 8px; background: #fee2e2; border: 1px solid #dc2626; border-radius: 6px; font-size: 11px; color: #991b1b;">
                Error parsing AI debug info: ${e.message}
            </div>
        `;
    }
}

// --- Knowledge Graph Panel Builder ---
function buildKGPanel(file) {
    return `
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
}

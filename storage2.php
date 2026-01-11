<?php
require_once __DIR__ . '/config.php';
$config = get_app_config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arkturian Storage v2</title>
    <link rel="stylesheet" href="storage2.css">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; padding-top: 32px;">
            <h1 style="margin: 0; padding: 0;">Storage</h1>
            <div style="display: flex; align-items: center; gap: 12px;">
                <label for="tenant-select" style="font-weight: 600; color: var(--muted); font-size: 14px;">Tenant:</label>
                <select id="tenant-select" style="border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 10px 14px; font-size: 14px; background-color: #f8fafc; cursor: pointer; font-weight: 500;">
                    <option value="arkturian">Arkturian</option>
                    <option value="oneal">O'Neal</option>
                    <option value="koralmbahn">Landesmuseum</option>
                </select>
            </div>
        </div>

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
                <summary style="cursor: pointer; font-weight: 600; color: var(--brand); user-select: none;">Advanced Options</summary>

                <div class="control-group" style="margin-top: 16px;">
                    <label for="storage-mode-select">Storage Mode</label>
                    <select id="storage-mode-select" style="border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 10px 14px; font-size: 1em; background-color: #f8fafc;">
                        <option value="copy" selected>Copy (Download & Store)</option>
                        <option value="reference">Reference (Local Filesystem)</option>
                        <option value="external">External (Web URI - Proxied)</option>
                    </select>
                    <span style="font-size: 12px; color: var(--muted);">
                        <strong>Copy:</strong> Standard mode - downloads and stores file<br>
                        <strong>Reference:</strong> References local filesystem path<br>
                        <strong>External:</strong> References external web URI (file stays on origin server, proxied on demand)
                    </span>
                </div>

                <div class="control-group" id="reference-path-group" style="display: none;">
                    <label for="reference-path-input">Reference Path</label>
                    <input type="text" id="reference-path-input" placeholder="/mnt/data/images/product.jpg" />
                    <span style="font-size: 12px; color: var(--muted);">Full filesystem path to existing file</span>
                </div>

                <div class="control-group" id="external-uri-group" style="display: none;">
                    <label for="external-uri-input">External URI</label>
                    <input type="text" id="external-uri-input" placeholder="https://example.com/images/product.jpg" />
                    <span style="font-size: 12px; color: var(--muted);">External web URI (file will be proxied via /storage/proxy/{id})</span>
                </div>

                <div class="control-group">
                    <label for="analyze-toggle">Run AI Analysis</label>
                    <input type="checkbox" id="analyze-toggle" checked />
                    <span style="font-size: 12px; color: var(--muted);">Enable AI analysis (category, safety, embeddings)</span>
                </div>

                <div class="control-group">
                    <label for="ai-mode-select">AI Analysis Mode</label>
                    <select id="ai-mode-select" style="border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 10px 14px; font-size: 1em; background-color: #f8fafc; width: 100%;">
                        <option value="none">Keine AI Analyse</option>
                        <option value="safety">Nur Safety Check (Gemini Flash, schnell)</option>
                        <option value="vision">Full Vision Analyse (ohne Embedding)</option>
                        <option value="full" selected>Full Analyse + Embedding (Standard)</option>
                    </select>
                    <span style="font-size: 12px; color: var(--muted);">Select AI analysis depth (safety=fast, vision=detailed, full=complete with embeddings)</span>
                </div>

                <div class="control-group">
                    <label for="ai-context-text-input">AI Context Text</label>
                    <textarea id="ai-context-text-input" placeholder="Product catalog from O'Neal 2026 collection..." style="width: 100%; min-height: 60px; border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 10px; font-size: 1em; background-color: #f8fafc; font-family: inherit;"></textarea>
                    <span style="font-size: 12px; color: var(--muted);">Free-form context hints for AI (e.g., "Product catalog", "Botanical illustration")</span>
                </div>

                <div class="control-group">
                    <label for="ai-file-path-input">AI File Path</label>
                    <input type="text" id="ai-file-path-input" placeholder="/OnEal/2026/Helmets/Airframe.jpg" />
                    <span style="font-size: 12px; color: var(--muted);">Original file path for AI context (e.g., from NAS/SharePoint)</span>
                </div>

                <div class="control-group">
                    <label for="ai-metadata-input">AI Metadata (JSON)</label>
                    <textarea id="ai-metadata-input" placeholder='{"brand": "O&#39;Neal", "year": "2026", "category": "helmets"}' style="width: 100%; min-height: 60px; border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 10px; font-size: 0.9em; background-color: #f8fafc; font-family: ui-monospace, monospace;"></textarea>
                    <span style="font-size: 12px; color: var(--muted);">Structured metadata as JSON (brand, year, category, etc.)</span>
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
            <div class="control-group">
                <label for="search-id-input">Search ID</label>
                <input type="text" id="search-id-input" placeholder="type to filter by storage ID" inputmode="numeric" />
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

        <div id="bulk-actions-container" style="margin-bottom: 16px; display: none;">
            <button id="bulk-delete-btn" style="background-color: #dc3545; color: white; border: none; padding: 10px 16px; border-radius: var(--radius-sm); cursor: pointer; font-weight: 500;"></button>
        </div>
        <div class="file-list" id="file-list"></div>
    </div>

    <!-- Delete Modal -->
    <div id="delete-modal" class="modal-overlay">
        <div class="modal-content">
            <p>Are you sure you want to permanently delete this file?</p>
            <div class="modal-buttons">
                <button id="modal-cancel">No</button>
                <button id="modal-confirm">Yes, Delete</button>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal" class="modal-overlay">
        <div class="modal-content" style="text-align: left; max-width: 80%;">
            <h3>Transcoding Error</h3>
            <pre id="error-log-content" style="white-space: pre-wrap; background-color: #eee; padding: 1em; border-radius: 5px; max-height: 60vh; overflow-y: auto;"></pre>
            <div class="modal-buttons" style="text-align: right;">
                <button id="error-modal-close">Close</button>
            </div>
        </div>
    </div>

    <!-- Editor Modal -->
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

    <!-- Configuration (passed from PHP to JS) -->
    <script>
        const API_BASE_URL = '<?= js_config('api_storage_base_url'); ?>';
        const SHARE_BASE_URL = '<?= js_config('share_base_url'); ?>';
    </script>
    <!-- Modular JavaScript (order matters!) -->
    <script src="storage2-core.js"></script>
    <script src="storage2-upload.js"></script>
    <script src="storage2-card.js"></script>
    <script src="storage2-api.js"></script>
    <script src="storage2-events.js"></script>
</body>
</html>

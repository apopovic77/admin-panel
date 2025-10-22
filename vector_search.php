<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vector DB Search Tool</title>
    <style>
        :root {
            --text: #1e293b; --muted: #475569; --brand: #1f2937; --brand-2: #8B9DC3;
            --ring: rgba(148, 163, 184, .3); --surface: rgba(255, 255, 255, 0.98);
            --background-gradient: linear-gradient(to bottom, #ffffff, #f8fafc, #e2e8f0);
            --radius-lg: 16px; --radius-md: 12px; --radius-sm: 8px;
            --shadow-primary: 0 10px 30px rgba(0, 0, 0, .07); --gap: 24px;
            --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Roboto, system-ui, sans-serif;
        }
        body {
            font-family: var(--font-family); margin: 0; background: var(--background-gradient);
            color: var(--text); padding-bottom: 64px;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 var(--gap); }
        h1 {
            font-size: clamp(34px, 6.2vw, 76px); font-weight: 700;
            padding-top: 32px; margin-bottom: 32px; text-align: center;
        }
        .nav-links {
            text-align: center; margin-bottom: 32px;
        }
        .nav-links a {
            margin: 0 12px; padding: 8px 16px; text-decoration: none; color: var(--brand);
            border: 1px solid var(--ring); border-radius: var(--radius-sm); background: var(--surface);
        }
        .nav-links a:hover { background: #f1f5f9; }

        .stats-row {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px; margin-bottom: var(--gap);
        }
        .stat-card {
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-md); padding: 20px; text-align: center;
        }
        .stat-value { font-size: 32px; font-weight: 700; color: var(--brand); }
        .stat-label { font-size: 14px; color: var(--muted); margin-top: 8px; }

        .search-section {
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            margin-bottom: var(--gap); box-shadow: var(--shadow-primary);
        }
        .search-modes {
            display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .mode-btn {
            padding: 10px 20px; border-radius: var(--radius-sm); border: 1px solid var(--ring);
            background: #f8fafc; cursor: pointer; font-weight: 600;
        }
        .mode-btn.active {
            background: var(--brand); color: white; border-color: var(--brand);
        }

        .query-section {
            margin-bottom: 20px;
        }
        .query-section h3 {
            margin-bottom: 12px; font-size: 16px; font-weight: 600;
        }
        .query-input {
            width: 100%; border: 1px solid var(--ring); border-radius: var(--radius-sm);
            padding: 12px; font-size: 16px; font-family: var(--font-family);
            background: #f8fafc; margin-bottom: 12px;
        }
        .query-input:focus { outline: 2px solid var(--brand-2); }

        .options-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px; margin-bottom: 20px;
        }
        .option-group label {
            display: block; font-weight: 500; color: var(--muted); margin-bottom: 6px;
        }
        .option-group input, .option-group select {
            width: 100%; border: 1px solid var(--ring); border-radius: var(--radius-sm);
            padding: 10px; background: #f8fafc;
        }

        .search-btn {
            padding: 14px 32px; border-radius: var(--radius-sm); border: none;
            background: var(--brand); color: white; font-weight: 600; font-size: 16px;
            cursor: pointer; width: 100%;
        }
        .search-btn:hover { background: #374151; }
        .search-btn:disabled { background: #9ca3af; cursor: not-allowed; }

        .results-section {
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            box-shadow: var(--shadow-primary);
        }
        .results-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--ring);
        }
        .results-header h2 { margin: 0; font-size: 24px; }
        .results-count { color: var(--muted); font-size: 14px; }

        .result-card {
            border: 1px solid var(--ring); border-radius: var(--radius-md);
            padding: 16px; margin-bottom: 16px; background: #fafbfc;
            transition: all 0.2s;
        }
        .result-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

        .result-header {
            display: flex; justify-content: space-between; align-items: start;
            margin-bottom: 12px;
        }
        .result-title {
            font-weight: 600; font-size: 16px; color: var(--brand);
            flex: 1;
        }
        .result-distance {
            background: #e0e7ff; color: #4338ca; padding: 4px 12px;
            border-radius: 12px; font-size: 13px; font-weight: 600;
        }

        .result-meta {
            display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;
        }
        .pill {
            font-size: 11px; font-weight: 500; padding: 4px 10px;
            border-radius: 12px; background-color: #e2e8f0; color: #475569;
        }
        .pill.id { background-color: #fef3c7; color: #92400e; }
        .pill.type { background-color: #dbeafe; color: #1e40af; }

        .result-content {
            color: var(--text); line-height: 1.6; margin-bottom: 12px;
            padding: 12px; background: white; border-radius: var(--radius-sm);
            max-height: 200px; overflow-y: auto;
        }

        .result-image {
            max-width: 200px; max-height: 150px; object-fit: contain;
            border-radius: var(--radius-sm); margin-top: 12px;
            border: 1px solid var(--ring);
        }

        .loading {
            text-align: center; padding: 40px; font-size: 16px; color: var(--muted);
        }

        .error {
            background: #fee2e2; border: 1px solid #fecaca; border-radius: var(--radius-md);
            padding: 16px; margin-bottom: 20px; color: #991b1b;
        }

        .example-queries {
            margin-top: 16px; padding: 12px; background: #fef3c7; border-radius: var(--radius-sm);
            border: 1px solid #fde047;
        }
        .example-queries strong {
            display: block; margin-bottom: 8px; color: #854d0e;
        }
        .example-queries code {
            background: white; padding: 4px 8px; border-radius: 4px;
            display: inline-block; margin: 4px; cursor: pointer; font-size: 13px;
        }
        .example-queries code:hover { background: #fef9c3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Vector DB Search Tool</h1>

        <div class="nav-links">
            <a href="storage2.php">Storage</a>
            <a href="oneal_products.php">Products</a>
            <a href="vector_search.php" style="background: #e0e7ff; border-color: #4338ca;">Vector Search</a>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value" id="embeddings-count">-</div>
                <div class="stat-label">Total Embeddings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="last-search-time">-</div>
                <div class="stat-label">Last Search Time (ms)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="results-count">-</div>
                <div class="stat-label">Results Found</div>
            </div>
        </div>

        <div class="search-section">
            <h2>Search Mode</h2>
            <div class="search-modes">
                <button class="mode-btn active" onclick="setMode('similar')" id="mode-similar">
                    Similar by ID
                </button>
                <button class="mode-btn" onclick="setMode('text')" id="mode-text">
                    Text Search
                </button>
            </div>

            <!-- Similar by ID Mode -->
            <div id="search-similar" class="query-section">
                <h3>Find Similar Assets by Object ID</h3>
                <input type="number" id="object-id" class="query-input"
                       placeholder="Enter storage object ID (e.g., 1888, 2694)">

                <div class="example-queries">
                    <strong>Try these Object IDs:</strong>
                    <code onclick="setObjectId(1888)">1888 (MTB Products CSV)</code>
                    <code onclick="setObjectId(2694)">2694 (MX Products CSV)</code>
                </div>
            </div>

            <!-- Text Search Mode -->
            <div id="search-text" class="query-section" style="display: none;">
                <h3>Natural Language Search</h3>
                <textarea id="text-query" class="query-input" rows="3"
                          placeholder="e.g., mountain bike helmets under 100 euros"></textarea>

                <div class="example-queries">
                    <strong>Try these queries:</strong>
                    <code onclick="setTextQuery('mountain bike helmets')">mountain bike helmets</code>
                    <code onclick="setTextQuery('motocross gear red')">motocross gear red</code>
                    <code onclick="setTextQuery('protective equipment')">protective equipment</code>
                </div>
            </div>

            <div class="options-grid">
                <div class="option-group">
                    <label>Max Results</label>
                    <input type="number" id="limit" value="10" min="1" max="50">
                </div>
                <div class="option-group">
                    <label>Max Distance</label>
                    <input type="number" id="max-distance" value="2.0" min="0" max="2" step="0.1">
                </div>
            </div>

            <button class="search-btn" onclick="search()" id="search-btn">
                🔍 Search Vector Database
            </button>
        </div>

        <div id="error-container"></div>

        <div class="results-section" id="results-section" style="display: none;">
            <div class="results-header">
                <h2>Search Results</h2>
                <span class="results-count" id="results-info"></span>
            </div>
            <div id="results-container"></div>
        </div>
    </div>

    <script>
        const API_BASE = 'https://api.arkturian.com';
        const API_KEY = 'Inetpass1';

        let currentMode = 'similar';

        // Load stats on page load
        async function loadStats() {
            try {
                const response = await fetch(`${API_BASE}/storage/kg/stats`, {
                    headers: { 'X-API-Key': API_KEY }
                });
                const data = await response.json();
                document.getElementById('embeddings-count').textContent = data.total_embeddings || '-';
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        function setMode(mode) {
            currentMode = mode;

            // Update buttons
            document.getElementById('mode-similar').classList.toggle('active', mode === 'similar');
            document.getElementById('mode-text').classList.toggle('active', mode === 'text');

            // Toggle sections
            document.getElementById('search-similar').style.display = mode === 'similar' ? 'block' : 'none';
            document.getElementById('search-text').style.display = mode === 'text' ? 'block' : 'none';
        }

        function setObjectId(id) {
            document.getElementById('object-id').value = id;
        }

        function setTextQuery(query) {
            document.getElementById('text-query').value = query;
        }

        async function search() {
            const errorContainer = document.getElementById('error-container');
            const resultsSection = document.getElementById('results-section');
            const resultsContainer = document.getElementById('results-container');
            const searchBtn = document.getElementById('search-btn');

            errorContainer.innerHTML = '';
            resultsContainer.innerHTML = '<div class="loading">Searching...</div>';
            resultsSection.style.display = 'block';
            searchBtn.disabled = true;

            try {
                const startTime = performance.now();

                if (currentMode === 'similar') {
                    await searchSimilar();
                } else {
                    await searchText();
                }

                const endTime = performance.now();
                const searchTime = Math.round(endTime - startTime);
                document.getElementById('last-search-time').textContent = searchTime;

            } catch (error) {
                console.error('Search error:', error);
                errorContainer.innerHTML = `
                    <div class="error">
                        <strong>Search Error:</strong> ${error.message}
                    </div>
                `;
                resultsSection.style.display = 'none';
            } finally {
                searchBtn.disabled = false;
            }
        }

        async function searchSimilar() {
            const objectId = document.getElementById('object-id').value;
            const limit = document.getElementById('limit').value;
            const maxDistance = document.getElementById('max-distance').value;

            if (!objectId) {
                throw new Error('Please enter an object ID');
            }

            const url = `${API_BASE}/storage/similar/${objectId}?limit=${limit}`;
            const response = await fetch(url, {
                headers: { 'X-API-Key': API_KEY }
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const data = await response.json();
            renderResults(data, objectId);
        }

        async function searchText() {
            const textQuery = document.getElementById('text-query').value.trim();
            const limit = document.getElementById('limit').value;

            if (!textQuery) {
                throw new Error('Please enter a search query');
            }

            const url = `${API_BASE}/storage/kg/search?query=${encodeURIComponent(textQuery)}&limit=${limit}&mine=false`;
            const response = await fetch(url, {
                headers: { 'X-API-Key': API_KEY }
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const data = await response.json();
            renderTextResults(data, textQuery);
        }

        function renderTextResults(data, queryText) {
            const resultsContainer = document.getElementById('results-container');
            const resultsInfo = document.getElementById('results-info');

            if (!data.results || data.results.length === 0) {
                resultsContainer.innerHTML = '<div class="loading">No results found</div>';
                resultsInfo.textContent = '0 results';
                document.getElementById('results-count').textContent = '0';
                return;
            }

            const count = data.results.length;
            resultsInfo.textContent = `${count} result${count !== 1 ? 's' : ''} for "${queryText}"`;
            document.getElementById('results-count').textContent = count;

            resultsContainer.innerHTML = data.results.map((chunk, index) => {
                const sourceFile = chunk.source_file;
                const distance = chunk.distance.toFixed(4);
                const similarity = chunk.similarity_score;

                // Get the actual matched content
                const matchedContent = chunk.content || 'No content';

                // Get embedding metadata
                const embeddingType = chunk.embedding_type || 'unknown';
                const embeddingIndex = chunk.embedding_index !== null ? chunk.embedding_index : '-';

                // Get image URL from metadata if available
                let imageUrl = null;
                if (chunk.metadata && chunk.metadata.uri_groups) {
                    try {
                        const uriGroups = JSON.parse(chunk.metadata.uri_groups);
                        if (uriGroups && uriGroups[0] && uriGroups[0].uris) {
                            imageUrl = uriGroups[0].uris[0];
                        }
                    } catch (e) {
                        // metadata.uri_groups might already be parsed
                        if (chunk.metadata.uri_groups[0]?.uris) {
                            imageUrl = chunk.metadata.uri_groups[0].uris[0];
                        }
                    }
                }

                return `
                    <div class="result-card">
                        <div class="result-header">
                            <div>
                                <strong>📄 Source:</strong> ${sourceFile.original_filename}<br>
                                <strong>🎯 Matched:</strong> ${embeddingType} #${embeddingIndex}
                            </div>
                            <div class="score-badge">
                                <div>Distance: ${distance}</div>
                                <div style="color: #059669;">✓ ${similarity}% match</div>
                            </div>
                        </div>

                        ${imageUrl ? `<img src="${imageUrl}" alt="Preview" class="result-image">` : ''}

                        <div class="result-content">
                            <strong>Matched Content:</strong>
                            <div class="content-preview">${matchedContent}</div>
                        </div>

                        <div class="result-meta">
                            <span><strong>File ID:</strong> ${sourceFile.id}</span>
                            <span><strong>Type:</strong> ${sourceFile.mime_type || 'unknown'}</span>
                            <span><strong>Created:</strong> ${new Date(sourceFile.created_at).toLocaleDateString()}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderResults(data, queryObjectId) {
            const resultsContainer = document.getElementById('results-container');
            const resultsInfo = document.getElementById('results-info');

            if (!data.similar_objects || data.similar_objects.length === 0) {
                resultsContainer.innerHTML = '<div class="loading">No similar objects found</div>';
                resultsInfo.textContent = '0 results';
                document.getElementById('results-count').textContent = '0';
                return;
            }

            const count = data.similar_objects.length;
            resultsInfo.textContent = `${count} result${count !== 1 ? 's' : ''} for object ${queryObjectId}`;
            document.getElementById('results-count').textContent = count;

            resultsContainer.innerHTML = data.similar_objects.map((result, index) => {
                const distance = data.distances[index].toFixed(4);
                const similarity = ((1 - data.distances[index] / 2) * 100).toFixed(1);

                // Parse metadata
                const metadata = result.ai_context_metadata || {};
                const embeddingInfo = metadata.embedding_info || {};

                // Get document preview
                let contentPreview = 'No content available';
                if (embeddingInfo.embeddingsList && embeddingInfo.embeddingsList[0]) {
                    contentPreview = embeddingInfo.embeddingsList[0].text || contentPreview;
                }

                // Get image URL if available
                let imageUrl = null;
                if (embeddingInfo.embeddingsList && embeddingInfo.embeddingsList[0]) {
                    const uriGroups = embeddingInfo.embeddingsList[0].metadata?.uri_groups;
                    if (uriGroups && uriGroups[0]?.uris && uriGroups[0].uris[0]) {
                        imageUrl = uriGroups[0].uris[0];
                    }
                }

                return `
                    <div class="result-card">
                        <div class="result-header">
                            <div class="result-title">${result.original_filename || `Object ${result.id}`}</div>
                            <div class="result-distance" title="Similarity: ${similarity}%">
                                Distance: ${distance}
                            </div>
                        </div>

                        <div class="result-meta">
                            <span class="pill id">ID: ${result.id}</span>
                            <span class="pill type">${result.mime_type || 'Unknown type'}</span>
                            ${result.collection_name ? `<span class="pill">${result.collection_name}</span>` : ''}
                        </div>

                        <div class="result-content">${contentPreview}</div>

                        ${imageUrl ? `<img src="${imageUrl}" class="result-image" alt="Preview">` : ''}

                        ${result.file_url ? `
                            <div style="margin-top: 12px;">
                                <a href="${result.file_url}" target="_blank" style="color: var(--brand-2); text-decoration: none;">
                                    🔗 View File
                                </a>
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadStats();

            // Search on Enter
            document.getElementById('object-id').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') search();
            });
        });
    </script>
</body>
</html>

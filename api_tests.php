<?php
// Einbinden des Menüs und anderer gemeinsamer Elemente
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Testing Dashboard</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container { 
            max-width: 1400px; 
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2d3748;
            font-size: 2em;
        }
        .header p {
            margin: 10px 0 0 0;
            color: #718096;
        }
        .api-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .api-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .api-card h2 {
            margin: 0 0 20px 0;
            color: #2d3748;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .api-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .icon-storage { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .icon-artrack { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .icon-oneal { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        
        .test-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .test-section h3 {
            margin: 0 0 10px 0;
            font-size: 1.1em;
            color: #4a5568;
        }
        .test-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            margin-right: 10px;
        }
        .test-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .test-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .test-all-button {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 12px 30px;
            font-size: 16px;
        }
        
        .result {
            margin-top: 15px;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 400px;
            overflow-y: auto;
            display: none;
            position: relative;
        }
        .result.success {
            background: #c6f6d5;
            border: 2px solid #38a169;
            color: #22543d;
            display: block;
        }
        .result.error {
            background: #fed7d7;
            border: 2px solid #e53e3e;
            color: #742a2a;
            display: block;
        }
        .result.loading {
            background: #bee3f8;
            border: 2px solid #3182ce;
            color: #2c5282;
            display: block;
        }
        .copy-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.1);
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }
        .copy-button:hover {
            background: rgba(0,0,0,0.2);
        }
        .copy-button.copied {
            background: #38a169;
            color: white;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .status-online {
            background: #c6f6d5;
            color: #22543d;
        }
        .status-offline {
            background: #fed7d7;
            color: #742a2a;
        }
        .status-testing {
            background: #bee3f8;
            color: #2c5282;
        }
        
        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-box {
            text-align: center;
            padding: 20px;
            background: #f7fafc;
            border-radius: 8px;
        }
        .stat-number {
            font-size: 3em;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            color: #718096;
            font-size: 14px;
        }
        
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
            padding-right: 100px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🧪 API Testing Dashboard</h1>
        <p>Comprehensive testing for all Arkturian APIs</p>
        <button class="test-button test-all-button" onclick="testAllAPIs()">🚀 Test All APIs</button>
    </div>

    <div class="api-grid">
        <!-- API Storage Card -->
        <div class="api-card">
            <h2>
                <div class="api-icon icon-storage">📦</div>
                Storage API
                <span id="status-storage" class="status-badge status-offline">Offline</span>
            </h2>

            <div class="test-section">
                <h3>Health Check</h3>
                <button class="test-button" onclick="testEndpoint('storage', 'health', '/health')">Test Health</button>
                <div id="result-storage-health" class="result"></div>
            </div>

            <div class="test-section">
                <h3>List Objects</h3>
                <button class="test-button" onclick="testEndpoint('storage', 'list', '/storage/list?limit=5')">Test List</button>
                <div id="result-storage-list" class="result"></div>
            </div>

            <div class="test-section">
                <h3>Collections</h3>
                <button class="test-button" onclick="testEndpoint('storage', 'collections', '/storage/admin/collections?public_only=true')">Test Collections</button>
                <div id="result-storage-collections" class="result"></div>
            </div>
        </div>

        <!-- API Artrack Card -->
        <div class="api-card">
            <h2>
                <div class="api-icon icon-artrack">🗺️</div>
                Artrack API
                <span id="status-artrack" class="status-badge status-offline">Offline</span>
            </h2>

            <div class="test-section">
                <h3>Health Check</h3>
                <button class="test-button" onclick="testEndpoint('artrack', 'health', '/health')">Test Health</button>
                <div id="result-artrack-health" class="result"></div>
            </div>

            <div class="test-section">
                <h3>List Tracks</h3>
                <button class="test-button" onclick="testEndpoint('artrack', 'tracks', '/tracks?limit=3')">Test Tracks</button>
                <div id="result-artrack-tracks" class="result"></div>
            </div>

            <div class="test-section">
                <h3>Sync Status</h3>
                <button class="test-button" onclick="testEndpoint('artrack', 'sync', '/sync/status')">Test Sync</button>
                <div id="result-artrack-sync" class="result"></div>
            </div>
        </div>

        <!-- Oneal API Card -->
        <div class="api-card">
            <h2>
                <div class="api-icon icon-oneal">🏍️</div>
                O'Neal API
                <span id="status-oneal" class="status-badge status-offline">Offline</span>
            </h2>

            <div class="test-section">
                <h3>Health Check</h3>
                <button class="test-button" onclick="testEndpoint('oneal', 'health', '/health')">Test Health</button>
                <div id="result-oneal-health" class="result"></div>
            </div>

            <div class="test-section">
                <h3>List Products</h3>
                <button class="test-button" onclick="testEndpoint('oneal', 'products', '/products?limit=3')">Test Products</button>
                <div id="result-oneal-products" class="result"></div>
            </div>

            <div class="test-section">
                <h3>Categories</h3>
                <button class="test-button" onclick="testEndpoint('oneal', 'categories', '/categories')">Test Categories</button>
                <div id="result-oneal-categories" class="result"></div>
            </div>
        </div>
    </div>

    <div class="summary-card">
        <h2>📊 Test Summary</h2>
        <div class="summary-stats">
            <div class="stat-box">
                <div class="stat-label">Total Tests</div>
                <div id="stat-total" class="stat-number" style="color: #667eea;">0</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Passed</div>
                <div id="stat-passed" class="stat-number" style="color: #38a169;">0</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Failed</div>
                <div id="stat-failed" class="stat-number" style="color: #e53e3e;">0</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Success Rate</div>
                <div id="stat-rate" class="stat-number" style="color: #4299e1;">0%</div>
            </div>
        </div>
    </div>
</div>

<script>
const APIs = {
    storage: 'https://api-storage.arkturian.com',
    artrack: 'https://api-artrack.arkturian.com',
    oneal: 'https://oneal-api.arkturian.com'
};

const API_KEY = 'Inetpass1';

let testStats = {
    total: 0,
    passed: 0,
    failed: 0
};

function updateStats() {
    document.getElementById('stat-total').textContent = testStats.total;
    document.getElementById('stat-passed').textContent = testStats.passed;
    document.getElementById('stat-failed').textContent = testStats.failed;
    
    const rate = testStats.total > 0 ? Math.round((testStats.passed / testStats.total) * 100) : 0;
    document.getElementById('stat-rate').textContent = rate + '%';
}

function copyToClipboard(text, buttonElement) {
    navigator.clipboard.writeText(text).then(() => {
        buttonElement.textContent = '✓ Copied!';
        buttonElement.classList.add('copied');
        setTimeout(() => {
            buttonElement.textContent = '📋 Copy';
            buttonElement.classList.remove('copied');
        }, 2000);
    });
}

async function testEndpoint(apiName, testName, endpoint) {
    const resultDiv = document.getElementById(`result-${apiName}-${testName}`);
    const statusBadge = document.getElementById(`status-${apiName}`);
    
    resultDiv.className = 'result loading';
    resultDiv.innerHTML = '<pre>⏳ Testing...</pre>';
    
    statusBadge.className = 'status-badge status-testing';
    statusBadge.textContent = 'Testing...';
    
    try {
        const url = APIs[apiName] + endpoint;
        const startTime = Date.now();
        
        const response = await fetch(url, {
            headers: {
                'X-API-KEY': API_KEY,
                'Accept': 'application/json'
            }
        });
        
        const endTime = Date.now();
        const duration = endTime - startTime;
        
        let data;
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            data = await response.text();
        }
        
        testStats.total++;
        
        if (response.ok) {
            testStats.passed++;
            resultDiv.className = 'result success';
            
            const output = `✅ SUCCESS (${response.status})
Duration: ${duration}ms
URL: ${url}

Response:
${JSON.stringify(data, null, 2)}`;
            
            resultDiv.innerHTML = `<button class="copy-button" onclick="copyToClipboard(\`${output.replace(/`/g, '\\`')}\`, this)">📋 Copy</button><pre>${output}</pre>`;
            
            statusBadge.className = 'status-badge status-online';
            statusBadge.textContent = 'Online';
        } else {
            testStats.failed++;
            resultDiv.className = 'result error';
            
            const output = `❌ ERROR (${response.status})
Duration: ${duration}ms
URL: ${url}
Status: ${response.statusText}

Response:
${JSON.stringify(data, null, 2)}

Troubleshooting:
- Check if API is running
- Verify API key is valid
- Check endpoint path
- Review CORS settings`;
            
            resultDiv.innerHTML = `<button class="copy-button" onclick="copyToClipboard(\`${output.replace(/`/g, '\\`')}\`, this)">📋 Copy</button><pre>${output}</pre>`;
            
            statusBadge.className = 'status-badge status-offline';
            statusBadge.textContent = 'Error';
        }
        
        updateStats();
        
    } catch (error) {
        testStats.total++;
        testStats.failed++;
        
        resultDiv.className = 'result error';
        
        const output = `❌ NETWORK ERROR
URL: ${APIs[apiName] + endpoint}
Error: ${error.message}

Possible causes:
- API server is down
- Network connectivity issues
- CORS blocked
- Invalid URL

Stack trace:
${error.stack || 'No stack trace available'}`;
        
        resultDiv.innerHTML = `<button class="copy-button" onclick="copyToClipboard(\`${output.replace(/`/g, '\\`')}\`, this)">📋 Copy</button><pre>${output}</pre>`;
        
        statusBadge.className = 'status-badge status-offline';
        statusBadge.textContent = 'Offline';
        
        updateStats();
    }
}

async function testAllAPIs() {
    // Reset stats
    testStats = { total: 0, passed: 0, failed: 0 };
    updateStats();
    
    // Test all endpoints in sequence
    const tests = [
        // Storage API
        ['storage', 'health', '/health'],
        ['storage', 'list', '/storage/list?limit=5'],
        ['storage', 'collections', '/storage/admin/collections?public_only=true'],
        
        // Artrack API
        ['artrack', 'health', '/health'],
        ['artrack', 'tracks', '/tracks?limit=3'],
        ['artrack', 'sync', '/sync/status'],
        
        // Oneal API
        ['oneal', 'health', '/health'],
        ['oneal', 'products', '/products?limit=3'],
        ['oneal', 'categories', '/categories']
    ];
    
    for (const [apiName, testName, endpoint] of tests) {
        await testEndpoint(apiName, testName, endpoint);
        // Small delay between tests
        await new Promise(resolve => setTimeout(resolve, 300));
    }
    
    console.log('✅ All tests completed!');
}

// Auto-test health endpoints on load
window.addEventListener('load', () => {
    setTimeout(() => {
        testEndpoint('storage', 'health', '/health');
        testEndpoint('artrack', 'health', '/health');
        testEndpoint('oneal', 'health', '/health');
    }, 500);
});
</script>

</body>
</html>

<!-- CI/CD Test: 2025-10-24 12:45:09 -->

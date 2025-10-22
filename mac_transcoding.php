<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mac Transcoding Monitor</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333; min-height: 100vh;
        }
        .container {
            max-width: 1200px; margin: 0 auto; background: rgba(255,255,255,0.95);
            border-radius: 12px; padding: 30px; box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 30px; }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .status-card {
            background: #fff; border-radius: 8px; padding: 20px; border-left: 4px solid #3498db;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-card h3 { margin-top: 0; color: #2c3e50; }
        .status-online { border-left-color: #27ae60; }
        .status-offline { border-left-color: #e74c3c; }
        .status-busy { border-left-color: #f39c12; }
        .job-list { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .job-item {
            border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-bottom: 15px;
            display: grid; grid-template-columns: 1fr 120px 100px; gap: 15px; align-items: center;
        }
        .job-queued { background: #fff3cd; border-color: #ffeaa7; }
        .job-downloading { background: #d4edda; border-color: #c3e6cb; }
        .job-transcoding { background: #cce7ff; border-color: #99d6ff; }
        .job-uploading { background: #e2d9f3; border-color: #d1c4e9; }
        .job-completed { background: #d4edda; border-color: #27ae60; }
        .job-failed { background: #f8d7da; border-color: #e74c3c; }
        .progress-bar {
            width: 100%; height: 8px; background: #ecf0f1; border-radius: 4px; overflow: hidden;
        }
        .progress-fill {
            height: 100%; background: linear-gradient(90deg, #3498db, #2ecc71); transition: width 0.3s;
        }
        .refresh-info {
            text-align: center; margin-top: 20px; color: #7f8c8d; font-size: 14px;
        }
        .log-viewer {
            background: #2c3e50; color: #ecf0f1; border-radius: 8px; padding: 20px;
            font-family: 'Monaco', 'Consolas', monospace; font-size: 12px; max-height: 400px; overflow-y: auto;
        }
        .error { color: #e74c3c; font-weight: bold; }
        .success { color: #27ae60; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .timestamp { color: #95a5a6; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖥️ Mac Transcoding Monitor</h1>
        
        <?php
        // Configuration
        $mac_api_url = 'http://arkturian.com:8087';
        $timeout = 5; // seconds
        
        // Helper function to make API calls with timeout
        function callMacAPI($endpoint, $timeout = 5) {
            global $mac_api_url;
            $context = stream_context_create([
                'http' => [
                    'timeout' => $timeout,
                    'method' => 'GET'
                ]
            ]);
            
            $result = @file_get_contents($mac_api_url . $endpoint, false, $context);
            return $result ? json_decode($result, true) : null;
        }
        
        // Get API status
        $health = callMacAPI('/health', 3);
        $jobs = callMacAPI('/jobs', 8);
        $api_info = callMacAPI('/', 3);
        
        // Determine overall status
        $api_status = 'offline';
        $status_class = 'status-offline';
        $status_text = '🔴 Offline';
        
        if ($health && $health['status'] === 'healthy') {
            if ($jobs && count($jobs['jobs']) > 0) {
                $active_jobs = array_filter($jobs['jobs'], function($job) {
                    return in_array($job['status'], ['queued', 'downloading', 'transcoding', 'uploading']);
                });
                if (count($active_jobs) > 0) {
                    $api_status = 'busy';
                    $status_class = 'status-busy';
                    $status_text = '🟡 Busy (' . count($active_jobs) . ' active)';
                } else {
                    $api_status = 'online';
                    $status_class = 'status-online';
                    $status_text = '🟢 Online & Ready';
                }
            } else {
                $api_status = 'online';
                $status_class = 'status-online';
                $status_text = '🟢 Online & Ready';
            }
        }
        ?>
        
        <div class="status-grid">
            <div class="status-card <?php echo $status_class; ?>">
                <h3>API Status</h3>
                <p><strong><?php echo $status_text; ?></strong></p>
                <p>Endpoint: <?php echo $mac_api_url; ?></p>
                <p>Last Check: <?php echo date('H:i:s'); ?></p>
            </div>
            
            <div class="status-card">
                <h3>📊 Statistics</h3>
                <?php if ($jobs): ?>
                    <p><strong>Total Jobs:</strong> <?php echo count($jobs['jobs']); ?></p>
                    <p><strong>Active:</strong> <?php echo count(array_filter($jobs['jobs'], function($j) { 
                        return in_array($j['status'], ['queued', 'downloading', 'transcoding', 'uploading']); 
                    })); ?></p>
                    <p><strong>Completed:</strong> <?php echo count(array_filter($jobs['jobs'], function($j) { 
                        return $j['status'] === 'completed'; 
                    })); ?></p>
                    <p><strong>Failed:</strong> <?php echo count(array_filter($jobs['jobs'], function($j) { 
                        return $j['status'] === 'failed'; 
                    })); ?></p>
                <?php else: ?>
                    <p><strong>Unable to fetch statistics</strong></p>
                    <p>API might be busy or offline</p>
                <?php endif; ?>
            </div>
            
            <div class="status-card">
                <h3>🔄 Auto-Refresh</h3>
                <p><strong>Refreshing every 10 seconds</strong></p>
                <p>Next refresh: <span id="countdown">10</span>s</p>
                <p><button onclick="location.reload()" style="background: #3498db; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">🔄 Refresh Now</button></p>
            </div>
        </div>
        
        <div class="job-list">
            <h3>📋 Current Jobs</h3>
            <div style="margin-bottom:12px; display:flex; gap:8px; align-items:center;">
                <input id="glbPath" type="text" placeholder="/absolute/path/to/file.glb or URL" style="flex:1; padding:8px; border:1px solid #ccc; border-radius:4px;">
                <button onclick="convertGLB()" style="background:#27ae60; color:#fff; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">Convert GLB→USDZ</button>
            </div>
            <pre id="glbLog" class="log-viewer" style="display:none;"></pre>
            
            <?php if ($jobs && count($jobs['jobs']) > 0): ?>
                <?php 
                // Sort jobs by creation time (newest first)
                usort($jobs['jobs'], function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                
                foreach ($jobs['jobs'] as $job): 
                    $status_icons = [
                        'queued' => '⏳',
                        'downloading' => '⬇️',
                        'transcoding' => '🎬',
                        'uploading' => '⬆️',
                        'completed' => '✅',
                        'failed' => '❌'
                    ];
                    $icon = $status_icons[$job['status']] ?? '❓';
                ?>
                    <div class="job-item job-<?php echo $job['status']; ?>">
                        <div>
                            <h4><?php echo $icon; ?> <?php echo htmlspecialchars($job['original_filename'] ?? 'Unknown File'); ?></h4>
                            <p><strong>Job ID:</strong> <?php echo $job['job_id']; ?></p>
                            <p><strong>Status:</strong> <?php echo ucfirst($job['status']); ?></p>
                            <p><strong>Message:</strong> <?php echo htmlspecialchars($job['message']); ?></p>
                            <p><strong>Size:</strong> <?php echo round($job['file_size_bytes'] / 1024 / 1024, 1); ?> MB</p>
                            <p><strong>Started:</strong> <?php echo date('H:i:s', strtotime($job['created_at'])); ?></p>
                        </div>
                        
                        <div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $job['progress']; ?>%"></div>
                            </div>
                            <p style="text-align: center; margin: 5px 0 0 0; font-size: 12px;">
                                <?php echo round($job['progress'], 1); ?>%
                            </p>
                        </div>
                        
                        <div style="text-align: center;">
                            <?php if ($job['status'] === 'completed'): ?>
                                <span style="color: #27ae60; font-weight: bold;">DONE</span>
                            <?php elseif ($job['status'] === 'failed'): ?>
                                <span style="color: #e74c3c; font-weight: bold;">FAILED</span>
                            <?php else: ?>
                                <span style="color: #3498db; font-weight: bold;">WORKING</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #7f8c8d; font-style: italic;">
                    <?php if ($api_status === 'offline'): ?>
                        ⚠️ Unable to connect to Mac API
                    <?php else: ?>
                        📝 No jobs found. Upload a video >100MB to see transcoding jobs here.
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="refresh-info">
            <p>🔄 This page auto-refreshes every 10 seconds to show real-time status</p>
            <p>📱 Best viewed on desktop or tablet for full details</p>
        </div>
    </div>

    <script>
        // Auto-refresh countdown and reload
        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        
        setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                location.reload();
            }
        }, 1000);
        
        // Refresh immediately if API was offline (might be back online)
        <?php if ($api_status === 'offline'): ?>
        setTimeout(() => {
            location.reload();
        }, 5000);
        <?php endif; ?>

        async function convertGLB(){
            const path = document.getElementById('glbPath').value.trim();
            const log = document.getElementById('glbLog');
            log.style.display = 'block';
            log.textContent = 'Submitting…';
            if(!path){ log.textContent='Enter a local absolute path or a URL'; return; }
            const payload = path.startsWith('http') ? { download_url: path } : { source_path: path };
            try{
                const res = await fetch('<?php echo $mac_api_url; ?>/convert_glb', {
                    method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)
                });
                const data = await res.json();
                if(!res.ok){ throw new Error(data.detail || JSON.stringify(data)); }
                log.textContent = 'OK\nOutput: ' + data.output_path + '\n\nLogs (tail)\n' + (data.logs||'');
            }catch(e){
                log.textContent = 'ERROR\n' + e.message;
            }
        }
    </script>
</body>
</html>
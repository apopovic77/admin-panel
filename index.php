<?php
// Helper functions
function getSystemStats() {
    $stats = [];
    
    // System load (1, 5, 15 min averages)
    $load = sys_getloadavg();
    $stats['load_1min'] = round($load[0], 2);
    $stats['load_5min'] = round($load[1], 2);
    $stats['load_15min'] = round($load[2], 2);
    
    // CPU count for load interpretation
    $stats['cpu_count'] = intval(shell_exec('nproc'));
    
    // Memory usage with detailed breakdown
    $meminfo = file_get_contents('/proc/meminfo');
    preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
    preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
    preg_match('/MemFree:\s+(\d+)/', $meminfo, $free);
    preg_match('/Buffers:\s+(\d+)/', $meminfo, $buffers);
    preg_match('/Cached:\s+(\d+)/', $meminfo, $cached);
    preg_match('/SwapTotal:\s+(\d+)/', $meminfo, $swap_total);
    preg_match('/SwapFree:\s+(\d+)/', $meminfo, $swap_free);
    
    $stats['memory_total'] = $total[1] * 1024;
    $stats['memory_available'] = $available[1] * 1024;
    $stats['memory_used'] = ($total[1] - $available[1]) * 1024;
    $stats['memory_free'] = $free[1] * 1024;
    $stats['memory_buffers'] = $buffers[1] * 1024;
    $stats['memory_cached'] = $cached[1] * 1024;
    $stats['memory_percent'] = round(($stats['memory_used'] / $stats['memory_total']) * 100, 1);
    
    $stats['swap_total'] = $swap_total[1] * 1024;
    $stats['swap_used'] = ($swap_total[1] - $swap_free[1]) * 1024;
    $stats['swap_percent'] = $stats['swap_total'] > 0 ? round(($stats['swap_used'] / $stats['swap_total']) * 100, 1) : 0;
    
    // Disk usage for multiple mount points
    $stats['disk_usage'] = [];
    $mounts = explode("\n", trim(shell_exec('df -h | grep -E \"^/dev/\" | awk \'{print $1 ":" $6 ":" $2 ":" $3 ":" $4 ":" $5}\'')));
    foreach ($mounts as $mount) {
        if (!empty($mount)) {
            list($device, $path, $total, $used, $free, $percent) = explode(':', $mount);
            $stats['disk_usage'][] = [
                'device' => $device,
                'path' => $path,
                'total' => $total,
                'used' => $used,
                'free' => $free,
                'percent' => intval(str_replace('%', '', $percent))
            ];
        }
    }
    
    // Main disk stats for backward compatibility
    $stats['disk_total'] = disk_total_space('/');
    $stats['disk_free'] = disk_free_space('/');
    $stats['disk_used'] = $stats['disk_total'] - $stats['disk_free'];
    $stats['disk_percent'] = round(($stats['disk_used'] / $stats['disk_total']) * 100, 1);
    
    // Uptime
    $uptime = file_get_contents('/proc/uptime');
    $stats['uptime'] = round(floatval(explode(' ', $uptime)[0]));
    
    // Network statistics
    $network = shell_exec('cat /proc/net/dev | grep -E "(eth0|ens|enp)" | head -1');
    if ($network) {
        $parts = preg_split('/\s+/', trim($network));
        $stats['network_rx_bytes'] = intval($parts[1]);
        $stats['network_tx_bytes'] = intval($parts[9]);
    }
    
    // Process count
    $stats['processes'] = intval(shell_exec('ps aux | wc -l')) - 1;
    
    // Active connections 
    $stats['connections'] = intval(shell_exec('ss -tuln | wc -l')) - 1;
    
    return $stats;
}


function getMailStats() {
    $stats = [];
    
    // Get recent mail log entries (more lines to capture full day)
    $maillog = shell_exec('tail -n 2000 /var/log/syslog 2>/dev/null || echo "No mail log available"');
    
    // Various date formats for counting
    $today = sprintf('%s %2s', date('M'), date('j'));
    $yesterday = sprintf('%s %2s', date('M', strtotime('-1 day')), date('j', strtotime('-1 day')));
    
    // Remove debug logging
    // error_log("Looking for date: '$today' in mail log");
    
    // Count different types of mail activity for today
    $lines = explode("\n", $maillog);
    $stats['sent_today'] = 0;
    $stats['delivered_today'] = 0;
    $stats['bounced_today'] = 0;
    $stats['rejected_today'] = 0;
    
    foreach ($lines as $line) {
        if (strpos($line, $today) !== false) {
            // Look for ALL successful deliveries (both external and local)
            if (strpos($line, 'status=sent') !== false) {
                $stats['sent_today']++;
            }
            // Local deliveries to mailbox (also count as delivered)
            if (strpos($line, 'status=sent') === false && (strpos($line, 'to=<') !== false || strpos($line, 'delivered to mailbox') !== false)) {
                $stats['delivered_today']++;
            }
            // Bounced emails
            if (strpos($line, 'bounced') !== false) {
                $stats['bounced_today']++;
            }
            // Rejected emails
            if (strpos($line, 'rejected') !== false || strpos($line, 'NOQUEUE: reject') !== false) {
                $stats['rejected_today']++;
            }
        }
    }
    
    // Weekly stats
    $stats['sent_week'] = 0;
    $stats['delivered_week'] = 0;
    for ($i = 0; $i < 7; $i++) {
        $day = sprintf('%s %2s', date('M', strtotime("-$i day")), date('j', strtotime("-$i day")));
        foreach ($lines as $line) {
            if (strpos($line, $day) !== false) {
                if (strpos($line, 'status=sent') !== false) {
                    $stats['sent_week']++;
                }
                if (strpos($line, 'delivered to mailbox') !== false) {
                    $stats['delivered_week']++;
                }
            }
        }
    }
    
    // Mail queue size
    $queue_active = intval(shell_exec('postqueue -p | tail -n 1 | grep -o "[0-9]*" | head -1 2>/dev/null || echo 0'));
    $stats['queue_size'] = $queue_active;
    
    // Service status
    $stats['postfix_status'] = trim(shell_exec('systemctl is-active postfix 2>/dev/null || echo "unknown"'));
    $stats['dovecot_status'] = trim(shell_exec('systemctl is-active dovecot 2>/dev/null || echo "unknown"'));
    $stats['opendkim_status'] = trim(shell_exec('systemctl is-active opendkim 2>/dev/null || echo "unknown"'));
    
    // Get recent entries with better filtering
    $filtered_lines = [];
    foreach ($lines as $line) {
        if (strpos($line, $today) !== false && 
            (strpos($line, 'delivered') !== false || 
             strpos($line, 'status=sent') !== false || 
             strpos($line, 'bounced') !== false || 
             strpos($line, 'rejected') !== false ||
             strpos($line, 'from=<') !== false ||
             strpos($line, 'to=<') !== false)) {
            $filtered_lines[] = $line;
        }
    }
    $stats['recent_activity'] = array_slice($filtered_lines, -15);
    
    // Mail user count and their individual mail stats
    $mail_users = shell_exec('grep ":/bin/" /etc/passwd | cut -d: -f1');
    $stats['mail_users'] = array_filter(explode("\n", $mail_users));
    $stats['user_mail_stats'] = [];

    foreach ($stats['mail_users'] as $user) {
        $user_sent = 0;
        $user_delivered = 0;
        foreach ($lines as $line) {
            if (strpos($line, $today) !== false) {
                if (strpos($line, 'from=<' . $user . '@') !== false && strpos($line, 'status=sent') !== false) {
                    $user_sent++;
                }
                if (strpos($line, 'to=<' . $user . '@') !== false && strpos($line, 'delivered to mailbox') !== false) {
                    $user_delivered++;
                }
            }
        }
        $stats['user_mail_stats'][$user] = ['sent' => $user_sent, 'delivered' => $user_delivered];
    }
    
    return $stats;
}

function formatBytes($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, 1) . ' ' . $units[$i];
}

function formatUptime($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return "{$days}d {$hours}h {$minutes}m";
}

function getWebServerStats() {
    $stats = [];
    
    // Nginx status
    $stats['nginx_status'] = trim(shell_exec('systemctl is-active nginx 2>/dev/null || echo "unknown"'));
    
    // Active connections
    $stats['nginx_connections'] = intval(shell_exec('ss -tuln | grep :80 | wc -l')) + intval(shell_exec('ss -tuln | grep :443 | wc -l'));
    
    // SSL certificates status
    $domains = ['arkturian.com', 'admin.arkturian.com', 'starburst.arkturian.com'];
    $stats['ssl_status'] = [];
    foreach ($domains as $domain) {
        $cert_path = "/etc/letsencrypt/live/$domain/cert.pem";
        if (file_exists($cert_path)) {
            $cert_info = shell_exec("openssl x509 -in $cert_path -noout -dates 2>/dev/null");
            preg_match('/notAfter=(.+)/', $cert_info, $matches);
            $expiry = isset($matches[1]) ? strtotime($matches[1]) : 0;
            $days_left = $expiry > 0 ? ceil(($expiry - time()) / 86400) : 0;
            $stats['ssl_status'][$domain] = [
                'expires' => $expiry > 0 ? date('Y-m-d', $expiry) : 'Unknown',
                'days_left' => $days_left,
                'status' => $days_left > 30 ? 'good' : ($days_left > 7 ? 'warning' : 'critical')
            ];
        }
    }
    
    // Log file sizes
    $stats['access_log_size'] = file_exists('/var/log/nginx/access.log') ? filesize('/var/log/nginx/access.log') : 0;
    $stats['error_log_size'] = file_exists('/var/log/nginx/error.log') ? filesize('/var/log/nginx/error.log') : 0;
    
    // Recent errors
    $error_log = shell_exec('tail -n 200 /var/log/nginx/error.log 2>/dev/null || echo "No errors found"');
    $today = date('Y/m/d');
    $today_errors = substr_count($error_log, $today);
    $stats['errors_today'] = $today_errors;
    
    // Get today's error lines
    $error_lines = explode("\n", $error_log);
    $todays_errors = [];
    foreach ($error_lines as $line) {
        if (strpos($line, $today) !== false && !empty(trim($line))) {
            $todays_errors[] = $line;
        }
    }
    $stats['recent_errors'] = array_slice($todays_errors, -20); // Last 20 errors from today
    
    return $stats;
}

function getSecurityStats() {
    $stats = [];
    
    // Failed login attempts (from auth.log)
    $auth_log = shell_exec('tail -n 200 /var/log/auth.log 2>/dev/null || echo ""');
    $today = date('M j');
    $stats['failed_logins_today'] = substr_count($auth_log, "$today") && substr_count($auth_log, 'Failed password') ? substr_count($auth_log, 'Failed password') : 0;
    
    // Firewall status
    $stats['ufw_status'] = trim(shell_exec('ufw status | head -1 | awk "{print $2}" 2>/dev/null || echo "unknown"'));
    
    // Active SSH connections
    $stats['ssh_connections'] = intval(shell_exec('ss -tuln | grep :22 | wc -l'));
    
    // Last login info
    $last_login = shell_exec('last -n 5 -F | grep -v "^$" | head -5');
    $stats['recent_logins'] = array_filter(explode("\n", $last_login));
    
    // Disk space alerts
    $stats['disk_alerts'] = [];
    foreach (getSystemStats()['disk_usage'] as $disk) {
        if ($disk['percent'] > 85) {
            $stats['disk_alerts'][] = $disk['path'] . ' is ' . $disk['percent'] . '% full';
        }
    }
    
    return $stats;
}

// Get data
$systemStats = getSystemStats();
$mailStats = getMailStats();
$webStats = getWebServerStats();
$securityStats = getSecurityStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - arkturian.com</title>
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
            color: var(--text); padding: 20px; min-height: 100vh;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 var(--gap); }
        h1 {
            font-size: clamp(34px, 6.2vw, 76px); font-weight: 700;
            margin-bottom: 32px; text-align: center; color: var(--text);
        }
        .header { 
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            margin-bottom: 32px; box-shadow: var(--shadow-primary);
        }
        .header h1 { 
            margin: 0; color: var(--text); font-weight: 700;
            font-size: var(--h2-size); text-align: left;
        }
        h2 {
            font-size: var(--h2-size); font-weight: 600; margin-bottom: var(--gap);
            border-bottom: 1px solid var(--ring); padding-bottom: 16px;
        }
        .dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: var(--gap); }
        .card { 
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            box-shadow: var(--shadow-primary); transition: transform 0.2s ease;
        }
        .card:hover { transform: translateY(-2px); }
        .stat { display: flex; justify-content: space-between; margin-bottom: 12px; align-items: center; }
        .stat strong { color: var(--muted); font-weight: 600; }
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 15px 0; }
        .stat-item { 
            text-align: center; padding: 15px; background: #f8fafc; 
            border-radius: var(--radius-sm); border: 1px solid var(--ring);
        }
        .stat-item .value { font-size: 24px; font-weight: 700; color: var(--text); }
        .stat-item .label { 
            font-size: var(--kicker-size); color: var(--muted); 
            text-transform: uppercase; letter-spacing: .08em;
        }
        .progress-bar { 
            width: 100%; height: 8px; background: #eee; 
            border-radius: var(--radius-sm); overflow: hidden; margin: 8px 0;
        }
        .progress { height: 100%; transition: width 0.3s ease; border-radius: var(--radius-sm); }
        .progress.low { background: linear-gradient(90deg, #28a745, #20c997); }
        .progress.medium { background: linear-gradient(90deg, #ffc107, #fd7e14); }
        .progress.high { background: linear-gradient(90deg, #dc3545, #e83e8c); }
        .activity { 
            max-height: 300px; overflow-y: auto; font-family: 'SF Mono', 'Monaco', 'Cascadia Code', monospace; 
            font-size: 11px; background: #f8fafc; padding: 15px; 
            border-radius: var(--radius-sm); border-left: 4px solid var(--brand-2);
        }
        .status-indicator { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 8px; }
        .status-active { background: #28a745; }
        .status-inactive { background: #dc3545; }
        .status-warning { background: #ffc107; }
        .alert { 
            padding: 10px 15px; margin: 10px 0; border-radius: var(--radius-sm); 
            font-size: 14px; border: 1px solid var(--ring);
        }
        .alert-warning { background: #fff3cd; color: #856404; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .alert-success { background: var(--success-color); color: #155724; }
        .claude-interface { margin-top: var(--gap); }
        .claude-interface textarea { 
            width: 100%; height: 100px; padding: 15px; border: 2px solid var(--ring); 
            border-radius: var(--radius-sm); font-family: 'SF Mono', monospace; 
            font-size: 14px; transition: border-color 0.3s ease; background-color: #f8fafc;
        }
        .claude-interface textarea:focus { 
            border-color: var(--brand-2); outline: none; 
            box-shadow: 0 0 0 3px rgba(139, 157, 195, 0.1);
        }
        .btn { 
            background: var(--brand-2); color: white; padding: 12px 24px; 
            border: none; border-radius: var(--radius-sm); cursor: pointer; 
            margin: 10px 5px 0 0; font-weight: 600; transition: all 0.3s ease;
        }
        .btn:hover { background: var(--brand); transform: translateY(-1px); }
        .btn:disabled { background: #a0aec0; cursor: not-allowed; transform: none; }
        .btn-warning { background: #ed8936; }
        .btn-warning:hover { background: #dd6b20; }
        .btn-success { background: #38a169; }
        .btn-success:hover { background: #2f855a; }
        .claude-response { 
            background: #f8fafc; border: 1px solid var(--ring); padding: 20px; 
            border-radius: var(--radius-sm); margin-top: 15px; white-space: pre-wrap; 
            font-family: 'SF Mono', monospace; font-size: 13px; max-height: 400px; overflow-y: auto;
        }
        .controls { text-align: right; margin-bottom: 15px; }
        .live-indicator { 
            display: inline-flex; align-items: center; margin-right: 15px; 
            font-size: 14px; color: var(--muted);
        }
        .live-dot { 
            width: 8px; height: 8px; background: #38a169; 
            border-radius: 50%; margin-right: 5px; animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .card-mini { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0; }
        .metric-card { 
            background: linear-gradient(135deg, var(--brand-2) 0%, var(--brand) 100%); 
            color: white; padding: 20px; border-radius: var(--radius-md); text-align: center;
        }
        .metric-value { font-size: 32px; font-weight: 700; margin-bottom: 5px; }
        .metric-label { 
            font-size: 14px; opacity: 0.9; text-transform: uppercase; 
            letter-spacing: .08em;
        }
        
        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            body { padding: 16px; }
            
            .container { 
                max-width: 100%; 
                padding: 0 16px; 
            }
            
            h1 { 
                font-size: clamp(24px, 8vw, 32px); 
                margin-bottom: 32px; 
            }
            
            .dashboard { 
                grid-template-columns: 1fr; 
                gap: 20px; 
            }
            
            .card { 
                padding: 20px; 
                border-radius: var(--radius-md);
                margin-bottom: 16px; 
            }
            
            .stat-grid { 
                grid-template-columns: 1fr 1fr; 
                gap: 12px; 
            }
            
            .stat-item { 
                padding: 12px; 
            }
            
            .stat-item .value { 
                font-size: 20px; 
            }
            
            .stat-item .label { 
                font-size: 11px; 
            }
            
            .activity { 
                font-size: 10px; 
                padding: 12px; 
                max-height: 200px; 
            }
            
            .controls { 
                flex-direction: column; 
                gap: 12px; 
                text-align: center; 
            }
            
            .live-indicator { 
                margin-right: 0; 
                margin-bottom: 8px; 
            }
            
            .btn { 
                padding: 10px 16px; 
                font-size: 14px; 
                width: 100%; 
                margin: 4px 0; 
            }
            
            .alert { 
                font-size: 13px; 
                padding: 8px 12px; 
            }
            
            .claude-interface textarea { 
                height: 80px; 
                font-size: 12px; 
            }
            
            .claude-response { 
                font-size: 11px; 
                max-height: 300px; 
            }
            
            h3 { 
                font-size: 16px; 
                margin-bottom: 12px; 
            }
        }
        
        @media (max-width: 480px) {
            .container { 
                padding: 0 12px; 
            }
            
            .card { 
                padding: 16px; 
            }
            
            .stat-grid { 
                grid-template-columns: 1fr; 
            }
            
            .stat { 
                flex-direction: column; 
                align-items: flex-start; 
                gap: 4px; 
            }
            
            .stat strong { 
                font-size: 14px; 
            }
            
            .progress-bar { 
                height: 6px; 
            }
            
            .metric-card { 
                padding: 16px; 
            }
            
            .metric-value { 
                font-size: 24px; 
            }
            
            .metric-label { 
                font-size: 12px; 
            }
        }
        
        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            .btn { 
                min-height: 44px; 
                padding: 12px 20px; 
            }
            
            .card:hover { 
                transform: none; 
            }
            
            .btn:hover { 
                transform: none; 
            }
        }
    </style>
    <script>
        let refreshInterval;
        let autoRefresh = true;
        
        function refreshData() {
            if (autoRefresh) {
                location.reload();
            }
        }
        
        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            const button = document.getElementById('refresh-toggle');
            button.textContent = autoRefresh ? 'Disable Auto-refresh' : 'Enable Auto-refresh';
            button.className = autoRefresh ? 'btn btn-warning' : 'btn btn-success';
            
            if (autoRefresh) {
                refreshInterval = setInterval(refreshData, 30000);
            } else {
                clearInterval(refreshInterval);
            }
        }
        
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleString();
            const clockElement = document.getElementById('server-clock');
            if (clockElement) {
                clockElement.textContent = timeString;
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Update clock every second
            setInterval(updateClock, 1000);
            
            // Auto-refresh every 30 seconds
            refreshInterval = setInterval(refreshData, 30000);
            
            // Initialize progress bars with animation
            setTimeout(() => {
                document.querySelectorAll('.progress').forEach(bar => {
                    bar.style.transition = 'width 1s ease-in-out';
                });
            }, 100);
        });
    </script>
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="controls">
        <div class="live-indicator">
            <div class="live-dot"></div>
            Live Dashboard
        </div>
        <button id="refresh-toggle" class="btn btn-warning" onclick="toggleAutoRefresh()">Disable Auto-refresh</button>
        <button class="btn" onclick="refreshData()">Refresh Now</button>
    </div>
    
    <div class="dashboard">
        <!-- System Statistics -->
        <div class="card">
            <h2>📊 System Performance</h2>
            
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="value"><?= $systemStats['cpu_count'] ?></div>
                    <div class="label">CPU Cores</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?= $systemStats['processes'] ?></div>
                    <div class="label">Processes</div>
                </div>
            </div>
            
            <div class="stat">
                <span>Load Average (1m/5m/15m):</span>
                <strong><?= $systemStats['load_1min'] ?> / <?= $systemStats['load_5min'] ?> / <?= $systemStats['load_15min'] ?></strong>
            </div>
            
            <h3>Memory Usage</h3>
            <div class="stat">
                <span>RAM:</span>
                <strong><?= formatBytes($systemStats['memory_used']) ?> / <?= formatBytes($systemStats['memory_total']) ?> (<?= $systemStats['memory_percent'] ?>%)</strong>
            </div>
            <div class="progress-bar">
                <div class="progress <?= $systemStats['memory_percent'] > 80 ? 'high' : ($systemStats['memory_percent'] > 60 ? 'medium' : 'low') ?>" 
                     style="width: <?= $systemStats['memory_percent'] ?>%"></div>
            </div>
            
            <?php if ($systemStats['swap_total'] > 0): ?>
            <div class="stat">
                <span>Swap:</span>
                <strong><?= formatBytes($systemStats['swap_used']) ?> / <?= formatBytes($systemStats['swap_total']) ?> (<?= $systemStats['swap_percent'] ?>%)</strong>
            </div>
            <div class="progress-bar">
                <div class="progress <?= $systemStats['swap_percent'] > 50 ? 'high' : ($systemStats['swap_percent'] > 20 ? 'medium' : 'low') ?>" 
                     style="width: <?= $systemStats['swap_percent'] ?>%"></div>
            </div>
            <?php endif; ?>
            
            <h3>Storage</h3>
            <?php foreach ($systemStats['disk_usage'] as $disk): ?>
            <div class="stat">
                <span><?= $disk['path'] ?> (<?= $disk['device'] ?>):</span>
                <strong><?= $disk['used'] ?> / <?= $disk['total'] ?> (<?= $disk['percent'] ?>%)</strong>
            </div>
            <div class="progress-bar">
                <div class="progress <?= $disk['percent'] > 80 ? 'high' : ($disk['percent'] > 60 ? 'medium' : 'low') ?>" 
                     style="width: <?= $disk['percent'] ?>%"></div>
            </div>
            <?php endforeach; ?>
            
            <div class="stat">
                <span>Uptime:</span>
                <strong><?= formatUptime($systemStats['uptime']) ?></strong>
            </div>
            
            <div class="stat">
                <span>Network Connections:</span>
                <strong><?= $systemStats['connections'] ?></strong>
            </div>
        </div>
        
        <!-- Mail Server Statistics -->
        <div class="card">
            <h2>📧 Mail Server Status</h2>
            
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="value"><?= $mailStats['delivered_today'] ?></div>
                    <div class="label">Delivered Today</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?= $mailStats['sent_today'] ?></div>
                    <div class="label">Sent Today</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?= $mailStats['bounced_today'] ?></div>
                    <div class="label">Bounced Today</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?= $mailStats['queue_size'] ?></div>
                    <div class="label">Queue Size</div>
                </div>
            </div>
            
            <h3>Service Status</h3>
            <div class="stat">
                <span>Postfix (SMTP):</span>
                <strong><span class="status-indicator status-<?= $mailStats['postfix_status'] === 'active' ? 'active' : 'inactive' ?>"></span><?= ucfirst($mailStats['postfix_status']) ?></strong>
            </div>
            <div class="stat">
                <span>Dovecot (IMAP/POP3):</span>
                <strong><span class="status-indicator status-<?= $mailStats['dovecot_status'] === 'active' ? 'active' : 'inactive' ?>"></span><?= ucfirst($mailStats['dovecot_status']) ?></strong>
            </div>
            <div class="stat">
                <span>OpenDKIM:</span>
                <strong><span class="status-indicator status-<?= $mailStats['opendkim_status'] === 'active' ? 'active' : 'inactive' ?>"></span><?= ucfirst($mailStats['opendkim_status']) ?></strong>
            </div>
            
            <div class="stat">
                <span>Weekly Stats:</span>
                <strong><?= $mailStats['delivered_week'] ?> delivered, <?= $mailStats['sent_week'] ?> sent</strong>
            </div>
            
            <?php if ($mailStats['rejected_today'] > 0): ?>
            <div class="alert alert-warning">
                ⚠️ <?= $mailStats['rejected_today'] ?> emails rejected today
            </div>
            <?php endif; ?>
            
            <h3>Recent Activity:</h3>
            <div class="activity">
                <?php if (empty($mailStats['recent_activity'])): ?>
                    <em>No recent mail activity</em>
                <?php else: ?>
                    <?php foreach ($mailStats['recent_activity'] as $line): ?>
                        <?= htmlspecialchars($line) ?><br>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <h3>Mail Usage by User (Today)</h3>
            <div class="activity">
                <?php if (empty($mailStats['mail_users'])): ?>
                    <em>No mail users found</em>
                <?php else: ?>
                    <?php foreach ($mailStats['user_mail_stats'] as $user => $user_stats): ?>
                        <strong><?= htmlspecialchars($user) ?>:</strong>
                        Sent: <?= $user_stats['sent'] ?>, Delivered: <?= $user_stats['delivered'] ?><br>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Web Server Statistics -->
        <div class="card">
            <h2>🌐 Web Server Status</h2>
            
            <div class="stat">
                <span>Nginx Status:</span>
                <strong><span class="status-indicator status-<?= $webStats['nginx_status'] === 'active' ? 'active' : 'inactive' ?>"></span><?= ucfirst($webStats['nginx_status']) ?></strong>
            </div>
            
            <div class="stat">
                <span>Active HTTP/HTTPS Connections:</span>
                <strong><?= $webStats['nginx_connections'] ?></strong>
            </div>
            
            <h3>SSL Certificates</h3>
            <?php foreach ($webStats['ssl_status'] as $domain => $ssl): ?>
            <div class="stat">
                <span><?= $domain ?>:</span>
                <strong><span class="status-indicator status-<?= $ssl['status'] === 'good' ? 'active' : ($ssl['status'] === 'warning' ? 'warning' : 'inactive') ?>"></span>
                Expires <?= $ssl['expires'] ?> (<?= $ssl['days_left'] ?> days)</strong>
            </div>
            <?php if ($ssl['days_left'] < 30): ?>
            <div class="alert <?= $ssl['days_left'] < 7 ? 'alert-danger' : 'alert-warning' ?>">
                <?= $ssl['days_left'] < 7 ? '🚨' : '⚠️' ?> SSL certificate for <?= $domain ?> expires in <?= $ssl['days_left'] ?> days!
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
            
            <div class="stat">
                <span>Access Log Size:</span>
                <strong><?= formatBytes($webStats['access_log_size']) ?></strong>
            </div>
            
            <div class="stat">
                <span>Error Log Size:</span>
                <strong><?= formatBytes($webStats['error_log_size']) ?></strong>
            </div>
            
            <?php if ($webStats['errors_today'] > 0): ?>
            <div class="alert alert-warning">
                ⚠️ <?= $webStats['errors_today'] ?> errors logged today
            </div>
            
            <h3>Recent Nginx Errors (Today):</h3>
            <div class="activity">
                <?php if (empty($webStats['recent_errors'])): ?>
                    <em>No errors found for today</em>
                <?php else: ?>
                    <?php foreach ($webStats['recent_errors'] as $error): ?>
                        <?= htmlspecialchars($error) ?><br>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Security Monitoring -->
        <div class="card">
            <h2>🔒 Security Status</h2>
            
            <div class="stat">
                <span>Firewall (UFW):</span>
                <strong><span class="status-indicator status-<?= $securityStats['ufw_status'] === 'active' ? 'active' : 'inactive' ?>"></span><?= ucfirst($securityStats['ufw_status']) ?></strong>
            </div>
            
            <div class="stat">
                <span>SSH Connections:</span>
                <strong><?= $securityStats['ssh_connections'] ?></strong>
            </div>
            
            <div class="stat">
                <span>Failed Logins Today:</span>
                <strong><?= $securityStats['failed_logins_today'] ?></strong>
            </div>
            
            <?php if ($securityStats['failed_logins_today'] > 10): ?>
            <div class="alert alert-warning">
                ⚠️ High number of failed login attempts detected!
            </div>
            <?php endif; ?>
            
            <?php if (!empty($securityStats['disk_alerts'])): ?>
            <div class="alert alert-danger">
                🚨 Disk Space Alerts:
                <?php foreach ($securityStats['disk_alerts'] as $alert): ?>
                    <br>• <?= $alert ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <h3>Recent Logins:</h3>
            <div class="activity">
                <?php foreach ($securityStats['recent_logins'] as $login): ?>
                    <?= htmlspecialchars($login) ?><br>
                <?php endforeach; ?>
            </div>
        </div>
        
    </div>
</body>
</html>
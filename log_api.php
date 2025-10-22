<?php
// Security headers
header('Content-Type: text/plain; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Access-Control-Allow-Origin: https://admin.arkturian.com'); // Allow requests only from your admin domain

// Whitelist of allowed logs
$allowedLogs = [
    'api' => 'journalctl -u api-arkturian.service -n 200 --no-pager',
    'nginx_access' => 'tail -n 200 /var/log/nginx/access.log',
    'nginx_error' => 'tail -n 200 /var/log/nginx/error.log',
    'syslog' => 'tail -n 200 /var/log/syslog'
];

$log = isset($_GET['log']) ? $_GET['log'] : 'api';

// Validate the requested log against the whitelist
if (!array_key_exists($log, $allowedLogs)) {
    header('HTTP/1.0 400 Bad Request');
    die('Invalid log specified.');
}

// Execute the command and output the result
$command = $allowedLogs[$log];
$output = shell_exec($command . " 2>&1"); // Also capture stderr

echo htmlspecialchars($output, ENT_QUOTES, 'UTF-8');

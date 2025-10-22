<?php
header('Content-Type: application/json');

// Whitelist of allowed actions
$allowedActions = ['restart_api', 'truncate_log', 'vacuum_journal'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if (!in_array($action, $allowedActions)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if ($action === 'restart_api') {
    // Execute the command with the secure sudo rule
    $command = 'sudo /bin/systemctl restart api-arkturian.service';
    $output = shell_exec($command . ' 2>&1');
    
    if ($output === null || $output === '') {
        $response = ['status' => 'success', 'message' => 'API service restart command issued successfully.'];
    } else {
        http_response_code(500);
        $response = ['status' => 'error', 'message' => 'Failed to issue restart command.', 'output' => $output];
    }
}

if ($action === 'truncate_log') {
    $log = isset($_REQUEST['log']) ? $_REQUEST['log'] : '';
    
    // Whitelist of truncatable logs
    $truncatableLogs = [
        'nginx_access' => '/var/log/nginx/access.log',
        'nginx_error'  => '/var/log/nginx/error.log',
        'syslog'       => '/var/log/syslog',
    ];

    if (array_key_exists($log, $truncatableLogs)) {
        $filePath = $truncatableLogs[$log];
        $command = 'sudo /usr/bin/truncate -s 0 ' . $filePath;
        $output = shell_exec($command . ' 2>&1');

        if ($output === null || $output === '') {
            $response = ['status' => 'success', 'message' => "Log file '{$log}' truncated successfully."];
        } else {
            http_response_code(500);
            $response = ['status' => 'error', 'message' => "Failed to truncate log file '{$log}'.", 'output' => $output];
        }
    } else {
        http_response_code(400);
        $response = ['status' => 'error', 'message' => 'Invalid or non-truncatable log specified.'];
    }
}

if ($action === 'vacuum_journal') {
    $command = 'sudo /usr/bin/journalctl --vacuum-size=10M';
    $output = shell_exec($command . ' 2>&1');

    if (strpos($output, 'Vacuuming done') !== false) {
        $response = ['status' => 'success', 'message' => 'System journal vacuumed successfully.', 'output' => $output];
    } else {
        http_response_code(500);
        $response = ['status' => 'error', 'message' => 'Failed to vacuum system journal.', 'output' => $output];
    }
}

echo json_encode($response);

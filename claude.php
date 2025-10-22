<?php
session_start();

// Check authentication
if (!isset($_SESSION['authenticated'])) {
    http_response_code(403);
    echo 'Authentication required';
    exit;
}

// Check if prompt is provided
if (!isset($_POST['prompt']) || empty(trim($_POST['prompt']))) {
    echo 'No prompt provided';
    exit;
}

$prompt = trim($_POST['prompt']);

// Security: Basic prompt validation
$dangerous_commands = ['rm -rf', 'sudo rm', 'mkfs', 'dd if=', 'shutdown', 'reboot', 'halt'];
foreach ($dangerous_commands as $cmd) {
    if (strpos(strtolower($prompt), $cmd) !== false) {
        echo 'Security Error: Dangerous command detected and blocked.';
        exit;
    }
}

// Log the request
$logEntry = date('Y-m-d H:i:s') . " - User executed Claude prompt: " . substr($prompt, 0, 100) . "\n";
file_put_contents('/var/log/claude-admin.log', $logEntry, FILE_APPEND | LOCK_EX);

// Execute Claude Code
$escapedPrompt = escapeshellarg($prompt);

// Run as root user via sudo (allowed in sudoers)
$command = "sudo -n -u root /usr/bin/claude --print $escapedPrompt 2>&1";

// Execute the command with explicit timeout handling
set_time_limit(120); // 2 minutes PHP timeout
$output = shell_exec($command);

// Check if command timed out or failed
if ($output === null || $output === '') {
    // Try to get more detailed error info
    $errorCommand = "sudo -n -u root /usr/bin/claude --help 2>&1";
    $testOutput = shell_exec($errorCommand);
    
    if ($testOutput === null) {
        echo 'Error: Claude command not accessible from web server environment.';
    } else {
        echo 'Error: Command timed out after 1 minute or returned empty output.';
    }
    exit;
}

// Format and return the output
echo '<pre>' . htmlspecialchars($output) . '</pre>';

// Show response div
echo '<script>document.getElementById("claude-response").style.display = "block";</script>';
?>
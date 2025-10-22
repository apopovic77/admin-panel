<?php
// Security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Access-Control-Allow-Origin: https://admin.arkturian.com');

// Config: API base and admin API key
$apiBase = getenv('API_BASE_URL') ?: 'https://api.arkturian.com';
$apiKey = getenv('API_KEY') ?: 'Inetpass1';

// Inputs
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$appName = isset($_GET['app_name']) ? $_GET['app_name'] : null;
$level = isset($_GET['level']) ? $_GET['level'] : null;
$userOnly = isset($_GET['user_only']) ? ($_GET['user_only'] === '1' || strtolower($_GET['user_only']) === 'true') : false;

// Build query string
$params = [ 'limit' => max(1, min(1000, $limit)) ];
if ($appName) $params['app_name'] = $appName;
if ($level) $params['level'] = $level;
if ($userOnly) $params['user_only'] = 'true';
$query = http_build_query($params);

// Perform request to FastAPI
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, rtrim($apiBase, '/') . '/logs' . ($query ? ('?' . $query) : ''));
curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'X-API-KEY: ' . $apiKey ]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($resp === false) {
    http_response_code(502);
    echo json_encode([ 'error' => 'Proxy request failed', 'detail' => $err ]);
    exit;
}

http_response_code($httpCode ?: 200);
echo $resp;
?>


<?php

function get_app_config(): array {
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');

    $defaults = [
        'api_base_url' => getenv('API_BASE_URL') ?: 'https://api.arkturian.com',
        'api_storage_base_url' => getenv('API_STORAGE_BASE_URL') ?: 'https://api-storage.arkturian.com',
        'api_ai_base_url' => getenv('API_AI_BASE_URL') ?: 'https://api-ai.arkturian.com',
    ];

    $hostOverrides = [
        'arkserver' => [
            'api_base_url' => getenv('API_BASE_URL_ARKSERVER') ?: 'https://api.arkserver.arkturian.com',
            'api_storage_base_url' => getenv('API_STORAGE_BASE_URL_ARKSERVER') ?: 'https://api-storage.arkserver.arkturian.com',
            'api_ai_base_url' => getenv('API_AI_BASE_URL_ARKSERVER') ?: 'https://api-ai.arkserver.arkturian.com',
        ],
    ];

    foreach ($hostOverrides as $needle => $override) {
        if ($host && strpos($host, $needle) !== false) {
            $config = array_merge($defaults, array_filter($override, 'strlen'));
            return $config;
        }
    }

    $config = $defaults;
    return $config;
}

function app_config(string $key, string $fallback = ''): string {
    $config = get_app_config();
    return $config[$key] ?? $fallback;
}

function js_config(string $key, string $fallback = ''): string {
    return htmlspecialchars(app_config($key, $fallback), ENT_QUOTES);
}

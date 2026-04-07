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
        'share_base_url' => getenv('SHARE_BASE_URL') ?: 'https://share.arkturian.com',
        'admin_base_url' => getenv('ADMIN_BASE_URL') ?: 'https://admin.arkturian.com',
        'oneal_api_base_url' => getenv('ONEAL_API_BASE_URL') ?: 'https://oneal-api.arkturian.com',
        'artrack_api_base_url' => getenv('ARTRACK_API_BASE_URL') ?: 'https://api-artrack.arkturian.com',
    ];

    // NOTE: Order matters! More specific patterns must come first
    $hostOverrides = [
        'pdrei' => [
            // Jascha's pdrei.arkturian.com instance — own backend stack
            'api_base_url' => getenv('API_BASE_URL_PDREI') ?: 'https://content-api.pdrei.arkturian.com',
            'api_storage_base_url' => getenv('API_STORAGE_BASE_URL_PDREI') ?: 'https://storage-api.pdrei.arkturian.com',
            'api_ai_base_url' => getenv('API_AI_BASE_URL_PDREI') ?: 'https://api-ai.arkturian.com',
            'share_base_url' => getenv('SHARE_BASE_URL_PDREI') ?: 'https://content.pdrei.arkturian.com',
            'admin_base_url' => getenv('ADMIN_BASE_URL_PDREI') ?: 'https://admin.pdrei.arkturian.com',
            'oneal_api_base_url' => getenv('ONEAL_API_BASE_URL_PDREI') ?: 'https://oneal-api.arkturian.com',
            'artrack_api_base_url' => getenv('ARTRACK_API_BASE_URL_PDREI') ?: 'https://api-artrack.arkturian.com',
        ],
        'gsgbot' => [
            // O'Neal aiserver - uses local nginx reverse proxy
            'api_base_url' => getenv('API_BASE_URL_GSGBOT') ?: 'https://gsgbot.arkturian.com/gsg-api',
            'api_storage_base_url' => getenv('API_STORAGE_BASE_URL_GSGBOT') ?: 'https://gsgbot.arkturian.com/storage-api',
            'api_ai_base_url' => getenv('API_AI_BASE_URL_GSGBOT') ?: 'https://gsgbot.arkturian.com/api-ai',
            'share_base_url' => getenv('SHARE_BASE_URL_GSGBOT') ?: 'https://gsgbot.arkturian.com/share',
            'admin_base_url' => getenv('ADMIN_BASE_URL_GSGBOT') ?: 'https://gsgbot.arkturian.com/admin',
            'oneal_api_base_url' => getenv('ONEAL_API_BASE_URL_GSGBOT') ?: 'https://gsgbot.arkturian.com/oneal-api',
            'artrack_api_base_url' => getenv('ARTRACK_API_BASE_URL_GSGBOT') ?: 'https://gsgbot.arkturian.com/artrack-api',
        ],
        'arkserver' => [
            'api_base_url' => getenv('API_BASE_URL_ARKSERVER') ?: 'https://api.arkserver.arkturian.com',
            'api_storage_base_url' => getenv('API_STORAGE_BASE_URL_ARKSERVER') ?: 'https://api-storage.arkserver.arkturian.com',
            'api_ai_base_url' => getenv('API_AI_BASE_URL_ARKSERVER') ?: 'https://api-ai.arkserver.arkturian.com',
            'share_base_url' => getenv('SHARE_BASE_URL_ARKSERVER') ?: 'https://share.arkserver.arkturian.com',
            'admin_base_url' => getenv('ADMIN_BASE_URL_ARKSERVER') ?: 'https://admin.arkserver.arkturian.com',
            'oneal_api_base_url' => getenv('ONEAL_API_BASE_URL_ARKSERVER') ?: 'https://oneal-api.arkserver.arkturian.com',
            'artrack_api_base_url' => getenv('ARTRACK_API_BASE_URL_ARKSERVER') ?: 'https://api-artrack.arkserver.arkturian.com',
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

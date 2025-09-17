<?php
// Lygos payment configuration

// Prefer environment variables; fallback to placeholders
$LYGOS_API_KEY = getenv('LYGOS_API_KEY') ?: ($_ENV['LYGOS_API_KEY'] ?? '');
$LYGOS_API_BASE = 'https://api.lygosapp.com/v1';

// Helper: build absolute URL from relative path
if (!function_exists('app_url')) {
    function app_url(string $path = ''): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
        $url = $scheme . '://' . $host . ($base ? $base : '');
        if ($path) {
            $url .= '/' . ltrim($path, '/');
        }
        return $url;
    }
}

// Simple HTTP POST JSON using cURL
if (!function_exists('http_post_json')) {
    function http_post_json(string $url, array $headers, array $payload): array {
        $ch = curl_init($url);
        $json = json_encode($payload);
        $headers[] = 'Content-Type: application/json';
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
        ]);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['status' => $status, 'body' => $body, 'error' => $err];
    }
}



<?php

function rl_key(string $bucket, string $identifier): string
{
    return 'rl:' . $bucket . ':' . sha1($identifier);
}

function rate_limit_check(string $bucket, string $identifier, int $max, int $windowSeconds): bool
{
    if (!isset($_SESSION)) {
        session_start();
    }
    $path = sys_get_temp_dir() . '/lain_rl';
    if (!is_dir($path)) {
        @mkdir($path, 0777, true);
    }
    $file = $path . '/' . rl_key($bucket, $identifier) . '.json';

    $now = time();
    $data = ['start' => $now, 'count' => 0];
    if (is_file($file)) {
        $decoded = json_decode((string)file_get_contents($file), true);
        if (is_array($decoded) && isset($decoded['start'], $decoded['count'])) {
            if ($now - (int)$decoded['start'] < $windowSeconds) {
                $data = $decoded;
            }
        }
    }

    $data['count'] = (int)$data['count'] + 1;

    $allowed = $data['count'] <= $max;
    file_put_contents($file, json_encode($data), LOCK_EX);

    return $allowed;
}

function client_identifier(): string
{
    // Di balik Cloudflare Tunnel, REMOTE_ADDR = IP cloudflared (sama untuk semua).
    // CF-Connecting-IP di-set oleh Cloudflare dan tidak bisa dilewati oleh client
    // karena semua request terpaksa lewat tunnel.
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
        ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return $ip . '|' . $ua;
}

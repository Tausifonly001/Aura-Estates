<?php

class Analytics {
    private static $enabled = null;
    private static $apiKey = '';
    private static $host = 'https://us.posthog.com';

    private static function init() {
        if (self::$enabled !== null) return;
        $env = parse_ini_file(__DIR__ . '/../../.env') ?: [];
        self::$apiKey = getenv('POSTHOG_API_KEY') ?: ($env['POSTHOG_API_KEY'] ?? $env['POSTHOG_KEY'] ?? '');
        self::$enabled = !empty(self::$apiKey);
        $host = getenv('POSTHOG_HOST') ?: ($env['POSTHOG_HOST'] ?? '');
        if (!empty($host)) {
            self::$host = rtrim($host, '/');
        }
    }

    public static function capture($distinctId, $event, $properties = []) {
        self::init();
        if (!self::$enabled) return false;
        return self::send([
            'api_key' => self::$apiKey,
            'event' => $event,
            'distinct_id' => $distinctId,
            'properties' => array_merge([
                '$lib' => 'aura-estates-php',
                '$lib_version' => '1.0.0',
            ], $properties),
        ]);
    }

    public static function identify($distinctId, $properties = []) {
        self::init();
        if (!self::$enabled) return false;
        return self::send([
            'api_key' => self::$apiKey,
            'event' => '$identify',
            'distinct_id' => $distinctId,
            '$set' => $properties,
        ]);
    }

    private static function send($payload) {
        $json = json_encode($payload);
        $ch = curl_init(self::$host . '/capture/');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Content-Length: ' . strlen($json)],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("PostHog error: HTTP $httpCode - $result");
            return false;
        }
        return true;
    }
}

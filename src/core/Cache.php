<?php
class Cache {
    private static $dir = null;
    private static $defaultTTL = 300;

    private static function dir() {
        if (self::$dir === null) {
            self::$dir = __DIR__ . '/../../storage/cache';
            if (!is_dir(self::$dir)) {
                mkdir(self::$dir, 0755, true);
            }
        }
        return self::$dir;
    }

    public static function get($key) {
        $path = self::dir() . '/' . self::hash($key) . '.cache';
        if (!file_exists($path)) return null;
        $data = file_get_contents($path);
        $entry = @unserialize($data);
        if (!$entry || !isset($entry['expires']) || !isset($entry['value'])) {
            @unlink($path);
            return null;
        }
        if (time() > $entry['expires']) {
            @unlink($path);
            return null;
        }
        return $entry['value'];
    }

    public static function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? self::$defaultTTL;
        $path = self::dir() . '/' . self::hash($key) . '.cache';
        $entry = serialize(['expires' => time() + $ttl, 'value' => $value]);
        file_put_contents($path, $entry, LOCK_EX);
    }

    public static function remember($key, $callback, $ttl = null) {
        $cached = self::get($key);
        if ($cached !== null) return $cached;
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    public static function forget($key) {
        $path = self::dir() . '/' . self::hash($key) . '.cache';
        if (file_exists($path)) @unlink($path);
    }

    public static function flush() {
        $files = glob(self::dir() . '/*.cache');
        foreach ($files as $f) @unlink($f);
    }

    private static function hash($key) {
        return hash('xxh3', $key);
    }
}

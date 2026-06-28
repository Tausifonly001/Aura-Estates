<?php
require_once __DIR__ . '/config/database.php';

function content($page, $section, $key, $default = '') {
    static $cache = [];
    $ck = "$page/$section/$key";
    if (!isset($cache[$ck])) {
        try {
            $db = (new Database())->getConnection();
            $stmt = $db->prepare("SELECT value FROM site_content WHERE page = ? AND section = ? AND key_name = ?");
            $stmt->execute([$page, $section, $key]);
            $row = $stmt->fetch();
            $cache[$ck] = $row ? $row['value'] : $default;
        } catch (Exception $e) {
            $cache[$ck] = $default;
        }
    }
    return $cache[$ck];
}

function cache_buster() {
    return '?v=' . substr(md5(__FILE__), 0, 8);
}

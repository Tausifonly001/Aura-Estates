<?php
class SiteContent {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function get($page, $section = null, $keyName = null) {
        $sql = "SELECT * FROM site_content WHERE page = ?";
        $params = [$page];
        if ($section) { $sql .= " AND section = ?"; $params[] = $section; }
        if ($keyName) { $sql .= " AND key_name = ?"; $params[] = $keyName; }
        $sql .= " ORDER BY sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        if ($keyName && $section) return $rows[0]['value'] ?? null;
        if ($section) {
            $result = [];
            foreach ($rows as $r) $result[$r['key_name']] = $r['value'];
            return $result;
        }
        return $rows;
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM site_content ORDER BY page, section, sort_order");
        return $stmt->fetchAll();
    }

    public function set($page, $section, $keyName, $value, $type = 'text', $sortOrder = 0) {
        $stmt = $this->db->prepare("INSERT INTO site_content (page, section, key_name, value, type, sort_order) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value), type = VALUES(type), sort_order = VALUES(sort_order)");
        return $stmt->execute([$page, $section, $keyName, $value, $type, $sortOrder]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM site_content WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getPages() {
        $stmt = $this->db->query("SELECT DISTINCT page FROM site_content ORDER BY page");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getSections($page) {
        $stmt = $this->db->prepare("SELECT DISTINCT section FROM site_content WHERE page = ? ORDER BY section");
        $stmt->execute([$page]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

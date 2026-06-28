<?php
class Paginator {
    public static function build(array $params = []): array {
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = min(100, max(1, (int)($params['per_page'] ?? 20)));
        $search = trim($params['search'] ?? '');
        $sort = $params['sort'] ?? 'created_at';
        $order = strtoupper($params['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        $allowedSorts = $params['allowed_sorts'] ?? ['created_at', 'id', 'name', 'status', 'updated_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'created_at';
        $offset = ($page - 1) * $perPage;
        $filter = $params['filter'] ?? [];
        return compact('page', 'perPage', 'offset', 'search', 'sort', 'order', 'filter');
    }

    public static function searchClause($columns, $search): string {
        if (!$search) return '';
        $escaped = [];
        foreach ((array)$columns as $col) {
            $escaped[] = "$col LIKE :search";
        }
        return '(' . implode(' OR ', $escaped) . ')';
    }

    public static function bindSearch($stmt, $search) {
        if ($search) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
    }

    public static function paginatedResponse($stmt, $countStmt, $page, $perPage) {
        $records = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $records[] = $row;
        }
        $total = (int)$countStmt->fetchColumn();
        $pages = (int)ceil($total / max($perPage, 1));
        return [
            'records' => $records,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => $pages
            ]
        ];
    }
}

<?php
class Response {
    public static function json($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = null, $message = 'OK', $code = 200) {
        self::json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    public static function error($message = 'Error', $code = 400, $errors = null) {
        $payload = ['success' => false, 'message' => $message];
        if ($errors !== null) $payload['errors'] = $errors;
        self::json($payload, $code);
    }

    public static function paginated($records, $total, $page, $perPage) {
        self::json([
            'success' => true,
            'data' => [
                'records' => $records,
                'pagination' => [
                    'total' => (int)$total,
                    'page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'pages' => (int)ceil($total / max($perPage, 1))
                ]
            ]
        ]);
    }
}

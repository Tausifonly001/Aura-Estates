<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';

Middleware::api();
Middleware::auth();

$database = new Database();
$db = $database->getConnection();

$data = Middleware::getJsonInput();
$action = $data->action ?? '';
$entity = $data->entity ?? '';
$ids = $data->ids ?? [];

if (!$action || !$entity || empty($ids) || !is_array($ids)) {
    Response::error('action, entity, and ids[] are required.', 400);
}

$allowedEntities = [
    'maintenance' => ['permission' => 'maintenance_update', 'table' => 'maintenance_requests'],
    'bookings' => ['permission' => 'bookings_update', 'table' => 'amenity_bookings'],
    'rentals' => ['permission' => 'rentals_terminate', 'table' => 'rentals'],
];

if (!isset($allowedEntities[$entity])) {
    Response::error('Invalid entity.', 400);
}

$cfg = $allowedEntities[$entity];
Middleware::auth($cfg['permission']);

$idPlaceholders = implode(',', array_fill(0, count($ids), '?'));

switch ($action) {
    case 'delete':
        Middleware::auth(str_replace('_update', '_delete', $cfg['permission']));
        $stmt = $db->prepare("DELETE FROM {$cfg['table']} WHERE id IN ($idPlaceholders)");
        $stmt->execute($ids);
        AuditLogger::log('batch_delete', $entity, null, 'Deleted ' . count($ids) . ' ' . $entity);
        Response::success(['deleted' => $stmt->rowCount()], 'Batch delete completed.');
        break;

    case 'update_status':
        $status = $data->status ?? '';
        if (!$status) Response::error('status field required for update_status.', 400);

        if ($entity === 'maintenance' && $status === 'completed') {
            $stmt = $db->prepare("UPDATE {$cfg['table']} SET status = ?, resolved_at = NOW() WHERE id IN ($idPlaceholders)");
        } else {
            $stmt = $db->prepare("UPDATE {$cfg['table']} SET status = ? WHERE id IN ($idPlaceholders)");
        }
        $stmt->execute(array_merge([$status], $ids));
        AuditLogger::log('batch_update', $entity, null, 'Updated ' . count($ids) . ' ' . $entity . ' to status=' . $status);
        Response::success(['updated' => $stmt->rowCount()], 'Batch update completed.');
        break;

    case 'assign':
        if ($entity !== 'maintenance') Response::error('Assign only supported for maintenance.', 400);
        $assignedTo = $data->assigned_to ?? null;
        $stmt = $db->prepare("UPDATE {$cfg['table']} SET assigned_to = ? WHERE id IN ($idPlaceholders)");
        $stmt->execute(array_merge([$assignedTo], $ids));
        AuditLogger::log('batch_assign', $entity, null, 'Assigned ' . count($ids) . ' ' . $entity . ' to user=' . ($assignedTo ?? 'none'));
        Response::success(['updated' => $stmt->rowCount()], 'Batch assign completed.');
        break;

    default:
        Response::error('Invalid action. Allowed: delete, update_status, assign.', 400);
}

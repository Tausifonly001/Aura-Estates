<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/core/AuditLogger.php';
require_once __DIR__ . '/../src/services/ResendService.php';
require_once __DIR__ . '/../src/services/EmailService.php';
require_once __DIR__ . '/../src/controllers/AmenityController.php';

Middleware::api();

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'System temporarily unavailable. Please try again later.']);
    exit;
}
$controller = new AmenityController($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            Middleware::ownerOrPermission('user_id', 'bookings_view');
            $result = json_decode($controller->getUserBookings($_GET['user_id']), true);
            Response::success($result);
        } elseif (isset($_GET['stats'])) {
            Middleware::auth('bookings_view');
            $result = json_decode($controller->getStats(), true);
            Response::success($result);
        } else {
            Middleware::auth('bookings_view');
            $result = json_decode($controller->getAllBookings(), true);
            Response::success($result);
        }
        break;

    case 'POST':
        Middleware::auth();
        $data = Middleware::getJsonInput();
        $output = $controller->createBooking($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            $bookingId = $db->lastInsertId();
            AuditLogger::log('create', 'amenity_booking', $bookingId, 'Booking created');
            try {
                $user = Auth::getUser();
                if ($user && !empty($user['email'])) {
                    ResendService::sendBookingConfirmation($user['email'], $user['name'] ?? 'Tenant', $data->amenity_name ?? 'Amenity', $data->booking_date ?? '', $data->start_time ?? '');
                    EmailService::sendBookingAlert($user['name'] ?? 'Tenant', $user['email'], $data->amenity_name ?? 'Amenity', $data->booking_date ?? '', $data->start_time ?? '');
                }
            } catch (Exception $e) { error_log("Booking email: " . $e->getMessage()); }
            Response::success(null, $result['message'] ?? 'Booking confirmed.', $code);
        } elseif ($code === 409) {
            Response::error($result['message'] ?? 'Conflict.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to create booking.', $code);
        }
        break;

    case 'PUT':
        Middleware::auth('bookings_update');
        $data = Middleware::getJsonInput();
        $output = $controller->updateBookingStatus($data);
        $code = http_response_code();
        $result = json_decode($output, true);
        if ($code >= 200 && $code < 300) {
            AuditLogger::log('update', 'amenity_booking', $data->id ?? null, 'Booking status updated');
            try {
                $stmt = $db->prepare("SELECT u.email, u.name FROM amenity_bookings b JOIN users u ON b.user_id = u.id WHERE b.id = ?");
                $stmt->execute([$data->id]);
                $bUser = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($bUser && !empty($bUser['email'])) {
                    ResendService::sendBookingConfirmation($bUser['email'], $bUser['name'], 'Booking', date('Y-m-d'), '');
                }
            } catch (Exception $e) { error_log("Booking status email: " . $e->getMessage()); }
            Response::success(null, $result['message'] ?? 'Booking status updated.', $code);
        } else {
            Response::error($result['message'] ?? 'Unable to update.', $code);
        }
        break;

    case 'DELETE':
        Middleware::auth('bookings_delete');
        if (isset($_GET['id'])) {
            $output = $controller->deleteBooking($_GET['id']);
            $code = http_response_code();
            $result = json_decode($output, true);
            if ($code >= 200 && $code < 300) {
                AuditLogger::log('delete', 'amenity_booking', $_GET['id'], 'Booking cancelled');
                Response::success(null, $result['message'] ?? 'Booking cancelled.', $code);
            } else {
                Response::error($result['message'] ?? 'Unable to delete.', $code);
            }
        }
        break;
}

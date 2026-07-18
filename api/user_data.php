<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/controllers/RentalController.php';
require_once __DIR__ . '/../src/controllers/MaintenanceController.php';
require_once __DIR__ . '/../src/models/AmenityBooking.php';

Middleware::api();
Middleware::auth();

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'System temporarily unavailable. Please try again later.']);
    exit;
}
$rentalCtrl = new RentalController($db);
$uid = $_SESSION['user_id'];

$type = $_GET['type'] ?? '';

switch($type) {
    case 'rentals':
        $result = json_decode($rentalCtrl->getByUser($uid), true);
        Response::success($result);
        break;

    case 'maintenance':
        $ctrl = new MaintenanceController($db);
        $result = json_decode($ctrl->getByUser($uid), true);
        Response::success($result);
        break;

    case 'bookings':
        $stmt = $db->prepare("SELECT ab.*, a.name as amenity_name, a.description as amenity_desc,
                              a.location as amenity_location, a.image, a.capacity
                              FROM amenity_bookings ab
                              LEFT JOIN amenities a ON ab.amenity_id = a.id
                              WHERE ab.user_id = ?
                              ORDER BY ab.booking_date DESC, ab.check_in_time DESC");
        $stmt->execute([$uid]);
        $records = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $records[] = $row;
        }
        Response::success(['records' => $records]);
        break;

    case 'available_properties':
        $stmt = $db->prepare("SELECT * FROM properties WHERE is_available = 1 OR status = 'available' OR status IS NULL ORDER BY created_at DESC");
        $stmt->execute();
        $records = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['is_available'] = 1;
            $row['status'] = 'available';
            $row['main_image'] = !empty($row['main_image']) ? $row['main_image'] : (!empty($row['image']) ? $row['image'] : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800');
            $row['image'] = $row['main_image'];
            $records[] = $row;
        }
        Response::success(['records' => $records]);
        break;

    default:
        $rentals = json_decode($rentalCtrl->getByUser($uid), true)['records'] ?? [];
        Response::success([
            'rentals' => $rentals,
            'user' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email']
            ]
        ]);
        break;
}

<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/core/Cache.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/controllers/MaintenanceController.php';
require_once __DIR__ . '/../src/controllers/AmenityController.php';

Middleware::api();
Middleware::auth('dashboard_view');

$database = new Database();
$db = $database->getConnection();

$data = Cache::remember('dashboard_kpi', function() use ($db) {
    $maintenanceCtrl = new MaintenanceController($db);
    $amenityCtrl = new AmenityController($db);

    $maintenanceStats = json_decode($maintenanceCtrl->getStats(), true);
    $amenityStats = json_decode($amenityCtrl->getStats(), true);

    $propQuery = "SELECT COUNT(*) as total FROM properties";
    $propStmt = $db->prepare($propQuery);
    $propStmt->execute();
    $propStats = $propStmt->fetch(PDO::FETCH_ASSOC);

    $recentQuery = "SELECT m.*, p.title as property_title FROM maintenance_requests m
                    LEFT JOIN properties p ON m.property_id = p.id
                    ORDER BY m.created_at DESC LIMIT 5";
    $recentStmt = $db->prepare($recentQuery);
    $recentStmt->execute();
    $recentRequests = [];
    while ($row = $recentStmt->fetch(PDO::FETCH_ASSOC)) {
        $recentRequests[] = $row;
    }

    $todayQuery = "SELECT b.*, a.name as amenity_name FROM amenity_bookings b
                   LEFT JOIN amenities a ON b.amenity_id = a.id
                   WHERE b.booking_date = CURDATE()
                   ORDER BY b.check_in_time ASC";
    $todayStmt = $db->prepare($todayQuery);
    $todayStmt->execute();
    $todayBookings = [];
    while ($row = $todayStmt->fetch(PDO::FETCH_ASSOC)) {
        $todayBookings[] = $row;
    }

    $inquiryQuery = "SELECT
        (SELECT COUNT(*) FROM inquiries WHERE status = 'pending') as pending_inquiries,
        (SELECT COUNT(*) FROM inquiries) as total_inquiries";
    $inquiryStmt = $db->prepare($inquiryQuery);
    $inquiryStmt->execute();
    $inquiryStats = $inquiryStmt->fetch(PDO::FETCH_ASSOC);

    $rentalQuery = "SELECT
        (SELECT COUNT(*) FROM rentals WHERE status = 'active') as active_rentals,
        (SELECT COUNT(*) FROM rentals) as total_rentals";
    $rentalStmt = $db->prepare($rentalQuery);
    $rentalStmt->execute();
    $rentalStats = $rentalStmt->fetch(PDO::FETCH_ASSOC);

    $conflictQuery = "SELECT COUNT(*) as today_conflicts FROM amenity_bookings
                      WHERE booking_date = CURDATE() AND status = 'cancelled'";
    $conflictStmt = $db->prepare($conflictQuery);
    $conflictStmt->execute();
    $conflictStats = $conflictStmt->fetch(PDO::FETCH_ASSOC);

    return [
        'properties' => $propStats,
        'maintenance' => $maintenanceStats,
        'amenities' => $amenityStats,
        'inquiries' => $inquiryStats,
        'rentals' => $rentalStats,
        'kpirecent_maintenance_requests' => $recentRequests,
        'today_bookings' => $todayBookings,
        'kpi' => [
            'avg_resolution_hours' => $maintenanceStats['avg_resolution_hours'],
            'completion_rate' => $maintenanceStats['completion_rate'],
            'overdue_count' => $maintenanceStats['overdue_count'],
            'today_conflicts' => $conflictStats['today_conflicts']
        ]
    ];
}, 60);

Response::success($data);

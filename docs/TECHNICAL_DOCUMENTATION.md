# Technical Documentation
**Real-Time Property Rental, Maintenance & Amenity Management Platform**

This document serves as the technical reference for the architecture, database schema, and implemented algorithms of the platform.

## System Architecture Overview

The platform uses a modular Client-Server Single Page Application (SPA) architecture to separate concerns, ensure high performance (sub-2 second response times), and provide a scalable foundation for future enhancements.

### 1. Frontend Architecture
- **Framework:** AngularJS 1.8
- **Styling:** Tailwind CSS for a clean, simple, and responsive UI.
- **Animations:** GSAP 3 (GreenSock Animation Platform) for smooth staggering and tab transitions.
- **Real-Time Integration:** The frontend utilizes AngularJS `$interval` polling every 5 seconds. This fetches data from the backend APIs silently, updating the scoped models if changes are detected. This achieves the "Real-Time Tracking" requirement for maintenance requests and amenity bookings without requiring complex WebSocket infrastructure.
- **Dashboards:** Both Tenant Portal (`user/dashboard.php`) and Admin Dashboard (`admin/dashboard.php`) are built as SPAs. The Admin Dashboard strictly consumes `/api/dashboard.php` for its aggregated metrics.

### 2. Backend Architecture
- **Language:** PHP 8+
- **Paradigm:** Object-Oriented MVC (Model-View-Controller) structure handling RESTful API requests.
- **Controllers:** Responsible for parsing JSON input, validating logic, and outputting standardized JSON responses. (e.g., `MaintenanceController.php`, `AmenityController.php`).
- **Authentication:** Session-based authentication ensuring secure data access based on role (Admin vs. Tenant).

### 3. Database Architecture
- **System:** MySQL (via PDO for security against SQL injection).
- **Integrity:** Strict relational schema utilizing Foreign Keys with `ON DELETE CASCADE` to ensure data consistency when entities (like properties or users) are removed.

---

## Conflict Prevention Algorithm (Amenities)
To achieve the KPI of **0 Amenity Booking Conflicts**, the system enforces strict overlap checking at the database level before committing any new booking.

```php
// Inside models/AmenityBooking.php -> checkConflict()
$query = "SELECT COUNT(*) as conflict_count FROM " . $this->table_name . "
          WHERE amenity_id = :amenity_id 
          AND booking_date = :booking_date
          AND status != 'cancelled'
          AND (
              (check_in_time <= :check_in_time AND check_out_time > :check_in_time) OR
              (check_in_time < :check_out_time AND check_out_time >= :check_out_time) OR
              (check_in_time >= :check_in_time AND check_out_time <= :check_out_time)
          )";
```
This algorithm ensures that no two active bookings for the same amenity on the same date can have overlapping check-in and check-out times.

---

## Database Schema Design

### `users`
- `id` (INT, PK, Auto Increment)
- `name` (VARCHAR)
- `email` (VARCHAR, Unique)
- `password` (VARCHAR, Hashed)
- `role` (ENUM: 'tenant', 'admin')

### `properties`
- `id` (INT, PK)
- `title` (VARCHAR)
- `location` (VARCHAR)
- `property_type` (ENUM: 'apartment', 'house', 'commercial')
- `monthly_rent` (DECIMAL)
- `status` (ENUM: 'available', 'rented', 'maintenance')

### `maintenance_requests`
- `id` (INT, PK)
- `property_id` (INT, FK -> properties.id)
- `user_id` (INT, FK -> users.id)
- `subject` (VARCHAR)
- `issue_description` (TEXT)
- `priority` (ENUM: 'low', 'medium', 'high', 'emergency')
- `status` (ENUM: 'pending', 'in_progress', 'completed')
- `created_at` (DATETIME)
- `resolution_date` (DATETIME, Nullable)

### `amenities`
- `id` (INT, PK)
- `property_id` (INT, FK -> properties.id)
- `name` (VARCHAR)
- `is_active` (BOOLEAN)

### `amenity_bookings`
- `id` (INT, PK)
- `amenity_id` (INT, FK -> amenities.id)
- `user_id` (INT, FK -> users.id)
- `booking_date` (DATE)
- `check_in_time` (TIME)
- `check_out_time` (TIME)
- `status` (ENUM: 'confirmed', 'checked_in', 'completed', 'cancelled')

---

## Deployment & Run Instructions

This project is built to run effortlessly on any standard LAMP stack (Linux, Apache, MySQL, PHP). 
For the assignment simulation (Localhost):

1. **Database Setup:** 
   Import the provided `database.sql` file into your local MySQL server (e.g., via phpMyAdmin) to instantly scaffold the schema and dummy data.
2. **Configuration:** 
   Update `config/database.php` with your local database credentials if they differ from the default (`root` / no password).
3. **Execution:** 
   Launch your local Apache server (e.g., XAMPP) and navigate to `http://localhost/aura-estates`. 
4. **Testing Real-Time features:**
   Open two browser windows. Log into the Tenant Portal in one, and the Admin Dashboard in the other. Create a new maintenance request as the Tenant, and watch the Admin Dashboard automatically update its metrics and lists within 5 seconds without refreshing.

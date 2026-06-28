<?php
include_once __DIR__ . '/src/config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    // 1. Create roles table
    $db->exec("CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        display_name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Create permissions table
    $db->exec("CREATE TABLE IF NOT EXISTS permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        display_name VARCHAR(100) NOT NULL,
        module VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Create role_permissions pivot table
    $db->exec("CREATE TABLE IF NOT EXISTS role_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_id INT NOT NULL,
        permission_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
        UNIQUE KEY unique_role_permission (role_id, permission_id)
    )");

    // 4. Add role_id to users table (keep old role column for backward compat)
    try {
        $db->exec("ALTER TABLE users ADD COLUMN role_id INT DEFAULT NULL AFTER role");
        $db->exec("ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL");
    } catch(PDOException $e) {
        // Column may already exist
    }

    // 5. Seed default roles
    $roles = [
        ['admin', 'Administrator', 'Full system access with all permissions'],
        ['property_manager', 'Property Manager', 'Manage properties, maintenance, amenities'],
        ['maintenance_staff', 'Maintenance Staff', 'View and update maintenance requests'],
        ['tenant', 'Tenant', 'Access tenant portal, submit requests, book amenities']
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO roles (name, display_name, description) VALUES (?, ?, ?)");
    foreach ($roles as $r) {
        $stmt->execute($r);
    }

    // 6. Seed permissions grouped by module
    $permissions = [
        ['properties_view', 'View Properties', 'properties'],
        ['properties_create', 'Create Properties', 'properties'],
        ['properties_edit', 'Edit Properties', 'properties'],
        ['properties_delete', 'Delete Properties', 'properties'],
        ['users_view', 'View Users', 'users'],
        ['users_create', 'Create Users', 'users'],
        ['users_edit', 'Edit Users', 'users'],
        ['users_delete', 'Delete Users', 'users'],
        ['maintenance_view', 'View Maintenance Requests', 'maintenance'],
        ['maintenance_create', 'Create Maintenance Requests', 'maintenance'],
        ['maintenance_update', 'Update Maintenance Requests', 'maintenance'],
        ['maintenance_delete', 'Delete Maintenance Requests', 'maintenance'],
        ['amenities_view', 'View Amenities', 'amenities'],
        ['amenities_create', 'Create Amenities', 'amenities'],
        ['amenities_edit', 'Edit Amenities', 'amenities'],
        ['amenities_delete', 'Delete Amenities', 'amenities'],
        ['bookings_view', 'View Bookings', 'bookings'],
        ['bookings_create', 'Create Bookings', 'bookings'],
        ['bookings_update', 'Update Booking Status', 'bookings'],
        ['bookings_delete', 'Delete Bookings', 'bookings'],
        ['inquiries_view', 'View Inquiries', 'inquiries'],
        ['inquiries_update', 'Update Inquiry Status', 'inquiries'],
        ['rentals_view', 'View Rentals', 'rentals'],
        ['rentals_create', 'Create Rentals', 'rentals'],
        ['rentals_terminate', 'Terminate Rentals', 'rentals'],
        ['dashboard_view', 'View Dashboard', 'dashboard'],
        ['settings_view', 'View Settings', 'settings'],
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO permissions (name, display_name, module) VALUES (?, ?, ?)");
    foreach ($permissions as $p) {
        $stmt->execute($p);
    }

    // 7. Assign all permissions to admin role (id=1)
    $adminRoleId = $db->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();
    $allPerms = $db->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
    
    $insertPerm = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    foreach ($allPerms as $permId) {
        $insertPerm->execute([$adminRoleId, $permId]);
    }

    // 8. Assign permissions to property_manager (role id=2)
    $pmRoleId = $db->query("SELECT id FROM roles WHERE name = 'property_manager'")->fetchColumn();
    $pmPerms = $db->query("SELECT id FROM permissions WHERE module IN ('properties', 'maintenance', 'amenities', 'bookings', 'dashboard', 'inquiries', 'rentals')")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($pmPerms as $permId) {
        $insertPerm->execute([$pmRoleId, $permId]);
    }

    // 9. Assign permissions to maintenance_staff (role id=3)
    $msRoleId = $db->query("SELECT id FROM roles WHERE name = 'maintenance_staff'")->fetchColumn();
    $msPerms = $db->query("SELECT id FROM permissions WHERE name IN ('maintenance_view', 'maintenance_update', 'dashboard_view')")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($msPerms as $permId) {
        $insertPerm->execute([$msRoleId, $permId]);
    }

    // 10. Assign permissions to tenant (role id=4)
    $tenantRoleId = $db->query("SELECT id FROM roles WHERE name = 'tenant'")->fetchColumn();
    $tenantPerms = $db->query("SELECT id FROM permissions WHERE name IN ('maintenance_create', 'bookings_view', 'bookings_create', 'properties_view', 'dashboard_view')")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tenantPerms as $permId) {
        $insertPerm->execute([$tenantRoleId, $permId]);
    }

    // 11. Update existing admin user to have admin role_id
    $stmt = $db->prepare("UPDATE users SET role_id = ? WHERE role = 'admin' AND (role_id IS NULL OR role_id = 0)");
    $stmt->execute([$adminRoleId]);
    
    // 12. Create a role_capabilities view for easy checking
    $db->exec("CREATE OR REPLACE VIEW user_permissions AS
        SELECT u.id as user_id, u.name as user_name, u.email, u.role as role_name,
               r.id as role_id, r.display_name as role_display,
               p.id as permission_id, p.name as permission_name, p.module
        FROM users u
        LEFT JOIN roles r ON (u.role_id = r.id) OR (u.role = r.name)
        LEFT JOIN role_permissions rp ON r.id = rp.role_id
        LEFT JOIN permissions p ON rp.permission_id = p.id
    ");

    echo "RBAC schema updated successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

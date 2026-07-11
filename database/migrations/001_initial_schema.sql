CREATE DATABASE IF NOT EXISTS aura_estates;
USE aura_estates;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO roles (name, display_name, description) VALUES
('admin', 'Administrator', 'Full system access with all permissions'),
('property_manager', 'Property Manager', 'Manage properties, maintenance, amenities'),
('maintenance_staff', 'Maintenance Staff', 'View and update maintenance requests'),
('tenant', 'Tenant', 'Access tenant portal, submit requests, book amenities');

CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO permissions (name, display_name, module) VALUES
('properties_view', 'View Properties', 'properties'),
('properties_create', 'Create Properties', 'properties'),
('properties_edit', 'Edit Properties', 'properties'),
('properties_delete', 'Delete Properties', 'properties'),
('users_view', 'View Users', 'users'),
('users_create', 'Create Users', 'users'),
('users_edit', 'Edit Users', 'users'),
('users_delete', 'Delete Users', 'users'),
('maintenance_view', 'View Maintenance Requests', 'maintenance'),
('maintenance_create', 'Create Maintenance Requests', 'maintenance'),
('maintenance_update', 'Update Maintenance Requests', 'maintenance'),
('maintenance_delete', 'Delete Maintenance Requests', 'maintenance'),
('amenities_view', 'View Amenities', 'amenities'),
('amenities_create', 'Create Amenities', 'amenities'),
('amenities_edit', 'Edit Amenities', 'amenities'),
('amenities_delete', 'Delete Amenities', 'amenities'),
('bookings_view', 'View Bookings', 'bookings'),
('bookings_create', 'Create Bookings', 'bookings'),
('bookings_update', 'Update Booking Status', 'bookings'),
('bookings_delete', 'Delete Bookings', 'bookings'),
('inquiries_view', 'View Inquiries', 'inquiries'),
('inquiries_update', 'Update Inquiry Status', 'inquiries'),
('rentals_view', 'View Rentals', 'rentals'),
('rentals_create', 'Create Rentals', 'rentals'),
('rentals_terminate', 'Terminate Rentals', 'rentals'),
('dashboard_view', 'View Dashboard', 'dashboard'),
('settings_view', 'View Settings', 'settings');

CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission_id)
);

-- Assign all permissions to admin
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'admin';

-- Assign property management permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'property_manager' AND p.module IN ('properties', 'maintenance', 'amenities', 'bookings', 'dashboard', 'inquiries', 'rentals');

-- Assign maintenance staff permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'maintenance_staff' AND p.name IN ('maintenance_view', 'maintenance_update', 'dashboard_view');

-- Assign tenant permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'tenant' AND p.name IN ('maintenance_create', 'bookings_view', 'bookings_create', 'properties_view', 'dashboard_view');

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    role_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
);

-- Update existing users to have proper role_id
UPDATE users SET role_id = (SELECT id FROM roles WHERE name = 'admin') WHERE role = 'admin' AND (role_id IS NULL OR role_id = 0);
UPDATE users SET role = 'tenant', role_id = (SELECT id FROM roles WHERE name = 'tenant') WHERE role = 'user' OR (role IS NULL AND role_id IS NOT NULL);

CREATE OR REPLACE VIEW user_permissions AS
    SELECT u.id as user_id, u.name as user_name, u.email, u.role as role_name,
           r.id as role_id, r.display_name as role_display,
           p.id as permission_id, p.name as permission_name, p.module
    FROM users u
    LEFT JOIN roles r ON (u.role_id = r.id) OR (u.role = r.name)
    LEFT JOIN role_permissions rp ON r.id = rp.role_id
    LEFT JOIN permissions p ON rp.permission_id = p.id;

CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(15, 2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    property_type VARCHAR(100) NOT NULL,
    bedrooms INT NOT NULL,
    bathrooms INT NOT NULL,
    area_sqft INT NOT NULL,
    main_image VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_property_title (title)
);

CREATE TABLE IF NOT EXISTS rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    monthly_rent DECIMAL(15, 2) NOT NULL,
    status ENUM('active', 'terminated', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'read', 'archived') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS maintenance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    property_id INT NOT NULL,
    tenant_name VARCHAR(100) NOT NULL,
    tenant_email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    issue_description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME DEFAULT NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    capacity INT DEFAULT 1,
    location VARCHAR(255),
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE INDEX idx_amenity_name_property (name, property_id)
);

CREATE TABLE IF NOT EXISTS amenity_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amenity_id INT NOT NULL,
    user_id INT NOT NULL,
    guest_name VARCHAR(100) NOT NULL,
    booking_date DATE NOT NULL,
    check_in_time TIME NOT NULL,
    check_out_time TIME NOT NULL,
    status ENUM('confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_amenity_date (amenity_id, booking_date)
);

INSERT IGNORE INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@aura.com', '$2y$10$be4f7G.nSVkP7ny98G1uq.DZLvpiteTKCLm6BEmgxWpflAJl5GRnq', 'admin');

INSERT IGNORE INTO properties (title, description, price, location, property_type, bedrooms, bathrooms, area_sqft, main_image) VALUES
('The Sapphire Penthouse', 'A stunning penthouse with panoramic ocean views and private elevator access.', 5000000.00, 'Beverly Hills, CA', 'Penthouse', 4, 5, 4500, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000'),
('Onyx Villa', 'Modern architectural masterpiece nestled in the hills with infinity pool.', 3500000.00, 'Malibu, CA', 'Villa', 5, 6, 6000, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000'),
('Emerald Estate', 'Classic luxury estate with sprawling gardens and tennis court.', 8200000.00, 'Hamptons, NY', 'Estate', 7, 8, 12000, 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000'),
('Golden Loft', 'Industrial chic loft in the heart of the city with floor-to-ceiling windows.', 1200000.00, 'Tribeca, NY', 'Loft', 2, 2, 2500, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000');

INSERT IGNORE INTO amenities (property_id, name, description, capacity, location) VALUES
(1, 'Infinity Pool', 'Rooftop infinity pool with panoramic ocean views', 20, 'Rooftop - Floor 45'),
(1, 'Fitness Center', 'State-of-the-art gym with personal trainer option', 10, 'Floor 3'),
(1, 'Private Cinema', 'Luxury home theater with 4K projection', 12, 'Floor B1'),
(2, 'Tennis Court', 'Professional grade clay tennis court', 4, 'East Garden'),
(2, 'Wine Cellar', 'Climate-controlled wine cellar with tasting room', 8, 'Basement'),
(2, 'Spa & Sauna', 'Full-service spa with steam room and sauna', 6, 'West Wing'),
(3, 'Tennis Court', 'Professional grade clay tennis court with lighting', 4, 'Sport Pavilion'),
(3, 'Helipad', 'Private helipad with 24/7 availability', 5, 'North Lawn'),
(3, 'Lake Dock', 'Private dock with boat and kayak storage', 10, 'Lakefront'),
(4, 'Rooftop Lounge', 'Panoramic city view lounge with BBQ area', 15, 'Rooftop'),
(4, 'Co-Working Space', 'Business lounge with conference facilities', 20, 'Floor 2'),
(4, 'Bike Storage', 'Secure bike storage with repair station', 30, 'Basement');

INSERT IGNORE INTO maintenance_requests (property_id, tenant_name, tenant_email, issue_description, priority, status, created_at, resolved_at) VALUES
(1, 'John Smith', 'john.smith@email.com', 'AC unit not cooling properly in master bedroom', 'high', 'in_progress', '2026-06-15 10:30:00', NULL),
(2, 'Sarah Johnson', 'sarah.j@email.com', 'Pool pump making strange noise and water circulation reduced', 'urgent', 'in_progress', '2026-06-14 14:00:00', NULL),
(3, 'Michael Brown', 'michael.b@email.com', 'Garden sprinkler system not activating on schedule', 'medium', 'pending', '2026-06-18 09:00:00', NULL),
(4, 'Emma Wilson', 'emma.w@email.com', 'Kitchen sink faucet leaking under cabinet', 'low', 'completed', '2026-06-10 11:00:00', '2026-06-11 15:00:00');

INSERT IGNORE INTO amenity_bookings (amenity_id, user_id, guest_name, booking_date, check_in_time, check_out_time, status) VALUES
(1, 1, 'John Smith', '2026-06-22', '10:00:00', '12:00:00', 'confirmed'),
(4, 1, 'Sarah Johnson', '2026-06-23', '14:00:00', '16:00:00', 'confirmed'),
(2, 1, 'Emma Wilson', '2026-06-21', '07:00:00', '08:30:00', 'checked_in');

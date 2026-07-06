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
    latitude DECIMAL(10, 7) DEFAULT NULL,
    longitude DECIMAL(10, 7) DEFAULT NULL,
    property_type VARCHAR(100) NOT NULL,
    bedrooms INT NOT NULL,
    bathrooms INT NOT NULL,
    area_sqft INT NOT NULL,
    main_image VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_coordinates (latitude, longitude)
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
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
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

-- Default admin credentials removed for security.
-- Use the CLI setup to create the first admin account.

INSERT IGNORE INTO properties (title, description, price, location, latitude, longitude, property_type, bedrooms, bathrooms, area_sqft, main_image) VALUES
('The Sapphire Penthouse', 'A stunning penthouse with panoramic ocean views and private elevator access.', 5000000.00, 'Beverly Hills, CA', 34.0736, -118.4004, 'Penthouse', 4, 5, 4500, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000'),
('Onyx Villa', 'Modern architectural masterpiece nestled in the hills with infinity pool.', 3500000.00, 'Malibu, CA', 34.0259, -118.7798, 'Villa', 5, 6, 6000, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000'),
('Emerald Estate', 'Classic luxury estate with sprawling gardens and tennis court.', 8200000.00, 'Hamptons, NY', 40.9006, -72.3018, 'Estate', 7, 8, 12000, 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000'),
('Golden Loft', 'Industrial chic loft in the heart of the city with floor-to-ceiling windows.', 1200000.00, 'Tribeca, NY', 40.7178, -74.0060, 'Loft', 2, 2, 2500, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000'),
('Amalfi Cliff Residence', 'Perched above the Pacific, this glass-and-stone villa features cantilevered terraces over the ocean with an infinity edge that merges into the horizon.', 9750000.00, 'Pacific Palisades, CA', 34.0459, -118.5260, 'Villa', 6, 7, 7800, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000'),
('The Monolith Tower Penthouse', 'A triple-height penthouse crowning a 60-storey tower. Floor-to-ceiling glazing wraps 360 degrees, framing the entire city skyline.', 14500000.00, 'Manhattan, NY', 40.7614, -73.9716, 'Penthouse', 5, 6, 8200, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000'),
('Maison du Vignoble', 'A 19th-century French estate reimagined for modern living. Original limestone walls meet contemporary steel-and-glass extensions across three connected pavilions.', 6400000.00, 'Napa Valley, CA', 38.2975, -122.2869, 'Estate', 8, 9, 14500, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000'),
('Glacier Point Lodge', 'A timber-and-glass mountain retreat inspired by Scandinavian stave churches. Double-height living spaces open to alpine meadows and glacier views.', 4200000.00, 'Aspen, CO', 39.1869, -106.8178, 'Lodge', 5, 5, 5600, 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=1000'),
('Dune House', 'An earth-sheltered residence built into coastal dunes. Rammed-earth walls, a living green roof, and floor-to-ceiling ocean-facing glass define this sustainable masterpiece.', 5800000.00, 'Montauk, NY', 41.0704, -71.9235, 'House', 4, 4, 4200, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&q=80&w=1000'),
('The Glass Pavilion', 'A Miesian glass box reinterpreted for the desert. Steel columns, polished concrete floors, and floor-to-ceiling panels frame the Sonoran landscape.', 7200000.00, 'Scottsdale, AZ', 33.4942, -111.9261, 'Villa', 4, 5, 6100, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&q=80&w=1000'),
('Harbour View Tower', 'A 42nd-floor residence in a sculptural waterfront tower. Every room frames the harbour, with private lift lobby and wraparound terrace.', 8900000.00, 'Sydney, NSW', -33.8568, 151.2153, 'Penthouse', 3, 4, 3800, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000'),
('Palazzo Nero', 'A Venetian palazzo restored with museum-grade precision. Original frescoed ceilings, Carrara marble bathrooms, and a private canal mooring.', 11500000.00, 'Venice, IT', 45.4408, 12.3155, 'Estate', 7, 8, 11000, 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000'),
('Cedar Bridge Farmhouse', 'A timber-frame farmhouse on 12 acres of rolling pasture. Board-formed concrete, charred cedar cladding, and a geothermal-heated indoor pool.', 3650000.00, 'Hudson Valley, NY', 41.9845, -73.9080, 'Farmhouse', 5, 4, 5200, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1000'),
('The Vertex', 'A 28-storey sculptural tower with rotating floor plates. Each unit offers a unique vantage, with private sky gardens on every fifth floor.', 6800000.00, 'Miami Beach, FL', 25.7907, -80.1300, 'Penthouse', 3, 4, 3600, 'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&q=80&w=1000'),
('Amanoi Retreat', 'A resort-inspired residence nestled in hillside jungle. Open-air pavilions, private plunge pools, and wraparound verandas blur the line between indoors and out.', 4500000.00, 'Tulum, MX', 20.2145, -87.4291, 'Villa', 4, 5, 4800, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000'),
('The Foundry', 'A converted ironworks with triple-height spaces, raw steel trusses, and 14-foot windows. The industrial shell houses a refined, light-filled interior.', 5100000.00, 'Brooklyn, NY', 40.7128, -73.9654, 'Loft', 3, 3, 4500, 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&q=80&w=1000'),
('Villa Aether', 'A cantilevered concrete and timber villa hovering above a private cove. The cantilever extends 12 metres over the cliff edge, sheltering a natural swimming pool below.', 12800000.00, 'Santorini, GR', 36.3932, 25.4615, 'Villa', 6, 6, 7200, 'https://images.unsplash.com/photo-1600585154363-67eb9e2e2099?auto=format&fit=crop&q=80&w=1000'),
('Maison Terre', 'A rammed-earth compound in the hills above Malibu. Three interconnected pods share a central courtyard with a reflecting pool and mature olive trees.', 7500000.00, 'Malibu, CA', 34.0259, -118.7798, 'Compound', 6, 7, 8500, 'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?auto=format&fit=crop&q=80&w=1000'),
('The Observatory', 'A cylindrical glass residence perched on a desert bluff. A rotating living room platform offers 360-degree views from the Mojave to the Pacific.', 5400000.00, 'Joshua Tree, CA', 34.1226, -116.3131, 'House', 3, 3, 3200, 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000'),
('Schwarzwald Chalet', 'A Black Forest-inspired timber chalet with hand-carved details, a heated outdoor infinity pool, and a private forest trail network.', 3900000.00, 'Whistler, BC', 50.1163, -122.9574, 'Chalet', 6, 5, 6400, 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=1000'),
('Skybridge Residences', 'Two towers connected by a sky bridge on the 40th floor. The bridge houses a shared infinity pool, gym, and residents lounge with 360-degree views.', 8200000.00, 'Dubai, UAE', 25.1972, 55.2744, 'Penthouse', 4, 5, 5100, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000'),
('The Copper House', 'A weathered copper-clad residence that evolves with the seasons. The patina shifts from burnished orange to verdigris green over the years.', 4800000.00, 'Portland, OR', 45.5155, -122.6789, 'House', 4, 4, 4100, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000'),
('Marina Bay Grand', 'A waterfront duplex penthouse with a private marina berth. Floor-to-ceiling glass walls fold open to merge the living room with the 800 sq ft terrace.', 10200000.00, 'Singapore', 1.2647, 103.8222, 'Penthouse', 4, 5, 5800, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000'),
('The Lighthouse', 'A converted Victorian lighthouse keeper residence, fully modernised with a glass-walled upper floor offering unobstructed 360-degree ocean views.', 2800000.00, 'Big Sur, CA', 36.2704, -121.8081, 'House', 3, 3, 2800, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&q=80&w=1000'),
('Orchid Court', 'A heritage-listed Georgian townhouse with five floors, original plasterwork, a private garden, and a subterranean spa with plunge pool.', 9100000.00, 'London, UK', 51.5074, -0.1278, 'Townhouse', 6, 5, 6800, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1000');

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

CREATE TABLE IF NOT EXISTS payment_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razorpay_order_id VARCHAR(100) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    amount INT NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'INR',
    purpose VARCHAR(255) DEFAULT NULL,
    reference_type VARCHAR(50) DEFAULT NULL,
    reference_id INT DEFAULT NULL,
    status ENUM('created', 'attempted', 'paid', 'failed') DEFAULT 'created',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_razorpay (razorpay_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    subdir VARCHAR(50) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_filename (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sse_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    connected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_time (user_id, connected_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

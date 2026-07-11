-- 007: Create all tables using pure PostgreSQL syntax
-- This migration uses IF NOT EXISTS so it's safe to run multiple times

CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO roles (name, display_name, description) VALUES
('admin', 'Administrator', 'Full system access with all permissions'),
('property_manager', 'Property Manager', 'Manage properties, maintenance, amenities'),
('maintenance_staff', 'Maintenance Staff', 'View and update maintenance requests'),
('tenant', 'Tenant', 'Access tenant portal, submit requests, book amenities')
ON CONFLICT (name) DO NOTHING;

CREATE TABLE IF NOT EXISTS permissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO permissions (name, display_name, module) VALUES
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
('settings_view', 'View Settings', 'settings')
ON CONFLICT (name) DO NOTHING;

CREATE TABLE IF NOT EXISTS role_permissions (
    id SERIAL PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE (role_id, permission_id)
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'admin'
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'property_manager' AND p.module IN ('properties', 'maintenance', 'amenities', 'bookings', 'dashboard', 'inquiries', 'rentals')
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'maintenance_staff' AND p.name IN ('maintenance_view', 'maintenance_update', 'dashboard_view')
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'tenant' AND p.name IN ('maintenance_create', 'bookings_view', 'bookings_create', 'properties_view', 'dashboard_view')
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    role_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
);

INSERT INTO users (name, email, password, role, role_id)
SELECT 'Admin User', 'admin@aura.com', '$2y$10$be4f7G.nSVkP7ny98G1uq.DZLvpiteTKCLm6BEmgxWpflAJl5GRnq', 'admin', r.id
FROM roles r WHERE r.name = 'admin'
AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@aura.com')
ON CONFLICT DO NOTHING;

INSERT INTO users (name, email, password, role, role_id)
SELECT 'John Tenant', 'tenant@aura.com', '$2y$10$be4f7G.nSVkP7ny98G1uq.DZLvpiteTKCLm6BEmgxWpflAJl5GRnq', 'tenant', r.id
FROM roles r WHERE r.name = 'tenant'
AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'tenant@aura.com')
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS properties (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(15, 2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    property_type VARCHAR(100) NOT NULL,
    bedrooms INT NOT NULL,
    bathrooms INT NOT NULL,
    area_sqft INT NOT NULL,
    main_image VARCHAR(255),
    is_available BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO properties (title, description, price, location, property_type, bedrooms, bathrooms, area_sqft, main_image) VALUES
('The Sapphire Penthouse', 'A stunning penthouse with panoramic ocean views and private elevator access.', 5000000.00, 'Beverly Hills, CA', 'Penthouse', 4, 5, 4500, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000'),
('Onyx Villa', 'Modern architectural masterpiece nestled in the hills with infinity pool.', 3500000.00, 'Malibu, CA', 'Villa', 5, 6, 6000, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000'),
('Emerald Estate', 'Classic luxury estate with sprawling gardens and tennis court.', 8200000.00, 'Hamptons, NY', 'Estate', 7, 8, 12000, 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000'),
('Golden Loft', 'Industrial chic loft in the heart of the city with floor-to-ceiling windows.', 1200000.00, 'Tribeca, NY', 'Loft', 2, 2, 2500, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000')
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS rentals (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    monthly_rent DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS inquiries (
    id SERIAL PRIMARY KEY,
    property_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS maintenance_requests (
    id SERIAL PRIMARY KEY,
    user_id INT DEFAULT NULL,
    property_id INT NOT NULL,
    tenant_name VARCHAR(100) NOT NULL,
    tenant_email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    issue_description TEXT NOT NULL,
    priority VARCHAR(20) DEFAULT 'medium',
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP DEFAULT NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS amenities (
    id SERIAL PRIMARY KEY,
    property_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    capacity INT DEFAULT 1,
    location VARCHAR(255),
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE (name, property_id)
);

CREATE TABLE IF NOT EXISTS amenity_bookings (
    id SERIAL PRIMARY KEY,
    amenity_id INT NOT NULL,
    user_id INT NOT NULL,
    guest_name VARCHAR(100) NOT NULL,
    booking_date DATE NOT NULL,
    check_in_time TIME NOT NULL,
    check_out_time TIME NOT NULL,
    status VARCHAR(20) DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS password_resets (
    id SERIAL PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sessions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (session_id)
);

CREATE TABLE IF NOT EXISTS rate_limits (
    id BIGSERIAL PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    endpoint VARCHAR(100) NOT NULL,
    hits INT DEFAULT 1,
    window_start TIMESTAMP NOT NULL
);

-- Seed 25 properties for portfolio
INSERT INTO properties (title, description, price, location, property_type, bedrooms, bathrooms, area_sqft, main_image) VALUES
('The Meridian Penthouse', 'Sky-high living with wraparound terrace and chef kitchen.', 4800000.00, 'Manhattan, NY', 'Penthouse', 3, 3, 3800, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000'),
('Villa Aura', 'Mediterranean-inspired estate with private garden courtyard.', 6200000.00, 'Palm Beach, FL', 'Villa', 6, 7, 8500, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000'),
('The Obsidian', 'Sleek modern tower with floor-to-ceiling glass and smart home.', 3200000.00, 'San Francisco, CA', 'Apartment', 2, 2, 2200, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000'),
('Cliffside Retreat', 'Perched above the Pacific with cantilevered infinity pool.', 7500000.00, 'Laguna Beach, CA', 'Villa', 5, 5, 6200, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&q=80&w=1000'),
('Park Avenue Residence', 'Pre-war elegance meets contemporary luxury on Billionaires Row.', 9100000.00, 'Manhattan, NY', 'Penthouse', 4, 4, 5200, 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000'),
('Coral Bay Estate', 'Waterfront compound with private dock and tropical gardens.', 5800000.00, 'Miami Beach, FL', 'Estate', 6, 6, 7800, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000'),
('The Loft at SoHo', 'Open-plan artist loft with original cast-iron columns.', 2800000.00, 'New York, NY', 'Loft', 2, 2, 3000, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000'),
('Alpine Modern', 'Mountain retreat with heated outdoor pool and ski access.', 4200000.00, 'Aspen, CO', 'Villa', 4, 4, 4600, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000'),
('The Waverly', 'Boutique residence with private rooftop garden in Chelsea.', 3600000.00, 'London, UK', 'Apartment', 3, 2, 2800, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000'),
('Silicon Valley Estate', 'Tech-enabled smart estate with home theater and wellness spa.', 8500000.00, 'Palo Alto, CA', 'Estate', 5, 6, 9200, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&q=80&w=1000'),
('The Belvedere', 'Classic brownstone reimagined with modern amenities.', 4100000.00, 'Brooklyn, NY', 'Villa', 4, 3, 3600, 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000'),
('Seaside Pavilion', 'Glass-walled beachfront with direct sand access.', 6900000.00, 'Hamptons, NY', 'Villa', 5, 5, 5800, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000'),
('Metropolitan Tower', 'Ultra-modern high-rise with panoramic city skyline views.', 2900000.00, 'Chicago, IL', 'Apartment', 2, 2, 2100, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000'),
('The Heritage', 'Restored 1920s gem with modern glass extension.', 5400000.00, 'Paris, France', 'Estate', 4, 4, 4800, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000'),
('Pacific Heights', 'Victorian masterpiece with bay views and gardens.', 7200000.00, 'San Francisco, CA', 'Estate', 5, 5, 6400, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&q=80&w=1000'),
('The Chelsea Loft', 'Industrial chic with exposed brick and steel beams.', 2400000.00, 'London, UK', 'Loft', 1, 1, 1800, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000'),
('Dubai Marina Penthouse', 'Waterfront luxury with private elevator and infinity pool.', 5500000.00, 'Dubai, UAE', 'Penthouse', 3, 3, 4200, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000'),
('Aspen Ridge', 'Ski-in ski-out with mountain views and stone fireplace.', 6100000.00, 'Aspen, CO', 'Villa', 5, 4, 5600, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000'),
('Nob Hill Classic', 'Timeless elegance with cable car views and period details.', 3800000.00, 'San Francisco, CA', 'Apartment', 3, 2, 2600, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000'),
('The Monarch', 'Regal estate with gated entry and landscaped grounds.', 9800000.00, 'Greenwich, CT', 'Estate', 7, 8, 11000, 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000'),
('SoHo Studio', 'Designer studio with mezzanine sleeping loft.', 1900000.00, 'New York, NY', 'Loft', 1, 1, 1200, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000'),
('Coastal Modern', 'Beach house with cantilevered deck and ocean panorama.', 4700000.00, 'Malibu, CA', 'Villa', 4, 3, 3800, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000'),
('The Pinnacle', 'Top-floor penthouse with wraparound terrace.', 6800000.00, 'Manhattan, NY', 'Penthouse', 3, 3, 4000, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000'),
('Georgetown Manor', 'Historic estate with updated interiors and pool house.', 5200000.00, 'Washington, DC', 'Estate', 5, 4, 5400, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&q=80&w=1000')
ON CONFLICT DO NOTHING;

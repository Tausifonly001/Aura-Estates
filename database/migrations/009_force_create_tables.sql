-- 009: Force create missing tables (IF NOT EXISTS)
-- This fixes cases where previous migrations were marked as "applied" 
-- but the actual tables were never created due to syntax errors.

CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS permissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS role_permissions (
    id SERIAL PRIMARY KEY,
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    permission_id INTEGER NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE(role_id, permission_id)
);

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'tenant',
    role_id INTEGER REFERENCES roles(id),
    phone VARCHAR(20),
    avatar VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS properties (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    property_type VARCHAR(50) NOT NULL,
    status VARCHAR(50) DEFAULT 'available',
    price NUMERIC(12,2) NOT NULL,
    bedrooms INTEGER DEFAULT 0,
    bathrooms INTEGER DEFAULT 0,
    area_sqft INTEGER DEFAULT 0,
    location VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    latitude NUMERIC(10,7),
    longitude NUMERIC(10,7),
    image VARCHAR(500),
    images JSONB DEFAULT '[]',
    features JSONB DEFAULT '[]',
    agent_id INTEGER REFERENCES users(id),
    year_built INTEGER,
    garage INTEGER DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rentals (
    id SERIAL PRIMARY KEY,
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    start_date DATE NOT NULL,
    end_date DATE,
    monthly_rent NUMERIC(12,2) NOT NULL,
    security_deposit NUMERIC(12,2),
    status VARCHAR(50) DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inquiries (
    id SERIAL PRIMARY KEY,
    property_id INTEGER REFERENCES properties(id) ON DELETE SET NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    message TEXT,
    status VARCHAR(50) DEFAULT 'new',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS maintenance_requests (
    id SERIAL PRIMARY KEY,
    property_id INTEGER REFERENCES properties(id) ON DELETE SET NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority VARCHAR(20) DEFAULT 'normal',
    status VARCHAR(50) DEFAULT 'open',
    assigned_to INTEGER REFERENCES users(id),
    resolved_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS amenities (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    capacity INTEGER DEFAULT 1,
    is_available BOOLEAN DEFAULT TRUE,
    image VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS amenity_bookings (
    id SERIAL PRIMARY KEY,
    amenity_id INTEGER NOT NULL REFERENCES amenities(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status VARCHAR(50) DEFAULT 'confirmed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rate_limits (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    attempts INTEGER DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for rate limiting
CREATE INDEX IF NOT EXISTS idx_rate_limits_identifier ON rate_limits(identifier, endpoint);

-- Seed roles
INSERT INTO roles (name, display_name, description) VALUES 
('admin', 'Administrator', 'Full system access'),
('manager', 'Property Manager', 'Property and tenant management'),
('tenant', 'Tenant', 'Resident portal access')
ON CONFLICT DO NOTHING;

-- Seed permissions
INSERT INTO permissions (name, module, description) VALUES 
('manage_properties', 'properties', 'Create, edit, delete properties'),
('manage_users', 'users', 'Manage user accounts'),
('manage_inquiries', 'inquiries', 'Handle property inquiries'),
('manage_maintenance', 'maintenance', 'Handle maintenance requests'),
('manage_amenities', 'amenities', 'Manage amenities and bookings'),
('view_reports', 'reports', 'View analytics and reports'),
('manage_content', 'content', 'Manage site content')
ON CONFLICT DO NOTHING;

-- Seed permissions for roles
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'admin'
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'manager' AND p.name IN ('manage_properties', 'manage_inquiries', 'manage_maintenance', 'manage_amenities')
ON CONFLICT DO NOTHING;

-- Seed admin user (password: admin123)
INSERT INTO users (name, email, password, role, role_id)
SELECT 'Administrator', 'admin@aura.com', '$2y$10$be4f7G.nSVkP7ny98G1uq.DZLvpiteTKCLm6BEmgxWpflAJl5GRnq', 'admin', r.id
FROM roles r WHERE r.name = 'admin'
ON CONFLICT (email) DO UPDATE SET 
    password = '$2y$10$be4f7G.nSVkP7ny98G1uq.DZLvpiteTKCLm6BEmgxWpflAJl5GRnq',
    role = 'admin',
    role_id = (SELECT id FROM roles WHERE name = 'admin');

-- Seed tenant user (password: tenant123)
INSERT INTO users (name, email, password, role, role_id)
SELECT 'John Tenant', 'tenant@aura.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant', r.id
FROM roles r WHERE r.name = 'tenant'
ON CONFLICT (email) DO NOTHING;

-- Seed properties (31 total with fallback data)
INSERT INTO properties (title, description, property_type, status, price, bedrooms, bathrooms, area_sqft, location, address, city, state, zip_code, latitude, longitude, image, features, is_featured) VALUES
('The Meridian Penthouse', 'A crown-jewel penthouse with panoramic city views, designer finishes, and private rooftop terrace.', 'Penthouse', 'available', 18500.00, 4, 4, 4200, 'Upper East Side, New York', '1200 Park Avenue', 'New York', 'NY', '10128', 40.7831, -73.9565, 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800', '["Rooftop Terrace","Private Elevator","Wine Cellar","Smart Home","Concierge"]', true),
('Villa Serenity', 'Mediterranean-inspired estate with infinity pool, landscaped gardens, and ocean-facing grand salon.', 'Villa', 'available', 22000.00, 6, 5, 6800, 'Malibu, California', '24500 Pacific Coast Highway', 'Malibu', 'CA', '90265', 34.0259, -118.7798, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800', '["Infinity Pool","Ocean View","Home Theater","Guest House","Wine Cellar"]', true),
('Skyline Tower Residence', 'Ultra-modern residence in the heart of downtown with floor-to-ceiling glass and smart home integration.', 'Apartment', 'available', 9500.00, 3, 3, 2800, 'Downtown, Chicago', '150 North Michigan Avenue', 'Chicago', 'IL', '60601', 41.8827, -87.6233, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', '["Floor-to-Ceiling Windows","Smart Home","Gym Access","Rooftop Lounge","Doorman"]', true),
('The Grand Manor', 'A stately heritage property with modern amenities, sprawling gardens, and classic architectural details.', 'Villa', 'available', 35000.00, 7, 6, 9500, 'Beverly Hills, California', '612 N Rodeo Drive', 'Beverly Hills', 'CA', '90210', 34.0674, -118.4003, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800', '["Gardens","Pool","Tennis Court","Home Theater","Wine Cellar","Staff Quarters"]', true),
('Harbour View Suite', 'Waterfront luxury with marina views, contemporary design, and resort-style amenities.', 'Apartment', 'available', 12000.00, 2, 2, 1800, 'Marina Bay, San Francisco', '88 Marina Boulevard', 'San Francisco', 'CA', '94123', 37.8070, -122.4095, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800', '["Marina View","Balcony","Concierge","Parking","Gym"]', false),
('Alpine Retreat', 'Mountain estate with ski-in access, timber-frame great room, and panoramic valley views.', 'Villa', 'available', 28000.00, 5, 4, 5200, 'Aspen, Colorado', '315 Snowmass Drive', 'Aspen', 'CO', '81611', 39.1911, -106.8175, 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?w=800', '["Ski-In Access","Hot Tub","Fireplace","Mud Room","Equipment Storage"]', false),
('The Chelsea Loft', 'Art-inspired loft in the gallery district with soaring ceilings, exposed brick, and custom millwork.', 'Apartment', 'rented', 7800.00, 2, 2, 2200, 'Chelsea, New York', '456 West 25th Street', 'New York', 'NY', '10001', 40.7490, -74.0004, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800', '["Exposed Brick","Soaring Ceilings","Custom Millwork","Gallery District"]', false),
('Pacific Heights Estate', 'Legacy property with Golden Gate views, classical proportions, and museum-quality finishes.', 'Villa', 'available', 42000.00, 6, 5, 7800, 'Pacific Heights, San Francisco', '2899 Broadway', 'San Francisco', 'CA', '94115', 37.7920, -122.4350, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800', '["Golden Gate Views","Gardens","Library","Wine Cellar","Pool"]', false),
('The Observatory', 'A penthouse observatory with retractable glass roof, private elevator, and 360-degree skyline views.', 'Penthouse', 'available', 25000.00, 3, 3, 3500, 'Midtown, Manhattan', '432 Park Avenue', 'New York', 'NY', '10022', 40.7614, -73.9718, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=800', '["360 Views","Private Elevator","Retractable Roof","Smart Home","Wine Room"]', false),
('Riverside Walk', 'Contemporary townhome with river pathway access, double-height living, and landscaped courtyard.', 'Apartment', 'available', 8200.00, 3, 3, 2600, 'Georgetown, Washington DC', '3200 M Street NW', 'Washington', 'DC', '20007', 38.9055, -77.0636, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800', '["River Access","Courtyard","Double-Height Living","Parking","Storage"]', false),
('The Astor Collection', 'Pre-war grandeur meets contemporary luxury in this meticulously restored full-floor residence.', 'Apartment', 'available', 32000.00, 5, 4, 5500, 'Park Avenue, New York', '740 Park Avenue', 'New York', 'NY', '10021', 40.7689, -73.9640, 'https://images.unsplash.com/photo-1600607687644-aac4c3eac7f4?w=800', '["Full Floor","Pre-War Details","Restored Mouldings","Central Park Views","Doorman"]', false),
('Sunset Plaza Residence', 'California modern with retractable glass walls, infinity pool, and canyon-to-ocean panorama.', 'Villa', 'available', 15000.00, 4, 3, 3800, 'Sunset Plaza, Los Angeles', '8221 Sunset Boulevard', 'Los Angeles', 'CA', '90046', 34.0920, -118.3700, 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800', '["Infinity Pool","Glass Walls","Canyon Views","Outdoor Kitchen","Smart Home"]', false),
('The Pinnacle', 'A peak-luxury ski chalet with timber beams, stone fireplace, and direct mountain access.', 'Villa', 'available', 19500.00, 5, 4, 4800, 'Park City, Utah', '1200 Empire Avenue', 'Park City', 'UT', '84060', 40.6461, -111.4980, 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?w=800', '["Ski Access","Hot Tub","Sauna","Mudroom","Game Room"]', false),
('Embarcadero Loft', 'Industrial-chic loft with waterfront promenade, chef kitchen, and automated blinds.', 'Apartment', 'available', 6800.00, 1, 1, 1400, 'Embarcadero, San Francisco', '100 The Embarcadero', 'San Francisco', 'CA', '94111', 37.7940, -122.3910, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', '["Waterfront","Chef Kitchen","Automated Blinds","Gym Access","Bike Storage"]', false),
('The Belvedere', 'Hilltop estate with city panorama, resort pool, and detached guest casita.', 'Villa', 'available', 27500.00, 5, 5, 6200, 'Bel Air, Los Angeles', '1200 Bel Air Road', 'Los Angeles', 'CA', '90077', 34.0856, -118.4500, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800', '["City Panorama","Resort Pool","Guest Casita","Tennis Court","Wine Cellar"]', false),
('The Gramercy', 'Boutique residence overlooking the private park with herringbone floors and marble baths.', 'Apartment', 'available', 11000.00, 2, 2, 1900, 'Gramercy Park, New York', '36 Gramercy Park East', 'New York', 'NY', '10003', 40.7370, -73.9850, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800', '["Park View","Herringbone Floors","Marble Baths","Doorman","Pet Friendly"]', false),
('Napa Valley Estate', 'Wine country retreat with vineyard, barrel room, and al fresco dining terrace.', 'Villa', 'available', 38000.00, 6, 5, 7200, 'Napa Valley, California', '2900 Silverado Trail', 'Napa', 'CA', '94558', 38.3650, -122.3100, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800', '["Vineyard","Barrel Room","Pool","Olive Grove","Guest House"]', false),
('The Crescent', 'Art Deco landmark conversion with curved facade, terrazzo lobby, and private garden.', 'Apartment', 'available', 9800.00, 2, 2, 2100, 'Dupont Circle, Washington DC', '1700 New Hampshire Avenue NW', 'Washington', 'DC', '20009', 38.9110, -77.0430, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800', '["Art Deco Details","Terrazzo Lobby","Private Garden","Metro Access","Rooftop"]', false),
('Lake Shore Drive', 'Lakefront classic with panoramic water views, formal dining, and morning sun terraces.', 'Apartment', 'available', 8500.00, 3, 2, 2400, 'Gold Coast, Chicago', '1200 North Lake Shore Drive', 'Chicago', 'IL', '60610', 41.9030, -87.6260, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', '["Lake Views","Terrace","Doorman","Parking","Storage"]', false),
('The Kensington', 'English-inspired townhome with mullioned windows, walled garden, and climate-controlled cellar.', 'Villa', 'available', 16500.00, 4, 3, 3600, 'Georgetown, Washington DC', '3101 N Street NW', 'Washington', 'DC', '20007', 38.9076, -77.0610, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800', '["Walled Garden","Climate Cellar","Mullioned Windows","Fireplace","Library"]', false),
('The Monarch', 'A regal penthouse with private pool, art-gallery hallway, and automated climate zones.', 'Penthouse', 'available', 21000.00, 4, 3, 3800, 'Brickell, Miami', '1010 Brickell Avenue', 'Miami', 'FL', '33131', 25.7580, -80.1900, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=800', '["Private Pool","Art Gallery Hallway","Climate Zones","Bay Views","Concierge"]', false),
('Windsor Park', 'Estate property with equestrian facilities, formal gardens, and grand ballroom.', 'Villa', 'available', 32000.00, 7, 6, 10000, 'Greenwich, Connecticut', '45 Havemeyer Place', 'Greenwich', 'CT', '06830', 41.0262, -73.6282, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800', '["Equestrian Facilities","Formal Gardens","Ballroom","Pool House","Tennis Court"]', false),
('The Solstice', 'Sun-drenched loft with double-height windows, polished concrete, and gallery lighting.', 'Apartment', 'available', 5800.00, 1, 1, 1100, 'SoHo, New York', '78 Greene Street', 'New York', 'NY', '10012', 40.7240, -73.9990, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', '["Double-Height Windows","Gallery Lighting","Polished Concrete","Freight Elevator"]', false),
('Coral Gables Villa', 'Spanish Revival with clay tile roof, courtyard fountain, and resort pool.', 'Villa', 'available', 13500.00, 4, 3, 3400, 'Coral Gables, Miami', '4000 Alhambra Circle', 'Miami', 'FL', '33146', 25.7390, -80.2940, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800', '["Courtyard Fountain","Resort Pool","Clay Tile","Arched Doorways","Terrace"]', false),
('The Meridian Club', 'Club residence with shared pool, spa, and concierge — modern finishes throughout.', 'Apartment', 'available', 7200.00, 2, 2, 1600, 'South Beach, Miami', '1100 Collins Avenue', 'Miami', 'FL', '33139', 25.7750, -80.1300, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800', '["Pool Access","Spa","Concierge","Beach Access","Gym"]', false),
('Pinnacle Ridge', 'Mountain-modern with cantilevered deck, floor-to-ceiling glass, and ski room.', 'Villa', 'available', 24000.00, 4, 3, 4500, 'Jackson Hole, Wyoming', '2850 Teton Village Road', 'Jackson Hole', 'WY', '83025', 43.5830, -110.8300, 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?w=800', '["Cantilevered Deck","Ski Room","Mountain Views","Hot Tub","Mudroom"]', false),
('The Regatta', 'Nautical-inspired waterfront condo with marina views and yacht club privileges.', 'Apartment', 'available', 8800.00, 2, 2, 1700, 'Inner Harbor, Baltimore', '500 International Drive', 'Baltimore', 'MD', '21202', 39.2850, -76.6100, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800', '["Marina Views","Yacht Club Access","Balcony","Parking","Concierge"]', false),
('The Heritage', 'Restored brownstone with original mantels, chef kitchen, and private courtyard.', 'Apartment', 'available', 10500.00, 3, 2, 2300, 'Beacon Hill, Boston', '45 Mount Vernon Street', 'Boston', 'MA', '02108', 42.3570, -71.0640, 'https://images.unsplash.com/photo-1600607687644-aac4c3eac7f4?w=800', '["Original Mantels","Chef Kitchen","Private Courtyard","Garden Access","Storage"]', false),
('The Apex', 'Glass-tower penthouse with wraparound terrace, smart glass, and chef kitchen.', 'Penthouse', 'available', 16000.00, 3, 3, 3200, 'South Lake Union, Seattle', '2200 Eastlake Avenue', 'Seattle', 'WA', '98102', 47.6340, -122.3260, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=800', '["Wraparound Terrace","Smart Glass","Chef Kitchen","Lake Views","Doorman"]', false),
('The Pemberley', 'Country estate with lake, stables, and 12-seat cinema — timeless elegance.', 'Villa', 'available', 45000.00, 8, 7, 12000, 'North Shore, Long Island', '1200 Meadow Lane', 'Southampton', 'NY', '11968', 40.8900, -72.4000, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800', '["Lake","Stables","Cinema","Tennis Court","Pool House","Guest Quarters"]', false),
('Harbour One', 'Harbour-front luxury with marina promenade, sky pool, and 24h butler service.', 'Apartment', 'available', 14000.00, 3, 3, 2800, 'Darling Harbour, Sydney', '1 Darling Drive', 'Sydney', 'NSW', '2000', -33.8730, 151.2000, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800', '["Marina Promenade","Sky Pool","Butler Service","Harbour Views","Spa"]', false),
('The Crescent Club', 'Curved facade residence with private club access, pool, and rooftop observatory.', 'Apartment', 'available', 9200.00, 2, 2, 1800, 'Mayfair, London', '23 Curzon Street', 'London', 'W1J 5HN', 51.5040, -0.1490, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800', '["Club Access","Pool","Observatory","Concierge","Underground Parking"]', false),
('Santorini Escape', 'Cycladic-inspired villa with infinity pool carved into cliffside and caldera views.', 'Villa', 'available', 20000.00, 4, 3, 3600, 'Oia, Santorini', 'Caldera Cliff Road', 'Oia', 'Cyclades', '84702', 36.4610, 25.3750, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800', '["Caldera Views","Infinity Pool","Cliffside","Wine Cellar","Private Terrace"]', false),
('The Alchemist', 'Avant-garde residence with kinetic facade, green roof, and solar-powered systems.', 'Apartment', 'available', 7500.00, 2, 2, 1500, 'Docklands, Melbourne', '88 Harbour Esplanade', 'Melbourne', 'VIC', '3008', -37.8200, 144.9380, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', '["Kinetic Facade","Green Roof","Solar Powered","Bay Views","Tram Access"]', false);

-- Seed amenities
INSERT INTO amenities (name, description, capacity, is_available) VALUES
('Rooftop Pool & Spa', 'Temperature-controlled rooftop pool with panoramic city views. Spa services available on request.', 12, true),
('Secure Parking Garage', 'Underground parking with 24/7 security monitoring and electric vehicle charging stations.', 50, true),
('Fitness Center', 'State-of-the-art gym with Peloton bikes, free weights, and yoga studio.', 20, true),
('Residents Lounge', 'Private lounge with fireplace, library, and complimentary refreshments.', 30, true),
('Conference Room', 'Professional meeting room with AV equipment and video conferencing.', 12, true)
ON CONFLICT DO NOTHING;

-- Create view
CREATE OR REPLACE VIEW user_permissions AS
SELECT u.id as user_id, u.name as user_name, u.email, u.role as role_name,
       r.id as role_id, r.display_name as role_display,
       p.id as permission_id, p.name as permission_name, p.module
FROM users u
LEFT JOIN roles r ON (u.role_id = r.id) OR (u.role = r.name)
LEFT JOIN role_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id;

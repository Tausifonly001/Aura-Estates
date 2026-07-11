-- Seed admin user and roles for PostgreSQL on Render
-- Uses ON CONFLICT to be idempotent

INSERT INTO roles (name, display_name, description) VALUES
('admin', 'Administrator', 'Full system access with all permissions'),
('property_manager', 'Property Manager', 'Manage properties, maintenance, amenities'),
('maintenance_staff', 'Maintenance Staff', 'View and update maintenance requests'),
('tenant', 'Tenant', 'Access tenant portal, submit requests, book amenities')
ON CONFLICT (name) DO NOTHING;

INSERT INTO users (name, email, password, role, role_id) 
SELECT 'Admin User', 'admin@aura.com', '$2y$10$be4f7G.nSVkP7ny98G1uq.DZLvpiteTKCLm6BEmgxWpflAJl5GRnq', 'admin', r.id
FROM roles r WHERE r.name = 'admin'
AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@aura.com');

INSERT INTO users (name, email, password, role, role_id)
SELECT 'John Tenant', 'tenant@aura.com', '$2y$10$be4f7G.nSVkP7ny98G1uq.DZLvpiteTKCLm6BEmgxWpflAJl5GRnq', 'tenant', r.id
FROM roles r WHERE r.name = 'tenant'
AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'tenant@aura.com');

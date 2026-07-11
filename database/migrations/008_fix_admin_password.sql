-- 008: Force update admin password to admin123
UPDATE users SET password = '$2y$10$be4f7G.nSVkP7ny98G1uq.DZLvpiteTKCLm6BEmgxWpflAJl5GRnq'
WHERE email = 'admin@aura.com';

-- Also ensure tenant user exists with correct password
INSERT INTO users (name, email, password, role, role_id)
SELECT 'John Tenant', 'tenant@aura.com', '$2y$10$be4f7G.nSVkP7ny98G1uq.DZLvpiteTKCLm6BEmgxWpflAJl5GRnq', 'tenant', r.id
FROM roles r WHERE r.name = 'tenant'
AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'tenant@aura.com')
ON CONFLICT DO NOTHING;

-- Ensure the user_permissions view exists
CREATE OR REPLACE VIEW user_permissions AS
    SELECT u.id as user_id, u.name as user_name, u.email, u.role as role_name,
           r.id as role_id, r.display_name as role_display,
           p.id as permission_id, p.name as permission_name, p.module
    FROM users u
    LEFT JOIN roles r ON (u.role_id = r.id) OR (u.role = r.name)
    LEFT JOIN role_permissions rp ON r.id = rp.role_id
    LEFT JOIN permissions p ON rp.permission_id = p.id;

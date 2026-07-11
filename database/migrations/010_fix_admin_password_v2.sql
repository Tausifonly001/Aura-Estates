-- 010: Fix admin password hash to match 'admin123'
-- The hash in 009 was for 'password', not 'admin123'
-- Correct bcrypt hash for 'admin123': $2y$10$VThHEVrK/ZTgcjP5kVj7MugzV1q8Ie9lTiFddeox2/UoT.0PNt.RC

UPDATE users 
SET password = '$2y$10$VThHEVrK/ZTgcjP5kVj7MugzV1q8Ie9lTiFddeox2/UoT.0PNt.RC',
    role = 'admin',
    role_id = (SELECT id FROM roles WHERE name = 'admin')
WHERE email = 'admin@aura.com';

-- Also ensure tenant user exists with correct hash for 'tenant123'
INSERT INTO users (name, email, password, role, role_id)
SELECT 'John Tenant', 'tenant@aura.com', '$2y$10$YSB.ryMZsCyJ2LATWD3Mte8iBJQRMtXN4AxdoJoDtJPdgblB3i7Ga', 'tenant', r.id
FROM roles r WHERE r.name = 'tenant'
ON CONFLICT (email) DO UPDATE SET
    password = '$2y$10$YSB.ryMZsCyJ2LATWD3Mte8iBJQRMtXN4AxdoJoDtJPdgblB3i7Ga',
    role = 'tenant';

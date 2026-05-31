INSERT INTO roles (name, description)
VALUES
    ('admin', 'Full access to all inventory modules'),
    ('staff', 'Operational access for daily inventory tasks'),
    ('viewer', 'Read-only access to reports and listings')
ON DUPLICATE KEY UPDATE
    description = VALUES(description);

INSERT INTO locations (location_code, location_name, city, country)
VALUES
    ('MAIN', 'Main Warehouse', 'Manila', 'Philippines')
ON DUPLICATE KEY UPDATE
    location_name = VALUES(location_name),
    city = VALUES(city),
    country = VALUES(country);

INSERT INTO users (role_id, full_name, email, password_hash, is_active)
VALUES (
    (SELECT id FROM roles WHERE name = 'admin' LIMIT 1),
    'System Administrator',
    'admin@local.test',
    '$2y$10$3hflpzmJ4J9SjYqQSFK4K.yj2i4PY5e2Z5lQMnAVaSATHVNblKF..',
    1
)
ON DUPLICATE KEY UPDATE
    role_id = VALUES(role_id),
    full_name = VALUES(full_name),
    password_hash = VALUES(password_hash),
    is_active = VALUES(is_active);

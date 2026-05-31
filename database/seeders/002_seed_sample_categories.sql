INSERT INTO categories (name, description, is_active)
VALUES
    ('Accessories', 'Stands, hubs, organizers, and other accessories', 1),
    ('Peripherals', 'Keyboards, mice, and other input devices', 1),
    ('Cables', 'Cables, adapters, and connectors', 1),
    ('Monitors', 'Computer monitors and displays', 1),
    ('Storage', 'External drives and storage devices', 1),
    ('Networking', 'Routers, switches, and networking gear', 1)
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    is_active = VALUES(is_active);

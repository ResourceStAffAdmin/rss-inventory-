INSERT INTO categories (name, description, is_active)
VALUES
    ('Computers', 'Laptops, desktops, and computer units', 1),
    ('Displays', 'Monitors and display equipment', 1),
    ('Peripherals', 'Keyboards, mice, headsets, webcams, and input devices', 1),
    ('Cables & Adapters', 'Cables, converters, display adapters, and connectors', 1),
    ('Power & Network', 'UPS units, power cables, and network adapters', 1),
    ('Accessories', 'Bags and other supporting accessories', 1)
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    is_active = VALUES(is_active);

SET NAMES utf8mb4;
START TRANSACTION;

SET @location_id := (SELECT id FROM locations ORDER BY id ASC LIMIT 1);
SET @moved_by := (SELECT id FROM users ORDER BY id ASC LIMIT 1);

UPDATE categories
SET is_active = 0 WHERE name NOT IN ('Computers', 'Displays', 'Peripherals', 'Cables & Adapters', 'Power & Network', 'Accessories');

INSERT INTO categories (name, description, is_active) VALUES ('Computers', 'Laptops, desktops, and computer units', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = 1;

INSERT INTO categories (name, description, is_active) VALUES ('Displays', 'Monitors and display equipment', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = 1;

INSERT INTO categories (name, description, is_active) VALUES ('Peripherals', 'Keyboards, mice, headsets, webcams, and input devices', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = 1;

INSERT INTO categories (name, description, is_active) VALUES ('Cables & Adapters', 'Cables, converters, display adapters, and connectors', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = 1;

INSERT INTO categories (name, description, is_active) VALUES ('Power & Network', 'UPS units, power cables, and network adapters', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = 1;

INSERT INTO categories (name, description, is_active) VALUES ('Accessories', 'Bags and other supporting accessories', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = 1;

UPDATE products
SET is_active = 0 WHERE sku IN ('4K-DP-TO-HDMI-ADAPTER-0022', 'AOC-MONITOR-0003', 'DESKTOP-NEW-0008', 'DESKTOP-OPTIPLEX-0009', 'DUAL-DISPLAY-ADAPTER-0021', 'HDMI-CABLES-0020', 'HEADSET-0016', 'LAPTOP-ACER-0007', 'LAPTOP-BAGS-0010', 'LAPTOP-DELL-0005', 'LAPTOP-LENOVO-0006', 'LAPTOP-MAC-0026', 'MALE-TO-HDMI-FEMALE-CONVERTER-0018', 'MINI-DP-TO-DP-ADAPTER-0023', 'MOUSE-KEYBOARD-WIRED-0011', 'POWER-CABLES-0024', 'TP-LINK-AC600-WIFI-ADAPTER-0025', 'UPS-0017', 'USB-C-MULTI-FUNCTION-ADAPTER-0019', 'VIEWSONIC-MONITOR-0004', 'WEBCAM-0015', 'WIRED-KEYBOARD-0012', 'WIRED-MOUSE-0013', 'WIRELESS-MOUSE-0014');

SET @category_id := (SELECT id FROM categories WHERE name = 'Computers' LIMIT 1);
SET @sku := 'INV-01';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'LAPTOP', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'LAPTOP',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 0.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 0.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(0.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 0.000, 'COMPLETED'
WHERE ABS(0.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Computers' LIMIT 1);
SET @sku := 'INV-02';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'PC', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'PC',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 5.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 5.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(5.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 5.000, 'COMPLETED'
WHERE ABS(5.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Accessories' LIMIT 1);
SET @sku := 'INV-03';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'LAPTOP BAGS', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'LAPTOP BAGS',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 2.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 2.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(2.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 2.000, 'COMPLETED'
WHERE ABS(2.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Displays' LIMIT 1);
SET @sku := 'INV-04';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'MONITORS', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'MONITORS',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 13.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 13.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(13.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 13.000, 'COMPLETED'
WHERE ABS(13.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Peripherals' LIMIT 1);
SET @sku := 'INV-05';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'MOUSE + KEYBOARD WIRED', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'MOUSE + KEYBOARD WIRED',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 6.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 6.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(6.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 6.000, 'COMPLETED'
WHERE ABS(6.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Peripherals' LIMIT 1);
SET @sku := 'INV-06';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'WIRED KEYBOARD', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'WIRED KEYBOARD',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 1.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 1.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(1.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 1.000, 'COMPLETED'
WHERE ABS(1.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Peripherals' LIMIT 1);
SET @sku := 'INV-07';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'WIRED MOUSE', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'WIRED MOUSE',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 4.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 4.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(4.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 4.000, 'COMPLETED'
WHERE ABS(4.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Peripherals' LIMIT 1);
SET @sku := 'INV-08';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'WIRELESS MOUSE', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'WIRELESS MOUSE',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 5.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 5.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(5.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 5.000, 'COMPLETED'
WHERE ABS(5.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Peripherals' LIMIT 1);
SET @sku := 'INV-09';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'WEBCAM', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'WEBCAM',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 6.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 6.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(6.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 6.000, 'COMPLETED'
WHERE ABS(6.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Peripherals' LIMIT 1);
SET @sku := 'INV-10';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'HEADSET', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'HEADSET',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 7.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 7.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(7.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 7.000, 'COMPLETED'
WHERE ABS(7.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Power & Network' LIMIT 1);
SET @sku := 'INV-11';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'UPS', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'UPS',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 1.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 1.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(1.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 1.000, 'COMPLETED'
WHERE ABS(1.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Cables & Adapters' LIMIT 1);
SET @sku := 'INV-12';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'MALE TO HDMI FEMALE CONVERTER', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'MALE TO HDMI FEMALE CONVERTER',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 0.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 0.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(0.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 0.000, 'COMPLETED'
WHERE ABS(0.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Cables & Adapters' LIMIT 1);
SET @sku := 'INV-13';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'USB C-MULTI FUNCTION ADAPTER', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'USB C-MULTI FUNCTION ADAPTER',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 4.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 4.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(4.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 4.000, 'COMPLETED'
WHERE ABS(4.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Cables & Adapters' LIMIT 1);
SET @sku := 'INV-14';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'HDMI CABLES', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'HDMI CABLES',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 13.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 13.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(13.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 13.000, 'COMPLETED'
WHERE ABS(13.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Cables & Adapters' LIMIT 1);
SET @sku := 'INV-15';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'DUAL DISPLAY ADAPTER', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'DUAL DISPLAY ADAPTER',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 1.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 1.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(1.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 1.000, 'COMPLETED'
WHERE ABS(1.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Cables & Adapters' LIMIT 1);
SET @sku := 'INV-16';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, '4K DP TO HDMI ADAPTER', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = '4K DP TO HDMI ADAPTER',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 15.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 15.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(15.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 15.000, 'COMPLETED'
WHERE ABS(15.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Cables & Adapters' LIMIT 1);
SET @sku := 'INV-17';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'MINI DP TO DP ADAPTER', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'MINI DP TO DP ADAPTER',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 6.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 6.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(6.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 6.000, 'COMPLETED'
WHERE ABS(6.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Power & Network' LIMIT 1);
SET @sku := 'INV-18';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'POWER CABLES', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'POWER CABLES',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 7.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 7.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(7.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 7.000, 'COMPLETED'
WHERE ABS(7.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Power & Network' LIMIT 1);
SET @sku := 'INV-19';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'TP LINK AC600 - WIFI ADAPTER', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'TP LINK AC600 - WIFI ADAPTER',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 1.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 1.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(1.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 1.000, 'COMPLETED'
WHERE ABS(1.000 - @previous_stock) > 0.0001;

SET @category_id := (SELECT id FROM categories WHERE name = 'Computers' LIMIT 1);
SET @sku := 'INV-20';
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
SET @previous_stock := IF(@product_id IS NULL, 0, COALESCE((SELECT quantity_on_hand FROM inventory_balances WHERE location_id = @location_id AND product_id = @product_id), 0));
INSERT INTO products (sku, name, description, category_id, preferred_supplier_id, unit_of_measure, cost_price, sell_price, reorder_level, is_active)
VALUES (@sku, 'LAPTOP MAC', NULL, @category_id, NULL, 'pcs', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = 'LAPTOP MAC',
    description = NULL,
    category_id = @category_id,
    unit_of_measure = 'pcs',
    reorder_level = 0,
    is_active = 1;
SET @product_id := (SELECT id FROM products WHERE sku = @sku LIMIT 1);
INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
VALUES (@location_id, @product_id, 2.000, 0)
ON DUPLICATE KEY UPDATE quantity_on_hand = 2.000, updated_at = CURRENT_TIMESTAMP;
INSERT INTO inventory_movements (movement_type, product_id, source_location_id, destination_location_id, quantity, unit_cost, reference_type, reference_id, reason, moved_by, moved_at, previous_stock, new_stock, status)
SELECT 'ADJUSTMENT', @product_id, NULL, @location_id, ABS(2.000 - @previous_stock), NULL, 'MANUAL', NULL, 'Imported from Inventory - (1).xlsx', @moved_by, NOW(), @previous_stock, 2.000, 'COMPLETED'
WHERE ABS(2.000 - @previous_stock) > 0.0001;

COMMIT;

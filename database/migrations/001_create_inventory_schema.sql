SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS `rss_inventory`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `rss_inventory`;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS roles (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id TINYINT UNSIGNED NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_code VARCHAR(40) NOT NULL UNIQUE,
    company_name VARCHAR(150) NOT NULL,
    contact_name VARCHAR(120) NULL,
    email VARCHAR(191) NULL,
    phone VARCHAR(40) NULL,
    address_line1 VARCHAR(191) NULL,
    address_line2 VARCHAR(191) NULL,
    city VARCHAR(120) NULL,
    state VARCHAR(120) NULL,
    postal_code VARCHAR(40) NULL,
    country VARCHAR(120) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(40) NOT NULL UNIQUE,
    customer_name VARCHAR(150) NOT NULL,
    email VARCHAR(191) NULL,
    phone VARCHAR(40) NULL,
    address_line1 VARCHAR(191) NULL,
    address_line2 VARCHAR(191) NULL,
    city VARCHAR(120) NULL,
    state VARCHAR(120) NULL,
    postal_code VARCHAR(40) NULL,
    country VARCHAR(120) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    location_code VARCHAR(40) NOT NULL UNIQUE,
    location_name VARCHAR(150) NOT NULL,
    address_line1 VARCHAR(191) NULL,
    address_line2 VARCHAR(191) NULL,
    city VARCHAR(120) NULL,
    state VARCHAR(120) NULL,
    postal_code VARCHAR(40) NULL,
    country VARCHAR(120) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(60) NOT NULL UNIQUE,
    barcode VARCHAR(80) NULL UNIQUE,
    name VARCHAR(191) NOT NULL,
    description TEXT NULL,
    category_id BIGINT UNSIGNED NULL,
    preferred_supplier_id BIGINT UNSIGNED NULL,
    unit_of_measure VARCHAR(20) NOT NULL DEFAULT 'pcs',
    cost_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    sell_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    reorder_level DECIMAL(14, 3) NOT NULL DEFAULT 0.000,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_products_supplier FOREIGN KEY (preferred_supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_products_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_balances (
    location_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity_on_hand DECIMAL(14, 3) NOT NULL DEFAULT 0.000,
    quantity_reserved DECIMAL(14, 3) NOT NULL DEFAULT 0.000,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (location_id, product_id),
    CONSTRAINT fk_inv_bal_location FOREIGN KEY (location_id) REFERENCES locations(id),
    CONSTRAINT fk_inv_bal_product FOREIGN KEY (product_id) REFERENCES products(id),
    CHECK (quantity_on_hand >= 0),
    CHECK (quantity_reserved >= 0),
    CHECK (quantity_reserved <= quantity_on_hand)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(40) NOT NULL UNIQUE,
    supplier_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    status ENUM('DRAFT', 'SENT', 'PARTIALLY_RECEIVED', 'RECEIVED', 'CANCELLED') NOT NULL DEFAULT 'DRAFT',
    order_date DATE NOT NULL,
    expected_date DATE NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_po_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    CONSTRAINT fk_po_location FOREIGN KEY (location_id) REFERENCES locations(id),
    CONSTRAINT fk_po_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_po_approved_by FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_po_status_date (status, order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    ordered_qty DECIMAL(14, 3) NOT NULL,
    received_qty DECIMAL(14, 3) NOT NULL DEFAULT 0.000,
    unit_cost DECIMAL(12, 2) NOT NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_po_item_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_po_item_product FOREIGN KEY (product_id) REFERENCES products(id),
    CHECK (ordered_qty > 0),
    CHECK (received_qty >= 0),
    CHECK (received_qty <= ordered_qty),
    INDEX idx_po_item_po (purchase_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sales_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    so_number VARCHAR(40) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    status ENUM('DRAFT', 'CONFIRMED', 'PACKED', 'SHIPPED', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'DRAFT',
    order_date DATE NOT NULL,
    required_date DATE NULL,
    shipped_at DATETIME NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_so_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    CONSTRAINT fk_so_location FOREIGN KEY (location_id) REFERENCES locations(id),
    CONSTRAINT fk_so_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_so_status_date (status, order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sales_order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sales_order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    ordered_qty DECIMAL(14, 3) NOT NULL,
    reserved_qty DECIMAL(14, 3) NOT NULL DEFAULT 0.000,
    shipped_qty DECIMAL(14, 3) NOT NULL DEFAULT 0.000,
    unit_price DECIMAL(12, 2) NOT NULL,
    discount_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_so_item_so FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_so_item_product FOREIGN KEY (product_id) REFERENCES products(id),
    CHECK (ordered_qty > 0),
    CHECK (reserved_qty >= 0),
    CHECK (shipped_qty >= 0),
    CHECK (reserved_qty <= ordered_qty),
    CHECK (shipped_qty <= ordered_qty),
    INDEX idx_so_item_so (sales_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transfer_number VARCHAR(40) NOT NULL UNIQUE,
    from_location_id BIGINT UNSIGNED NOT NULL,
    to_location_id BIGINT UNSIGNED NOT NULL,
    status ENUM('DRAFT', 'IN_TRANSIT', 'RECEIVED', 'CANCELLED') NOT NULL DEFAULT 'DRAFT',
    transfer_date DATE NOT NULL,
    received_date DATE NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_transfer_from_location FOREIGN KEY (from_location_id) REFERENCES locations(id),
    CONSTRAINT fk_transfer_to_location FOREIGN KEY (to_location_id) REFERENCES locations(id),
    CONSTRAINT fk_transfer_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CHECK (from_location_id <> to_location_id),
    INDEX idx_transfer_status_date (status, transfer_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_transfer_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stock_transfer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    transfer_qty DECIMAL(14, 3) NOT NULL,
    received_qty DECIMAL(14, 3) NOT NULL DEFAULT 0.000,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_transfer_item_transfer FOREIGN KEY (stock_transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
    CONSTRAINT fk_transfer_item_product FOREIGN KEY (product_id) REFERENCES products(id),
    CHECK (transfer_qty > 0),
    CHECK (received_qty >= 0),
    CHECK (received_qty <= transfer_qty),
    INDEX idx_transfer_item_transfer (stock_transfer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    movement_type ENUM(
        'PO_RECEIVE',
        'SO_SHIP',
        'ADJUSTMENT',
        'TRANSFER_OUT',
        'TRANSFER_IN',
        'RESERVE',
        'RELEASE'
    ) NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    source_location_id BIGINT UNSIGNED NULL,
    destination_location_id BIGINT UNSIGNED NULL,
    quantity DECIMAL(14, 3) NOT NULL,
    unit_cost DECIMAL(12, 2) NULL,
    reference_type ENUM('PURCHASE_ORDER', 'SALES_ORDER', 'STOCK_TRANSFER', 'ADJUSTMENT', 'MANUAL') NOT NULL DEFAULT 'MANUAL',
    reference_id BIGINT UNSIGNED NULL,
    reason VARCHAR(255) NULL,
    moved_by BIGINT UNSIGNED NOT NULL,
    moved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movement_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_movement_source FOREIGN KEY (source_location_id) REFERENCES locations(id),
    CONSTRAINT fk_movement_destination FOREIGN KEY (destination_location_id) REFERENCES locations(id),
    CONSTRAINT fk_movement_user FOREIGN KEY (moved_by) REFERENCES users(id),
    CHECK (quantity > 0),
    INDEX idx_movements_product_date (product_id, moved_at),
    INDEX idx_movements_reference (reference_type, reference_id),
    INDEX idx_movements_type_date (movement_type, moved_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

SET FOREIGN_KEY_CHECKS = 1;

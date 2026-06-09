SET NAMES utf8mb4;
SET time_zone = '+00:00';

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



SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE categories
    ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER description;

ALTER TABLE inventory_movements
    ADD COLUMN IF NOT EXISTS previous_stock DECIMAL(14, 3) NULL AFTER quantity,
    ADD COLUMN IF NOT EXISTS new_stock DECIMAL(14, 3) NULL AFTER previous_stock,
    ADD COLUMN IF NOT EXISTS status ENUM('COMPLETED', 'REVERSED', 'PENDING') NOT NULL DEFAULT 'COMPLETED' AFTER moved_at;

SET FOREIGN_KEY_CHECKS = 1;



SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS employees (
  id INT(11) NOT NULL AUTO_INCREMENT,
  fname VARCHAR(255) DEFAULT NULL,
  lname VARCHAR(255) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  personal_email VARCHAR(255) DEFAULT NULL,
  contact VARCHAR(255) DEFAULT NULL,
  position VARCHAR(255) DEFAULT NULL,
  status ENUM('active','inactive') DEFAULT NULL,
  Emp_Type ENUM('Probationary','Regular','Old_Regular') DEFAULT 'Probationary',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  username VARCHAR(100) DEFAULT NULL,
  password VARCHAR(255) DEFAULT NULL,
  company VARCHAR(255) DEFAULT NULL,
  profile_image VARCHAR(255) DEFAULT NULL,
  profile_picture VARCHAR(255) DEFAULT NULL,
  official_sched INT(11) DEFAULT NULL,
  role ENUM('employee','internal') NOT NULL DEFAULT 'employee',
  admin_rights_hdesk ENUM('it','hr','superadmin') DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;



SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS asset_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL,
    assigned_date DATE NOT NULL,
    returned_date DATE NULL,
    status ENUM('ACTIVE', 'RETURNED') NOT NULL DEFAULT 'ACTIVE',
    notes TEXT NULL,
    attachment_path VARCHAR(255) NULL,
    attachment_name VARCHAR(255) NULL,
    attachment_mime VARCHAR(120) NULL,
    attachment_size BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_asset_assignments_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
    INDEX idx_asset_assignments_employee (employee_id),
    INDEX idx_asset_assignments_status_date (status, assigned_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS asset_assignment_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_assignment_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    equipment_name VARCHAR(191) NOT NULL,
    description VARCHAR(255) NULL,
    serial_number VARCHAR(120) NULL,
    reason VARCHAR(255) NULL,
    quantity DECIMAL(14, 3) NOT NULL DEFAULT 1.000,
    returned_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assignment_items_assignment FOREIGN KEY (asset_assignment_id) REFERENCES asset_assignments(id) ON DELETE CASCADE,
    CONSTRAINT fk_assignment_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    CHECK (quantity > 0),
    INDEX idx_assignment_items_assignment (asset_assignment_id),
    INDEX idx_assignment_items_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;



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



INSERT INTO `employees` (`id`, `fname`, `lname`, `email`, `personal_email`, `contact`, `position`, `status`, `Emp_Type`, `created_at`, `username`, `password`, `company`, `profile_image`, `profile_picture`, `official_sched`, `role`, `admin_rights_hdesk`) VALUES
(1, 'Vincent Kevin', 'Santos', 'Vincent.Antonio@entrygroup.com.au', 'vincentvinz17@gmail.com', '0905 919 2943', 'Student Support - Marking', 'active', 'Regular', '2025-04-28 17:04:12', 'vincent.santos', '121819', 'Entry Education', NULL, 'profile_1_1750742776.png', 4, 'employee', NULL),
(2, 'Shaina', 'Dela Cruz', 'shainadc86@gmail.com', 'shainadc86@gmail.com', '0935 766 5039', 'Student Support Mentoring', 'active', 'Regular', '2025-04-28 17:04:12', 'shaina.dela cruz', 'Macchiato14', 'Entry Education', NULL, 'profile_2_1749605234.jpeg', 5, 'employee', NULL),
(3, 'Renalyn Abamo', 'Josafat', 'reny@entrygroup.com.au', 'rajosafat.cca@gmail.com', '0939 245 6150', 'Student Support Mentoring', 'active', 'Regular', '2025-04-28 17:04:12', 'renalyn.josafat', '123456', 'Entry Education', NULL, 'profile_3_1752052468.png', 12, 'employee', NULL),
(4, 'Joel Lusung', 'Alimurong', 'Joel.Alimurong@entrygroup.com.au', 'jhei.el1768@gmail.com', '0966 539 4550', 'Student Support - Marking', 'active', 'Regular', '2025-04-28 17:04:12', 'joel.alimurong', '261768', 'Entry Education', NULL, 'profile_4_1749606383.jpeg', 13, 'employee', NULL),
(5, 'Aizel Santos', 'Castro', 'aizel.castro@entrygroup.com.au', 'aizel.castro01@gmail.com', '0926 215 0722', 'Student Support - Marking', 'active', 'Regular', '2025-04-28 17:04:12', 'aizel.castro', '123789', 'Entry Education', NULL, NULL, 13, 'employee', NULL),
(6, 'Reymark Bryan Silvano', 'Colis', 'bryan@entrygroup.com.au', 'reymarkbryancolis@gmail.com', '0927 014 4692', 'Technical Student Support - Team Leader', 'active', 'Regular', '2025-04-28 17:04:12', 'reymark.colis', 'iodine86236', 'Entry Education', NULL, 'profile_6_1751354099.png', 8, 'employee', NULL),
(7, 'Francis Emmanuel Veloso', 'Fernandez', 'francis@entrygroup.com.au', 'francis2208@gmail.com', '0967 201 4330', 'Student Support - Marking Team Leader', 'inactive', 'Regular', '2025-04-28 17:04:12', 'francis.fernandez', 'RSS@69', 'Entry Education', NULL, 'profile_7_1751353606.png', 13, 'employee', NULL),
(8, 'Cedrick', 'Galgo', 'Cedrick.Galgo@entrygroup.com.au', 'cedrickgalgo@gmail.com', '0948 914 9733', 'IT Support', 'inactive', 'Regular', '2025-04-28 17:04:12', 'cedrick.galgo', '123456', 'Entry Education', NULL, NULL, 4, 'employee', NULL),
(9, 'Shigeru', 'Otsuka', 'Shigeru.Otsuka@entrygroup.com.au', 'shigeruslayer12345@gmail.com', '0939 286 1648', 'Instructional Designer - Technical Specialist', 'active', 'Regular', '2025-04-28 17:04:12', 'shigeru.centina', 'P@ssw0rd01', 'Entry Education', NULL, 'profile_9_1751354113.jpg', 4, 'employee', NULL),
(10, 'Rhegene', 'Ronquillo', 'reggie@entrygroup.com.au', 'rronquillo0727@gmail.com', '0916 936 6370', 'Technical Student Support', 'active', 'Regular', '2025-04-28 17:04:12', 'rhegene.ronquillo', '123456', 'Entry Education', NULL, 'profile_10_1751353416.JPG', 4, 'employee', NULL),
(11, 'Mary Ann Vallejos ', 'Soriano', 'mary@entrygroup.com.au', 'habibisoriano@yahoo.com', '0906 255 2990', 'Sales New Student Enquiries', 'active', 'Regular', '2025-04-28 17:04:12', 'mary.soriano', '022714', 'Entry Education', NULL, 'profile_11_1749624970.jpeg', 13, 'employee', NULL),
(12, 'Beverly Taloban ', 'Gatbonton', 'beverly.gatbonton@entrygroup.com.au', 'beverlygatbonton29@gmail.com', '0920 403 7997', 'Team Leader Sales - New Student Enquiries', 'active', 'Regular', '2025-04-28 17:04:12', 'beverly.gatbonton', '$Richbev29', 'Entry Education', NULL, NULL, 4, 'employee', NULL),
(13, 'Rogelio', ' Malinao Jr', 'rogelio.malinao@entrygroup.com.au', 'rogelio.malinao@gmail.com', '0976 212 6539', 'Student Support - Marking', 'active', 'Regular', '2025-04-28 17:04:12', 'rogelio.malinao', 'Entry_Rogelio143', 'Entry Education', NULL, NULL, 2, 'employee', NULL),
(14, 'Jillian', 'Agas', 'jillian.agas@entrygroup.com.au', 'jjjillian052089@gmail.com', '0915 546 4289', 'Technical Student Support', 'active', 'Regular', '2025-06-10 20:25:36', 'Jillian.Agas', 'Sesshou#052089', 'Entry Education', NULL, NULL, 13, 'employee', NULL),
(15, 'Evanel', ' Navalon', 'evanel.navalon@entrygroup.com.au', 'evanelnavalon@gmail.com', '0946 882 2198', 'Technical Student Support', 'active', 'Regular', '2025-04-28 17:04:12', 'evanel.navalon', '033096', 'Entry Education', NULL, 'profile_15_1752052633.png', 6, 'employee', NULL),
(16, 'Ian Myco', 'Aguilar', 'ian.aguilar@entrygroup.com.au', 'iammycovital@gmail.com', '0976 198 9787', 'Student Support - Marking', 'active', 'Regular', '2025-04-28 17:04:12', 'ian.aguilar', 'ian1998', 'Entry Education', NULL, NULL, 13, 'employee', NULL),
(17, 'Reneeca Villapaña', ' Benalla', 'Reneeca@entrygroup.com.au', 'reneeca.benalla@gmail.com', '0909 204 3758', 'Content Writer & Instructional Designer', 'active', 'Regular', '2025-04-28 17:04:12', 'reneeca.benalla', '123456', 'Entry Education', NULL, NULL, 4, 'employee', NULL),
(18, 'Edith', 'David Mataga', 'Edith@entrygroup.com.au', 'edghie03@gmail.com', '0935 563 9451', 'Sales New Student Enquiries', 'active', 'Regular', '2025-04-28 17:04:12', 'edith.mataga', '123456', 'Entry Education', NULL, NULL, 5, 'employee', NULL),
(20, 'Alfred Naguit', 'Ocampo', 'Alfred@entrygroup.com.au', 'derfla61@gmail.com', '0963 256 7621', 'Conveyancing Client Support', 'inactive', 'Regular', '2025-04-28 17:04:12', 'alfred.ocampo', '123456', 'Entry Education', NULL, NULL, 4, 'employee', NULL),
(21, 'Jennifer', 'Trinidad', 'jennifer.trinidad@entrygroup.com.au', 'jennifertrinidad0103@gmail.com', '0928 225 5869', 'Student Support - Marking', 'active', 'Regular', '2023-08-06 17:04:12', 'jennifer.trinidad', '123456', 'Entry Education', NULL, 'profile_21_1752047143.png', 13, 'employee', NULL),
(22, 'Sean Justine', 'Mendoza', 'sean.mendoza@entrygroup.com.au', 'mendozaseanjustine@gmail.com', '0977 019 9064', 'Student Support - Marking', 'active', 'Regular', '2023-08-15 17:04:12', 'sean.mendoza', '123456', 'Entry Education', NULL, 'profile_22_1751353445.png', 13, 'employee', NULL),
(24, 'Elritz', 'Crisanto', 'elritz.crisanto@entrygroup.com.au', 'ritztiong@gmail.com', '0961 289 3349', 'Student Support - Marking', 'active', 'Regular', '2023-08-20 17:04:12', 'elritz.crisanto', '123456', 'Entry Education', NULL, 'profile_24_1751354386.png', 2, 'employee', NULL),
(25, 'Analiza Taloban ', 'Gatbonton', 'analiza.gatbonton@entrygroup.com.au', 'analizagatbonton05@gmail.com', '0961 820 1167', 'Student Support - Marking', 'active', 'Regular', '2023-08-20 17:04:12', 'analiza.gatbonton', 'AnaLiza31!', 'Entry Education', NULL, 'profile_25_1751353994.JPG', 14, 'employee', NULL),
(26, 'Franklin Roos', 'Cinco Pabillano', 'estimating@empirewestelectrical.com.au', 'frankpabillano@gmail.com', '0961 498 0228', 'Electrical Estimator', 'inactive', 'Regular', '2023-11-02 17:04:12', 'franklin.pabillano', '123456', 'onn one', NULL, NULL, 4, 'employee', NULL),
(27, 'Kristian David', 'Bansil', 'ITsupport@mtunderground.com', 'ian_pudz@icloud.com', '0939 905 0288', 'Web Developer / Admin & IT Support', 'active', 'Regular', '2024-02-11 17:04:12', 'kristian.bansil', 'Anew903west889!', 'Maintenance Tech', NULL, NULL, 4, 'employee', NULL),
(28, 'Louis Fernand', 'Austria', 'louis.austria@entrygroup.com.au', 'louisaustria0@gmail.com', '0998 423 4020', 'Graphic Designer', 'active', 'Regular', '2024-04-03 17:04:12', 'louis.austria', 'Bri+yulo123', 'Entry Group', NULL, 'profile_28_1751353318.jpeg', 6, 'employee', NULL),
(29, 'Johana Rose Perez ', 'Gueco', 'johanarose.gueco@entrygroup.com.au', 'johanagueco@gmail.com', '0906 213 7926', 'Student Support - Marking', 'active', 'Regular', '2024-04-03 17:04:12', 'johana.gueco', 'Rebisco21*', 'Entry Education', NULL, 'profile_29_1751450128.png', 13, 'employee', NULL),
(30, 'Erika Seriosa ', 'Pineda', 'erika.seriosa@entrygroup.com.au', 'rickzseriosa@gmail.com', '0926 355 6900', 'Student Support - Marking', 'active', 'Regular', '2024-04-03 17:04:12', 'erika.pineda', 'Erse@1023', 'Entry Education', NULL, 'profile_30_1751450142.png', 13, 'employee', NULL),
(31, 'Jhunel Carlo Traifalgar ', 'Samodio', 'jhunelcarlo.samodio@entrygroup.com.au', 'gun.lazuli@gmail.com', '0995 483 5711', 'Student Support - Marking', 'active', 'Regular', '2024-04-03 17:04:12', 'jhunel.samodio', 'mamamo0514', 'Entry Education', NULL, 'profile_31_1751598183.png', 13, 'employee', NULL),
(33, 'Aldwin John', 'Arceo Lozano', 'aldwinjohn.lozano@entrygroup.com.au', 'imaj.lozano@gmail.com', '0949 369 7174', 'Sales New Student Enquiries', 'active', 'Regular', '2024-04-07 17:04:12', 'aldwin.lozano', 'Dumbapples@0417!', 'Entry Education', NULL, 'profile_33_1751407949.jpg', 7, 'employee', NULL),
(34, 'Yris Gaelle', ' Camerino', 'yyrish@gmail.com', 'yyrish@gmail.com', '0912 937 9482', 'Student Support - Marking', 'active', 'Regular', '2024-04-30 17:04:12', 'yris.camerino', 'qwe987*', 'Entry Education', NULL, NULL, 13, 'employee', NULL),
(35, 'Nika', 'Bacongallo', 'nika.bacongallo@gmail.com', 'nika.bacongallo@gmail.com', '0919 263 0516', 'Student Support - Marking', 'active', 'Regular', '2024-05-19 17:04:12', 'nika.bacongallo', 'Jn@052312', 'Entry Education', NULL, NULL, 13, 'employee', NULL),
(36, 'Denver Orlanda', 'Castillano', 'dcstudio.creative@gmail.com', 'dcstudio.creative@gmail.com', '0991 933 2312', 'Draftsman', 'inactive', 'Regular', '2024-06-09 17:04:12', 'denver.castillano', '123456', 'DNA Furniture & Cabinets', NULL, NULL, 4, 'employee', NULL),
(37, 'Marnie Perez ', 'Catalogo', 'marniecatalogo99@gmail.com', 'marniecatalogo99@gmail.com', '0905 453 8974', 'Technical Student Support', 'active', 'Probationary', '2024-06-09 17:04:12', 'marnie.catalogo', '112620', 'Entry Education', NULL, 'profile_37_1769514812.jpg', 13, 'employee', NULL),
(38, 'Rex Ryan', 'Patrimonio', 'rexryanpatrimonio@gmail.com', 'rexryanpatrimonio@gmail.com', '0916 189 2527', 'Technical Student Support', 'active', 'Regular', '2024-06-09 17:04:12', 'ryan.patrimonio', 'rexryanpogi', 'Entry Education', NULL, NULL, 7, 'employee', NULL),
(41, 'Ma. Charisma S.', 'Platero', 'charisma.platero@gmail.com', 'charisma.platero@gmail.com', '09566998110', 'Estimator', 'active', 'Regular', '2024-07-21 17:04:12', 'charisma.platero', '123456', 'Fratelli Homes', NULL, NULL, 4, 'employee', NULL),
(43, 'Lovelaine', 'Celeste', 'Lovelaine.Celeste@entrygroup.com.au', 'lovelaineceleste@yahoo.com', '09761349029', 'Sales New Student Enquiries', 'active', 'Regular', '2024-08-11 17:04:12', 'lovelaine.celeste', 'RSS2024', 'Entry Education', NULL, NULL, 5, 'employee', NULL),
(46, 'Ivy', 'Nuñez', 'ivynunez26@gmail.com', 'ivynunez26@gmail.com', 'N/A', 'Student Support - Marking', 'active', 'Regular', '2024-08-11 17:04:12', 'ivy.nuñez', 'Lois0707@', 'Entry Education', NULL, NULL, 5, 'employee', NULL),
(47, 'Glory Ann', 'Balderas', 'Glory@fratellihomeswa.com.au', 'gloryannbalderas@gmail.com', '0981 107 2866', 'Estimator', 'active', 'Regular', '2024-09-15 17:04:12', 'glory.balderas', '123456', 'Fratelli Homes', NULL, 'profile_47_1751848664.png', 4, 'employee', NULL),
(49, 'Julie Anne', 'Maclang', 'macjulg08@gmail.com', 'macjulg08@gmail.com', 'N/A', 'Student Support - Marking', 'active', 'Regular', '2024-10-13 17:04:12', 'julie.maclang', '100284', 'Entry Education', NULL, NULL, 4, 'employee', NULL),
(50, 'Francis Eugene Aguhayon', 'Bondoc', 'francis.bondoc22@gmail.com', 'francis.bondoc22@gmail.com', '629-683-416-000', 'Draftsman', 'inactive', 'Regular', '2025-04-29 06:02:24', 'francis.bondoc', '123456', 'Venaso', NULL, 'profile_50_1751860113.jpg', 16, 'employee', NULL),
(52, 'Althea Tansingco', 'Makabenta', 'document.control@bugardi.com.au', 'atansingcomakabenta@yahoo.com', '165-661-778-000', 'Document Controller', 'active', 'Regular', '2025-04-29 06:02:24', 'althea.makabenta', '123456', 'Bugardi', NULL, NULL, 6, 'employee', NULL),
(53, 'Christian Nioda', 'Mar', 'christianmar673@gmail.com', 'christianmar673@gmail.com', '387-307-553-000', 'Sales New Student Enquiries', 'active', 'Regular', '2025-04-29 06:02:24', 'christian.mar', 'Apple@123!', 'Entry Education', NULL, 'profile_53_1773136921.PNG', 13, 'employee', NULL),
(55, 'Jeffry', 'Macapagal', 'jeff.macapagal017@gmail.com', 'jeff.macapagal017@gmail.com', '09984097709', 'Operations Administrator', 'active', 'Regular', '2025-04-29 06:02:24', 'jeffry.macapagal', '123456', 'TRSWA', NULL, NULL, 3, 'employee', NULL),
(56, 'Allen Sobrepeña', 'Capati', 'allen.capati@entrygroup.com.au', 'allen.capati95@gmail.com', '468-497-485-000', 'Technical Student Support', 'active', 'Regular', '2025-04-29 06:02:24', 'allen.capati', '042316!', 'Entry Education', NULL, 'profile_56_1751353871.png', 4, 'employee', NULL),
(57, 'Angelica Rosario', 'Estanio', 'angelica.estanio@entrygroup.com.au', 'angelica.estanio4@gmail.com', '09666483316', 'Student Support - Marking', 'active', 'Regular', '2025-04-29 06:02:24', 'angelica.estanio', '096664', 'Entry Education', NULL, NULL, 13, 'employee', NULL),
(58, 'Adonis Del Mundo', 'Jabinal', 'adonis.jabinal@bugardi.com.au', 'donjabinal@gmail.com', '175-092-008-000', 'Project Coordinator', 'active', 'Regular', '2025-04-29 06:02:24', 'adonis.jabinal', 'atleast6', 'Bugardi', NULL, NULL, 5, 'employee', NULL),
(59, 'Joshwea Mercado', 'Monis', 'joshwea.monis@entrygroup.com.au', 'mjoshwea@gmail.com', '332-760-833-000', 'Student Support - Marking', 'active', 'Regular', '2025-04-29 06:02:24', 'joshwea.monis', '942103', 'Entry Education', NULL, NULL, 13, 'employee', NULL),
(60, 'Janeth Sedon', 'Solayao', 'janeth.solayao@entrygroup.com.au', 'janethsolayao32@gmail.com', 'TO FOLLOW', 'Student Support - Marking', 'active', 'Regular', '2025-04-29 06:02:24', 'janeth.solayao', '123456', 'Entry Education', NULL, NULL, 4, 'employee', NULL),
(61, 'Ray Jinder Villena', 'Singh', 'rvschk@gmail.com', 'rvschk@gmail.com', '350-760-267-000', 'Executive Assistant', 'active', 'Regular', '2025-04-29 06:02:24', 'rj.singh', '123456', 'Rowland Plumbing & Gas', NULL, NULL, 4, 'employee', NULL),
(62, 'Shirmiley Canlas', 'Quizon', 'shirmiley.quizon@bugardi.com.au', 'shirmiley.quizon@gmail.com', '210-283-638-000', 'Recruitment Mobilization Officer', 'active', 'Regular', '2025-04-29 06:02:24', 'shirmiley.quizon', '123456', 'Bugardi', NULL, NULL, 6, 'employee', NULL),
(63, 'Maria Ñina', 'Dollentes Cruz', 'nina.dollentes@entrygroup.com.au', 'marianinadollentes@gmail.com', '09122208976', 'Accountant', 'active', 'Regular', '2025-04-29 06:02:24', 'Nina.dollentes', '347243', 'Entry Education', NULL, 'profile_63_1752186681.jpeg', 4, 'employee', NULL),
(64, 'Jerzi Chezka Medel', 'Libatique', 'jerzi.libatique@entrygroup.com.au', 'jerzichezkamedel@gmail.com', '396-119-405-000', 'Accountant', 'inactive', 'Regular', '2025-04-29 06:02:24', 'jerzi.libatique', '123456', 'Entry Education', NULL, 'profile_64_1751353441.jpg', 4, 'employee', NULL),
(65, 'Dou Lester Sabando', 'Nuñeza ', 'lester.nuneza@bugardi.com.au', 'lesternuneza@gmail.com', '+639618436508', 'HSEQ Assistant Manager', 'active', 'Regular', '2025-04-29 06:02:24', 'Lester.nuñeza ', '123456', 'Bugardi', NULL, NULL, 5, 'employee', NULL),
(66, 'Godwin', 'Ocampo', 'tgodbtg04@gmail.com', 'tgodbtg04@gmail.com', '09603985500', 'Tax Accountant', 'active', 'Regular', '2025-04-29 06:02:24', 'godwin.ocampo ', '123456', 'Denning & Associates', NULL, NULL, 4, 'employee', NULL),
(67, 'Apryl Pasion ', 'Yap', 'apryl.pasion@bugardi.com.au', 'aprylpolicarpio@gmail.com', 'TO FOLLOW', 'Recruitment Mobilization Officer', 'active', 'Regular', '2025-04-29 06:02:24', 'apryl.pasion', 'apyap25!', 'Bugardi', NULL, NULL, 6, 'employee', NULL),
(68, 'Christine Khlaryss', 'Angeles', 'christinekhlaryss@gmail.com', 'christinekhlaryss@gmail.com', '351-635-569-000', 'Tax Accountant', 'active', 'Regular', '2025-04-29 06:02:24', 'christine.angeles', 'CKAngeles1997.', 'Denning', NULL, NULL, 8, 'employee', NULL),
(69, 'Trisha Mae Adriano', 'McGregor', 'trisha_mcgregor@yahoo.com', 'trisha_mcgregor@yahoo.com', '09289852739', 'Renovation Draftsman', 'active', 'Probationary', '2025-04-29 06:02:24', 'trisha.mcgregor', '123456', 'Ridge Renovation', NULL, NULL, 4, 'employee', NULL),
(70, 'John Michael Comprado', 'Briones', 'jamenabriones14@gmail.com', 'jamenabriones14@gmail.com', '620-743-947-000', 'Commercial Estimator', 'active', 'Regular', '2025-04-29 06:02:24', 'jm.briones', '123456', 'TRSWA', NULL, NULL, 4, 'employee', NULL),
(71, 'Precious Zahra Cortez', 'Cabusao', 'zahracortez95@gmail.com', 'zahracortez95@gmail.com', '09209089122', 'Hydraulics Estimator', 'active', 'Probationary', '2025-04-29 06:02:24', 'zahra.cabusao', '051995', 'Leeway Group', NULL, NULL, 4, 'employee', NULL),
(72, 'Milbert ', 'Sambile', 'milbert@millersroofing.com.au', 'milbert.sambile@gmail.com', '09663995493', 'Estimator', 'inactive', 'Regular', '2025-06-02 17:08:23', 'Milbert.Sambile', '123456', 'Miller\'s Roofing', NULL, 'profile_72_1751614448.png', 4, 'employee', NULL),
(73, 'Ryan Arwin', 'David', 'davidryarwin@gmail.com', 'davidryarwin@gmail.com', NULL, 'Operations Admin', 'active', 'Regular', '2025-06-23 16:11:24', 'Ryan.David', '123456', 'TTS', NULL, NULL, 9, 'employee', NULL),
(74, 'John Bryan', 'Alvarez', 'johnalvarez930@gmail.com', 'johnalvarez930@gmail.com', NULL, 'Operations Admin', 'active', 'Regular', '2025-06-23 16:11:24', 'John.Alvarez', '654321', 'TTS', NULL, NULL, 9, 'employee', NULL),
(75, 'Oliva', 'Bautista', 'olivesantos.bautista@gmail.com', 'olivesantos.bautista@gmail.com', '', 'Operations Admin', 'active', 'Regular', '2025-06-23 16:11:24', 'Oliva.Bautista', 'ttsbuilt20', 'TTS', NULL, NULL, 9, 'employee', NULL),
(76, 'Sarah', 'Caraan', 'sarahcaraan31@gmail.com', 'sarahcaraan31@gmail.com', '', 'Operations Admin', 'active', 'Regular', '2025-06-23 16:11:24', 'Sarah.Caraan', '123456', 'TTS', NULL, NULL, 9, 'employee', NULL),
(77, 'Alfie', 'Guillermo', 'alfie.guillermo0219@gmail.com', 'alfie.guillermo0219@gmail.com', '09773853986', 'Estimator', 'active', 'Regular', '2025-06-23 16:11:24', 'Alfie.Guillermo', '123456', 'TTS', NULL, NULL, 9, 'employee', NULL),
(78, 'Brittany', 'Yulo', 'yulobrittany@gmail.com', 'brittany@trswa.net.au', '09201337394', 'Commercial Assistant Administrator', 'active', 'Regular', '2025-04-29 06:02:24', 'Brittany.Yulo', 'Trswabrit', 'TRSWA', NULL, NULL, 5, 'employee', NULL),
(79, 'John ', 'Sanoza', 'sanozajohn@gmail.com', 'sanozajohn@gmail.com', '', 'Senior Full Stack Developer ', 'active', 'Regular', '2025-07-02 21:24:31', 'John.Sanoza', '123456', 'Viper', NULL, NULL, 4, 'employee', NULL),
(80, 'Jae', 'Fernandez', 'jae@kurved.com.au', 'mjae.fernandez@yahoo.com', '09610912234', 'Roofing Estimator', 'active', 'Regular', '2025-07-03 23:37:19', 'Jae.Fernandez', 'Fernandez@1215', 'Miller\'s Roofing', NULL, NULL, 8, 'employee', NULL),
(81, 'Sherry Rose Ann', 'Patawaran', 'marketing@hammerhire.com.au', 'sherryrosepatawaran@gmail.com', '09398522815', 'Marketing Coordinator', 'active', 'Regular', '2025-07-03 23:37:19', 'Sherry.Patawaran', '123456', 'HammerHire', NULL, NULL, 8, 'employee', NULL),
(82, 'Gabriel', 'Capiral', 'capiralgabriel@gmail.com', NULL, '', '', 'active', 'Regular', '2025-07-13 15:16:10', 'Gabriel.Capiral', 'Zappa_Gab08', 'Fratelli Homes', NULL, NULL, 4, 'employee', NULL),
(83, 'Joshua', 'Manalili', 'jshmnl1528@gmail.com', NULL, '09616126980', 'Network Controller', 'active', 'Regular', '2025-07-13 15:18:58', 'Joshua.Manalili', 'Myamarra@13', 'BUSQLD', NULL, NULL, 4, 'employee', NULL),
(84, 'Roi Dane', 'Pangilinan', 'roidane.pangilinan@gmail.com', NULL, '', ' Network Controller', 'active', 'Regular', '2025-07-13 15:19:28', 'Roi.Pangilinan', 'Akopanaman24!!!', 'BUSQLD', NULL, NULL, 4, 'employee', NULL),
(85, 'Paul', 'Pasion', 'Tofollow@gmail.com2', NULL, '', ' Network Controller', 'inactive', 'Probationary', '2025-07-13 15:20:02', 'Paul.Pasion', '123456', 'BUSQLD', NULL, NULL, 4, 'employee', NULL),
(86, 'Jonas', 'Dela Cruz', '', NULL, '', 'Draftsman', 'active', 'Regular', '2025-07-13 15:18:11', 'Jonas.Delacruz', '123456', 'Alpha Industry', NULL, NULL, 8, 'employee', NULL),
(87, 'Lance', 'Libo', 'lancejomerlibo@gmail.com', NULL, '0951106693', 'Front-end Flutter Developer', 'inactive', 'Regular', '2025-07-17 22:06:34', 'lance.libo', '123456', 'Viper', NULL, 'profile_87_1753088590.png', 5, 'employee', NULL),
(88, 'Vincent', 'Cabico', 'michealvincicabico@gmail.com', NULL, '09995582957', 'Draftsman', 'inactive', 'Regular', '2025-07-17 23:50:49', 'vincent.cabico', '123456', 'Venaso', NULL, NULL, 16, 'employee', NULL),
(89, 'Roxanne', 'Tulaytay', 'roxanne.tulaytay@gmail.com', NULL, '09668276474', 'Accounts Payable Officer', 'active', 'Regular', '2025-07-17 23:56:05', 'roxanne.tulaytay', '123456', 'Hammerhire', NULL, NULL, NULL, 'employee', NULL),
(1002, 'Neil Anthony', 'Costelloe', 'Neil.Costelloe@resourcestaff.com.ph', 'neilcosetelloe@gmail.com', NULL, 'General Manager', 'active', 'Regular', '2024-05-19 08:00:00', 'neil.costelloe', '123456', 'RSS', NULL, NULL, 5, 'internal', NULL),
(1003, 'Cristina Miranda', 'Pangan', 'Tina.Pangan@resourcestaff.com.ph', 'thine2miranda@gmail.com', '0915 056 1780', 'Business Operations Lead', 'active', 'Regular', '2024-03-31 08:00:00', 'tina.pangan', '123456', 'RSS', NULL, NULL, 4, 'internal', NULL),
(1004, 'Rica Joy Viray', 'Tolomia', 'Rica.Tolomia@resourcestaff.com.ph', 'Rica.Tolomia@resourcestaff.com.ph', '0917 389 7962', 'TA/HR Specialist', 'active', 'Regular', '2024-08-11 08:00:00', 'rj.tolomia', '123456', 'RSS', NULL, 'profile_1004_1751339641.png', 4, 'internal', NULL),
(1005, 'Johsua Torninos', 'Dimla', 'johsua.dimla1986@gmail.com', 'johsua.dimla1986@gmail.com', '09292741102', 'Facilities and Admin Support', 'active', 'Regular', '2024-09-29 08:00:00', 'johsua.dimla', '123456', 'RSS', NULL, NULL, 7, 'employee', NULL),
(1006, 'Cedrick', 'Arnigo', 'Cedrick.Arnigo@resourcestaff.com.ph', 'cedrickarnigo1723@gmail.com', '09938642974', 'IT Support Specialist', 'inactive', 'Regular', '2025-05-27 15:09:46', 'Cedrick.Arnigo', '123456', 'RSS', NULL, 'profile_1006_1751347603.jpg', 4, 'internal', NULL),
(1007, 'Felicci Alejan Mariesther', 'Herrera', 'herrerafelicci@gmail.com', 'herrerafelicci@gmail.com', '09982971494', 'Admin', 'active', 'Regular', '2025-06-01 22:19:09', 'Peach.Herrera', 'seasalt', 'RSS', NULL, 'profile_1007.jpg', 4, 'internal', NULL),
(1009, 'Resty James', 'Nazareno', 'rjmanago@gmail.com', 'rjmanago@gmail.com', '09763659773', 'IT Intern', 'active', 'Regular', '2025-06-09 18:48:29', 'Kiras001', 'vosfows13', 'RSS', NULL, 'profile_1009_1749770448.jpeg', 4, 'internal', 'superadmin'),
(1024, 'Alexander', 'Tayao', 'alextayao23@gmail.com', NULL, '+639063177751', 'Draftsman', 'active', 'Regular', '2025-08-03 22:46:57', 'alex.tayao', 'P@xxword!', 'RSS', NULL, NULL, 4, 'employee', NULL),
(1025, 'Kimberly', 'Dacquil', 'Tofollow@gmail.com2', NULL, '', 'Accounts Administrator', 'active', 'Regular', '2025-08-14 06:34:40', 'kim.dacquil', 'OnTime_Kim1225!', 'Onn1', NULL, NULL, NULL, 'employee', NULL),
(1026, 'Karen', 'Belangel', 'karen@zeelkitchens.com.au', NULL, '', 'Executive Assistant', 'active', 'Regular', '2025-08-15 05:30:49', 'karen.belangel', 'zeels_karen', 'ZeelKitchens', NULL, NULL, NULL, 'employee', NULL),
(1027, 'Rebecca', 'David', 'rebeccad.david@gmail.com', NULL, '09177047621', 'Bookkeeper', 'inactive', 'Probationary', '2025-08-21 23:43:59', 'rebecca.david', '123456', 'Venaso Selections', NULL, NULL, 16, 'employee', NULL),
(1028, 'Joseph', 'David', 'josephdavid521@gmail.com', NULL, '09355156272', 'Electrical Estimator', 'inactive', 'Probationary', '2025-09-07 12:10:29', 'Joseph.David', 'david042519', 'Boiso\'s Electrical', NULL, NULL, 4, 'employee', NULL),
(1029, 'Chloedean', 'Flores', 'deaaan920@gmail.com', NULL, '09359157318', 'Genereal Service Administrator', 'active', 'Regular', '2025-09-07 12:12:15', 'Chloedean.Flores', '123456', 'Hammerhire', NULL, NULL, 8, 'employee', NULL),
(1030, 'Ron', 'Cueto', 'ron.cueto@bugardi.com.au', NULL, '09770931165', 'Design Coordinator', 'active', 'Regular', '2025-09-14 09:09:35', 'Ron.Cueto', '123456', 'Bugardi', NULL, NULL, 4, 'employee', NULL),
(1031, 'Kryssa', 'Gabatino', 'kryssagabatino17@gmail.com', NULL, '09399348559', 'Administrative Assistant', 'inactive', 'Probationary', '2025-09-14 09:15:50', 'Kryssa.Gabatino', '072221Kryssa!!!', 'Ford and Doonan', NULL, NULL, 4, 'employee', NULL),
(1032, 'Carl', 'Tupaz', 'tupaz.carldave@gmail.com', NULL, '09276009751', 'Electrical Estimator', 'inactive', 'Probationary', '2025-09-14 23:02:07', 'Carl.Tupaz', 'Carldave08!', 'Eastman Electrical', NULL, NULL, 8, 'employee', NULL),
(1033, 'Mylene', 'Torres', 'mylene.dauz@yahoo.com', NULL, '09202575993', 'Accounts Payable Officer', 'active', 'Probationary', '2025-10-05 23:58:12', 'Mylene.Torres', '123456', '', NULL, NULL, NULL, 'employee', NULL),
(1034, 'Jairus', 'Ignacio', 'jairusignaciogomez@gmail.com', NULL, '09171553644', 'Digital Marketing', 'active', 'Probationary', '2025-11-14 07:53:56', 'Jairus.Ignacio', '123456', 'Quality Roofing', NULL, NULL, 8, 'employee', NULL),
(1035, 'Ryan Jae', 'Tiglao', 'jaeliz0529@gmail.com', NULL, '09284652540', 'IT/Data Analyst', 'active', 'Regular', '2025-11-14 07:58:57', 'RJ.Tiglao', 'bugardi_ryanjae', 'Bugardi', NULL, NULL, 5, 'employee', NULL),
(1036, 'Jeiel Nash', 'Guevarra', 'Jeiel.Guevarra@stoddarts.com.au', NULL, '09760645925', 'Architectural Detailer', 'active', 'Probationary', '2025-11-21 07:13:33', 'Nash.Guevarra', '123456', '', NULL, NULL, 8, 'employee', NULL),
(1037, 'Elmer', 'Gagote', 'Elmer.Gagote@stoddarts.com.au', NULL, '09083100906', 'Architectural Detailer', 'active', 'Probationary', '2025-11-21 07:28:01', 'Elmer.Gagote', '123456', 'Stoddart Group', NULL, NULL, 8, 'employee', NULL),
(1038, 'Jay Kimbert', 'Asuncion', 'Jay.Asuncion@stoddarts.com.au', NULL, '09192566802', 'Architectural Detailer', 'active', 'Probationary', '2025-11-21 07:30:22', 'Jay.Asuncion', '123456', '', NULL, NULL, 8, 'employee', NULL),
(1039, 'Abigael', 'Bunda', 'Abigael.Bunda@stoddarts.com.au', NULL, '09277375956', 'Architectural Detailer', 'active', 'Probationary', '2025-11-21 07:37:21', 'Abi.Bunda', 'Acb9293!!', 'Stoddart Group', NULL, NULL, 8, 'employee', NULL),
(1040, 'Eri', 'Otaka', 'Eri.Otaka@stoddarts.com.au', NULL, '09663190631', 'Architectural Detailer', 'active', 'Probationary', '2025-11-21 07:40:19', 'Eri.Otaka', '123456', 'Stoddart Group', NULL, NULL, 8, 'employee', NULL),
(1041, 'Cesar', 'Cochon', 'Cesar.Cochon@stoddarts.com.au', NULL, '09086320321', 'Architectural Detailer', 'active', 'Probationary', '2025-11-21 07:42:26', 'Cesar.Cochon', '123456', 'Stoddart Group', NULL, NULL, 8, 'employee', NULL),
(1042, 'Karla', 'Arceo', 'jkzarceo@gmail.com', NULL, '09556515833', 'Admin Assistant', 'active', 'Regular', '2025-12-01 00:03:43', 'Karla.Arceo', 'harryS143!', NULL, NULL, NULL, NULL, 'employee', NULL),
(1043, 'Alvir', 'Rivera', 'alvirrivera0628@gmail.com', NULL, '09171487721', 'Administrative Assistant - Tax Support', 'active', 'Probationary', '2025-12-30 05:39:50', 'Alvir.Tayag', 'denning_alvir', 'Denning & Associates', NULL, NULL, NULL, 'employee', NULL),
(1044, 'Efrael', 'Agkis', 'efraelagkis0211@gmail.com', NULL, '09277112098', 'Structural/Mechanical Draftsman', 'active', 'Regular', '2025-12-30 06:19:15', 'Efrael.Agkis', '123456', 'Leading Cranes', NULL, 'profile_1044_1768168549.png', NULL, 'employee', NULL),
(1045, 'Mikaella', 'Capiral', 'capiralmikaella@gmail.com', NULL, '09169062104', 'Virtual Assistant', 'active', 'Regular', '2025-12-30 06:22:24', 'Mikaella.Capiral', 'mikaella_capiral', NULL, NULL, NULL, NULL, 'employee', NULL),
(1046, 'Russell', 'Bautista', 'russellrudolfhbautista@gmail.com', NULL, '09555642628', 'Network Controller', 'active', 'Probationary', '2026-01-12 06:08:16', 'Russell.Bautista', 'IAMLEGEND123', NULL, NULL, NULL, NULL, 'employee', NULL),
(1047, 'Gavin', 'James', 'gavin@integrityit.co.nz', NULL, 'N/A', 'IT administrator', 'active', 'Probationary', '2026-01-15 02:46:30', 'Gavin.James', 'KH8yt%4%4', NULL, NULL, NULL, NULL, 'employee', NULL),
(1048, 'Bon Febryx', 'Patacsil', 'IT@resourcestaff.com.ph', NULL, '09776867141', 'IT Support Specialist', 'active', 'Probationary', '2026-01-26 22:57:02', 'Bon.Patacsil', 'Fidelis14!!', 'RSS', NULL, 'profile_1048_1773373830.jpg', NULL, 'internal', NULL),
(1049, 'Marirey', 'Garcia', 'mariareynaldagarcia0312@gmail.com', NULL, '', 'Sales Support', 'active', 'Probationary', '2026-01-26 23:00:01', 'Mari.Garcia', 'rss_mari', '', NULL, NULL, NULL, 'employee', NULL),
(1050, 'Trisha', 'Elias', 'trisha.elias@entrygroup.com.au', NULL, '09993339039', 'Conveyancing Client Officer', 'active', 'Probationary', '2026-01-26 23:01:35', 'Trisha.Elias', 'Tanginamo123!', 'Entry Group', NULL, 'profile_1050_1777603893.png', NULL, 'employee', NULL),
(1051, 'Ria', 'Santiago', 'Ria@fairviewplumbing.com.au', NULL, '09460291262', 'Accounts Payable and Admin Officer', 'active', 'Probationary', '2026-01-30 06:58:05', 'Ria.Santiago', 'fairview_ria', 'Fairview Plumbing', NULL, NULL, NULL, 'employee', NULL),
(1052, 'Ria', 'Agabin', NULL, NULL, NULL, 'Virtual Executive Assistant', 'active', 'Probationary', '2026-01-30 06:58:44', 'Ria.Agabin', 'eastman_ria', NULL, NULL, NULL, NULL, 'employee', NULL),
(1053, 'Abigail', 'Basilio', NULL, NULL, NULL, 'Content Writer & Instructional Designer', 'active', 'Probationary', '2026-01-30 06:59:22', 'Abi.Basilio', 'entry_abi', NULL, NULL, NULL, NULL, 'employee', NULL),
(1054, 'Michelle', 'Calma', 'Michellepcalma@marleeresources.com.au', NULL, '09913274893', 'Operations & Mobilisation Coordinator', 'active', 'Probationary', '2026-01-30 07:06:08', 'Michelle.Calma', 'marlee_michelle', 'Marlee Resources', NULL, NULL, NULL, 'employee', NULL),
(1055, 'Cherry', 'Gonzales', NULL, NULL, NULL, 'Administrative Assistant', 'active', 'Probationary', '2026-02-06 07:55:12', 'Cherry.Gonzales', '123456', NULL, NULL, NULL, NULL, 'employee', NULL),
(1056, 'Jerome', 'Espinosa', 'jeromeespinosa270@gmail.com', NULL, '09496987384', 'Administrative Assistant', 'active', 'Probationary', '2026-02-06 07:57:09', 'Jerome.Espinosa', 'f&d_jerome', 'Ford & Doonan', NULL, NULL, NULL, 'employee', NULL),
(1057, 'Hdesk', 'test', 'hdesk.test@gmail.com', NULL, '0905 219 2943', 'Architectural Detailer', 'active', 'Probationary', '2026-02-11 05:22:57', 'hdesk.test', '123456', NULL, NULL, NULL, NULL, 'employee', NULL),
(1058, 'Justine', 'Jabat', NULL, NULL, NULL, 'Operations Administrator', 'active', 'Probationary', '2026-02-13 08:06:31', 'Justine.Jabat', 'trswa_justine', NULL, NULL, NULL, NULL, 'employee', NULL),
(1059, 'Rowen Daniel', 'Mira', 'daniel.mira@entrygroup.com.au', NULL, '09294648226', 'Sales Support', 'active', 'Probationary', '2026-02-13 08:18:40', 'Daniel.Mira', 'entry_daniel', 'Entry Education', NULL, 'profile_1059_1771210616.png', NULL, 'employee', NULL),
(1060, 'Lance Jio', 'Fernandez', 'lancejio_fernandez@yahoo.com', NULL, '09670571981', 'Digital Marketing', 'inactive', 'Probationary', '2026-02-14 06:46:18', 'Lance.Fernandez', 'rss_lance', 'Axis Health Clinic', NULL, NULL, NULL, 'employee', NULL),
(1061, 'Nerine', 'Calabia', 'calabianerine24@gmail.com', NULL, '09914047580', 'Technical Student Support', 'active', 'Probationary', '2026-02-21 08:39:51', 'Nerine.Calabia', 'entry_nerine', 'Entry Education', NULL, NULL, NULL, 'employee', NULL),
(1062, 'Trixie Jhem', 'Linga', 'trixiejhemlinga@gmail.com', NULL, '09928902147', 'Technical Student Support', 'active', 'Probationary', '2026-02-21 08:42:06', 'Trixie.Linga', 'entry_trixie', 'Resourcestaff', NULL, NULL, NULL, 'employee', NULL),
(1063, 'Sofia', 'Maniti', 'sofia.maniti@yahoo.com', NULL, NULL, 'Accounts Payable Officer', 'active', 'Probationary', '2026-02-24 09:34:33', 'Sofia.Maniti', 'hammerhire_sofia', NULL, NULL, NULL, NULL, 'employee', NULL),
(1064, 'Jessica', 'Uncad', NULL, NULL, NULL, 'Bookkeeper', 'active', 'Probationary', '2026-03-01 22:58:39', 'Jessica.Uncad', 'entry_jessica', NULL, NULL, NULL, NULL, 'employee', NULL),
(1065, 'Kenneth', 'Dacanay', NULL, NULL, NULL, 'Accounts Payable Officer', 'active', 'Probationary', '2026-03-03 00:18:35', 'Kenneth.Dacanay', 'rss_kenneth', NULL, NULL, NULL, NULL, 'employee', NULL),
(1066, 'Levigiene', 'Tadeo', NULL, NULL, NULL, 'Facilities', 'active', 'Probationary', '2026-03-24 01:14:40', 'Levi.Tadeo', 'rss_levi', NULL, NULL, NULL, NULL, 'employee', NULL),
(1067, 'Gener', 'Rosario', NULL, NULL, NULL, 'Structural Engineer', 'active', 'Probationary', '2026-04-06 05:26:26', 'Gener.Rosario', 'n9%rloqP', NULL, NULL, NULL, NULL, 'employee', NULL),
(1068, 'Raymund', 'Santos', NULL, NULL, NULL, NULL, 'active', 'Probationary', '2026-04-06 12:58:40', 'Raymund.Santos', 'aba_raymund', NULL, NULL, NULL, NULL, 'employee', NULL),
(1069, 'Irish', 'Palisoc', 'palisocirish35@gmail.com', NULL, NULL, 'Conveyancing Officer', 'active', 'Probationary', '2026-04-24 05:32:42', 'Irish.Palisoc', 'entry_irish', NULL, NULL, NULL, NULL, 'employee', NULL),
(1070, 'Dan Lawrence', 'Damilig', 'danlawrencedamilig@gmail.com', NULL, '09279448931', 'Electrical Engineer', 'active', 'Probationary', '2026-05-01 01:52:19', 'Dan.Damilig', 'lc_dan', NULL, NULL, NULL, NULL, 'employee', NULL),
(1071, 'Gabriel', 'Laredo', 'gabriel.laredo03@gmail.com', NULL, '09959963191', 'Senior Estimator', 'active', 'Probationary', '2026-05-01 07:13:48', 'Gabriel.Laredo', 'venaso_gabriel', NULL, NULL, NULL, NULL, 'employee', NULL),
(1072, 'Catherine', 'Lompero', 'erinlompero@gmail.com', NULL, '09953818636', 'Operations Administrator', 'active', 'Probationary', '2026-05-09 15:05:47', 'Catherine.Lompero', 'trswa_catherine', 'TRSWA ', NULL, NULL, NULL, 'employee', NULL);




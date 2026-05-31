SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS asset_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL,
    assigned_date DATE NOT NULL,
    returned_date DATE NULL,
    status ENUM('ACTIVE', 'RETURNED') NOT NULL DEFAULT 'ACTIVE',
    notes TEXT NULL,
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

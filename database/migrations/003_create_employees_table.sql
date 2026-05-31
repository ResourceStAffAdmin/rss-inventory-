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

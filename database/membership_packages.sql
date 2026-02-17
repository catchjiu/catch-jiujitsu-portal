-- SQL to create membership_packages table
-- Run this on your production database

CREATE TABLE IF NOT EXISTS membership_packages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    duration_type ENUM('days', 'weeks', 'months', 'years', 'classes') NOT NULL DEFAULT 'months',
    duration_value INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    age_group ENUM('Adults', 'Kids', 'All') NOT NULL DEFAULT 'All',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default membership packages
INSERT INTO membership_packages (name, description, duration_type, duration_value, price, age_group, is_active, sort_order, created_at, updated_at) VALUES
('1 Week Trial', 'Try out our gym for a week', 'weeks', 1, 500.00, 'All', 1, 1, NOW(), NOW()),
('2 Week Pass', 'Short-term training pass', 'weeks', 2, 900.00, 'All', 1, 2, NOW(), NOW()),
('1 Month', 'Monthly unlimited training', 'months', 1, 1500.00, 'Adults', 1, 3, NOW(), NOW()),
('3 Months', 'Quarterly membership', 'months', 3, 4000.00, 'Adults', 1, 4, NOW(), NOW()),
('6 Months', 'Semi-annual membership', 'months', 6, 7500.00, 'Adults', 1, 5, NOW(), NOW()),
('1 Year', 'Annual membership - best value', 'years', 1, 14000.00, 'Adults', 1, 6, NOW(), NOW()),
('10 Class Pass', 'Flexible class-based package', 'classes', 10, 2000.00, 'All', 1, 7, NOW(), NOW()),
('Kids Monthly', 'Monthly kids program', 'months', 1, 1200.00, 'Kids', 1, 8, NOW(), NOW()),
('Kids 3 Months', 'Quarterly kids program', 'months', 3, 3200.00, 'Kids', 1, 9, NOW(), NOW());

-- Optional: Add membership_package_id to users table to track current membership
-- ALTER TABLE users ADD COLUMN membership_package_id BIGINT UNSIGNED NULL AFTER age_group;
-- ALTER TABLE users ADD COLUMN membership_expires_at DATE NULL AFTER membership_package_id;
-- ALTER TABLE users ADD COLUMN classes_remaining INT NULL AFTER membership_expires_at;

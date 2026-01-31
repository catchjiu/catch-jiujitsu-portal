-- SQL to add membership tracking to users table
-- Run this on your production database

-- Add membership columns to users table
ALTER TABLE users 
ADD COLUMN membership_package_id BIGINT UNSIGNED NULL AFTER age_group,
ADD COLUMN membership_status ENUM('active', 'expired', 'pending', 'none') NOT NULL DEFAULT 'none' AFTER membership_package_id,
ADD COLUMN membership_expires_at DATE NULL AFTER membership_status,
ADD COLUMN classes_remaining INT NULL AFTER membership_expires_at;

-- Add index for faster lookups
ALTER TABLE users ADD INDEX idx_membership_status (membership_status);
ALTER TABLE users ADD INDEX idx_membership_expires (membership_expires_at);

-- Optional: Set all existing members to 'active' status with a month membership
-- UPDATE users SET membership_status = 'active', membership_expires_at = DATE_ADD(CURDATE(), INTERVAL 1 MONTH) WHERE is_admin = 0;

-- SQL to add discount_type column to users table
-- Run this on your production database

ALTER TABLE users ADD COLUMN discount_type ENUM('none', 'gratis', 'half_price') NOT NULL DEFAULT 'none' AFTER is_coach;

-- Add index for faster lookups
ALTER TABLE users ADD INDEX idx_discount_type (discount_type);

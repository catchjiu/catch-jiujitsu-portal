-- Update discount system to allow custom percentage discounts
-- Run this SQL on your production database

-- Step 1: Add the discount_percentage column
ALTER TABLE users ADD COLUMN discount_percentage INT NOT NULL DEFAULT 0 AFTER discount_type;

-- Step 2: Update the discount_type enum to include 'percentage' instead of 'half_price'
-- First, convert existing 'half_price' to 'percentage' with 50% discount
UPDATE users SET discount_percentage = 50 WHERE discount_type = 'half_price';

-- Step 3: Modify the enum (this requires recreating the column in MySQL)
-- Option A: If you can modify the enum directly
ALTER TABLE users MODIFY COLUMN discount_type ENUM('none', 'gratis', 'percentage') NOT NULL DEFAULT 'none';

-- Note: If the above fails because 'half_price' values exist, run this first:
-- UPDATE users SET discount_type = 'none' WHERE discount_type = 'half_price';
-- Then update those rows after modifying the enum:
-- UPDATE users SET discount_type = 'percentage' WHERE discount_percentage = 50;

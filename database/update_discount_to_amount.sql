-- Update discount system from percentage to fixed dollar amount
-- Run this SQL on your production database

-- Step 1: Add the discount_amount column (if not exists)
ALTER TABLE users ADD COLUMN IF NOT EXISTS discount_amount INT NOT NULL DEFAULT 0 AFTER discount_type;

-- Step 2: If you have a discount_percentage column, you can drop it (optional)
-- ALTER TABLE users DROP COLUMN IF EXISTS discount_percentage;

-- Step 3: Update the discount_type enum to include 'fixed'
ALTER TABLE users MODIFY COLUMN discount_type ENUM('none', 'gratis', 'fixed', 'percentage', 'half_price') NOT NULL DEFAULT 'none';

-- Step 4: Convert any existing 'percentage' or 'half_price' to 'fixed' (with a default amount)
-- UPDATE users SET discount_type = 'fixed', discount_amount = 500 WHERE discount_type IN ('percentage', 'half_price');

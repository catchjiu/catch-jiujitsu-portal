-- =====================================================
-- SQL to add age_group field to classes and users tables
-- Run this on your production database
-- =====================================================

-- Add age_group to classes table
-- Values: 'Kids', 'Adults', 'All' (default: 'Adults')
ALTER TABLE `classes` 
ADD COLUMN `age_group` ENUM('Kids', 'Adults', 'All') NOT NULL DEFAULT 'Adults' 
AFTER `type`;

-- Add age_group to users table  
-- Values: 'Kids', 'Adults' (default: 'Adults')
ALTER TABLE `users` 
ADD COLUMN `age_group` ENUM('Kids', 'Adults') NOT NULL DEFAULT 'Adults' 
AFTER `gender`;

-- =====================================================
-- Optional: Update existing records if needed
-- =====================================================

-- Set all existing classes to Adults (they already default to 'Adults')
-- UPDATE `classes` SET `age_group` = 'Adults' WHERE `age_group` IS NULL;

-- Set all existing users to Adults (they already default to 'Adults')  
-- UPDATE `users` SET `age_group` = 'Adults' WHERE `age_group` IS NULL;

-- =====================================================
-- Example: Bulk update kids based on age (if you have DOB)
-- =====================================================

-- Set users under 18 to 'Kids' based on date of birth
-- UPDATE `users` 
-- SET `age_group` = 'Kids' 
-- WHERE `dob` IS NOT NULL 
--   AND TIMESTAMPDIFF(YEAR, `dob`, CURDATE()) < 18;

-- =====================================================
-- Verify the changes
-- =====================================================

-- Check classes table structure
-- DESCRIBE `classes`;

-- Check users table structure  
-- DESCRIBE `users`;

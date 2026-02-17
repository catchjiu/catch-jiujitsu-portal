-- SQL to add is_coach column to users table
-- Run this on your production database

ALTER TABLE users ADD COLUMN is_coach TINYINT(1) NOT NULL DEFAULT 0 AFTER is_admin;

-- Add index for faster lookups
ALTER TABLE users ADD INDEX idx_is_coach (is_coach);

-- Add instructor_id to classes table (links to coach)
ALTER TABLE classes ADD COLUMN instructor_id BIGINT UNSIGNED NULL AFTER capacity;

-- Optional: Set specific users as coaches
-- UPDATE users SET is_coach = 1 WHERE email IN ('coach1@example.com', 'coach2@example.com');

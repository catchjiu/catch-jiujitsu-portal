-- Add Chinese title column to classes table
ALTER TABLE classes ADD COLUMN title_zh VARCHAR(255) NULL AFTER title;

-- Add locale preference to users table
ALTER TABLE users ADD COLUMN locale VARCHAR(10) DEFAULT 'en' AFTER public_profile;

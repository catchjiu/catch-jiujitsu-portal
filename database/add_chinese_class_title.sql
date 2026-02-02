-- Add Chinese title column to class_sessions table
ALTER TABLE class_sessions ADD COLUMN title_zh VARCHAR(255) NULL AFTER title;

-- Add locale preference to users table
ALTER TABLE users ADD COLUMN locale VARCHAR(10) DEFAULT 'en' AFTER public_profile;

-- Add is_cancelled column to classes table
-- Run this SQL on your production database

ALTER TABLE classes ADD COLUMN is_cancelled TINYINT(1) NOT NULL DEFAULT 0 AFTER capacity;

-- Create index for faster queries on cancelled classes
CREATE INDEX idx_classes_is_cancelled ON classes(is_cancelled);

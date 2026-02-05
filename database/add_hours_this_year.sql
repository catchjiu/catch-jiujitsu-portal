-- Add hours_this_year column to users table.
-- Tracks classes booked this calendar year; resets on Jan 1 via cron.
-- Run this SQL on your database once.

ALTER TABLE users ADD COLUMN hours_this_year INT NOT NULL DEFAULT 0 AFTER mat_hours;

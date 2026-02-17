-- Add phone to users table (run once).
-- Or use: php artisan migrate

ALTER TABLE users
ADD COLUMN phone VARCHAR(50) NULL AFTER email;

-- Add date of birth to users table (run once if not using Laravel migrations).
-- Or use: php artisan migrate
-- If the column already exists, this will error; that's OK.
-- If you have a 'gender' column, you can use: ADD COLUMN dob DATE NULL AFTER gender;

ALTER TABLE users
ADD COLUMN dob DATE NULL;

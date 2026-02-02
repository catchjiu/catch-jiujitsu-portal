-- Add payment details columns to payments table
-- Run this SQL in phpMyAdmin or your MySQL client

-- Add payment_method column (bank or linepay)
ALTER TABLE payments ADD COLUMN payment_method ENUM('bank', 'linepay') NULL AFTER status;

-- Add payment_date column (the date the user made the payment)
ALTER TABLE payments ADD COLUMN payment_date DATE NULL AFTER payment_method;

-- Add account_last_5 column (last 5 digits of bank account for verification)
ALTER TABLE payments ADD COLUMN account_last_5 VARCHAR(5) NULL AFTER payment_date;

-- Add index for payment_method for faster filtering
CREATE INDEX idx_payments_payment_method ON payments(payment_method);

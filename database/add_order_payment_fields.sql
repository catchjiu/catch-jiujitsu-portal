-- Add bank transfer payment fields to orders (for My Orders pay flow).
-- Run manually: mysql -u user -p database < add_order_payment_fields.sql

ALTER TABLE orders
    ADD COLUMN payment_method VARCHAR(20) NULL AFTER notes,
    ADD COLUMN account_last_5 VARCHAR(5) NULL AFTER payment_method,
    ADD COLUMN payment_submitted_at TIMESTAMP NULL AFTER account_last_5;

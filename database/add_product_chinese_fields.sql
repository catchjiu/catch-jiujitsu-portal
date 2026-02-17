-- Add Chinese translation fields to products table.
-- Run this SQL if you are not using Laravel migrations. Otherwise: php artisan migrate.

ALTER TABLE products
    ADD COLUMN product_name_zh VARCHAR(255) NULL AFTER name,
    ADD COLUMN product_desc_zh TEXT NULL AFTER description;

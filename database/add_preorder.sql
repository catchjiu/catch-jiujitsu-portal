-- Pre-order flag: products and order_items.
-- Run this if you are not using Laravel migrations. Otherwise: php artisan migrate.

ALTER TABLE products
    ADD COLUMN is_preorder TINYINT(1) NOT NULL DEFAULT 0 AFTER image_url;

ALTER TABLE order_items
    ADD COLUMN is_preorder TINYINT(1) NOT NULL DEFAULT 0 AFTER unit_price;

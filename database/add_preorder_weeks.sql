-- Estimated pre-order wait time (weeks). Run if not using migrations: php artisan migrate

ALTER TABLE products
    ADD COLUMN preorder_weeks TINYINT UNSIGNED NULL AFTER is_preorder;

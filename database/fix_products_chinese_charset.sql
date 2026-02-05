-- Fix product_name_zh and product_desc_zh to support Chinese (UTF-8).
-- Run this on your DB if you get "Incorrect string value" when saving Chinese in those columns.

ALTER TABLE products
    MODIFY product_name_zh VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    MODIFY product_desc_zh TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

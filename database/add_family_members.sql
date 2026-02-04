-- Family dashboard support: one profile can manage up to 3 kids + 1 other parent.
-- Creates: families (primary_user_id), family_members (family_id, user_id, role).
-- Run this if you are not using Laravel migrations. Otherwise use: php artisan migrate.

CREATE TABLE IF NOT EXISTS families (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    primary_user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT families_primary_user_id_foreign
        FOREIGN KEY (primary_user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS family_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    family_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'parent',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY family_members_family_id_user_id_unique (family_id, user_id),
    UNIQUE KEY family_members_user_id_unique (user_id),
    CONSTRAINT family_members_family_id_foreign
        FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE,
    CONSTRAINT family_members_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

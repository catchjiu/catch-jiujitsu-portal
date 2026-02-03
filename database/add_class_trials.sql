-- Add class_trials table for trial members (name, age) per class.
-- Run this if you are not using Laravel migrations.

CREATE TABLE IF NOT EXISTS class_trials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    age TINYINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT class_trials_class_id_foreign
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

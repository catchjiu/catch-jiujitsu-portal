-- Private class booking: coach availability and member requests.
-- Run once after add_family_members.sql if not using Laravel migrations.
-- Or use: php artisan migrate

ALTER TABLE users
    ADD COLUMN accepting_private_classes TINYINT(1) NOT NULL DEFAULT 0 AFTER is_coach,
    ADD COLUMN private_class_price DECIMAL(10,2) NULL AFTER accepting_private_classes;

CREATE TABLE IF NOT EXISTS coach_availability (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT coach_availability_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS private_class_bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coach_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    scheduled_at DATETIME NOT NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    price DECIMAL(10,2) NULL,
    requested_at TIMESTAMP NULL,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT private_class_bookings_coach_id_foreign
        FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT private_class_bookings_member_id_foreign
        FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Drop the existing table if needed and create new structure
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `chinese_name` varchar(255) DEFAULT NULL,
  `belt_color` varchar(50) DEFAULT 'White Belt',
  `line_id` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') NOT NULL DEFAULT 'male',
  `dob` date DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rank` enum('White','Blue','Purple','Brown','Black') NOT NULL DEFAULT 'White',
  `stripes` int(11) NOT NULL DEFAULT 0,
  `mat_hours` int(11) NOT NULL DEFAULT 0,
  `monthly_class_goal` int(11) NOT NULL DEFAULT 12,
  `monthly_hours_goal` int(11) NOT NULL DEFAULT 15,
  `reminders_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `public_profile` tinyint(1) NOT NULL DEFAULT 0,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `avatar_url` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: Run this INSERT query on your OLD database to generate the data for the NEW schema
-- This query transforms old columns to new columns:
-- - Combines first_name + last_name into name
-- - Maps password_hash to password  
-- - Maps profile_picture_url to avatar_url
-- - Maps role (admin/coach) to is_admin
-- - Extracts rank from belt_color (White Belt -> White)

INSERT INTO `users` (`id`, `name`, `email`, `chinese_name`, `belt_color`, `line_id`, `gender`, `dob`, `email_verified_at`, `password`, `rank`, `stripes`, `mat_hours`, `monthly_class_goal`, `monthly_hours_goal`, `reminders_enabled`, `public_profile`, `is_admin`, `avatar_url`, `remember_token`, `created_at`, `updated_at`)
SELECT 
    id,
    CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as name,
    email,
    chinese_name,
    belt_color,
    line_id,
    gender,
    CASE WHEN dob = '0000-00-00' THEN NULL ELSE dob END as dob,
    NULL as email_verified_at,
    password_hash as password,
    CASE 
        WHEN belt_color LIKE '%Black%' THEN 'Black'
        WHEN belt_color LIKE '%Brown%' THEN 'Brown'
        WHEN belt_color LIKE '%Purple%' THEN 'Purple'
        WHEN belt_color LIKE '%Blue%' THEN 'Blue'
        ELSE 'White'
    END as `rank`,
    0 as stripes,
    0 as mat_hours,
    12 as monthly_class_goal,
    15 as monthly_hours_goal,
    1 as reminders_enabled,
    0 as public_profile,
    CASE WHEN role IN ('admin', 'coach') THEN 1 ELSE 0 END as is_admin,
    profile_picture_url as avatar_url,
    NULL as remember_token,
    created_at,
    updated_at
FROM `users`;

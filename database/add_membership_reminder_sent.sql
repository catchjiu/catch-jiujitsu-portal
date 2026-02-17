-- Track when we sent LINE reminders so we don't duplicate.
-- Run this if you are not using Laravel migrations.

ALTER TABLE `users`
ADD COLUMN `membership_expiry_reminder_sent_at` DATE NULL DEFAULT NULL AFTER `classes_remaining`,
ADD COLUMN `classes_zero_reminder_sent_at` TIMESTAMP NULL DEFAULT NULL AFTER `membership_expiry_reminder_sent_at`;

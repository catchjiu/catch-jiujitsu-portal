-- Track when we last sent a "we miss you" re-engagement LINE message (at most once per 7 days).
-- Run this if you are not using Laravel migrations.

ALTER TABLE `users`
ADD COLUMN `last_reengagement_line_sent_at` TIMESTAMP NULL DEFAULT NULL AFTER `classes_zero_reminder_sent_at`;

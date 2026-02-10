-- Add LINE Notify token for class reminders (one-way notifications to user's LINE).
-- Run this if you are not using Laravel migrations.

ALTER TABLE `users`
ADD COLUMN `line_notify_token` VARCHAR(500) NULL DEFAULT NULL AFTER `line_id`;

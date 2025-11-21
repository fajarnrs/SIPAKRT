-- Run this if you can't use php artisan migrate
-- Execute via phpMyAdmin or MySQL command line

-- Add family_card_number to users table
ALTER TABLE `users` 
ADD COLUMN `family_card_number` VARCHAR(20) NULL UNIQUE AFTER `email`;

-- Make email nullable
ALTER TABLE `users` 
MODIFY COLUMN `email` VARCHAR(255) NULL;

-- Verify changes
DESCRIBE `users`;

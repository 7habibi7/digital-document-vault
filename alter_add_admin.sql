-- Run this ONLY if you already created document_vault earlier
-- (i.e. you are NOT running schema.sql fresh).
-- This just adds the missing admin column to your existing users table.

USE document_vault;

ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0;

-- Make one of your existing accounts an admin.
-- Replace the email with the account you want to promote.
UPDATE users SET is_admin = 1 WHERE email = 'your-email@example.com';

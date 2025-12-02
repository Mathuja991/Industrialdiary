-- Add must_change_password column to users table
-- This tracks whether a user needs to change their password on first login

ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) DEFAULT 0;

-- Update existing users to not require password change
UPDATE users SET must_change_password = 0;

-- Verify the column was added
DESCRIBE users;

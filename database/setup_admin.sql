-- ============================================
-- STEP 1: Add must_change_password column (if not exists)
-- ============================================
-- Check if column exists first, if it gives error "Duplicate column", that's okay - skip to Step 2

ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) DEFAULT 0;

-- ============================================
-- STEP 2: Update existing users
-- ============================================
-- Set all existing users to NOT require password change
UPDATE users SET must_change_password = 0;

-- ============================================
-- STEP 3: Create admin account
-- ============================================
-- This creates an admin account with:
-- Username: admin
-- Password: admin123

INSERT INTO users (username, password, role, full_name, must_change_password) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', 0);

-- ============================================
-- STEP 4: Verify everything worked
-- ============================================
-- Run this to check:
SELECT id, username, role, full_name, must_change_password FROM users WHERE role = 'admin';

-- ============================================
-- TROUBLESHOOTING
-- ============================================
-- If you get "Duplicate column 'must_change_password'" error:
--   - The column already exists, skip Step 1
--   - Just run Steps 2, 3, and 4
--
-- If you get "Duplicate entry" error on Step 3:
--   - Admin already exists
--   - To reset admin password, run this instead:
--   UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin';
--
-- ============================================

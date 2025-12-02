-- ============================================
-- CREATE ADMIN ACCOUNT
-- ============================================
-- This script creates an admin account in the database
-- Run this SQL script in phpMyAdmin or MySQL command line
-- 
-- IMPORTANT: Change the username and password below!
-- ============================================

-- Step 1: Insert admin user into users table
-- Password: 'admin123' (change this!)
-- The password is already hashed using PASSWORD_DEFAULT
INSERT INTO users (username, password, role, full_name) 
VALUES (
    'admin',  -- Change this username
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- This is 'admin123' hashed
    'admin',
    'System Administrator'  -- Change this name
);

-- ============================================
-- ALTERNATIVE: Create admin with custom password
-- ============================================
-- If you want to use a different password, you need to hash it first.
-- 
-- Option 1: Use this PHP script to generate a hash:
-- 
-- <?php
-- echo password_hash('your_password_here', PASSWORD_DEFAULT);
-- ?>
--
-- Then replace the hash in the INSERT statement above
--
-- ============================================

-- ============================================
-- VERIFY ADMIN ACCOUNT
-- ============================================
-- After running the INSERT, verify the admin was created:
SELECT id, username, role, full_name FROM users WHERE role = 'admin';

-- ============================================
-- NOTES:
-- ============================================
-- 1. The default password is 'admin123' - CHANGE THIS IMMEDIATELY!
-- 2. Admin accounts are stored in the 'users' table with role = 'admin'
-- 3. Admin login uses the same login.php as other users
-- 4. After first login, change the password through your profile
-- 5. For security, never share admin credentials
-- ============================================

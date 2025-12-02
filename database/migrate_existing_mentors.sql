-- Migrate existing mentors from users table to mentor table
-- This script adds entries to the mentor table for any users with role='mentor' who don't have a mentor record

INSERT INTO mentor (user_id, working_organization)
SELECT id, 'Not Specified'
FROM users
WHERE role = 'mentor'
AND id NOT IN (SELECT user_id FROM mentor);

-- Verify the migration
SELECT u.id, u.username, u.full_name, m.working_organization
FROM users u
LEFT JOIN mentor m ON u.id = m.user_id
WHERE u.role = 'mentor';

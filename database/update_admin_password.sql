-- Update Admin Password to: admin123
-- Run this SQL in your database

UPDATE admin_users 
SET password = '$2y$10$7IG3rMY/y676v.46HwHjReWoT5rUkamnAw7Jdj2UmDkGSz9KJg2le'
WHERE username = 'admin';

-- Or if you want to set a different password, generate a new hash using:
-- php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"


-- Run as mysql/root with password Admin1234
-- mysql -h localhost -u root -p 
CREATE DATABASE IF NOT EXISTS eldercare_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- CREATE USER IF NOT EXISTS 'eldercare_admin'@'%' IDENTIFIED WITH mysql_native_password BY 'your_secure_password';
CREATE USER IF NOT EXISTS 'eldercare'@'%' IDENTIFIED BY 'eldercare_secret';

-- GRANT ALL PRIVILEGES ON eldercare_db.* TO 'eldercare_admin'@'%';
GRANT ALL PRIVILEGES ON eldercare_db.* TO 'eldercare'@'%';
FLUSH PRIVILEGES;

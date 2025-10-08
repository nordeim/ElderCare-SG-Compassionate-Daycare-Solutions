-- This script is executed automatically by the MySQL container
-- during initialization (docker-entrypoint-initdb.d).

DROP USER IF EXISTS 'eldercare_admin'@'%';

CREATE DATABASE IF NOT EXISTS eldercare_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'eldercare_admin'@'%'
  IDENTIFIED WITH mysql_native_password BY 'your_secure_password';

GRANT ALL PRIVILEGES ON eldercare_db.* TO 'eldercare_admin'@'%';
FLUSH PRIVILEGES;

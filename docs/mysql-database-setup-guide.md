# MySQL Database Setup Guide

This guide outlines how to provision the ElderCare SG application database and user accounts using the MySQL root (or system-level) credentials. Follow these steps before running `php artisan migrate:fresh` or other Laravel Artisan commands.

## 1. Prerequisites
- MySQL 8.0 or MariaDB 10.11 installed locally or accessible remotely.
- System-level (root) MySQL user credentials. For development, use:
  - Username: `mysql` (or `root`, depending on your installation)
  - Password: `Admin1234`
- Terminal access to the MySQL client (`mysql`) or a GUI like MySQL Workbench.

## 2. Create Initialization Script
For reproducibility, create an SQL script at `docker/mysql/init/000_create_eldercare_db.sql` with the following contents:

```sql
-- Run this script as the system/root MySQL user
-- Adjust host specification to `localhost` if restricting access.
DROP USER IF EXISTS 'eldercare_admin'@'%';
CREATE DATABASE IF NOT EXISTS eldercare_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'eldercare_admin'@'%'
  IDENTIFIED WITH mysql_native_password BY 'Admin1234';

GRANT ALL PRIVILEGES ON eldercare_db.* TO 'eldercare_admin'@'%';
FLUSH PRIVILEGES;
```

Notes:
- The `DROP USER` statement ensures the user can be recreated cleanly when rerunning the script.
- `mysql_native_password` maintains compatibility across MySQL 8+ and MariaDB.
- Use `'localhost'` instead of `'%'` if you only plan to connect locally.

## 3. Execute the Script
Run the script using the system-level credentials:

```bash
mysql -u mysql -pAdmin1234 < docker/mysql/init/000_create_eldercare_db.sql
```

If your root user is `root` instead of `mysql`, adjust the command accordingly:

```bash
mysql -u root -pAdmin1234 < docker/mysql/init/000_create_eldercare_db.sql
```

## 4. Verify Application Credentials
Confirm that the application user can connect and that the database exists:

```bash
mysql -u eldercare_admin -pAdmin1234 -e "SHOW DATABASES LIKE 'eldercare_db';"
mysql -u eldercare_admin -pAdmin1234 eldercare_db -e "SHOW TABLES;"
```

If both commands succeed, the application user is ready to run migrations.

## 5. Run Laravel Migrations
Ensure `.env` (or `.env.example`) specifies the same credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eldercare_db
DB_USERNAME=eldercare_admin
DB_PASSWORD=Admin1234
```

With the database and user created, run:

```bash
php artisan migrate:fresh
```

## 6. Troubleshooting
- **Access denied**: Verify the user/password and ensure privileges were granted. Check `mysql.user` table if necessary.
- **Connection refused**: Confirm MySQL is running on `127.0.0.1:3306`. Adjust firewall or `bind-address` in `my.cnf`.
- **SQL syntax errors in script**: Ensure you’re using MySQL/MariaDB 10.11+. Some older versions may require different syntax for user creation or password plugins.

## 7. Automation Tips
- The SQL script can be mounted into the MySQL Docker container (`docker-compose.yml` volume) so it executes automatically on first boot.
- Record credentials and scripts securely; never commit actual production secrets to the repository.

---
Prepared by Cascade AI Agent — 2025-10-09.

cd H:\project\web-platform\backend

$envPath = '.\.env'
$env = Get-Content $envPath -Raw

# Set DB connection and host/credentials (adjust values below)
$env = $env -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=mysql'
if ($env -match 'DB_HOST=') { $env = $env -replace 'DB_HOST=.*', 'DB_HOST=127.0.0.1' } else { $env += "`nDB_HOST=127.0.0.1" }
if ($env -match 'DB_PORT=') { $env = $env -replace 'DB_PORT=.*', 'DB_PORT=3306' } else { $env += "`nDB_PORT=3306" }
if ($env -match 'DB_DATABASE=') { $env = $env -replace 'DB_DATABASE=.*', 'DB_DATABASE=eldercare_db' } else { $env += "`nDB_DATABASE=eldercare_local" }
if ($env -match 'DB_USERNAME=') { $env = $env -replace 'DB_USERNAME=.*', 'DB_USERNAME=root' } else { $env += "`nDB_USERNAME=root" }
if ($env -match 'DB_PASSWORD=') { $env = $env -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=Admin1234' } else { $env += "`nDB_PASSWORD=secret" }

# Persist changes
$env | Set-Content $envPath

# Test DB connection by running migrations (MySQL must be available)
php artisan migrate:fresh --seed

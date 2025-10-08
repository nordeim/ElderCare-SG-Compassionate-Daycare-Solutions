# ElderCare SG Deployment Guide

## Overview

This guide provides instructions for deploying the ElderCare SG application to various environments.

## Prerequisites

- Docker and Docker Compose installed
- SSH access to the server
- Domain name configured
- SSL certificates (for production)

## Environment Setup

### Environment Templates

| Scope | Template | Copy Command | Notes |
| --- | --- | --- | --- |
| Local development | `.env.example` | `cp .env.example .env` | Mirrors Laravel defaults for Docker Compose stack |
| Frontend (local) | `frontend/.env.local.example` | `cp frontend/.env.local.example frontend/.env.local` | Includes GA/Hotjar/Sentry placeholders |
| **Staging** | `.env.staging` | `cp .env.staging .env` (inside staging deployment) | Disable debug, point to staging RDS/Redis, enable analytics flags |
| **Production** | `.env.production.template` | `cp .env.production.template .env.production` (outside Git repo) | Populate with production secrets before deploy |
| Frontend staging | `frontend/.env.local.example` (copy then edit) | — | Define `NEXT_PUBLIC_API_URL` for staging gateway |
| Frontend production | `frontend/.env.local.example` (duplicate to `.env.local.production`) | — | Ensure public analytics IDs are populated |

> **Secret Handling:** Never commit populated `.env*` files. Populate sensitive values via secrets management (AWS Secrets Manager, SSM Parameter Store, or GitHub Actions Secrets) and inject during CI/CD.

#### Required Environment Variables (Backend)

- **Core:** `APP_KEY`, `APP_URL`, `APP_ENV`, `APP_DEBUG`
- **Database:** `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- **Cache/Queue:** `REDIS_HOST`, `REDIS_PORT`, `QUEUE_CONNECTION`, `CACHE_STORE`
- **External Services:** `CALENDLY_API_KEY`, `MAILCHIMP_API_KEY`, `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `CLOUDFLARE_STREAM_API_TOKEN`
- **Storage:** `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`
- **Monitoring:** `SENTRY_LARAVEL_DSN`, `NEWRELIC_LICENSE_KEY`
- **Compliance:** `PRIVACY_POLICY_VERSION`, `DATA_RETENTION_DAYS`, `AUDIT_LOG_RETENTION_YEARS`

#### Required Environment Variables (Frontend)

- `NEXT_PUBLIC_API_URL`
- `NEXT_PUBLIC_APP_URL`
- `NEXT_PUBLIC_GA_MEASUREMENT_ID`
- `NEXT_PUBLIC_HOTJAR_ID`
- `NEXT_PUBLIC_SENTRY_DSN`
- `API_SECRET_KEY`
- `REVALIDATE_SECRET`

### Development Environment

1. Clone the repository:
   ```bash
   git clone https://github.com/eldercare-sg/web-platform.git
   cd web-platform
   ```

2. Copy environment files:
   ```bash
   cp .env.example .env
   cp frontend/.env.local.example frontend/.env.local
   ```

3. Start the development environment:
   ```bash
   docker-compose up -d
   ```

4. Run database migrations:
   ```bash
   docker-compose exec backend php artisan migrate
   ```

5. Seed the database (optional):
   ```bash
   docker-compose exec backend php artisan db:seed
   ```

### Production Environment

1. Set up the server:
   ```bash
   # Update system packages
   sudo apt update && sudo apt upgrade -y
   
   # Install Docker
   curl -fsSL https://get.docker.com -o get-docker.sh
   sudo sh get-docker.sh
   
   # Install Docker Compose
   sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
   sudo chmod +x /usr/local/bin/docker-compose
   ```

2. Clone the repository:
   ```bash
   git clone https://github.com/eldercare-sg/web-platform.git
   cd web-platform
   ```

3. Set up environment variables:
   ```bash
   cp .env.production.template .env
   # Populate .env with production secrets (do not check into Git)
   ```

4. Create SSL certificates:
   ```bash
   mkdir -p docker/nginx/ssl
   # Copy your SSL certificates to docker/nginx/ssl/
   ```

5. Deploy the application:
   ```bash
   docker-compose -f docker-compose.prod.yml up -d --build
   ```

6. Run database migrations:
   ```bash
   docker-compose -f docker-compose.prod.yml exec backend php artisan migrate --force
   ```

7. Optimize the application:
   ```bash
   docker-compose -f docker-compose.prod.yml exec backend php artisan config:cache
   docker-compose -f docker-compose.prod.yml exec backend php artisan route:cache
   docker-compose -f docker-compose.prod.yml exec backend php artisan view:cache
   ```

## Monitoring and Maintenance

### Logs

View application logs:
```bash
# Frontend logs
docker-compose logs -f frontend

# Backend logs
docker-compose logs -f backend

# Database logs
docker-compose logs -f mysql
```

### Backups

Create database backup:
```bash
./scripts/backup.sh
```

### Updates

Update the application:
```bash
git pull origin main
docker-compose -f docker-compose.prod.yml up -d --build
```

## Troubleshooting

### Common Issues

1. **Database connection errors**
   - Check database credentials in .env file
   - Ensure database container is running
   - Check database logs for errors

2. **Frontend build errors**
   - Check Node.js version compatibility
   - Clear node_modules and reinstall dependencies
   - Check for any syntax errors in the code

3. **Permission issues**
   - Ensure proper file permissions for storage directories
   - Check user and group ownership of files

### Performance Optimization

1. **Database optimization**
   - Regularly optimize database tables
   - Implement proper indexing
   - Monitor slow queries

2. **Caching**
   - Implement Redis caching for frequently accessed data
   - Use CDN for static assets
   - Enable browser caching

3. **Server resources**
   - Monitor CPU and memory usage
   - Scale resources as needed
   - Implement load balancing for high traffic

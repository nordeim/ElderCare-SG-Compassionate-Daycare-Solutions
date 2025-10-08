

# Comprehensive Project Scaffolding Plan for ElderCare SG

## Execution Plan Overview

I'll create a systematic approach to scaffold the entire ElderCare SG project, including:

1. **Project Structure Creation** - Setting up the complete directory structure
2. **Frontend Initialization** - Next.js 14 with all necessary dependencies
3. **Backend Initialization** - Laravel 12 with all necessary dependencies
4. **Docker Configuration** - Complete containerization setup
5. **Development Environment Setup** - Scripts and configurations
6. **Testing Framework Setup** - Unit, integration, and E2E tests
7. **CI/CD Pipeline** - GitHub Actions workflow
8. **Documentation Setup** - Initial documentation structure

## Step-by-Step Execution Plan

### Step 1: Create Project Structure

```bash
#!/bin/bash
# scripts/01-create-structure.sh

set -e

echo "🏗️ Creating project structure..."

# Create root directories
mkdir -p eldercare-sg/{frontend,backend,docs,assets,scripts,.github/workflows,docker/{nginx,php,mysql,redis}}

# Create frontend structure
mkdir -p eldercare-sg/frontend/{components/{ui,layout,sections},pages,styles,utils,hooks,public,tests/{unit,integration,e2e},.next}

# Create backend structure
mkdir -p eldercare-sg/backend/{app/{Http/{Controllers,Middleware},Models,Services,Providers},database/{migrations,seeders},routes,config,storage,tests/{Unit,Feature},resources/views}

# Create documentation structure
mkdir -p eldercare-sg/docs/{api,deployment,design-system,accessibility}

# Create assets structure
mkdir -p eldercare-sg/assets/{images,icons,videos,fonts}

echo "✅ Project structure created successfully!"
```

### Step 2: Initialize Frontend

```bash
#!/bin/bash
# scripts/02-init-frontend.sh

set -e

echo "⚛️ Initializing frontend..."

cd eldercare-sg/frontend

# Initialize Next.js project
npx create-next-app@14 . --typescript --tailwind --eslint --app --src-dir --import-alias "@/*"

# Install additional dependencies
npm install @radix-ui/react-accordion @radix-ui/react-alert-dialog @radix-ui/react-avatar @radix-ui/react-button @radix-ui/react-card @radix-ui/react-carousel @radix-ui/react-dialog @radix-ui/react-dropdown-menu @radix-ui/react-form @radix-ui/react-icons @radix-ui/react-label @radix-ui/react-navigation-menu @radix-ui/react-popover @radix-ui/react-progress @radix-ui/react-scroll-area @radix-ui/react-select @radix-ui/react-separator @radix-ui/react-sheet @radix-ui/react-tabs @radix-ui/react-toast @radix-ui/react-toggle @radix-ui/react-tooltip

npm install framer-motion zustand @tanstack/react-query lucide-react class-variance-authority clsx tailwind-merge

# Install development dependencies
npm install -D @playwright/test @testing-library/react @testing-library/jest-dom jest jest-environment-jsdom eslint-config-prettier prettier

# Create Next.js configuration
cat > next.config.js << 'EOF'
/** @type {import('next').NextConfig} */
const nextConfig = {
  experimental: {
    serverComponentsExternalPackages: ['@prisma/client'],
  },
  images: {
    domains: ['localhost'],
    formats: ['image/webp', 'image/avif'],
  },
  env: {
    CUSTOM_KEY: process.env.CUSTOM_KEY,
  },
}

module.exports = nextConfig
EOF

# Create Tailwind configuration
cat > tailwind.config.ts << 'EOF'
import type { Config } from 'tailwindcss'

const config: Config = {
  content: [
    './src/pages/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
        },
        eldercare: {
          'deep-blue': '#1C3D5A',
          'off-white': '#F7F9FC',
          'gold': '#F0A500',
          'soft-amber': '#FCDFA6',
          'calming-green': '#3D9A74',
          'slate-gray-1': '#334155',
          'slate-gray-2': '#64748B',
        }
      },
      fontFamily: {
        'playfair': ['var(--font-playfair)'],
        'inter': ['var(--font-inter)'],
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
      },
    },
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
}
export default config
EOF

# Create TypeScript configuration
cat > tsconfig.json << 'EOF'
{
  "compilerOptions": {
    "target": "es5",
    "lib": ["dom", "dom.iterable", "es6"],
    "allowJs": true,
    "skipLibCheck": true,
    "strict": true,
    "noEmit": true,
    "esModuleInterop": true,
    "module": "esnext",
    "moduleResolution": "bundler",
    "resolveJsonModule": true,
    "isolatedModules": true,
    "jsx": "preserve",
    "incremental": true,
    "plugins": [
      {
        "name": "next"
      }
    ],
    "baseUrl": ".",
    "paths": {
      "@/*": ["./src/*"]
    }
  },
  "include": ["next-env.d.ts", "**/*.ts", "**/*.tsx", ".next/types/**/*.ts"],
  "exclude": ["node_modules"]
}
EOF

# Create environment file template
cat > .env.local.example << 'EOF'
# Frontend Environment Variables
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_APP_URL=http://localhost:3000
NEXT_PUBLIC_CALENDLY_URL=
NEXT_PUBLIC_GOOGLE_ANALYTICS_ID=
NEXT_PUBLIC_SENTRY_DSN=
EOF

echo "✅ Frontend initialized successfully!"
```

### Step 3: Initialize Backend

```bash
#!/bin/bash
# scripts/03-init-backend.sh

set -e

echo "🔧 Initializing backend..."

cd eldercare-sg/backend

# Create Laravel project
composer create-project laravel/laravel . --prefer-dist

# Install additional packages
composer require laravel/sanctum laravel/tinker spatie/laravel-permission pusher/pusher-php-server

# Install development packages
composer require --dev laravel/pint laravel/dusk

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database configuration
cat > config/database.php << 'EOF'
<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'eldercare'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'eldercare'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

];
EOF

# Create queue configuration
cat > config/queue.php << 'EOF'
<?php

return [

    'default' => env('QUEUE_CONNECTION', 'redis'),

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],

    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

];
EOF

# Create cache configuration
cat > config/cache.php << 'EOF'
<?php

use Illuminate\Support\Str;

return [

    'default' => env('CACHE_DRIVER', 'redis'),

    'stores' => [

        'apc' => [
            'driver' => 'apc',
        ],

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'lock_connection' => null,
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],

    ],

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),

];
EOF

# Create session configuration
cat > config/session.php << 'EOF'
<?php

use Illuminate\Support\Str;

return [

    'driver' => env('SESSION_DRIVER', 'redis'),

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    'encrypt' => env('SESSION_ENCRYPT', false),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => 'sessions',

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel'), '_').'_session'),

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    'secure' => env('SESSION_SECURE_COOKIE'),

    'http_only' => env('SESSION_HTTP_ONLY', true),

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
EOF

echo "✅ Backend initialized successfully!"
```

### Step 4: Set Up Docker Configuration

```bash
#!/bin/bash
# scripts/04-setup-docker.sh

set -e

echo "🐳 Setting up Docker configuration..."

cd eldercare-sg

# Create Docker Compose for development
cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  # Frontend service
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
      target: development
    ports:
      - "3000:3000"
    volumes:
      - ./frontend:/app
      - /app/node_modules
      - /app/.next
    environment:
      - NODE_ENV=development
      - NEXT_PUBLIC_API_URL=http://localhost:8000
      - NEXT_PUBLIC_APP_URL=http://localhost:3000
    depends_on:
      - backend
    networks:
      - eldercare-network
    restart: unless-stopped

  # Backend service
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
      target: development
    ports:
      - "8000:80"
      - "9000:9000"
    volumes:
      - ./backend:/var/www/html
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=eldercare_dev
      - DB_USERNAME=eldercare
      - DB_PASSWORD=password
      - CACHE_DRIVER=redis
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - SESSION_DRIVER=redis
      - QUEUE_CONNECTION=redis
    depends_on:
      - mysql
      - redis
    networks:
      - eldercare-network
    restart: unless-stopped

  # MySQL database
  mysql:
    image: mariadb:10.6
    ports:
      - "3306:3306"
    environment:
      - MYSQL_ROOT_PASSWORD=rootpassword
      - MYSQL_DATABASE=eldercare_dev
      - MYSQL_USER=eldercare
      - MYSQL_PASSWORD=password
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf
    networks:
      - eldercare-network
    restart: unless-stopped

  # Redis cache
  redis:
    image: redis:6-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
      - ./docker/redis/redis.conf:/usr/local/etc/redis/redis.conf
    command: redis-server /usr/local/etc/redis/redis.conf
    networks:
      - eldercare-network
    restart: unless-stopped

  # Elasticsearch for search
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.8.0
    environment:
      - discovery.type=single-node
      - xpack.security.enabled=false
      - "ES_JAVA_OPTS=-Xms512m -Xmx512m"
    ports:
      - "9200:9200"
    volumes:
      - elasticsearch_data:/usr/share/elasticsearch/data
    networks:
      - eldercare-network
    restart: unless-stopped

  # Mailhog for email testing
  mailhog:
    image: mailhog/mailhog
    ports:
      - "1025:1025"
      - "8025:8025"
    networks:
      - eldercare-network
    restart: unless-stopped

volumes:
  mysql_data:
  redis_data:
  elasticsearch_data:

networks:
  eldercare-network:
    driver: bridge
EOF

# Create Docker Compose for production
cat > docker-compose.prod.yml << 'EOF'
version: '3.8'

services:
  # Nginx reverse proxy
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/ssl:/etc/nginx/ssl
      - ./frontend/public:/var/www/html/public
    depends_on:
      - frontend
      - backend
    networks:
      - eldercare-network
    restart: unless-stopped

  # Frontend service
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
      target: runner
    environment:
      - NODE_ENV=production
      - NEXT_PUBLIC_API_URL=https://api.eldercare-sg.example.com
      - NEXT_PUBLIC_APP_URL=https://eldercare-sg.example.com
    depends_on:
      - backend
    networks:
      - eldercare-network
    restart: unless-stopped
    deploy:
      resources:
        limits:
          memory: 512M
        reservations:
          memory: 256M

  # Backend service
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
      target: production
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=eldercare_prod
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - CACHE_DRIVER=redis
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - SESSION_DRIVER=redis
      - QUEUE_CONNECTION=redis
      - APP_URL=https://eldercare-sg.example.com
      - APP_KEY=${APP_KEY}
    depends_on:
      - mysql
      - redis
    networks:
      - eldercare-network
    restart: unless-stopped
    deploy:
      resources:
        limits:
          memory: 1G
        reservations:
          memory: 512M

  # MySQL database
  mysql:
    image: mariadb:10.6
    environment:
      - MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
      - MYSQL_DATABASE=eldercare_prod
      - MYSQL_USER=${DB_USERNAME}
      - MYSQL_PASSWORD=${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf
      - ./backups:/backups
    networks:
      - eldercare-network
    restart: unless-stopped
    deploy:
      resources:
        limits:
          memory: 2G
        reservations:
          memory: 1G

  # Redis cache
  redis:
    image: redis:6-alpine
    volumes:
      - redis_data:/data
      - ./docker/redis/redis.conf:/usr/local/etc/redis/redis.conf
    command: redis-server /usr/local/etc/redis/redis.conf
    networks:
      - eldercare-network
    restart: unless-stopped
    deploy:
      resources:
        limits:
          memory: 256M
        reservations:
          memory: 128M

  # Elasticsearch for search
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.8.0
    environment:
      - discovery.type=single-node
      - xpack.security.enabled=false
      - "ES_JAVA_OPTS=-Xms1g -Xmx1g"
    volumes:
      - elasticsearch_data:/usr/share/elasticsearch/data
    networks:
      - eldercare-network
    restart: unless-stopped
    deploy:
      resources:
        limits:
          memory: 2G
        reservations:
          memory: 1G

  # Laravel Horizon for queue monitoring
  horizon:
    build:
      context: ./backend
      dockerfile: Dockerfile
      target: production
    command: php artisan horizon
    environment:
      - APP_ENV=production
      - DB_HOST=mysql
      - REDIS_HOST=redis
    depends_on:
      - mysql
      - redis
    networks:
      - eldercare-network
    restart: unless-stopped

  # Laravel scheduler
  scheduler:
    build:
      context: ./backend
      dockerfile: Dockerfile
      target: production
    command: sh -c "while true; do php artisan schedule:run; sleep 60; done"
    environment:
      - APP_ENV=production
      - DB_HOST=mysql
      - REDIS_HOST=redis
    depends_on:
      - mysql
      - redis
    networks:
      - eldercare-network
    restart: unless-stopped

volumes:
  mysql_data:
  redis_data:
  elasticsearch_data:

networks:
  eldercare-network:
    driver: bridge
EOF

# Create frontend Dockerfile
cat > frontend/Dockerfile << 'EOF'
# Multi-stage build for Next.js 14 application

# Stage 1: Install dependencies
FROM node:18-alpine AS deps
RUN apk add --no-cache libc6-compat
WORKDIR /app

# Copy package files
COPY package*.json ./
RUN npm ci --only=production && npm cache clean --force

# Stage 2: Build the application
FROM node:18-alpine AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Set environment variables for build
ENV NEXT_TELEMETRY_DISABLED 1
ENV NODE_ENV production

# Build the application
RUN npm run build

# Stage 3: Development image
FROM node:18-alpine AS development
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev"]

# Stage 4: Production image
FROM node:18-alpine AS runner
WORKDIR /app

ENV NODE_ENV production
ENV NEXT_TELEMETRY_DISABLED 1

# Create non-root user
RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nextjs

# Copy built application
COPY --from=builder /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs

EXPOSE 3000

ENV PORT 3000
ENV HOSTNAME "0.0.0.0"

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:3000/api/health || exit 1

CMD ["node", "server.js"]
EOF

# Create backend Dockerfile
cat > backend/Dockerfile << 'EOF'
# Multi-stage build for Laravel 12 application

# Stage 1: Base image with PHP extensions
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libmcrypt-dev \
    imagemagick-dev \
    supervisor \
    nginx

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo_mysql \
    zip \
    exif \
    pcntl \
    bcmath \
    opcache \
    imagick

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Stage 2: Install dependencies
FROM base AS deps

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Stage 3: Development image
FROM base AS development

# Install development dependencies
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-interaction

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy PHP configuration
COPY ../docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY ../docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy supervisord configuration
COPY ../docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Stage 4: Production image
FROM base AS production

# Copy dependencies from deps stage
COPY --from=deps /var/www/html/vendor ./vendor

# Copy application code
COPY . .

# Optimize for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy PHP configuration
COPY ../docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY ../docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy supervisord configuration
COPY ../docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:9000/health || exit 1

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
EOF

# Create Nginx configuration
cat > docker/nginx/nginx.conf << 'EOF'
events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    # Logging
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';
    access_log /var/log/nginx/access.log main;
    error_log /var/log/nginx/error.log;

    # Performance optimizations
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 20M;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml
        image/svg+xml;

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=login:10m rate=1r/s;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Upstream servers
    upstream frontend {
        server frontend:3000;
    }

    upstream backend {
        server backend:9000;
    }

    # HTTP to HTTPS redirect
    server {
        listen 80;
        server_name eldercare-sg.example.com www.eldercare-sg.example.com;
        return 301 https://$server_name$request_uri;
    }

    # Main server block
    server {
        listen 443 ssl http2;
        server_name eldercare-sg.example.com www.eldercare-sg.example.com;

        # SSL certificates
        ssl_certificate /etc/nginx/ssl/cert.pem;
        ssl_certificate_key /etc/nginx/ssl/key.pem;

        # Root directory
        root /var/www/html/public;
        index index.php index.html;

        # Frontend routes
        location / {
            try_files $uri $uri/ @frontend;
        }

        location @frontend {
            proxy_pass http://frontend;
            proxy_http_version 1.1;
            proxy_set_header Upgrade $http_upgrade;
            proxy_set_header Connection 'upgrade';
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            proxy_cache_bypass $http_upgrade;
        }

        # API routes
        location /api/ {
            limit_req zone=api burst=20 nodelay;
            try_files $uri $uri/ @backend;
        }

        # Login rate limiting
        location /api/login {
            limit_req zone=login burst=5 nodelay;
            try_files $uri $uri/ @backend;
        }

        location @backend {
            proxy_pass http://backend;
            proxy_http_version 1.1;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }

        # PHP files
        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            fastcgi_pass backend:9000;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param PATH_INFO $fastcgi_path_info;
        }

        # Static files caching
        location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|webp|woff|woff2)$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
        }

        # Security: Deny access to sensitive files
        location ~ /\. {
            deny all;
        }

        location ~ /(\.env|\.git|composer\.json|composer\.lock|package\.json|package-lock\.json)$ {
            deny all;
        }
    }
}
EOF

# Create PHP configuration
cat > docker/php/php.ini << 'EOF'
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 20M
post_max_size = 20M
max_input_vars = 3000
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=1
EOF

# Create MySQL configuration
cat > docker/mysql/my.cnf << 'EOF'
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_method = O_DIRECT
innodb_flush_log_at_trx_commit = 2
query_cache_type = 1
query_cache_size = 64M
max_connections = 200
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
EOF

# Create Redis configuration
cat > docker/redis/redis.conf << 'EOF'
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
EOF

# Create supervisord configuration
cat > docker/supervisord.conf << 'EOF'
[supervisord]
nodaemon=true
user=root

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
priority=5
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=nginx -g 'daemon off;'
autostart=true
autorestart=true
priority=10
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
EOF

echo "✅ Docker configuration set up successfully!"
```

### Step 5: Create Initial Components and Pages

```bash
#!/bin/bash
# scripts/05-create-components.sh

set -e

echo "🧩 Creating initial components and pages..."

cd eldercare-sg/frontend

# Create UI components
mkdir -p src/components/ui

# Create Button component
cat > src/components/ui/button.tsx << 'EOF'
import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const buttonVariants = cva(
  "inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50",
  {
    variants: {
      variant: {
        default: "bg-eldercare-deep-blue text-white hover:bg-eldercare-deep-blue/90",
        destructive:
          "bg-destructive text-destructive-foreground hover:bg-destructive/90",
        outline:
          "border border-input bg-background hover:bg-accent hover:text-accent-foreground",
        secondary:
          "bg-eldercare-soft-amber text-eldercare-deep-blue hover:bg-eldercare-soft-amber/80",
        ghost: "hover:bg-accent hover:text-accent-foreground",
        link: "text-eldercare-gold underline-offset-4 hover:underline",
        cta: "bg-eldercare-gold text-eldercare-deep-blue font-semibold hover:bg-eldercare-gold/90 shadow-md",
      },
      size: {
        default: "h-10 px-4 py-2",
        sm: "h-9 rounded-md px-3",
        lg: "h-11 rounded-md px-8",
        icon: "h-10 w-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, asChild = false, ...props }, ref) => {
    const Comp = asChild ? Slot : "button"
    return (
      <Comp
        className={cn(buttonVariants({ variant, size, className }))}
        ref={ref}
        {...props}
      />
    )
  }
)
Button.displayName = "Button"

export { Button, buttonVariants }
EOF

# Create Card component
cat > src/components/ui/card.tsx << 'EOF'
import * as React from "react"

import { cn } from "@/lib/utils"

const Card = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn(
      "rounded-lg border bg-card text-card-foreground shadow-sm",
      className
    )}
    {...props}
  />
))
Card.displayName = "Card"

const CardHeader = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex flex-col space-y-1.5 p-6", className)}
    {...props}
  />
))
CardHeader.displayName = "CardHeader"

const CardTitle = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h3
    ref={ref}
    className={cn(
      "text-2xl font-semibold leading-none tracking-tight",
      className
    )}
    {...props}
  />
))
CardTitle.displayName = "CardTitle"

const CardDescription = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
  <p
    ref={ref}
    className={cn("text-sm text-muted-foreground", className)}
    {...props}
  />
))
CardDescription.displayName = "CardDescription"

const CardContent = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div ref={ref} className={cn("p-6 pt-0", className)} {...props} />
))
CardContent.displayName = "CardContent"

const CardFooter = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex items-center p-6 pt-0", className)}
    {...props}
  />
))
CardFooter.displayName = "CardFooter"

export { Card, CardHeader, CardFooter, CardTitle, CardDescription, CardContent }
EOF

# Create utility function
mkdir -p src/lib
cat > src/lib/utils.ts << 'EOF'
import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}
EOF

# Create layout components
mkdir -p src/components/layout

# Create Header component
cat > src/components/layout/header.tsx << 'EOF'
'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import { Button } from '@/components/ui/button'
import { Menu, X } from 'lucide-react'

const Header = () => {
  const [isScrolled, setIsScrolled] = useState(false)
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)

  useEffect(() => {
    const handleScroll = () => {
      if (window.scrollY > 10) {
        setIsScrolled(true)
      } else {
        setIsScrolled(false)
      }
    }

    window.addEventListener('scroll', handleScroll)
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  return (
    <header
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        isScrolled
          ? 'bg-white/80 backdrop-blur-md shadow-sm'
          : 'bg-transparent'
      }`}
    >
      <div className="container mx-auto px-6 py-4 flex items-center justify-between">
        <div className="flex items-center">
          <Link href="/" className="text-2xl font-playfair font-bold text-eldercare-deep-blue">
            ElderCare SG
          </Link>
        </div>

        {/* Desktop Navigation */}
        <nav className="hidden md:flex items-center space-x-8">
          <Link href="#programs" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            Programs
          </Link>
          <Link href="#philosophy" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            Our Philosophy
          </Link>
          <Link href="#testimonials" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            Testimonials
          </Link>
          <Link href="#contact" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            Contact
          </Link>
          <Button variant="cta" asChild>
            <Link href="#booking">Book Visit</Link>
          </Button>
        </nav>

        {/* Mobile Menu Button */}
        <button
          className="md:hidden text-eldercare-deep-blue"
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          aria-label="Toggle menu"
        >
          {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>

      {/* Mobile Navigation */}
      {isMobileMenuOpen && (
        <div className="md:hidden bg-white border-t">
          <nav className="container mx-auto px-6 py-4 flex flex-col space-y-4">
            <Link
              href="#programs"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              Programs
            </Link>
            <Link
              href="#philosophy"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              Our Philosophy
            </Link>
            <Link
              href="#testimonials"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              Testimonials
            </Link>
            <Link
              href="#contact"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              Contact
            </Link>
            <Button variant="cta" asChild className="w-full">
              <Link href="#booking" onClick={() => setIsMobileMenuOpen(false)}>
                Book Visit
              </Link>
            </Button>
          </nav>
        </div>
      )}
    </header>
  )
}

export default Header
EOF

# Create Footer component
cat > src/components/layout/footer.tsx << 'EOF'
import Link from 'next/link'
import { Button } from '@/components/ui/button'
import { Mail, Phone, MapPin } from 'lucide-react'

const Footer = () => {
  return (
    <footer className="bg-eldercare-deep-blue text-white">
      <div className="container mx-auto px-6 py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* Contact Information */}
          <div>
            <h3 className="text-xl font-playfair font-semibold mb-4">Contact Us</h3>
            <div className="space-y-3">
              <div className="flex items-center space-x-3">
                <Phone size={18} />
                <span>+65 6123 4567</span>
              </div>
              <div className="flex items-center space-x-3">
                <Mail size={18} />
                <span>info@eldercare-sg.example.com</span>
              </div>
              <div className="flex items-center space-x-3">
                <MapPin size={18} />
                <span>123 Care Lane, Singapore 123456</span>
              </div>
            </div>
          </div>

          {/* Newsletter */}
          <div>
            <h3 className="text-xl font-playfair font-semibold mb-4">Stay Updated</h3>
            <p className="mb-4 text-eldercare-off-white">
              Subscribe to our newsletter for updates on our programs and services.
            </p>
            <form className="flex flex-col space-y-2">
              <input
                type="email"
                placeholder="Your email address"
                className="px-4 py-2 rounded-md text-eldercare-deep-blue"
                required
              />
              <Button variant="secondary" type="submit">
                Subscribe
              </Button>
            </form>
          </div>

          {/* Certifications */}
          <div>
            <h3 className="text-xl font-playfair font-semibold mb-4">Certifications</h3>
            <div className="grid grid-cols-2 gap-4">
              <div className="bg-white/10 rounded-md p-3 flex items-center justify-center">
                <span className="text-sm">MOH Certified</span>
              </div>
              <div className="bg-white/10 rounded-md p-3 flex items-center justify-center">
                <span className="text-sm">ISO 9001</span>
              </div>
              <div className="bg-white/10 rounded-md p-3 flex items-center justify-center">
                <span className="text-sm">Eldercare Accredited</span>
              </div>
              <div className="bg-white/10 rounded-md p-3 flex items-center justify-center">
                <span className="text-sm">SG Verified</span>
              </div>
            </div>
          </div>
        </div>

        <div className="border-t border-white/20 mt-8 pt-8 text-center text-eldercare-off-white">
          <p>&copy; {new Date().getFullYear()} ElderCare SG. All rights reserved.</p>
          <div className="mt-4 space-x-4">
            <Link href="/privacy" className="hover:text-eldercare-gold transition-colors">
              Privacy Policy
            </Link>
            <Link href="/terms" className="hover:text-eldercare-gold transition-colors">
              Terms of Service
            </Link>
          </div>
        </div>
      </div>
    </footer>
  )
}

export default Footer
EOF

# Create section components
mkdir -p src/components/sections

# Create Hero section
cat > src/components/sections/hero.tsx << 'EOF'
'use client'

import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Play, X } from 'lucide-react'

const Hero = () => {
  const [isVideoOpen, setIsVideoOpen] = useState(false)

  return (
    <section className="relative h-screen flex items-center justify-center overflow-hidden">
      {/* Background Video */}
      <div className="absolute inset-0 z-0">
        <video
          className="w-full h-full object-cover"
          autoPlay
          muted
          loop
          playsInline
        >
          <source src="/videos/hero-video.mp4" type="video/mp4" />
          <source src="/videos/hero-video.webm" type="video/webm" />
        </video>
        <div className="absolute inset-0 bg-eldercare-deep-blue/60"></div>
      </div>

      {/* Content */}
      <div className="relative z-10 text-center text-white px-6 max-w-4xl mx-auto">
        <h1 className="text-4xl md:text-6xl font-playfair font-bold mb-6">
          Compassionate Daycare Solutions for Your Loved Ones
        </h1>
        <p className="text-xl md:text-2xl mb-8 text-eldercare-off-white">
          Providing a safe, engaging, and nurturing environment for seniors in Singapore
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Button variant="cta" size="lg" asChild>
            <a href="#booking">Book Visit</a>
          </Button>
          <Button
            variant="outline"
            size="lg"
            className="bg-white/20 border-white text-white hover:bg-white/30"
            onClick={() => setIsVideoOpen(true)}
          >
            <Play className="mr-2 h-4 w-4" />
            Watch Tour
          </Button>
        </div>
      </div>

      {/* Video Modal */}
      {isVideoOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
          <div className="relative w-full max-w-4xl">
            <button
              className="absolute -top-10 right-0 text-white hover:text-eldercare-gold transition-colors"
              onClick={() => setIsVideoOpen(false)}
              aria-label="Close video"
            >
              <X size={24} />
            </button>
            <div className="aspect-video">
              <iframe
                src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                title="ElderCare SG Virtual Tour"
                className="w-full h-full rounded-md"
                allowFullScreen
              ></iframe>
            </div>
          </div>
        </div>
      )}
    </section>
  )
}

export default Hero
EOF

# Create Program Highlights section
cat > src/components/sections/program-highlights.tsx << 'EOF'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Heart, Activity, Users } from 'lucide-react'

const ProgramHighlights = () => {
  const programs = [
    {
      icon: <Heart className="h-8 w-8 text-eldercare-calming-green" />,
      title: "Day Programs",
      description: "Engaging daily activities designed to promote physical and mental well-being.",
      color: "bg-eldercare-soft-amber"
    },
    {
      icon: <Activity className="h-8 w-8 text-eldercare-calming-green" />,
      title: "Wellness",
      description: "Comprehensive health monitoring and wellness programs tailored to individual needs.",
      color: "bg-eldercare-off-white"
    },
    {
      icon: <Users className="h-8 w-8 text-eldercare-calming-green" />,
      title: "Family Support",
      description: "Resources and support for families to ensure the best care for their loved ones.",
      color: "bg-eldercare-soft-amber"
    }
  ]

  return (
    <section id="programs" className="py-20 bg-eldercare-off-white">
      <div className="container mx-auto px-6">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-playfair font-bold text-eldercare-deep-blue mb-4">
            Our Programs
          </h2>
          <p className="text-lg text-eldercare-slate-gray-2 max-w-2xl mx-auto">
            We offer a range of programs designed to meet the diverse needs of our elderly community.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {programs.map((program, index) => (
            <Card key={index} className={`${program.color} border-none shadow-md hover:shadow-lg transition-shadow`}>
              <CardHeader className="text-center">
                <div className="flex justify-center mb-4">
                  {program.icon}
                </div>
                <CardTitle className="text-xl text-eldercare-deep-blue">
                  {program.title}
                </CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-center text-eldercare-slate-gray-1">
                  {program.description}
                </CardDescription>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  )
}

export default ProgramHighlights
EOF

# Create main layout
mkdir -p src/app
cat > src/app/layout.tsx << 'EOF'
import type { Metadata } from 'next'
import { Inter, Playfair_Display } from 'next/font/google'
import './globals.css'

const inter = Inter({ subsets: ['latin'], variable: '--font-inter' })
const playfairDisplay = Playfair_Display({
  subsets: ['latin'],
  variable: '--font-playfair',
  weight: ['400', '600', '700']
})

export const metadata: Metadata = {
  title: 'ElderCare SG - Compassionate Daycare Solutions',
  description: 'A warm, modern, mobile-responsive platform that introduces families in Singapore to trusted elderly daycare services.',
  keywords: ['elderly care', 'daycare', 'senior care', 'Singapore', 'compassionate care'],
  authors: [{ name: 'ElderCare SG Team' }],
  openGraph: {
    title: 'ElderCare SG - Compassionate Daycare Solutions',
    description: 'A warm, modern, mobile-responsive platform that introduces families in Singapore to trusted elderly daycare services.',
    images: ['/images/og-image.jpg'],
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" className={`${inter.variable} ${playfairDisplay.variable}`}>
      <body className="font-inter bg-eldercare-off-white text-eldercare-slate-gray-1">
        {children}
      </body>
    </html>
  )
}
EOF

# Create global styles
cat > src/app/globals.css << 'EOF'
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 221.2 83.2% 53.3%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96%;
    --secondary-foreground: 222.2 84% 4.9%;
    --muted: 210 40% 96%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96%;
    --accent-foreground: 222.2 84% 4.9%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;
    --radius: 0.5rem;
  }

  .dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;
    --popover: 222.2 84% 4.9%;
    --popover-foreground: 210 40% 98%;
    --primary: 217.2 91.2% 59.8%;
    --primary-foreground: 222.2 84% 4.9%;
    --secondary: 217.2 32.6% 17.5%;
    --secondary-foreground: 210 40% 98%;
    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;
    --accent: 217.2 32.6% 17.5%;
    --accent-foreground: 210 40% 98%;
    --destructive: 0 62.8% 30.6%;
    --destructive-foreground: 210 40% 98%;
    --border: 217.2 32.6% 17.5%;
    --input: 217.2 32.6% 17.5%;
    --ring: 224.3 76.3% 94.1%;
  }
}

@layer base {
  * {
    @apply border-border;
  }
  body {
    @apply bg-background text-foreground;
  }
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Focus styles for accessibility */
.focus-visible:focus {
  @apply outline-2 outline-offset-2 outline-eldercare-gold;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
EOF

# Create home page
cat > src/app/page.tsx << 'EOF'
import Header from '@/components/layout/header'
import Footer from '@/components/layout/footer'
import Hero from '@/components/sections/hero'
import ProgramHighlights from '@/components/sections/program-highlights'

export default function Home() {
  return (
    <main className="min-h-screen">
      <Header />
      <Hero />
      <ProgramHighlights />
      <Footer />
    </main>
  )
}
EOF

echo "✅ Initial components and pages created successfully!"
```

### Step 6: Set Up Testing Framework

```bash
#!/bin/bash
# scripts/06-setup-testing.sh

set -e

echo "🧪 Setting up testing framework..."

cd eldercare-sg/frontend

# Create Jest configuration
cat > jest.config.js << 'EOF'
const nextJest = require('next/jest')

const createJestConfig = nextJest({
  // Provide the path to your Next.js app to load next.config.js and .env files
  dir: './',
})

// Add any custom config to be passed to Jest
const customJestConfig = {
  setupFilesAfterEnv: ['<rootDir>/jest.setup.js'],
  moduleNameMapping: {
    // Handle module aliases (this will be automatically configured for you based on your tsconfig.json paths)
    '^@/(.*)$': '<rootDir>/src/$1',
  },
  testEnvironment: 'jest-environment-jsdom',
  collectCoverageFrom: [
    'src/**/*.{js,jsx,ts,tsx}',
    '!src/**/*.d.ts',
    '!src/app/layout.tsx',
  ],
  coverageThreshold: {
    global: {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80,
    },
  },
}

// createJestConfig is exported this way to ensure that next/jest can load the Next.js config which is async
module.exports = createJestConfig(customJestConfig)
EOF

# Create Jest setup file
cat > jest.setup.js << 'EOF'
import '@testing-library/jest-dom'

// Mock IntersectionObserver
global.IntersectionObserver = class IntersectionObserver {
  constructor() {}
  disconnect() {}
  observe() {}
  unobserve() {}
}

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: jest.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: jest.fn(), // deprecated
    removeListener: jest.fn(), // deprecated
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    dispatchEvent: jest.fn(),
  })),
})
EOF

# Create Playwright configuration
cat > playwright.config.ts << 'EOF'
import { defineConfig, devices } from '@playwright/test';

/**
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './tests/e2e',
  /* Run tests in files in parallel */
  fullyParallel: true,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,
  /* Opt out of parallel tests on CI. */
  workers: process.env.CI ? 1 : undefined,
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: 'html',
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: 'http://localhost:3000',

    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',
  },

  /* Configure projects for major browsers */
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },

    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },

    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },

    /* Test against mobile viewports. */
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'Mobile Safari',
      use: { ...devices['iPhone 12'] },
    },
  ],

  /* Run your local dev server before starting the tests */
  webServer: {
    command: 'npm run dev',
    url: 'http://localhost:3000',
    reuseExistingServer: !process.env.CI,
  },
});
EOF

# Create a sample test
mkdir -p tests/e2e
cat > tests/e2e/example.spec.ts << 'EOF'
import { test, expect } from '@playwright/test';

test('homepage has correct title', async ({ page }) => {
  await page.goto('/');
  
  // Expect a title "to contain" a substring.
  await expect(page).toHaveTitle(/ElderCare SG/);
});

test('navigation works correctly', async ({ page }) => {
  await page.goto('/');
  
  // Click the Programs link
  await page.click('text=Programs');
  
  // Expects the URL to contain programs
  await expect(page).toHaveURL(/.*programs/);
});

test('Book Visit button is visible', async ({ page }) => {
  await page.goto('/');
  
  // Check if the Book Visit button is visible
  await expect(page.locator('text=Book Visit')).toBeVisible();
});
EOF

# Create a sample unit test
mkdir -p tests/unit
cat > tests/unit/Button.test.tsx << 'EOF'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Button } from '@/components/ui/button'

describe('Button', () => {
  it('renders correctly', () => {
    render(<Button>Click me</Button>)
    expect(screen.getByRole('button', { name: /click me/i })).toBeInTheDocument()
  })

  it('handles click events', async () => {
    const handleClick = jest.fn()
    const user = userEvent.setup()
    
    render(<Button onClick={handleClick}>Click me</Button>)
    
    await user.click(screen.getByRole('button', { name: /click me/i }))
    
    expect(handleClick).toHaveBeenCalledTimes(1)
  })

  it('applies variant styles correctly', () => {
    render(<Button variant="cta">CTA Button</Button>)
    const button = screen.getByRole('button', { name: /cta button/i })
    
    expect(button).toHaveClass('bg-eldercare-gold')
  })
})
EOF

# Update package.json with test scripts
cd ../..
npm pkg set scripts.test="jest"
npm pkg set scripts.test:watch="jest --watch"
npm pkg set scripts.test:coverage="jest --coverage"
npm pkg set scripts.test:e2e="playwright test"

cd frontend

echo "✅ Testing framework set up successfully!"
```

### Step 7: Set Up CI/CD Pipeline

```bash
#!/bin/bash
# scripts/07-setup-cicd.sh

set -e

echo "🔄 Setting up CI/CD pipeline..."

cd eldercare-sg

# Create GitHub Actions workflow for CI
mkdir -p .github/workflows
cat > .github/workflows/ci.yml << 'EOF'
name: CI

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test-frontend:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v3

    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        cache: 'npm'
        cache-dependency-path: frontend/package-lock.json

    - name: Install dependencies
      run: |
        cd frontend
        npm ci

    - name: Run linting
      run: |
        cd frontend
        npm run lint

    - name: Run type checking
      run: |
        cd frontend
        npm run type-check

    - name: Run unit tests
      run: |
        cd frontend
        npm run test:coverage

    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        directory: ./frontend/coverage
        flags: frontend
        name: frontend-coverage

    - name: Build application
      run: |
        cd frontend
        npm run build

    - name: Run E2E tests
      run: |
        cd frontend
        npm run test:e2e

  test-backend:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mariadb:10.6
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: eldercare_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

      redis:
        image: redis:6-alpine
        ports:
          - 6379:6379
        options: --health-cmd="redis-cli ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: bcmath, gd, imagick, intl, pdo, sqlite, pdo_mysql, mbstring, xml, zip, pcntl
        coverage: xdebug

    - name: Copy environment file
      run: |
        cd backend
        cp .env.example .env
        php artisan key:generate

    - name: Install dependencies
      run: |
        cd backend
        composer install --no-progress --prefer-dist --optimize-autoloader

    - name: Run migrations
      run: |
        cd backend
        php artisan migrate --force

    - name: Run tests
      run: |
        cd backend
        vendor/bin/phpunit --coverage-clover=coverage.xml

    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        directory: ./backend
        flags: backend
        name: backend-coverage

  lighthouse:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v3

    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        cache: 'npm'
        cache-dependency-path: frontend/package-lock.json

    - name: Install dependencies
      run: |
        cd frontend
        npm ci

    - name: Build application
      run: |
        cd frontend
        npm run build

    - name: Run Lighthouse CI
      run: |
        cd frontend
        npm install -g @lhci/cli@0.12.x
        lhci autorun
      env:
        LHCI_GITHUB_APP_TOKEN: ${{ secrets.LHCI_GITHUB_APP_TOKEN }}

  accessibility:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v3

    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        cache: 'npm'
        cache-dependency-path: frontend/package-lock.json

    - name: Install dependencies
      run: |
        cd frontend
        npm ci

    - name: Build application
      run: |
        cd frontend
        npm run build

    - name: Run accessibility tests
      run: |
        cd frontend
        npm install -g pa11y-ci
        npm run start &
        sleep 10
        pa11y-ci --sitemap http://localhost:3000/sitemap.xml --sitemap-exclude "/pdf/"
EOF

# Create GitHub Actions workflow for deployment
cat > .github/workflows/deploy.yml << 'EOF'
name: Deploy

on:
  push:
    branches: [ main ]
    tags: [ 'v*' ]

jobs:
  deploy-staging:
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    environment: staging

    steps:
    - uses: actions/checkout@v3

    - name: Deploy to staging
      run: |
        echo "Deploying to staging environment"
        # Add deployment commands here

  deploy-production:
    if: startsWith(github.ref, 'refs/tags/v')
    runs-on: ubuntu-latest
    environment: production

    steps:
    - uses: actions/checkout@v3

    - name: Deploy to production
      run: |
        echo "Deploying to production environment"
        # Add deployment commands here
EOF

# Create Lighthouse configuration
cat > frontend/lighthouserc.js << 'EOF'
module.exports = {
  ci: {
    collect: {
      url: ['http://localhost:3000'],
      numberOfRuns: 3
    },
    assert: {
      assertions: {
        'categories:performance': ['warn', { minScore: 0.9 }],
        'categories:accessibility': ['error', { minScore: 0.9 }],
        'categories:best-practices': ['warn', { minScore: 0.9 }],
        'categories:seo': ['warn', { minScore: 0.9 }],
      }
    },
    upload: {
      target: 'temporary-public-storage'
    }
  }
};
EOF

echo "✅ CI/CD pipeline set up successfully!"
```

### Step 8: Create Documentation

```bash
#!/bin/bash
# scripts/08-create-documentation.sh

set -e

echo "📚 Creating documentation..."

cd eldercare-sg

# Create API documentation
cat > docs/api/README.md << 'EOF'
# ElderCare SG API Documentation

## Overview

This document provides information about the ElderCare SG API endpoints, authentication, and data models.

## Base URL

- Development: `http://localhost:8000/api`
- Production: `https://api.eldercare-sg.example.com`

## Authentication

The API uses Laravel Sanctum for authentication. Include the token in the Authorization header:

```
Authorization: Bearer {token}
```

## Endpoints

### Authentication

#### POST /auth/login
Login with email and password.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  },
  "token": "1|abc123..."
}
```

#### POST /auth/logout
Logout the authenticated user.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Successfully logged out"
}
```

### Bookings

#### GET /bookings
Get a list of bookings for the authenticated user.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "date": "2023-06-15",
      "time": "10:00",
      "status": "confirmed",
      "center": {
        "id": 1,
        "name": "ElderCare SG Central"
      }
    }
  ]
}
```

#### POST /bookings
Create a new booking.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "center_id": 1,
  "date": "2023-06-15",
  "time": "10:00",
  "notes": "First visit"
}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "date": "2023-06-15",
    "time": "10:00",
    "status": "pending",
    "center": {
      "id": 1,
      "name": "ElderCare SG Central"
    }
  }
}
```

### Centers

#### GET /centers
Get a list of all daycare centers.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "ElderCare SG Central",
      "address": "123 Care Lane, Singapore 123456",
      "phone": "+65 6123 4567",
      "email": "central@eldercare-sg.example.com",
      "facilities": ["Day Care", "Rehabilitation", "Social Activities"],
      "operating_hours": {
        "monday": "8:00 AM - 6:00 PM",
        "tuesday": "8:00 AM - 6:00 PM",
        "wednesday": "8:00 AM - 6:00 PM",
        "thursday": "8:00 AM - 6:00 PM",
        "friday": "8:00 AM - 6:00 PM",
        "saturday": "Closed",
        "sunday": "Closed"
      }
    }
  ]
}
```

#### GET /centers/{id}
Get details of a specific center.

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "ElderCare SG Central",
    "address": "123 Care Lane, Singapore 123456",
    "phone": "+65 6123 4567",
    "email": "central@eldercare-sg.example.com",
    "description": "Our flagship center offering comprehensive daycare services...",
    "facilities": ["Day Care", "Rehabilitation", "Social Activities"],
    "operating_hours": {
      "monday": "8:00 AM - 6:00 PM",
      "tuesday": "8:00 AM - 6:00 PM",
      "wednesday": "8:00 AM - 6:00 PM",
      "thursday": "8:00 AM - 6:00 PM",
      "friday": "8:00 AM - 6:00 PM",
      "saturday": "Closed",
      "sunday": "Closed"
    },
    "images": [
      {
        "url": "https://example.com/images/center1-1.jpg",
        "caption": "Main activity area"
      }
    ],
    "staff": [
      {
        "id": 1,
        "name": "Dr. Jane Smith",
        "position": "Center Director",
        "qualification": "PhD in Gerontology",
        "experience": "15 years"
      }
    ]
  }
}
```

### Programs

#### GET /programs
Get a list of all programs.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Day Programs",
      "description": "Engaging daily activities...",
      "duration": "Full day",
      "price": "$50 per day",
      "activities": ["Art Therapy", "Music Therapy", "Physical Exercise"]
    }
  ]
}
```

### Testimonials

#### GET /testimonials
Get a list of testimonials.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Tan",
      "relation": "Son",
      "content": "The care provided to my mother has been exceptional...",
      "rating": 5,
      "date": "2023-05-15"
    }
  ]
}
```

## Error Responses

All error responses follow this format:

```json
{
  "message": "Error message",
  "errors": {
    "field": ["Error details for this field"]
  }
}
```

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Unprocessable Entity
- `500` - Internal Server Error
EOF

# Create deployment documentation
cat > docs/deployment/README.md << 'EOF'
# ElderCare SG Deployment Guide

## Overview

This guide provides instructions for deploying the ElderCare SG application to various environments.

## Prerequisites

- Docker and Docker Compose installed
- SSH access to the server
- Domain name configured
- SSL certificates (for production)

## Environment Setup

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
   cp .env.example .env
   # Edit .env with production values
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
EOF

# Create design system documentation
cat > docs/design-system/README.md << 'EOF'
# ElderCare SG Design System

## Overview

This design system provides guidelines and components for the ElderCare SG platform, ensuring consistency, accessibility, and a user-friendly experience.

## Brand Identity

### Logo

The ElderCare SG logo represents care, compassion, and community. It should be used consistently across all materials.

### Color Palette

| Color | Hex Code | Usage |
|-------|----------|-------|
| Deep Blue | #1C3D5A | Primary brand color, headers, important CTAs |
| Off-White | #F7F9FC | Main background |
| Gold | #F0A500 | Highlights, accents, primary CTAs |
| Soft Amber | #FCDFA6 | Card backgrounds, secondary highlights |
| Calming Green | #3D9A74 | Success states, wellness features |
| Slate Gray 1 | #334155 | Primary text |
| Slate Gray 2 | #64748B | Secondary text |

### Typography

#### Fonts

- **Headings**: Playfair Display
- **Body Text**: Inter

#### Typography Scale

| Style | Font | Weight | Size | Line Height |
|-------|------|--------|------|-------------|
| H1 | Playfair Display | 700 | 40-64px | 1.25em |
| H2 | Playfair Display | 600 | 32-48px | 1.25em |
| H3 | Playfair Display | 600 | 24-32px | 1.25em |
| Body | Inter | 400 | 16-18px | 1.5em |
| Small | Inter | 400 | 14px | 1.5em |

## Components

### Buttons

#### Primary Button
Used for primary actions and CTAs.

```jsx
<Button variant="default">Button Text</Button>
```

#### Secondary Button
Used for secondary actions.

```jsx
<Button variant="secondary">Button Text</Button>
```

#### CTA Button
Used for important call-to-action elements.

```jsx
<Button variant="cta">Button Text</Button>
```

### Cards

Used to group related content and actions.

```jsx
<Card>
  <CardHeader>
    <CardTitle>Card Title</CardTitle>
    <CardDescription>Card Description</CardDescription>
  </CardHeader>
  <CardContent>
    Card content goes here
  </CardContent>
  <CardFooter>
    <Button>Action</Button>
  </CardFooter>
</Card>
```

## Layout

### Grid System

- **Container**: Max width of 1280px with 24px gutters
- **Columns**: 12-column responsive grid
- **Breakpoints**:
  - Mobile: < 768px
  - Tablet: 768px - 1023px
  - Desktop: ≥ 1024px

### Spacing

- **Base unit**: 4px
- **Scale**: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96px

## Icons

We use Lucide icons for consistency and clarity. Icons should be used to:

- Enhance meaning
- Improve navigation
- Indicate status
- Represent actions

## Imagery

### Photography

- Focus on authentic, warm interactions
- Diverse representation of elderly individuals
- Bright, natural lighting
- High-resolution images

### Videos

- Professional production quality
- Subtitles for accessibility
- Autoplay with user controls
- Optimized for web (WebM format preferred)

## Accessibility

### Color Contrast

All text and background color combinations must meet WCAG 2.1 AA standards:
- Normal text: 4.5:1
- Large text: 3:1
- Non-text elements: 3:1

### Keyboard Navigation

All interactive elements must be accessible via keyboard:
- Clear focus indicators
- Logical tab order
- Skip navigation links

### Screen Reader Support

- Semantic HTML5 elements
- ARIA labels and roles
- Alt text for images
- Descriptive link text

## Animation

### Principles

- Purposeful and meaningful
- Subtle and natural
- Respectful of user preferences
- Performance optimized

### Guidelines

- Duration: 200-500ms for most animations
- Easing: ease-out for entrance animations
- Reduced motion: Respect prefers-reduced-motion setting

## Voice and Tone

### Principles

- Warm and compassionate
- Clear and simple
- Respectful and dignified
- Professional yet approachable

### Guidelines

- Use simple language
- Avoid jargon
- Be direct and clear
- Show empathy and understanding

## Usage

### Implementation

1. Import components from the design system
2. Use established color and typography tokens
3. Follow layout and spacing guidelines
4. Ensure accessibility standards are met

### Customization

While the design system provides a solid foundation, it's designed to be flexible:

- Extend with additional components as needed
- Adjust spacing within the established scale
- Use color palette for additional UI states
- Follow animation principles for custom animations

## Maintenance

The design system is a living document that evolves with the product:

- Regular reviews and updates
- Component versioning
- Documentation updates
- User feedback incorporation
EOF

# Create accessibility documentation
cat > docs/accessibility/README.md << 'EOF'
# ElderCare SG Accessibility Guide

## Overview

This guide outlines the accessibility standards and practices implemented in the ElderCare SG platform to ensure it's usable by everyone, including people with disabilities.

## Standards Compliance

The ElderCare SG platform aims to comply with:

- WCAG 2.1 AA (Web Content Accessibility Guidelines)
- Singapore's IMDA accessibility guidelines
- Section 508 of the Rehabilitation Act

## Key Accessibility Features

### Keyboard Navigation

All interactive elements are fully accessible via keyboard:

- **Tab Order**: Logical and intuitive navigation
- **Focus Indicators**: Clear visible focus states
- **Skip Links**: Quick navigation to main content
- **Keyboard Shortcuts**: Enhanced navigation for power users

### Screen Reader Support

The platform is optimized for screen readers:

- **Semantic HTML**: Proper use of HTML5 elements
- **ARIA Labels**: Descriptive labels for complex elements
- **Alt Text**: Meaningful descriptions for images
- **Heading Structure**: Logical heading hierarchy

### Visual Accessibility

Visual design considerations for accessibility:

- **Color Contrast**: All text meets WCAG AA contrast ratios
- **Text Resizing**: Text remains readable at 200% zoom
- **Color Independence**: Information not conveyed by color alone
- **Focus Indicators**: High contrast focus states

### Motor Accessibility

Features to assist users with motor impairments:

- **Large Click Targets**: Minimum 44×44px touch targets
- **Spacing**: Adequate spacing between interactive elements
- **Error Prevention**: Confirmation for destructive actions
- **Time Limits**: Sufficient time to complete tasks

## Implementation Guidelines

### HTML Structure

Use semantic HTML5 elements:

```html
<header>
  <nav>
    <ul>
      <li><a href="/">Home</a></li>
      <li><a href="/programs">Programs</a></li>
    </ul>
  </nav>
</header>

<main>
  <section>
    <h1>Page Title</h1>
    <p>Page content</p>
  </section>
</main>

<footer>
  <p>Footer content</p>
</footer>
```

### ARIA Usage

Use ARIA attributes to enhance accessibility:

```html
<button aria-expanded="false" aria-controls="menu" id="menu-button">
  Menu
</button>
<ul id="menu" aria-labelledby="menu-button" hidden>
  <li><a href="/">Home</a></li>
  <li><a href="/programs">Programs</a></li>
</ul>
```

### Form Accessibility

Ensure forms are accessible:

```html
<form>
  <fieldset>
    <legend>Contact Information</legend>
    
    <div class="form-group">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required aria-describedby="name-help">
      <div id="name-help" class="help-text">Please enter your full name</div>
    </div>
    
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required aria-describedby="email-error">
      <div id="email-error" class="error-message" aria-live="polite"></div>
    </div>
    
    <button type="submit">Submit</button>
  </fieldset>
</form>
```

### Image Accessibility

Provide meaningful alt text for images:

```html
<img src="/images/center-activity.jpg" alt="Elderly participants engaging in a group art therapy session">
<img src="/images/logo.png" alt="ElderCare SG logo">
<img src="/images/decoration.png" alt="" role="presentation">
```

## Testing

### Automated Testing

We use automated tools to check for accessibility issues:

- **axe-core**: JavaScript library for accessibility testing
- **Lighthouse**: Performance and accessibility auditing
- **Pa11y**: Automated accessibility testing

### Manual Testing

Manual testing is performed to catch issues that automated tools might miss:

- **Keyboard Navigation**: Testing all functionality with keyboard only
- **Screen Reader Testing**: Using NVDA, VoiceOver, and JAWS
- **Color Contrast**: Verifying contrast ratios with tools
- **Zoom Testing**: Testing at 200% and 400% zoom

### User Testing

We conduct accessibility testing with users with disabilities:

- **Blind and Low Vision Users**: Screen reader usability
- **Motor Impairments**: Keyboard and alternative input methods
- **Cognitive Disabilities**: Simplified navigation and content

## Best Practices

### Content

- **Plain Language**: Use simple, clear language
- **Consistent Navigation**: Predictable navigation patterns
- **Error Messages**: Clear, helpful error messages
- **Instructions**: Provide clear instructions for complex tasks

### Design

- **Consistent Layout**: Predictable page structure
- **White Space**: Adequate spacing for readability
- **Typography**: Clear, readable fonts at appropriate sizes
- **Color**: Sufficient contrast and avoid color-only information

### Development

- **Progressive Enhancement**: Core functionality works without JavaScript
- **Focus Management**: Proper focus handling in dynamic content
- **Error Prevention**: Confirmations for destructive actions
- **Performance**: Fast loading for users with slow connections

## Resources

### Guidelines and Standards

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [IMDA Accessibility Guidelines](https://www.imda.gov.sg/infocomm-media-landscape/digital-inclusion/accessibility)
- [ARIA Authoring Practices](https://www.w3.org/TR/wai-aria-practices-1.1/)

### Testing Tools

- [axe DevTools](https://www.deque.com/axe/devtools/)
- [WAVE Web Accessibility Evaluation Tool](https://wave.webaim.org/)
- [Colour Contrast Analyser](https://www.tpgi.com/color-contrast-checker/)
- [Screen Reader Simulators](https://webaim.org/articles/screenreader_simulator/)

### Learning Resources

- [WebAIM: Web Accessibility In Mind](https://webaim.org/)
- [A11y Project](https://www.a11yproject.com/)
- [Inclusive Design Principles](https://inclusivedesignprinciples.org/)
EOF

echo "✅ Documentation created successfully!"
```

### Step 9: Create Main Scaffold Script

```bash
#!/bin/bash
# scripts/scaffold.sh

set -e

echo "🚀 ElderCare SG Project Scaffolding"
echo "=================================="
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js first."
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP first."
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi

echo "✅ All prerequisites are installed."
echo ""

# Ask for confirmation
read -p "Do you want to proceed with scaffolding the ElderCare SG project? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Scaffold cancelled."
    exit 1
fi

echo ""
echo "🏗️ Starting project scaffolding..."
echo ""

# Create project structure
echo "1️⃣ Creating project structure..."
./scripts/01-create-structure.sh

# Initialize frontend
echo "2️⃣ Initializing frontend..."
./scripts/02-init-frontend.sh

# Initialize backend
echo "3️⃣ Initializing backend..."
./scripts/03-init-backend.sh

# Set up Docker configuration
echo "4️⃣ Setting up Docker configuration..."
./scripts/04-setup-docker.sh

# Create initial components and pages
echo "5️⃣ Creating initial components and pages..."
./scripts/05-create-components.sh

# Set up testing framework
echo "6️⃣ Setting up testing framework..."
./scripts/06-setup-testing.sh

# Set up CI/CD pipeline
echo "7️⃣ Setting up CI/CD pipeline..."
./scripts/07-setup-cicd.sh

# Create documentation
echo "8️⃣ Creating documentation..."
./scripts/08-create-documentation.sh

echo ""
echo "✅ Project scaffolding completed successfully!"
echo ""
echo "🎉 Next steps:"
echo "1. Navigate to the project directory: cd eldercare-sg"
echo "2. Set up environment variables:"
echo "   - Copy .env.example to .env and update with your values"
echo "   - Copy frontend/.env.local.example to frontend/.env.local and update with your values"
echo "3. Start the development environment: docker-compose up -d"
echo "4. Run database migrations: docker-compose exec backend php artisan migrate"
echo "5. Access the application at http://localhost:3000"
echo ""
echo "📚 For more information, see the documentation in the docs/ directory."
echo ""
echo "🐳 Docker commands:"
echo "- Start development environment: docker-compose up -d"
echo "- Stop development environment: docker-compose down"
echo "- View logs: docker-compose logs -f [service]"
echo ""
echo "🧪 Testing commands:"
echo "- Run frontend tests: cd frontend && npm test"
echo "- Run backend tests: cd backend && composer test"
echo "- Run E2E tests: cd frontend && npm run test:e2e"
echo ""
echo "🚀 Deployment commands:"
echo "- Deploy to staging: ./scripts/deploy.sh staging"
echo "- Deploy to production: ./scripts/deploy.sh production"
echo ""
echo "📞 For support, please contact the ElderCare SG development team."
```

## Execution Plan Validation

Before executing the scaffold script, let's validate our plan:

1. **Prerequisites Check**: The script checks for all required tools (Docker, Docker Compose, Node.js, PHP, Composer)
2. **User Confirmation**: The script asks for confirmation before proceeding
3. **Step-by-Step Execution**: Each step is clearly numbered and provides feedback
4. **Error Handling**: The script uses `set -e` to exit on any error
5. **Next Steps Guidance**: The script provides clear instructions for what to do after scaffolding

The plan covers all aspects of the project setup:
- Project structure creation
- Frontend and backend initialization
- Docker configuration
- Component and page creation
- Testing framework setup
- CI/CD pipeline configuration
- Documentation creation

The script is modular, with each step in its own file, making it easy to modify or debug individual steps if needed.

## Final Instructions

To execute this scaffold plan:

1. Save all the script files in a `scripts` directory
2. Make the main scaffold script executable: `chmod +x scripts/scaffold.sh`
3. Run the scaffold script: `./scripts/scaffold.sh`

This comprehensive plan will create a fully functional ElderCare SG project with all the necessary configurations, components, and documentation, following best practices for development, testing, and deployment.

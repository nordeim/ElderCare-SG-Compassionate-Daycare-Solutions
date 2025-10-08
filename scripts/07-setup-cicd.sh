#!/bin/bash
# scripts/07-setup-cicd.sh

set -e

echo "🔄 Setting up CI/CD pipeline..."

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

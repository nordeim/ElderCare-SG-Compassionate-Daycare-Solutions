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

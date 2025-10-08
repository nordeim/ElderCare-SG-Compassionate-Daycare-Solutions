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

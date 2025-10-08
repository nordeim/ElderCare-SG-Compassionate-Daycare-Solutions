#!/bin/bash
# scripts/01-create-structure.sh

set -e

echo "🏗️ Creating project structure..."

# Create root directories
mkdir -p {frontend,backend,docs,assets,scripts,.github/workflows,docker/{nginx,php,mysql,redis}}

# Create frontend structure
mkdir -p frontend/{components/{ui,layout,sections},pages,styles,utils,hooks,public,tests/{unit,integration,e2e},.next}

# Create backend structure
mkdir -p backend/{app/{Http/{Controllers,Middleware},Models,Services,Providers},database/{migrations,seeders},routes,config,storage,tests/{Unit,Feature},resources/views}

# Create documentation structure
mkdir -p docs/{api,deployment,design-system,accessibility}

# Create assets structure
mkdir -p assets/{images,icons,videos,fonts}

echo "✅ Project structure created successfully!"

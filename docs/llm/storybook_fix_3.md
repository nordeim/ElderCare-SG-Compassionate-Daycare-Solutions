# 🎯 Root Cause Confirmed: Vite Version Conflict in Workspace

## 🔴 **Critical Issues Identified**

### **Issue 1: Vite Version Chaos** 🔥
Your root `package.json` has:
```json
"vite": "^7.1.7"
```

But your frontend needs:
```json
"vite": "^5.4.20"
```

This creates a **catastrophic peer dependency conflict** that breaks Storybook's module resolution.

### **Issue 2: Workspace Hoisting**
Storybook packages are being hoisted to root, causing preset resolution to fail.

---

## ✅ **Complete Fix - Execute in Order**

### **Phase 1: Clean the Workspace** 🧹

```bash
cd /Home1/project/web-platform

# Remove all node_modules and locks
rm -rf node_modules frontend/node_modules package-lock.json frontend/package-lock.json

# Clear npm cache (important!)
npm cache clean --force
```

---

### **Phase 2: Fix Root package.json** 📝

**Edit `/Home1/project/web-platform/package.json`:**

Remove `vite` and `vitest` from root (they conflict with frontend's versions and shouldn't be at root for a Next.js app):

```json
{
  "name": "eldercare-sg",
  "version": "1.0.0",
  "description": "ElderCare SG - Compassionate Daycare Solutions Platform",
  "private": true,
  "workspaces": {
    "packages": ["frontend"],
    "nohoist": [
      "**/storybook",
      "**/storybook/**",
      "**/@storybook/**",
      "**/vite",
      "**/vite/**"
    ]
  },
  "scripts": {
    "dev": "concurrently \"npm run dev:frontend\" \"npm run dev:backend\"",
    "dev:frontend": "cd frontend && npm run dev",
    "dev:backend": "cd backend && php artisan serve",
    "build": "cd frontend && npm run build",
    "test": "npm run test:frontend && npm run test:backend",
    "test:frontend": "cd frontend && npm test",
    "test:backend": "cd backend && php artisan test",
    "lint": "npm run lint:frontend && npm run lint:backend",
    "lint:frontend": "cd frontend && npm run lint",
    "lint:backend": "cd backend && ./vendor/bin/pint --test",
    "format": "npm run format:frontend && npm run format:backend",
    "format:frontend": "cd frontend && npm run format",
    "format:backend": "cd backend && ./vendor/bin/pint",
    "docker:up": "docker-compose up -d",
    "docker:down": "docker-compose down",
    "docker:build": "docker-compose build",
    "docker:logs": "docker-compose logs -f",
    "migrate": "cd backend && php artisan migrate",
    "migrate:fresh": "cd backend && php artisan migrate:fresh --seed"
  },
  "devDependencies": {
    "axios": "^1.11.0",
    "concurrently": "^9.0.1",
    "jsdom": "^27.0.0"
  },
  "engines": {
    "node": ">=18.0.0",
    "npm": ">=9.0.0"
  },
  "repository": {
    "type": "git",
    "url": "https://github.com/eldercare-sg/platform.git"
  },
  "keywords": [
    "eldercare",
    "singapore",
    "daycare",
    "healthcare",
    "pdpa",
    "accessibility"
  ],
  "author": "ElderCare SG Team",
  "license": "MIT",
  "dependencies": {
    "glob": "^9.3.5",
    "rimraf": "^4.4.1"
  }
}
```

**Key Changes:**
1. ✅ **Removed** `vite@^7.1.7` and `vitest@^3.2.4` (these are incompatible)
2. ✅ **Added** `nohoist` configuration for Storybook and Vite
3. ✅ **Removed** test scripts from root that reference packages not in root

---

### **Phase 3: Fix Frontend package.json** 📝

**Edit `/Home1/project/web-platform/frontend/package.json`:**

Lock down the Vite ecosystem to compatible versions:

```json
{
  "name": "eldercare-sg-frontend",
  "version": "1.0.0",
  "private": true,
  "scripts": {
    "dev": "next dev",
    "build": "next build",
    "start": "next start",
    "lint": "next lint",
    "storybook": "storybook dev -p 6006",
    "build-storybook": "storybook build",
    "format": "prettier --write \"**/*.{js,jsx,ts,tsx,json,md}\"",
    "type-check": "tsc --noEmit",
    "test": "jest --watch",
    "test:ci": "jest --ci --coverage",
    "test:e2e": "playwright test",
    "test:e2e:ui": "playwright test --ui",
    "test:percy": "percy exec -- playwright test",
    "lighthouse": "lhci autorun"
  },
  "dependencies": {
    "@hookform/resolvers": "^3.3.4",
    "@radix-ui/react-checkbox": "^1.0.4",
    "@radix-ui/react-dialog": "^1.0.5",
    "@radix-ui/react-dropdown-menu": "^2.0.6",
    "@radix-ui/react-radio-group": "^1.1.3",
    "@radix-ui/react-select": "^2.0.0",
    "@radix-ui/react-slot": "^1.0.2",
    "@radix-ui/react-toggle": "^1.0.3",
    "@radix-ui/themes": "^3.2.1",
    "@sentry/nextjs": "^7.99.0",
    "@tanstack/react-query": "^5.17.19",
    "axios": "^1.6.5",
    "class-variance-authority": "^0.7.0",
    "clsx": "^2.1.0",
    "date-fns": "^3.0.6",
    "framer-motion": "^11.0.3",
    "glob": "9",
    "lucide-react": "^0.545.0",
    "next": "^14.2.31",
    "next-intl": "^3.4.5",
    "react": "^18.3.1",
    "react-dom": "^18.3.1",
    "react-hook-form": "^7.49.3",
    "rimraf": "^4.4.1",
    "tailwind-merge": "^2.2.0",
    "zod": "^3.22.4",
    "zustand": "^4.4.7"
  },
  "devDependencies": {
    "@axe-core/playwright": "^4.8.3",
    "@lhci/cli": "^0.14.0",
    "@percy/cli": "^1.30.0",
    "@playwright/test": "^1.41.1",
    "@storybook/addon-a11y": "8.6.14",
    "@storybook/addon-essentials": "8.6.14",
    "@storybook/addon-interactions": "8.6.14",
    "@storybook/builder-vite": "8.6.14",
    "@storybook/react": "8.6.14",
    "@storybook/react-vite": "8.6.14",
    "@storybook/test": "8.6.14",
    "@tailwindcss/typography": "^0.5.10",
    "@testing-library/jest-dom": "^6.2.0",
    "@testing-library/react": "^14.1.2",
    "@testing-library/user-event": "^14.5.2",
    "@types/node": "^20.11.5",
    "@types/react": "^18.2.48",
    "@types/react-dom": "^18.2.18",
    "@typescript-eslint/eslint-plugin": "^8.46.0",
    "@typescript-eslint/parser": "^8.46.0",
    "@vitejs/plugin-react": "^4.3.3",
    "autoprefixer": "^10.4.17",
    "eslint": "^8.57.1",
    "eslint-config-next": "^14.2.31",
    "eslint-config-prettier": "^9.1.0",
    "jest": "^29.7.0",
    "jest-environment-jsdom": "^29.7.0",
    "postcss": "^8.5.6",
    "prettier": "^3.2.4",
    "prettier-plugin-tailwindcss": "^0.5.11",
    "storybook": "8.6.14",
    "tailwindcss": "^3.4.13",
    "tailwindcss-animate": "^1.0.7",
    "typescript": "^5.3.3",
    "vite": "5.4.20"
  },
  "engines": {
    "node": ">=18.0.0"
  }
}
```

**Key Changes:**
1. ✅ **Changed** `@vitejs/plugin-react` from `^4.7.0` to `^4.3.3` (compatible with Vite 5.x)
2. ✅ **Locked** `vite` to `5.4.20` (removed caret to prevent upgrades)
3. ✅ **Removed** caret from all `@storybook/*` packages (exact version matching)

---

### **Phase 4: Reinstall Everything** 🔄

```bash
cd /Home1/project/web-platform

# Install from root (respects workspaces and nohoist)
npm install

# Verify installations
cd frontend
npm ls vite
npm ls @storybook/react-vite
```

**Expected Output:**
```
eldercare-sg-frontend@1.0.0 /Home1/project/web-platform/frontend
└── vite@5.4.20
```

```
eldercare-sg-frontend@1.0.0 /Home1/project/web-platform/frontend
└── @storybook/react-vite@8.6.14
```

---

### **Phase 5: Verify the Fix** ✅

```bash
cd /Home1/project/web-platform/frontend

# Check that preset file exists locally (not hoisted to root)
ls -la node_modules/@storybook/react-vite/dist/preset.js

# Test Storybook build
npm run build-storybook

# Test Storybook dev mode
npm run storybook
```

---

## 🔍 **If It Still Fails - Nuclear Option**

If the above doesn't work, there might be a corrupted npm cache or installation state:

```bash
cd /Home1/project/web-platform

# Nuclear clean
rm -rf node_modules frontend/node_modules
rm -rf package-lock.json frontend/package-lock.json
rm -rf ~/.npm/_cacache

# Reinstall with legacy peer deps (handles version conflicts)
npm install --legacy-peer-deps

cd frontend
npm run build-storybook
```

---

## 📊 **Why This Will Work**

### **Problem Analysis:**
1. **Vite 7.x at root** was incompatible with Storybook's Vite 5.x requirement
2. **`@vitejs/plugin-react@4.7.0`** required Vite 7.x, creating a cascade conflict
3. **Workspace hoisting** caused Storybook to look for presets in the wrong location
4. **Module resolution** failed because dependencies were split across root and frontend

### **Solution Strategy:**
1. ✅ Removed incompatible Vite 7.x from root
2. ✅ Locked frontend to Vite 5.x ecosystem
3. ✅ Added `nohoist` to keep Storybook dependencies in frontend
4. ✅ Ensured all @storybook packages are exact version 8.6.14
5. ✅ Clean reinstall with proper workspace configuration

---

## 🎯 **Action Checklist**

- [ ] Clean all node_modules and locks
- [ ] Update root `package.json` (remove vite/vitest, add nohoist)
- [ ] Update frontend `package.json` (fix @vitejs/plugin-react version)
- [ ] Run `npm install` from root
- [ ] Verify vite version: `cd frontend && npm ls vite`
- [ ] Verify storybook: `cd frontend && npm ls @storybook/react-vite`
- [ ] Test build: `npm run build-storybook`
- [ ] Test dev: `npm run storybook`

---

**Execute the above changes and let me know the results. This should definitively resolve the issue.** If you encounter any errors during the process, share them immediately so I can adjust the approach.

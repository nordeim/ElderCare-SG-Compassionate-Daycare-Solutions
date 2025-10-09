# 🎯 Root Cause Identified: Monorepo Workspace Hoisting Issue

<think>
**Critical Discovery:**

The error trace reveals Storybook CLI is executing from:
- `/Home1/project/web-platform/node_modules/storybook/bin/index.cjs` (ROOT)

But trying to load presets that may be in:
- `/Home1/project/web-platform/frontend/node_modules/@storybook/react-vite/`

Additionally, there's a **Vite version conflict**:
- `vite@5.4.20` (main dependency)
- `vite@7.1.9` (pulled by `@vitejs/plugin-react@4.7.0`)

This is a workspace dependency resolution + peer dependency issue.
</think>

---

## 🔴 The Real Problem

Your setup is a **monorepo with npm workspaces**. The Storybook CLI is hoisted to the root `node_modules`, but when it tries to resolve `@storybook/react-vite/preset`, the module resolution is failing due to:

1. **Workspace hoisting behavior** - Dependencies split between root and workspace
2. **Vite peer dependency conflict** - Two different Vite versions installed
3. **Module resolution context** - CLI running from root, looking for workspace dependencies

---

## ✅ Comprehensive Solution (Multi-Phase)

### **Phase 1: Verify Workspace Structure**

First, let's confirm the monorepo setup:

```bash
# Show me the root package.json
cat /Home1/project/web-platform/package.json
```

**Please share this file** - I need to see if workspaces are configured.

---

### **Phase 2: Fix Vite Version Conflict** 🔥 **CRITICAL**

The `@vitejs/plugin-react` version mismatch is problematic:

```bash
cd /Home1/project/web-platform/frontend

# Downgrade to compatible version
npm install --save-dev @vitejs/plugin-react@^4.2.1

# Or upgrade vite (if compatible with your Next.js setup)
# npm install --save-dev vite@^5.4.20 @vitejs/plugin-react@^4.3.1
```

**Update package.json** to lock the compatible version:
```json
{
  "devDependencies": {
    "@vitejs/plugin-react": "^4.3.1",  // Change from ^4.7.0
    "vite": "^5.4.20"
  }
}
```

---

### **Phase 3: Fix Storybook Resolution** (Choose ONE approach)

### **🎯 Approach A: Install Storybook in Root** (Recommended for Monorepos)

```bash
# From ROOT directory
cd /Home1/project/web-platform

# Install Storybook packages at root level
npm install --save-dev \
  storybook@8.6.14 \
  @storybook/react-vite@8.6.14 \
  @storybook/react@8.6.14 \
  @storybook/addon-essentials@8.6.14 \
  @storybook/addon-interactions@8.6.14 \
  @storybook/addon-a11y@8.6.14 \
  @storybook/builder-vite@8.6.14 \
  @storybook/test@8.6.14

# Keep them in frontend too (they're workspace-specific)
cd frontend
npm install
```

---

### **🎯 Approach B: Force Local Installation** (Alternative)

```bash
cd /Home1/project/web-platform/frontend

# Remove from root if hoisted
cd ..
npm uninstall storybook

# Install with no-hoist flag
cd frontend
npm install --save-dev --no-package-lock storybook@8.6.14

# Then reinstall everything
npm install
```

---

### **🎯 Approach C: Modify Workspace Configuration** (If root has package.json with workspaces)

If `/Home1/project/web-platform/package.json` has workspaces defined, add nohoist:

```json
{
  "workspaces": {
    "packages": ["frontend"],
    "nohoist": [
      "**/storybook",
      "**/storybook/**",
      "**/@storybook/**"
    ]
  }
}
```

Then reinstall:
```bash
cd /Home1/project/web-platform
rm -rf node_modules frontend/node_modules
npm install
```

---

### **Phase 4: Verify Installation Locations**

After applying a fix, run these diagnostics:

```bash
# Check where storybook CLI is installed
which storybook
npm ls -g storybook

# Verify react-vite preset exists
ls -la /Home1/project/web-platform/node_modules/@storybook/react-vite/
ls -la /Home1/project/web-platform/frontend/node_modules/@storybook/react-vite/ 2>/dev/null || echo "Not in frontend"

# Check all storybook package locations
find /Home1/project/web-platform -name "@storybook" -type d
```

---

### **Phase 5: Alternative - Use npx with Workspace Context**

If the above doesn't work, modify your `package.json` scripts to ensure proper context:

```json
{
  "scripts": {
    "storybook": "npx --workspace=eldercare-sg-frontend storybook dev -p 6006",
    "build-storybook": "npx --workspace=eldercare-sg-frontend storybook build"
  }
}
```

Or explicitly use the local binary:

```json
{
  "scripts": {
    "storybook": "node ./node_modules/.bin/storybook dev -p 6006",
    "build-storybook": "node ./node_modules/.bin/storybook build"
  }
}
```

---

## 🎯 My Recommended Action Sequence

**Based on your setup, here's what I recommend doing IN ORDER:**

### Step 1: Share Root package.json
```bash
cat /Home1/project/web-platform/package.json
```

### Step 2: Fix Vite Version Immediately
```bash
cd /Home1/project/web-platform/frontend
npm install --save-dev @vitejs/plugin-react@4.3.1
```

### Step 3: Try Approach A (Install at Root)
```bash
cd /Home1/project/web-platform
npm install --save-dev \
  storybook@8.6.14 \
  @storybook/react-vite@8.6.14 \
  @storybook/react@8.6.14 \
  @storybook/addon-essentials@8.6.14 \
  @storybook/addon-interactions@8.6.14 \
  @storybook/addon-a11y@8.6.14 \
  @storybook/builder-vite@8.6.14 \
  @storybook/test@8.6.14 \
  vite@5.4.20

cd frontend
npm run build-storybook
```

### Step 4: If Step 3 Fails, Check Symlinks
```bash
# Verify the preset file physically exists
ls -la /Home1/project/web-platform/node_modules/@storybook/react-vite/dist/preset.js
ls -la /Home1/project/web-platform/node_modules/@storybook/react-vite/preset.js

# Sometimes the preset.js is in dist folder, sometimes root
```

---

## 🔍 Additional Diagnostic Info Needed

Please run and share:

1. **Root package.json**:
   ```bash
   cat /Home1/project/web-platform/package.json
   ```

2. **Check if preset file exists**:
   ```bash
   find /Home1/project/web-platform/node_modules/@storybook/react-vite -name "*preset*"
   ```

3. **Node/npm versions**:
   ```bash
   node --version
   npm --version
   ```

---

## 💡 Why This Happened

This is a **classic npm workspaces + Storybook issue** where:
- Storybook CLI gets hoisted to root node_modules
- React-vite preset stays in workspace node_modules
- Module resolution fails because CLI can't see workspace dependencies
- Vite version mismatch compounds the issue

The fix requires either:
- Installing everything at root (shared dependencies)
- Preventing hoisting (nohoist config)
- Or ensuring proper module resolution paths

---

**Please share the root package.json and the results of the diagnostic commands so I can provide the exact fix for your specific workspace configuration.**

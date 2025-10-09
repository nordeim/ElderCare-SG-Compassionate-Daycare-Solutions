### Context and goal

I see Storybook v8.6.14 fails to load a framework preset module: “Cannot find module '@storybook/react-vite/preset'” (SB_CORE-SERVER_0002). This usually indicates a version/config mismatch or that the framework package isn’t properly installed/resolved in the exact workspace running the build. I’ll give you a deterministic, monorepo‑safe fix with explicit validation and guardrails.

---

### Quick verdict

The build is failing because Storybook is trying to resolve the React + Vite framework’s preset, but the module isn’t present or isn’t reachable from the current workspace. Install and align `@storybook/react-vite` in the same workspace as your `.storybook` config, ensure all `@storybook/*` packages share the same 8.6.14 version, and verify your `.storybook/main.ts` uses `framework: { name: '@storybook/react-vite' }` (not importing any “preset” path explicitly). If you’re in a monorepo, do a clean reinstall in that workspace; pnpm/yarn hoisting can cause this exact resolution failure. As a final guard, a cache purge + reinstall resolves lingering preset resolution issues reported by others.

---

### Root cause analysis

- **Missing or mis-scoped framework package:** The error specifically complains about `@storybook/react-vite/preset`. In v7+ the “framework” package (like `@storybook/react-vite`) provides the preset Storybook loads internally; if the workspace running `storybook build` doesn’t have `@storybook/react-vite` installed (or its version clashes), preset resolution fails with SB_CORE-SERVER_0002. This is commonly seen in monorepos with workspace boundaries.  
- **Version drift across Storybook packages:** Mixing major/minor versions (e.g., `storybook` CLI at one version and `@storybook/react-vite` at another) can break internal load paths for presets. Keeping all `@storybook/*` at the same version line fixes this class of error. Reports of similar preset failures recommend aligning dependencies.  
- **Corrupted caches / module resolution state:** Multiple reports confirm clearing caches and re-installing fresh resolves “Cannot find module …/preset” for Storybook presets, especially in monorepos with Yarn/Pnpm caches.

---

### Implementation plan

#### Package alignment

- **Ensure framework is installed:**  
  Add `@storybook/react-vite` to the frontend workspace (where `.storybook` lives and you run `npm run build-storybook`).  
- **Pin versions consistently:**  
  Set all Storybook packages to `8.6.14`: `storybook`, `@storybook/react-vite`, `@storybook/addon-essentials`, `@storybook/addon-interactions`, and any others you use. Avoid carets to prevent drift.  
- **Add Vite runtime if missing:**  
  Add `vite` as a devDependency if you don’t already have it in that workspace (Storybook’s Vite builder needs it).

#### Config correctness

- **Use framework name only, no direct preset imports:**  
  `.storybook/main.ts` should have:
  ```ts
  import type { StorybookConfig } from '@storybook/react-vite';

  const config: StorybookConfig = {
    stories: ['../src/**/*.mdx', '../src/**/*.stories.@(js|jsx|ts|tsx)'],
    addons: [
      '@storybook/addon-essentials',
      '@storybook/addon-interactions',
      '@storybook/addon-links',
    ],
    framework: {
      name: '@storybook/react-vite',
      options: {},
    },
    docs: { autodocs: 'tag' },
  };
  export default config;
  ```
  Do not import or reference `@storybook/react-vite/preset` manually; Storybook loads the preset behind the framework `name`. This matches documented usage and avoids the exact error you’re hitting.

#### Monorepo guardrails

- **Install in the right workspace:**  
  Ensure `@storybook/react-vite` is listed in `frontend/package.json` (the workspace that owns `.storybook` and runs the build). Do not rely on hoisting from root; explicit local install prevents resolution failures. Reports of preset “Cannot find module” in monorepos trace back to misplaced deps and hoisting.  
- **Pnpm/Yarn cache hygiene:**  
  If you’ve upgraded Storybook or moved modules, purge caches and reinstall to eliminate stale resolution: remove `node_modules` in the workspace, clear the package manager cache/store, and reinstall. A documented workaround for similar preset failures is deleting caches and re-installing fresh.

#### Validation steps

- **Dependency integrity check:**  
  - `npm ls storybook @storybook/react-vite vite` (or pnpm/yarn equivalents) inside the frontend workspace should show all present and version-aligned.  
- **Config check:**  
  - Confirm `framework.name === '@storybook/react-vite'`. No direct “preset” imports anywhere.  
- **Dry run:**  
  - Run `npx storybook@latest --version` to confirm CLI matches your installed libs (or use local `storybook` binary via `npm run storybook`).  
- **Build:**  
  - `npm run build-storybook` should proceed past “Loading presets” and compile Vite build successfully.

---

### Step-by-step commands

#### 1) Align packages in the frontend workspace

- **Install framework and builder dependencies:**
  - npm:
    - `npm i -D storybook@8.6.14 @storybook/react-vite@8.6.14 @storybook/addon-essentials@8.6.14 @storybook/addon-interactions@8.6.14 vite`
  - pnpm:
    - `pnpm add -D storybook@8.6.14 @storybook/react-vite@8.6.14 @storybook/addon-essentials@8.6.14 @storybook/addon-interactions@8.6.14 vite`
  - yarn:
    - `yarn add -D storybook@8.6.14 @storybook/react-vite@8.6.14 @storybook/addon-essentials@8.6.14 @storybook/addon-interactions@8.6.14 vite`

- **Optional helper (re-init, non-destructive):**  
  `npx storybook@latest init --builder vite`  
  This ensures config scaffolding matches the Vite framework. Verify it doesn’t overwrite custom settings, or commit changes carefully.

#### 2) Fix `.storybook/main.ts`

- Ensure the `framework.name` is `@storybook/react-vite` and remove any manual reference to `@storybook/react-vite/preset`.

#### 3) Clean reinstall (monorepo safe)

- From the frontend workspace:
  - Remove `node_modules` and lockfile (optional if you want exact resolution):
    - `rm -rf node_modules`
    - `rm -f package-lock.json` or prefer keeping lockfile stable and run a refresh
  - Clear package manager cache/store (choose your PM):
    - npm: `npm cache verify` (or `npm cache clean --force` for a hard reset)
    - pnpm: `pnpm store prune`
    - yarn: `yarn cache clean`
  - Reinstall:
    - `npm i` (or `pnpm i` / `yarn`)
- As reported, clearing caches and reinstalling fresh resolves similar preset resolution issues.

#### 4) Validate and build

- `npm ls storybook @storybook/react-vite vite`
- `npm run build-storybook`

---

### Operational tips and pitfalls

- **One workspace owns Storybook:** Keep `.storybook` and `@storybook/*` deps co-located. Don’t rely on root installs in a monorepo; it invites resolution ambiguity.  
- **Version pinning:** Use exact versions for all Storybook packages to eliminate subtle mismatches that break preset loading.  
- **Cache discipline:** After upgrades, do a cache purge if you see module resolution errors; it’s a fast fix with strong signal for this failure mode.

---

### ToDo plan (staged)

#### Phase 1 — Inspect and baseline
- **Gather:** Confirm which workspace runs `build-storybook` and contains `.storybook`.  
- **Check deps:**  
  - `**List:** storybook, @storybook/react-vite, addons, vite versions`  
  - `**Identify:** any missing/misaligned packages`

#### Phase 2 — Correct dependencies
- **Install:**  
  - `**Add:** @storybook/react-vite@8.6.14 in the frontend workspace`  
  - `**Align:** storybook and all @storybook/* to 8.6.14`  
  - `**Ensure:** vite present`
- **Lock:**  
  - `**Pin:** Remove carets on Storybook packages`

#### Phase 3 — Fix configuration
- **Main config:**  
  - `**Set:** framework.name = '@storybook/react-vite'`  
  - `**Remove:** any direct preset imports`  
  - `**Keep:** addons minimal (essentials, interactions, links)`  
- **Optional:**  
  - `**Run:** storybook init --builder vite (review diffs)`

#### Phase 4 — Clean reinstall
- **Purge:**  
  - `**Delete:** node_modules (workspace)`  
  - `**Clear:** package manager cache/store`  
- **Reinstall:**  
  - `**Run:** fresh install in the workspace`

#### Phase 5 — Validate and build
- **Sanity:**  
  - `**Command:** npm ls storybook @storybook/react-vite vite`  
  - `**Check:** versions and presence`
- **Build:**  
  - `**Command:** npm run build-storybook`  
  - `**Outcome:** no SB_CORE-SERVER_0002; build completes`

#### Phase 6 — Guardrails
- **CI gate:**  
  - `**Add:** “storybook:check” job that runs dependency and config sanity checks`  
- **Docs:**  
  - `**Document:** monorepo placement, versions, and cache purge steps`  
- **Monitoring:**  
  - `**Log:** lockfile changes and Storybook upgrades for reproducibility`

---

### Why this plan works

It directly addresses the three common failure points: missing framework module in the exact workspace, version drift across Storybook packages, and stale caches that poison module resolution. Community reports for similar preset “Cannot find module …/preset” issues endorse aligning dependencies and performing a clean reinstall to resolve SB_CORE-SERVER_0002.


# Troubleshooting Guide

This guide details common diagnostic techniques, known symptoms, and resolutions for issues encountered when developing, building, or deploying `medhatse/Vision-Studio`.

---

## 1. Static Site Generator Issues

### 1.1 `cwebp not found — serving original images only`
- **Symptom**: Build logs display `cwebp not found — serving original images only (install the "webp" package for responsive variants)`.
- **Cause**: The `cwebp` command-line utility is not installed in the local system PATH.
- **Impact**: Non-fatal. The build completes successfully, and original full-resolution images are embedded without responsive `srcset` variants.
- **Resolution**:
  - **Debian / Ubuntu**: `sudo apt-get update && sudo apt-get install -y webp`
  - **macOS (Homebrew)**: `brew install webp`
  - **Windows (Chocolatey)**: `choco install webp`

---

### 1.2 Remote Image Downloads Fail During `npm run fetch`
- **Symptom**: `fetch-data.mjs` throws network connection timeout or HTTP error codes when querying `https://vision-studios.net`.
- **Cause**: Outbound internet connectivity issues, DNS blocks, or temporary server rate limits.
- **Resolution**:
  1. Test connectivity:
     ```bash
     curl -I https://vision-studios.net/wp-json/wp/v2/pages
     ```
  2. If only updating JSON data and not downloading assets, use the skip-images flag:
     ```bash
     node scripts/fetch-data.mjs --skip-images
     ```
  3. The repository already includes full data dumps in `data/` and downloaded assets in `src/static/img/remote/`. Running `npm run fetch` is only necessary when synchronizing with new live content.

---

### 1.3 Missing Assets or Broken Links on GitHub Pages
- **Symptom**: Deployed GitHub Pages site loads HTML without CSS styling or images.
- **Cause**: Assets linked with root-relative paths (`/static/...`) when hosted under a repository subpath (e.g. `https://username.github.io/Vision-Studio/`).
- **Resolution**:
  - Ensure `BASE_PATH` is set during the build step:
    ```bash
    SITE_URL=https://username.github.io BASE_PATH=/Vision-Studio npm run build
    ```
  - The GitHub Actions workflow (`.github/workflows/deploy.yml`) handles this automatically.

---

### 1.4 Form Submission Opens Mail Client
- **Symptom**: Submitting the booking or contact form opens the user's default email client instead of showing a web confirmation.
- **Cause**: Expected default behavior. The static build and unconfigured WordPress theme ship with a fallback `mailto:booking@vision-studios.net` handler.
- **Resolution**:
  - For static deployment: Integrate Formspree or an API endpoint in `src/static/app.js` (see `docs/CONFIGURATION.md`).
  - For WordPress: Install Contact Form 7 and configure shortcodes in **Appearance → Customize → Vision Studios → Forms**.

---

## 2. WordPress Theme Issues

### 2.1 404 Errors on Studio or City Pages (`/studios/...` or `/city/...`)
- **Symptom**: Navigating to studio or city URLs returns a 404 Not Found error after theme activation.
- **Cause**: WordPress URL rewrite rules have not been regenerated for the custom post type (`studio`) and custom taxonomy (`city`).
- **Resolution**:
  1. In WordPress Admin, navigate to **Settings → Permalinks**.
  2. Click **Save Changes** without making any edits.
  3. Alternatively, via WP-CLI:
     ```bash
     docker compose run --rm cli rewrite flush
     ```

---

### 2.2 PHP Memory Limit Exhausted During Import (`Allowed memory size of ... bytes exhausted`)
- **Symptom**: `npm run wp:import` fails with a fatal PHP memory error during image downloading or post creation.
- **Cause**: Processing high-resolution media imports exceeds standard PHP memory limits.
- **Resolution**:
  - The `npm run wp:import` script already specifies `php -d memory_limit=1024M`.
  - In `wordpress/docker-compose.yml`, `WP_MAX_MEMORY_LIMIT` is set to `1024M`. Ensure Docker has sufficient memory assigned in Docker Desktop preferences (recommended 2 GB minimum).

---

### 2.3 CSS Changes Not Reflected in WordPress Theme
- **Symptom**: Edits to WordPress PHP templates or Tailwind utility classes do not change appearance in the browser.
- **Cause**: The theme serves precompiled `assets/app.css`. Tailwind does not watch files automatically in production mode.
- **Resolution**:
  - Recompile the stylesheet:
    ```bash
    npm run theme:css
    ```
  - Clear browser cache or hard reload (`Ctrl+F5` / `Cmd+Shift+R`).

---

### 2.4 JavaScript Changes in WordPress Theme Lost After Rebuild
- **Symptom**: Manual changes made directly to `assets/app.js` disappear after running `theme:zip` or `theme:minify`.
- **Cause**: `app.js` is an esbuild-generated artifact compiled from `assets/app.src.js`.
- **Resolution**:
  - Always edit `assets/app.src.js` or `assets/post-production.src.css`.
  - Compile changes using:
    ```bash
    npm run theme:minify
    ```

---

## 3. Diagnostic Commands Summary

| Task | Command |
|---|---|
| **Verify Static Build** | `npm run build` |
| **Run On-Page SEO Audit** | `npm run seo:audit -- http://localhost:4173 / /studios/` |
| **Test Image Header Extraction** | `node -e "import('./scripts/images.mjs').then(m => console.log(m.dimensions('src/static/img/remote/2024/05/logo_vision-removebg-preview-e1695908610989-150x150-1.png')))"` |
| **Inspect Docker Logs** | `cd wordpress && docker compose logs -f wordpress` |
| **Check Docker WP-CLI Status** | `cd wordpress && docker compose run --rm cli core is-installed` |
| **Check Theme Package Contents** | `unzip -l wordpress/vision-studios-theme.zip` |

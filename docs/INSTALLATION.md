# Installation & Setup Guide

This guide provides step-by-step instructions for installing and running both the **Static Site Generator** and the **Local WordPress Theme Environment** for `medhatse/Vision-Studio`.

---

## 1. System Prerequisites

| Component | Static Site Generator | Local WordPress Environment | Live Theme Deployment |
|---|---|---|---|
| **Operating System** | Linux, macOS, or Windows (WSL2) | Linux, macOS, or Windows (WSL2) | Web host running Linux / Apache / Nginx |
| **Node.js** | `>= 18.0.0` (v20 or v22 LTS recommended) | `>= 18.0.0` (for asset builds) | `>= 18.0.0` (build machine only) |
| **npm** | Bundled with Node.js | Bundled with Node.js | Bundled with Node.js |
| **Docker & Docker Compose** | Not required | Docker Engine `>= 20.10`, Compose v2 | Not required |
| **PHP / Database** | Not required | Handled by Docker containers | PHP `>= 8.1` (8.3 recommended), MySQL 5.7+ / MariaDB 10.4+ |
| **`cwebp` CLI** | Optional (for responsive WebP image variants) | Optional | Optional |

---

## 2. Repository Setup

Clone the repository and enter the project directory:

```bash
git clone https://github.com/medhatse/Vision-Studio.git
cd Vision-Studio
```

### Dependency Note
The static generator has **zero third-party npm dependencies**. You do **not** need to run `npm install`. All build scripts rely on Node.js standard libraries, and external tools (`tailwindcss`, `esbuild`) are executed on demand via `npx -y`.

---

## 3. Track 1: Static Site Generator Setup

### 3.1 Optional: Install `cwebp` for Responsive Images
The build script automatically detects `cwebp`. If present, it creates responsive WebP images at `640px`, `1024px`, and `1600px`. Without `cwebp`, the original full-size images are served without errors.

- **Ubuntu / Debian**:
  ```bash
  sudo apt-get update && sudo apt-get install -y webp
  ```
- **macOS (Homebrew)**:
  ```bash
  brew install webp
  ```

### 3.2 Fetch Content & Remote Media (Optional)
The repository already includes extracted JSON records in `data/` and media files in `src/static/img/remote/`. If you want to refresh content directly from `vision-studios.net`:

```bash
# Pull all content and download ~228 images (~25 MB)
npm run fetch

# Or pull content JSON only, skipping image downloads
node scripts/fetch-data.mjs --skip-images
```

### 3.3 Build the Static Site
Compile templates, stylesheets, redirects, sitemaps, and images into the `dist/` directory:

```bash
npm run build
```

Expected output:
```text
Built 49 pages + 18 redirects → dist/ (X responsive image variants generated)
```

To configure the base canonical domain or hosting subpath:
```bash
SITE_URL=https://example.com BASE_PATH=/Vision-Studio npm run build
```

### 3.4 Launch Local Development Server
Serve the compiled `dist/` directory using the built-in, zero-dependency HTTP server:

```bash
npm run serve
```

Alternatively, run build and serve in a single command:
```bash
npm run dev
```

Open your browser to:
```text
http://localhost:4173
```

---

## 4. Track 2: Local WordPress Environment Setup (Docker)

The repository provides a complete local WordPress stack under `wordpress/docker-compose.yml` comprising:
- **db**: MariaDB 11
- **wordpress**: WordPress 6.8 on PHP 8.3 Apache (listening on `http://127.0.0.1:8080`)
- **cli**: WP-CLI 2.11 for headless administration and data migrations

### 4.1 Start Docker Containers
```bash
npm run wp:up
```

### 4.2 Initialize WordPress Core & Install Database
Run the following WP-CLI commands via the CLI container:

```bash
cd wordpress

# Install WordPress database and admin account
docker compose run --rm cli core install \
  --url=http://localhost:8080 \
  --title="Vision Studios" \
  --admin_user=admin \
  --admin_password=admin \
  --admin_email=admin@example.com \
  --skip-email

# Set SEO-friendly permalink rewrite structure
docker compose run --rm cli rewrite structure '/%postname%/'

# Activate the mounted Vision Studios theme
docker compose run --rm cli theme activate vision-studios

cd ..
```

### 4.3 Import Content & Images
Populate the local WordPress site with all studios, cities, news posts, services, and clients:

```bash
npm run wp:import
```

This runs WP-CLI `wp vs import --posts` inside the container, loading `data/import.json` and matching or downloading media assets.

### 4.4 Access Local WordPress
- **Frontend**: [http://localhost:8080](http://localhost:8080)
- **WP Admin**: [http://localhost:8080/wp-admin](http://localhost:8080/wp-admin)
  - Username: `admin`
  - Password: `admin`

### 4.5 Stopping the Local Environment
To halt containers without removing database volumes:
```bash
npm run wp:down
```

---

## 5. Track 3: Installing the Theme on a Live WordPress Site

### 5.1 Package the Theme
Run the packaging command to compile CSS, minify JavaScript, generate import payloads, and create a production zip file:

```bash
npm run theme:zip
```

Artifact created: `wordpress/vision-studios-theme.zip`.

### 5.2 Upload and Activate
1. Take a full backup of the live WordPress database and file uploads.
2. In WordPress Admin: **Appearance → Themes → Add New → Upload Theme**.
3. Choose `wordpress/vision-studios-theme.zip` and click **Install Now**.
4. Click **Activate**.

### 5.3 Flush Permalinks
Navigate to **Settings → Permalinks** and click **Save Changes** (without modifying options). This flushes URL rewrite rules for `/studios/` and `/city/`.

### 5.4 Import Content
1. Go to **Studios → Import Content** in the WordPress admin sidebar.
2. Leave *"Also import news posts"* unchecked if your live site already has blog posts.
3. Click **Run import**.
   - The importer links directly to existing media library uploads by matching filenames and paths.
   - Previous Elementor pages are set to draft so legacy URLs redirect cleanly.

---

## 6. Verification Checklist

| Test Item | Verification Command / Procedure | Expected Result |
|---|---|---|
| **Static Build** | `npm run build` | `dist/` created with 49 pages and `index.html`. |
| **Local Static Preview** | `npm run serve` | Pages load at `http://localhost:4173/` without console errors. |
| **SEO Audit** | `npm run seo:audit -- http://localhost:4173 / /studios/` | Returns 0 critical SEO issues. |
| **Theme Assets** | `npm run theme:css && npm run theme:minify` | Outputs compiled `assets/app.css` and `assets/app.js`. |
| **Theme Zip Package** | `npm run theme:zip` | Generates clean `wordpress/vision-studios-theme.zip`. |

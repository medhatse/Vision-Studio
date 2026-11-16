# Operations & Maintenance Guide

This document describes day-to-day operational procedures, content updates, SEO validation, and routine maintenance for `medhatse/Vision-Studio`.

---

## 1. Content Synchronization Pipeline

### 1.1 Updating Content from Upstream WordPress
When studio specifications, new blog articles, or media assets are updated on `https://vision-studios.net`:

```bash
# 1. Fetch updated data and remote images
npm run fetch

# 2. Verify git diff on data/ files
git diff data/

# 3. Rebuild static site and test locally
npm run build
npm run serve
```

> **Safety Check**: `scripts/fetch-data.mjs` updates `data/studios.json`, `data/cities.json`, `data/about.json`, `data/contact.json`, `data/news.json`, and `data/media-index.json`. It **never** modifies `data/curated.json` (hero text, coordinates, service picks).

### 1.2 Synchronizing Data to the WordPress Theme
When static data files change, compile the updated import payload for the theme:

```bash
npm run theme:import-data
```

This updates:
- `wordpress/vision-studios-theme/data/import.json`
- `wordpress/vision-studios-theme/data/tr.json`

On an active WordPress instance, re-running the importer updates existing studios, cities, and terms without creating duplicates.

---

## 2. On-Page SEO Auditing

The repository includes an on-page SEO verification script (`scripts/seo-audit.mjs`):

```bash
# Audit a local development server
npm run seo:audit -- http://localhost:4173 / /studios/ /london/ /news/

# Audit the local WordPress instance
npm run seo:audit -- http://localhost:8080 / /studios/ /city/london/

# Audit a live deployment
node scripts/seo-audit.mjs https://vision-studios.net
```

### Audit Validations:
- HTTP 200 / 404 response codes.
- `<title>` presence and character length (≤ 65 chars).
- `<meta name="description">` length (50–160 chars).
- Canonical URL consistency.
- Single `<h1>` landmark per page.
- Image `alt` attribute coverage.
- Open Graph tags.
- Schema.org JSON-LD structured data syntax and types (`LocalBusiness`, `Organization`, `WebSite`, `BreadcrumbList`, `NewsArticle`).
- External render-blocking resources or missing preconnect directives.
- `noindex` robots tag on 404 error pages.
- Accessibility of `robots.txt` and XML sitemaps.

---

## 3. Managing Studio & City Information

### Adding or Modifying a Studio (Static Site Track)
1. Edit `data/studios.json`:
   - Specify `slug`, `city`, `title`, `areaSqm`, `specs`, `highlights`, `useCases`, and image URLs.
2. If custom marketing badges are needed, update `studioTags` in `data/curated.json`.
3. If custom introductory copy or FAQs are required, edit `data/copy.json`.
4. If in Istanbul, add Turkish translations to `data/tr/studios.json`.
5. Run `npm run build` to verify page rendering.

### Managing Turkish Translations (`/tr/`)
Translations for the Istanbul facilities are managed in `data/tr/`:
- `data/tr/city.json`: Istanbul overview, taglines, FAQs.
- `data/tr/studios.json`: Specifications and use-cases for Istanbul studios.
- `data/tr/ui.json`: User interface translations.

After editing files in `data/tr/`:
```bash
npm run theme:import-data
```
The WordPress theme automatically synchronizes Turkish content the next time wp-admin is loaded, or immediately via `/wp-admin/?vs_tr_import=1`.

---

## 4. Local Docker Environment Maintenance

### Managing Docker Containers
```bash
# Check status of WordPress and MariaDB containers
cd wordpress && docker compose ps

# View WordPress server logs
docker compose logs -f wordpress

# View database logs
docker compose logs -f db

# Restart services
docker compose restart
```

### Database Backup & Restore in Docker
```bash
# Create a SQL dump from the container
docker compose exec db mariadb-dump -u wordpress -pwordpress wordpress > local-backup.sql

# Restore a SQL dump into the container
docker compose exec -T db mariadb -u wordpress -pwordpress wordpress < local-backup.sql
```

### Resetting the Local Database
To start with a fresh database:
```bash
docker compose down -v
docker compose up -d
# Follow docs/INSTALLATION.md for initial WP-CLI installation
```

---

## 5. Production Notes (live WordPress site)

These are the behaviours that are easy to miss when changing the live site at `vision-studios.net`.

- **Release process:** the live site is the WordPress theme (Track 2). Run `npm run theme:zip`, upload the zip under *Appearance → Themes → Add New → Upload*, then purge the cache by opening `/?vs_cache_purge=1` while logged in as an admin.
- **Cache-busting:** CSS and JS are enqueued with `VS_VERSION` (`functions.php`). Bump it whenever `app.css`, `app.js` or `post-production.css` change, otherwise returning visitors can combine new HTML with old cached styles.
- **Content import:** *Studios → Import Content* updates studios, cities, pages and options from `data/import.json`, but only **inserts** news posts whose slug does not exist yet. Changing the text of an existing post needs an edit in wp-admin (or the `?vs_replace=1` tool below). Renaming a slug in `data/news.json` creates a duplicate post.
- **Rank Math:** auto-generated `rank_math_title` / `rank_math_description` are stored as post meta and are not refreshed by the importer. Use the admin tools below to correct them.
- **Admin-only maintenance tools** (`inc/compat.php`, dry-run by default; add `&apply=1` to write): `?vs_replace=1` (find/replace across content, with `&preset=` shortcuts), `?vs_cache_purge=1`, `?vs_perf_diag=1`, `?vs_tr_import=1`, `?vs_fix_dup_titles=1`, `?vs_fix_studio_desc=1`, `?vs_remove_dublin=1`, `?vs_swap_share_image=1`. Always run the dry run first and read the output.
- **Styling:** the site uses a "Glass" look (`.glass`, `.glass-strong` in `wordpress/tailwind.input.css`) with fallbacks for browsers without `backdrop-filter` and for `prefers-reduced-transparency`. The image viewer is a native `<dialog id="lightbox">`.
- **Hidden content:** Dublin and the Europe `+353` number were removed from the site; old Dublin URLs redirect to the studios archive (`inc/redirects.php`).

# Development & Contribution Guide

This guide describes local development practices, coding conventions, asset build workflows, and contribution procedures for `medhatse/Vision-Studio`.

---

## 1. Development Principles

1. **Zero-Dependency Core**: The static site generator does not use external runtime or npm dependencies. Use Node.js built-ins (`node:fs/promises`, `node:path`, `node:child_process`, `node:http`). External tools (`tailwindcss`, `esbuild`) must be executed via `npx -y` as specified in `package.json`.
2. **Dual-Track Parity**: Any visual or structural changes made to the static templates (`src/templates/`) should be reviewed for parity in the WordPress theme templates (`wordpress/vision-studios-theme/`).
3. **Source vs. Artifact Separation**:
   - In static builds: Edit files in `src/templates/`, `src/static/`, and `data/`. Never edit `dist/` directly.
   - In the WordPress theme: Edit `assets/app.src.js` and `assets/post-production.src.css`. Never edit the generated `assets/app.js` or `assets/post-production.css` directly.
4. **Data Separation**: Dynamic content belongs in `data/*.json`, not hardcoded into HTML or PHP templates. Hand-curated editorial copy belongs in `data/curated.json` or `data/copy.json`.

---

## 2. Directory Structure

```text
Vision-Studio/
├── .github/
│   └── workflows/
│       └── deploy.yml          # GitHub Pages automated build & deployment workflow
├── data/                       # Extracted & curated structured content (JSON)
│   ├── curated.json            # Hand-maintained presentation overlay (never overwritten)
│   ├── copy.json               # Introductions, FAQs, pricing guidelines
│   ├── site.json               # Global metadata, addresses, contacts
│   ├── studios.json            # Studio facilities data (14 studios)
│   ├── cities.json             # City metadata (London, Dublin, Paris, Istanbul)
│   ├── news.json               # Blog posts & updates (20 posts)
│   ├── media-index.json        # Media library catalog
│   └── tr/                     # Turkish translations for Istanbul facilities
│       ├── city.json
│       ├── studios.json
│       └── ui.json
├── docs/                       # Technical architecture, setup, and operations guides
├── scripts/                    # Automation & build scripts (ES modules)
│   ├── build.mjs               # Static site compiler
│   ├── fetch-data.mjs          # Content extraction tool from live WordPress
│   ├── images.mjs              # Image dimensions inspector & cwebp variant generator
│   ├── serve.mjs               # Zero-dependency local preview server
│   ├── seo-audit.mjs           # On-page SEO crawler and validator
│   ├── build-wp-import.mjs     # Generates theme import payload (import.json)
│   └── build-wp-tr.mjs         # Generates theme Turkish payload (tr.json)
├── src/                        # Static site source files
│   ├── static/                 # Static assets (copied to dist/static)
│   │   ├── app.js              # Vanilla JS interactive components
│   │   ├── fonts/              # Self-hosted woff2 font files
│   │   └── img/remote/         # Cached WordPress upload assets (~228 files)
│   ├── templates/              # HTML generation functions (ES modules)
│   │   ├── helpers.mjs         # Core layout, head tags, schema generators
│   │   ├── home.mjs            # Homepage template
│   │   ├── city.mjs            # City landing page template
│   │   ├── studio.mjs          # Individual studio template
│   │   └── pages.mjs           # Directory, news, about, contact, gallery, 404
│   └── tailwind.input.css      # Tailwind CSS entrypoint for static site
├── tailwind.config.js          # Tailwind configuration for static site
├── package.json                # Project scripts and engines definition
├── README.md                   # Primary repository documentation
├── README-wordpress.md         # WordPress theme reference
└── wordpress/                  # WordPress theme & containerized environment
    ├── docker-compose.yml      # MariaDB 11 + WordPress 6.8 + WP-CLI
    ├── tailwind.config.js      # Theme-specific Tailwind configuration
    ├── tailwind.input.css      # Theme Tailwind input styles
    └── vision-studios-theme/   # Production WordPress theme
        ├── functions.php       # Bootstrap, enqueuing, font preloading
        ├── inc/                # Custom post types, meta boxes, SEO, importer, etc.
        ├── assets/             # Compiled CSS/JS and source files (.src.js/.src.css)
        └── data/               # import.json and tr.json compiled payloads
```

---

## 3. Working with Static Templates

Templates are ES module files located in `src/templates/`. Each function takes a rendering context (`ctx`):

```javascript
// Example template function signature
export function renderCustomPage(ctx) {
  const { data, path, baseUrl, basePath, srcDir } = ctx;
  const content = `
    <section class="py-16">
      <h1 class="text-4xl font-display text-white">Title</h1>
      <p class="text-zinc-400">Content goes here...</p>
    </section>
  `;
  return wrapPage({ title: 'Custom Page', content, ...ctx });
}
```

### Context Properties:
- `data`: Aggregated JSON data (`site`, `studios`, `cities`, `home`, `about`, `contact`, `news`, `curated`, `copy`).
- `path`: Current URL path (e.g., `'/studios/london-studio-1/'`).
- `baseUrl`: Base URL from `SITE_URL` (e.g., `'https://example.com'`).
- `basePath`: Subpath prefix from `BASE_PATH` (e.g., `'/Vision-Studio'`).
- `srcDir`: Absolute path to `src/` directory.

---

## 4. Working with the WordPress Theme

### Theme Asset Compilation
When modifying styles or client-side JavaScript in `wordpress/vision-studios-theme/`:

```bash
# 1. Compile Tailwind CSS
npm run theme:css

# 2. Minify JavaScript and post-production CSS from .src files
npm run theme:minify

# 3. Build full theme archive
npm run theme:zip
```

### Working with Theme Meta Boxes
Custom fields for studios are defined natively in `wordpress/vision-studios-theme/inc/meta-boxes.php` without relying on Advanced Custom Fields (ACF). To register a new studio attribute:
1. Add the field definition to `vs_render_studio_meta_box()` in `inc/meta-boxes.php`.
2. Add sanitization logic in `vs_save_studio_meta()`.
3. Display the value in `single-studio.php` using `get_post_meta($post->ID, '_vs_field_key', true)`.

---

## 5. Testing & Validation Checklist

Before opening a pull request, run the following verification checks:

1. **Verify Static Build**:
   ```bash
   npm run build
   ```
2. **Verify Theme Data Compilation**:
   ```bash
   npm run theme:import-data
   ```
3. **Verify SEO & Markup**:
   Start preview server (`npm run serve`) and run:
   ```bash
   npm run seo:audit -- http://localhost:4173 / /studios/
   ```
4. **Git Status Cleanliness**:
   Ensure generated files in `dist/` or unneeded artifacts are not staged.

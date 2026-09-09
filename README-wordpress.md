# Vision Studios — WordPress theme

`wordpress/vision-studios-theme/` is a classic WordPress theme in the same design as the static site, for
installing on the existing vision-studios.net WordPress. It adds:

- **Studios** post type with a **City** taxonomy (specs, highlights, gallery, floor plan, address, phone,
  "what to shoot here", all as plain meta boxes — no ACF or other plugin required)
- **Services** (home/city service cards and the "Why Vision" reasons) and **Clients** (logo wall)
- Templates: home, city, studio, all-studios, news list + article, About, Contact, Gallery, 404
- **Studios → Import Content**: loads the content pulled from vision-studios.net (`data/import.json`),
  reusing images already in the media library
- Redirects from the old page URLs (`/london-studio-1/`, `/about-us/`, …) to the new structure
- Contact Form 7 styling; built-in mail-to forms when no CF7 form is configured
- Customizer panel **Vision Studios** for contact details, hero, stats, counters and form shortcodes

## Install on the live site

1. **Back up** the site (files + database) and keep the current theme installed so you can switch back.
2. `npm run theme:zip` produces `wordpress/vision-studios-theme.zip` (or use the zip already provided).
3. In WordPress admin: **Appearance → Themes → Add New → Upload Theme**, upload the zip, click **Activate**.
4. **Settings → Permalinks → Save Changes** (flushes URL rules for `/studios/…` and `/city/…`).
5. **Studios → Import Content → Run import**. Leave "Also import news posts" unticked on the live site
   (the posts already exist). This creates the 14 studios, 4 cities, services, clients, the Home / About /
   Contact / Gallery / News pages, sets the front page, and drafts the old Elementor pages so their URLs
   redirect. It matches images by URL in your media library, so it does not re-upload anything.
6. Check the site. If anything is wrong, **Appearance → Themes → activate the previous theme** and
   republish the drafted pages (Pages → Drafts) to roll back completely.

### After activation

- **Forms**: create two Contact Form 7 forms (booking and contact) and paste their shortcodes in
  **Appearance → Customize → Vision Studios → Forms**. Until then the forms open the visitor's mail client.
  A booking form template:
  ```
  <label><span>Your name *</span>[text* your-name]</label>
  <label><span>Your email *</span>[email* your-email]</label>
  <label><span>Company</span>[text company]</label>
  <label><span>Phone</span>[tel phone]</label>
  <label><span>Check-in</span>[date checkin]</label>
  <label><span>Check-out</span>[date checkout]</label>
  <label><span>Studio</span>[text studio]</label>
  <label><span>Message</span>[textarea message]</label>
  [submit "Send booking request"]
  ```
- **Menu**: the header shows Studios / Services / Gallery / News / About / Contact by default. Assign a
  menu to the *Primary navigation* location to override it.
- **Editing**: studios are edited under **Studios** (gallery order = hero slider order). City details are
  under **Studios → Cities**. Home copy, stats and phone numbers are in the Customizer.
- Elementor and the old page builder content are untouched; you can deactivate Elementor once you are
  happy, or keep it for the news posts.

## Local development

```bash
npm run wp:up                 # WordPress 6.8 at http://localhost:8080 (theme is mounted live)
cd wordpress && docker compose run --rm cli core install --url=http://localhost:8080 --title="Vision Studios" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email
docker compose run --rm cli rewrite structure '/%postname%/'
docker compose run --rm cli theme activate vision-studios
cd .. && npm run wp:import    # imports everything incl. news; downloads ~230 images from vision-studios.net
npm run theme:css             # recompile Tailwind after editing templates
```

Admin: http://localhost:8080/wp-admin (admin / admin).

## Updating the content payload

`npm run fetch` pulls fresh content from vision-studios.net; `npm run theme:import-data` rebuilds
`data/import.json` inside the theme. Re-running the importer updates existing items instead of duplicating.

## SEO

What the theme does on its own (verified with `npm run seo:audit -- <site url>`):

- One `<h1>` per page, semantic `<main>` / `<article>` / `<nav>` landmarks, skip link, visible breadcrumbs
- Descriptive `<title>` per view (studio: "Istanbul Studio 1 — TV Studio Hire in Istanbul | Vision Studios")
- Meta description generated from studio specs, city description, page/post excerpts
- Canonical URLs, Open Graph and Twitter Card tags with a share image on every page
- JSON-LD: `Organization` + `WebSite`, `LocalBusiness`/`Place` for every studio (address, phone, photos,
  amenities), `CollectionPage` + `ItemList` for cities, `NewsArticle` for posts, `BreadcrumbList` everywhere
- Descriptive `alt` text on all images, responsive `srcset` + lazy loading, width/height attributes (no CLS)
- Compiled CSS (no Tailwind CDN), self-hosted woff2 fonts with preload (no Google Fonts request — also
  avoids the EU GDPR issue with Google Fonts), inline SVG icons (no Font Awesome CDN), deferred JS,
  emoji/RSD/wlwmanifest/shortlink cruft removed
- LCP hero image preloaded; studio hero slides after the first load on demand; Google Maps is a
  click-to-load facade (the map iframe is ~1 MB of JavaScript); YouTube embeds are lazy
- Robots directives through WordPress's `wp_robots` API; `og:image` with dimensions and alt;
  favicon fallback; WCAG AA text contrast; 24 px tap targets on slider controls
- Studios and Cities included in the core XML sitemap; attachment pages 301 to their parent;
  search / 404 / paginated archives `noindex`; old page URLs 301 to the new ones
- 404 page with navigation back into the site

With **Rank Math** (already installed on vision-studios.net) the theme lets the plugin own titles, meta
tags, sitemaps and Organization schema, and only adds the studio / city / breadcrumb schema Rank Math
cannot generate. After activating the theme:

1. Rank Math → **Titles & Meta → Studios**: enable "Show in search results", set the title template to
   `%title% — TV Studio Hire in %primary_taxonomy_terms% %sep% %sitename%` (or leave the theme title).
2. Rank Math → **Titles & Meta → Cities** (taxonomy): show in search results; template
   `%term% TV & Production Studios for Hire %sep% %sitename%`.
3. Rank Math → **Sitemap Settings**: include Studios and Cities; exclude Services and Clients.
4. Rank Math → **General → Breadcrumbs** can stay off (the theme renders its own).
5. Re-submit `sitemap_index.xml` in Google Search Console after the switch and watch the Coverage report
   for the redirected old URLs.

**Lighthouse** (local WordPress 6.8, WP_DEBUG on, no caching plugin): home, studio, city, gallery, news
and contact pages score 100 / 100 / 100 / 100 (performance, accessibility, best practices, SEO) on the
desktop preset and 100 on SEO / accessibility / best practices on the mobile preset. Re-run on the live
host with `npx lighthouse https://vision-studios.net --preset=desktop` — hosting, caching and Cloudflare
settings decide the final mobile performance number.

Not covered by a theme (do these on the host): HTTPS, a caching/CDN layer (Cloudflare is already in front),
WebP originals (already the case) and a page-speed check with PageSpeed Insights after launch.

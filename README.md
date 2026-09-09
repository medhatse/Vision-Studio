# Vision Studios — website

Static marketing site for [Vision Studios](https://vision-studios.net) (broadcast & production studios in
London, Dublin, Paris and Istanbul). The design follows the dark, editorial landing-page reference
(Anton + Inter + Space Mono, orange `#f2703a` accent, Tailwind utility classes); **all content is pulled from
vision-studios.net** and rendered into plain HTML — no CMS, no framework runtime.

## Quick start

```bash
npm run fetch   # pull content + images from vision-studios.net into data/ and src/static/img/remote/
npm run build   # render dist/
npm run dev     # build + serve dist/ at http://localhost:4173
```

Requires Node 18+. There are no npm dependencies.

## What gets pulled (`scripts/fetch-data.mjs`)

| Source on vision-studios.net | Ends up in |
| --- | --- |
| WP REST `pages` (14 studio pages, 4 city pages, About, Contact) | `data/studios.json`, `data/cities.json`, `data/about.json`, `data/contact.json` |
| WP REST `posts` (20 news posts) | `data/news.json` |
| WP REST `media` (index of every upload) | `data/media-index.json` |
| Elementor widgets on each page: description list, icon boxes, call-to-action cards, google_maps query, counters | studio specs / highlights / use-cases / addresses, home counters |
| Elementor slide backgrounds (only present in the per-page minified CSS) | `gallery` of every studio |
| Client logos on the home page | `data/home.json → clients` |
| Every referenced image (original size, WordPress `-WxH` suffix stripped) | `src/static/img/remote/…` (228 files, ~25 MB) |

`data/curated.json` is **hand-maintained** presentation data layered on top (hero copy, per-city tags,
coordinates, service cards, gallery picks, client display names). The fetch script never touches it.

## Pages (`scripts/build.mjs` → `dist/`)

- `/` — home (hero, stats, 4 city cards, services, gallery, why Vision, clients, latest news, CTA)
- `/london/` `/dublin/` `/paris/` `/istanbul/` — city pages with every studio, services, map and contact
- `/studios/<slug>/` — 14 studio pages: hero slider, highlights, full spec list, floor plan, photo gallery,
  "what to shoot here", map and a booking form
- `/news/` and `/news/<slug>/` — 20 posts with galleries and embedded video
- `/about/`, `/contact/`, `/gallery/` (all studio photos, filterable by city), `404.html`
- Legacy vision-studios.net URLs (`/london-studio-1/`, `/about-us/`, `/blogs/`, …) are emitted as redirect
  stubs plus a Netlify/Cloudflare-style `_redirects` file
- `sitemap.xml`, `robots.txt`, JSON-LD on home / studio / article pages

Set `SITE_URL=https://example.com npm run build` to get absolute canonical / sitemap / Open Graph URLs.

## Forms

The booking and contact forms have no backend in this static build: submitting opens the visitor's mail
client with a pre-filled message to `booking@vision-studios.net`. Point `form.booking-form` at a form
service (Formspree, Netlify Forms, your own endpoint) in `src/static/app.js` when hosting.

## SEO & performance

The build ships the same standard as the WordPress theme (see `README-wordpress.md`): compiled Tailwind
(no CDN), self-hosted woff2 fonts with preload, inline SVG icons, deferred JS, responsive `srcset` images
generated at build time with `cwebp` (640 / 1024 / 1600 px variants — install the `webp` package; without it
the originals are served), LCP hero preload with `imagesrcset`, on-demand studio slides, click-to-load
Google Maps, per-page meta description / canonical / Open Graph / Twitter tags, JSON-LD (Organization,
WebSite, WebPage, LocalBusiness per studio and city, CollectionPage, NewsArticle, BreadcrumbList), visible
breadcrumbs, `noindex` 404, sitemap and robots.txt. `npm run seo:audit -- http://localhost:4173 / /london/`
checks a build; Lighthouse scores 100 on desktop for performance, accessibility, best practices and SEO.

Maps use the same Google Maps queries as the source site.

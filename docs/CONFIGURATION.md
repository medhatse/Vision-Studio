# Configuration Reference

This document details all configuration options, environment variables, data layer schemas, styling tokens, and CMS settings available across the **Vision Studios** project.

---

## 1. Environment Variables

Environment variables control the static site generation process (`scripts/build.mjs`) and the local preview server (`scripts/serve.mjs`).

| Variable | Default Value | Context | Description |
|---|---|---|---|
| `SITE_URL` | `http://localhost:4173` | Build (`scripts/build.mjs`) | Base origin URL used for canonical links, Open Graph tags, JSON-LD schemas, and `sitemap.xml`. In production or CI, set this to the public domain (e.g., `https://medhatse.github.io`). |
| `BASE_PATH` | `""` (empty string) | Build (`scripts/build.mjs`) | Subpath prefix when hosting in a subfolder (e.g., `/Vision-Studio` for GitHub Pages). Prepends all root-relative assets and internal links. |
| `CNAME_DOMAIN` | *None* | Build (`scripts/build.mjs`) | If set, generates a `CNAME` file at `dist/CNAME` for GitHub Pages custom domain routing (e.g., `studios.example.com`). |
| `PORT` | `4173` | Preview Server (`scripts/serve.mjs`) | Local TCP port for the built-in HTTP static preview server. |

### Example Usage:

```bash
# Build for custom root domain
SITE_URL=https://vision-studios.net npm run build

# Build for GitHub Pages subfolder
SITE_URL=https://medhatse.github.io BASE_PATH=/Vision-Studio npm run build

# Build with custom domain for GitHub Pages
SITE_URL=https://studios.example.com CNAME_DOMAIN=studios.example.com npm run build

# Run local server on a custom port
PORT=5000 npm run serve
```

---

## 2. Static Generator Data Layer (`data/`)

The content and behavior of the static generator are defined across JSON files in the `data/` directory:

### 2.1 Core Global Site Configuration (`data/site.json`)
```json
{
  "name": "Vision Studios",
  "origin": "https://vision-studios.net",
  "email": "booking@vision-studios.net",
  "social": {
    "instagram": "https://www.instagram.com/visionstudioslondon/",
    "linkedin": "https://www.linkedin.com/company/vision-studios-london/"
  },
  "hq": {
    "address": "Vision Studios, Kendal Avenue, London W3 0XA",
    "locality": "Kendal Avenue",
    "region": "London",
    "postalCode": "W3 0XA",
    "country": "UK"
  },
  "openingHours": "Mon–Sun 09:00–17:00",
  "logo": "/static/img/remote/2024/05/logo_vision-removebg-preview-e1695908610989-150x150-1.png"
}
```

### 2.2 Curated Presentation Layer (`data/curated.json`)
This file is **never overwritten** by `npm run fetch`. It contains hand-curated presentation attributes:
- `hero`: Top banner image, badges, and headline copy.
- `stats`: Key homepage metrics (e.g., number of studios, square meters, European cities).
- `cities`: Coordinates, tags, phone numbers, and display images for London, Dublin, Paris, and Istanbul.
- `studioTags`: Marketing taglines for individual studios (e.g., "Flagship Soundstage", "Virtual Production Hub").
- `services`: Service cards displayed on homepage and city pages.
- `why`: "Why Vision" value propositions.
- `gallery`: Featured picks for the global gallery.

### 2.3 Studio & City Data (`data/studios.json` & `data/cities.json`)
- `studios.json`: Array of 14 studio objects detailing `slug`, `city`, `title`, `areaSqm`, `specs` list, `highlights` list, `useCases`, `gallery` image paths, `floorPlan` image, and direct `contact` numbers.
- `cities.json`: Array of 4 city objects with descriptions, featured studio blurbs, and regional services.

### 2.4 Editorial Copy & FAQs (`data/copy.json`)
Contains introductions, regional studio pricing guidance (`priceFrom`), and frequently asked questions (`sharedFaq` and per-studio/city FAQs) formatted as `[question, answer]` tuples.

### 2.5 Turkish Localized Content (`data/tr/`)
- `data/tr/city.json`: Turkish translations for Istanbul city overview, headings, taglines, and FAQs.
- `data/tr/studios.json`: Turkish specifications, use cases, SEO meta titles, and descriptions for the 10 Istanbul studios.
- `data/tr/ui.json`: Key-value pairs for UI text localization (e.g., navigation labels, filter names, button labels).

---

## 3. Styling & Design Tokens

Tailwind CSS compiles utilities into minified production stylesheets.

### 3.1 Design System Tokens
- **Primary Accent**: `#f2703a` (Vision Studios Vibrant Orange).
- **Backgrounds**: Dark theme `#0a0a0a` (body background), `#141414` (surface card elevation), `#1f1f1f` (borders & dividers).
- **Typography**:
  - Headings / Display: `Anton` (Self-hosted woff2 font).
  - Body / UI: `Inter` (Variable woff2 font).
  - Technical Specs / Eyebrows: `Space Mono` (Self-hosted woff2 font).

### 3.2 Configuration Files
- **Static Site**: `tailwind.config.js` and `src/tailwind.input.css`.
- **WordPress Theme**: `wordpress/tailwind.config.js` and `wordpress/tailwind.input.css`.

---

## 4. WordPress Theme Customizer Options

When using the WordPress theme (`wordpress/vision-studios-theme/`), configure global options under **Appearance → Customize → Vision Studios**:

| Setting Key | Label / Section | Purpose |
|---|---|---|
| `vs_phone_uk` | Contact → UK Phone | Contact telephone for UK operations. |
| `vs_phone_eu` | Contact → EU Phone | Contact telephone for European operations (Dublin, Paris). |
| `vs_phone_tr` | Contact → Turkey Phone | Contact telephone for Istanbul operations. |
| `vs_email` | Contact → Booking Email | Default recipient for booking inquiries. |
| `vs_hq_address` | Contact → Headquarters | Primary postal address displayed in footer and schema. |
| `vs_opening_hours` | Contact → Opening Hours | Studio opening hours string (e.g., `Mon–Sun 09:00–17:00`). |
| `vs_form_booking` | Forms → Booking Form Shortcode | Contact Form 7 shortcode for studio bookings (e.g., `[contact-form-7 id="744"]`). |
| `vs_form_contact` | Forms → Contact Form Shortcode | Contact Form 7 shortcode for general inquiries (e.g., `[contact-form-7 id="743"]`). |
| `vs_form_booking_tr` | Forms → Turkish Booking Form | CF7 shortcode for Turkish-language inquiries on `/tr/` pages. |

---

## 5. Contact & Booking Form Integration

In the static HTML build, forms fall back to `mailto:` links pre-addressed to `booking@vision-studios.net`. To connect a third-party form handler:

### Formspree / Custom API Integration (`src/static/app.js`)
Edit the form submission handler in `src/static/app.js`:

```javascript
// Replace mailto fallback with fetch POST to your API or Formspree
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(form);
  
  const response = await fetch('https://formspree.io/f/YOUR_FORM_ID', {
    method: 'POST',
    body: formData,
    headers: { 'Accept': 'application/json' }
  });
  
  if (response.ok) {
    alert('Thank you! Your booking request has been sent.');
    form.reset();
  }
});
```

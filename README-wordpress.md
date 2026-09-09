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

#!/usr/bin/env node
// Turns data/*.json (from fetch-data.mjs) into the theme's importer payload, with image URLs pointing
// back at the original WordPress uploads so the importer can reuse the site's existing media library.
import fs from 'node:fs/promises'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const OUT = path.join(ROOT, 'wordpress/vision-studios-theme/data/import.json')
const read = async (n) => JSON.parse(await fs.readFile(path.join(ROOT, 'data', n + '.json'), 'utf8'))
const [site, studios, cities, home, about, contact, news, curated] = await Promise.all(['site', 'studios', 'cities', 'home', 'about', 'contact', 'news', 'curated'].map(read))

// /static/img/remote/2024/05/x.webp → https://vision-studios.net/wp-content/uploads/2024/05/x.webp
const remote = (p) => (p && p.startsWith('/static/img/remote/') ? site.origin + '/wp-content/uploads/' + p.slice('/static/img/remote/'.length) : p || '')

const order = ['london', 'dublin', 'paris', 'istanbul']
const payload = {
  generated: new Date().toISOString(),
  options: {
    email: site.email, instagram: site.social.instagram, linkedin: site.social.linkedin,
    phone_uk: contact.regions.find((r) => /Kingdom/.test(r.region))?.phone, phone_eu: contact.regions.find((r) => /Europe/.test(r.region))?.phone, phone_tr: contact.regions.find((r) => /Turkey/.test(r.region))?.phone,
    hq_address: site.hq.address, opening_hours: site.openingHours,
    hero_image: remote(curated.hero.image), hero_left: curated.hero.eyebrowLeft, hero_center: curated.hero.eyebrowCenter, hero_right: curated.hero.eyebrowRight,
    hero_intro: home.tagline + '. Fully equipped, modern studios for live news, TV shows, corporate advertising, virtual events and podcasts.',
    ...Object.fromEntries(curated.stats.flatMap((s, i) => [[`stat${i + 1}_value`, s.value], [`stat${i + 1}_label`, s.label]])),
    ...Object.fromEntries(home.counters.flatMap((c, i) => [[`counter${i + 1}_value`, c.value], [`counter${i + 1}_label`, c.label.replace(/​/g, '')], [`counter${i + 1}_suffix`, c.prefix]])),
    why_intro: about.mission[0], choose_intro: home.intro[1],
  },
  cities: cities.sort((a, b) => order.indexOf(a.slug) - order.indexOf(b.slug)).map((c) => {
    const m = curated.cities[c.slug]
    return { slug: c.slug, name: c.name, heading: c.heading, country: m.country, tagline: m.tag, phone: m.phone, address: m.address, map_query: m.mapQuery, coords: m.coords, image: remote(m.image) }
  }),
  studios: studios.sort((a, b) => order.indexOf(a.city) - order.indexOf(b.city) || a.slug.localeCompare(b.slug, undefined, { numeric: true })).map((s, i) => {
    const blurb = cities.find((c) => c.slug === s.city)?.studioBlurbs.find((b) => b.title === s.title)?.description || ''
    return {
      slug: s.slug, title: s.title, city: s.city, order: i + 1, excerpt: blurb,
      tagline: curated.studioTags[s.slug] || '', area: s.areaSqm || '', specs: s.specs.join('\n'), highlights: s.highlights.join('\n'),
      use_cases_heading: s.useCasesHeading, use_cases: s.useCases.map((u) => `${u.title} | ${u.description}`).join('\n'),
      gallery: s.gallery.map(remote), floor_plan: remote(s.floorPlan),
      phone: s.contact.phone && s.contact.phone.length > 8 ? s.contact.phone : '', address: s.address && !/vm cloud/i.test(s.address) ? s.address.replace(/\s+/g, ' ') : '', map_query: s.address && !/vm cloud/i.test(s.address) ? s.address : '',
    }
  }),
  services: [
    ...curated.services.map((s, i) => ({ title: s.title, icon: s.icon, type: 'service', order: i + 1, excerpt: s.description, content: cities.flatMap((c) => c.services).find((x) => x.title === s.title)?.description || s.description })),
    ...curated.why.map((w, i) => ({ title: w.title, icon: '', type: 'reason', order: i + 1, excerpt: w.description, content: w.description })),
  ],
  clients: home.clients.map((c, i) => ({ name: curated.clientNames[c.name] || c.name, logo: remote(c.logo), order: i + 1 })),
  pages: {
    about: {
      excerpt: about.who[0],
      image: remote(about.images[0]),
      blocks: [
        ['h2', '25+ Years of Experience'], ['p', about.experience[0]], ['h2', 'Our Vision'], ['p', about.vision[0]], ['h2', 'Our Mission'], ['p', about.mission[0]],
        ['h2', 'More About Us'], ['p', 'In addition to our state-of-the-art equipment and well-maintained facilities, we offer a wide range of services to meet all your production needs. Our team of:'], ['ul', about.team], ['p', 'are experts in their fields and will ensure that your broadcast runs smoothly from start to finish.'],
        ...about.cards.flatMap((c) => [['h3', c.title], ['p', c.description]]),
        ['h2', home.sustainability[0]], ['p', home.sustainability[1]], ['h2', 'We Follow Best Practices'], ['p', home.bestPractices[0]],
      ],
    },
    contact: { excerpt: contact.intro[1], blocks: [['p', contact.intro[0]]] },
    gallery: { excerpt: '', blocks: [] },
  },
  posts: news.map((n) => ({ slug: n.slug, title: n.title, date: n.date, excerpt: n.excerpt, image: remote(n.image), images: n.images.map(remote), videos: n.videos, paragraphs: n.paragraphs })),
  legacy_pages: [...studios.map((s) => s.slug), ...cities.map((c) => c.slug), 'about-us', 'contact-us', 'blogs', 'the-vision-studios'],
}
await fs.mkdir(path.dirname(OUT), { recursive: true })
await fs.writeFile(OUT, JSON.stringify(payload, null, 1) + '\n')
console.log(`Wrote ${path.relative(ROOT, OUT)}: ${payload.studios.length} studios, ${payload.cities.length} cities, ${payload.services.length} services/reasons, ${payload.clients.length} clients, ${payload.posts.length} posts`)

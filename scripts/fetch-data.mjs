#!/usr/bin/env node
/**
 * Pulls all public content from https://vision-studios.net (WordPress + Elementor)
 * into ./data/*.json and downloads every referenced image into ./src/static/img/remote/.
 *
 * Sources used:
 *   - WP REST API:   /wp-json/wp/v2/pages, /posts, /media
 *   - Rendered HTML: each page (Elementor widgets: icon-list, icon-box, call-to-action, slides, image-carousel, google_maps, counter)
 *   - Minified CSS:  the per-page wpo-minify stylesheet, which is where Elementor puts slide background images
 *
 * Usage: node scripts/fetch-data.mjs [--skip-images]
 */
import fs from 'node:fs/promises'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const ORIGIN = 'https://vision-studios.net'
const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const DATA = path.join(ROOT, 'data')
const IMG_DIR = path.join(ROOT, 'src/static/img/remote')
const SKIP_IMAGES = process.argv.includes('--skip-images')
const UA = 'Mozilla/5.0 (vision-studios-site fetch-data)'

const wanted = new Map() // remote url -> local relative path

async function get(url, as = 'text') {
  const res = await fetch(url, { headers: { 'user-agent': UA } })
  if (!res.ok) throw new Error(`${res.status} ${url}`)
  return as === 'json' ? res.json() : res.text()
}

async function getAllPages(endpoint) {
  const out = []
  for (let page = 1; ; page++) {
    const res = await fetch(`${ORIGIN}/wp-json/wp/v2/${endpoint}${endpoint.includes('?') ? '&' : '?'}per_page=100&page=${page}`, { headers: { 'user-agent': UA } })
    if (res.status === 400) break
    if (!res.ok) throw new Error(`${res.status} ${endpoint} page ${page}`)
    const items = await res.json()
    out.push(...items)
    if (items.length < 100) break
  }
  return out
}

const decode = (s = '') =>
  s
    .replace(/&#(\d+);/g, (_, n) => String.fromCharCode(Number(n)))
    .replace(/&#x([0-9a-f]+);/gi, (_, n) => String.fromCharCode(parseInt(n, 16)))
    .replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#039;|&apos;/g, "'").replace(/&nbsp;/g, ' ').replace(/&hellip;/g, '…').replace(/&ndash;/g, '–').replace(/&mdash;/g, '—').replace(/&rsquo;/g, '’').replace(/&lsquo;/g, '‘').replace(/&rdquo;/g, '”').replace(/&ldquo;/g, '“')
const strip = (html = '') => decode(html.replace(/<[^>]+>/g, ' ')).replace(/\s+/g, ' ').trim()
const stripLines = (html = '') =>
  decode(html.replace(/<script[\s\S]*?<\/script>/g, '').replace(/<style[\s\S]*?<\/style>/g, '').replace(/<[^>]+>/g, '\n'))
    .split('\n').map((l) => l.trim()).filter(Boolean)

/** Split rendered HTML on block-level tags only, keeping inline links/emphasis as running text. */
const blocks = (html = '') =>
  decode(
    html.replace(/<script[\s\S]*?<\/script>/g, '').replace(/<style[\s\S]*?<\/style>/g, '').replace(/<img[^>]*>/g, '')
      .replace(/<\/(p|li|h[1-6]|div|figure|figcaption|blockquote|tr)>|<br\s*\/?>/gi, '\n').replace(/<[^>]+>/g, ''),
  ).split('\n').map((l) => l.replace(/\s+/g, ' ').trim()).filter(Boolean)

/** Remove WordPress "-1024x683" size suffixes so we download the original. */
const original = (url) => url.replace(/-\d{2,4}x\d{2,4}(?=\.\w+$)/, '')

function localImage(url) {
  if (!url || !/^https?:/.test(url)) return url
  const u = original(url.replace(/^http:/, 'https:'))
  if (!u.includes('vision-studios.net/wp-content/uploads/')) return u // external stock images: leave as is
  const rel = u.split('/wp-content/uploads/')[1]
  const local = 'remote/' + rel.replace(/[^A-Za-z0-9._/-]/g, '_')
  wanted.set(u, local)
  return '/static/img/' + local
}

function mainOf(html) {
  const m = html.match(/<main[\s\S]*?<\/main>/)
  return m ? m[0] : html
}

/** Elementor slides widget: order from HTML, background URLs from the minified page CSS. */
async function extractSlides(html) {
  const cssUrl = html.match(/https:\/\/vision-studios\.net\/wp-content\/cache\/wpo-minify\/[^'"]+header[^'"]+\.css/)?.[0]
  const css = cssUrl ? await get(cssUrl) : ''
  const ids = [...html.matchAll(/elementor-repeater-item-([a-z0-9]+) swiper-slide/g)].map((m) => m[1])
  const slides = []
  for (const id of ids) {
    const re = new RegExp(`\\.elementor-repeater-item-${id}[^{]*\\{[^}]*background-image:url\\(["']?([^"')]+)`)
    const u = css.match(re)?.[1]
    if (u && !slides.includes(u)) slides.push(u)
  }
  return slides.map(localImage)
}

/** Split Elementor markup into widget chunks of one type (widgets never nest other elementor-elements). */
function widgets(main, type) {
  const re = new RegExp(`<div class="elementor-element[^"]*elementor-widget elementor-widget-${type}"[\\s\\S]*?(?=<div class="elementor-element |<\\/main>)`, 'g')
  return [...main.matchAll(re)].map((m) => m[0])
}

/** The "Description :" text-editor: one <p> per spec line. */
function extractSpecs(main) {
  for (const w of widgets(main, 'text-editor')) {
    const ps = [...w.matchAll(/<p>([\s\S]*?)<\/p>/g)].map((m) => strip(m[1])).filter(Boolean)
    if (ps.length >= 2 && /camera|studio|sq\. mt/i.test(w)) return ps
  }
  return []
}

function extractIconBoxes(main) {
  return widgets(main, 'icon-box').map((w) => ({
    title: strip(w.match(/elementor-icon-box-title">([\s\S]*?)<\/h\d>/)?.[1] || ''),
    description: strip(w.match(/elementor-icon-box-description">([\s\S]*?)<\/p>/)?.[1] || ''),
  })).filter((b) => b.title || b.description)
}

function extractHeadings(main) {
  return widgets(main, 'heading').map((w) => strip(w.match(/elementor-heading-title[^>]*>([\s\S]*?)<\/h\d>/)?.[1] || '')).filter(Boolean)
}

function extractCtas(main) {
  return widgets(main, 'call-to-action').map((w) => ({
    href: w.match(/<a class="elementor-cta" href="([^"]*)"/)?.[1] || '',
    image: localImage((w.match(/background-image: url\(([^)]*)\)/)?.[1] || '').replace(/['"]/g, '')),
    title: strip(w.match(/elementor-cta__title[^>]*>([\s\S]*?)<\/h\d>/)?.[1] || ''),
    description: strip(w.match(/elementor-cta__description[^>]*>([\s\S]*?)<\/div>/)?.[1] || ''),
  }))
}

function extractMap(main) {
  const src = main.match(/maps\.google\.com\/maps\?q=([^&"]+)/)?.[1]
  return src ? decodeURIComponent(src.replace(/\+/g, ' ')) : ''
}

function extractContact(main) {
  const text = stripLines(main)
  const i = text.findIndex((l) => l === 'Contact')
  const tail = i >= 0 ? text.slice(i + 1, i + 6) : []
  const phone = tail.find((l) => /^\+?\d[\d ()]+$/.test(l)) || ''
  const address = tail.filter((l) => l !== phone && !/email|@|\[/.test(l) && l.length > 12).join(' ')
  return { phone, address }
}

function extractCarousel(main) {
  const block = main.match(/elementor-widget-image-carousel[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/)?.[0] || ''
  return [...block.matchAll(/<img[^>]+src="([^"]+)"[^>]*alt="([^"]*)"/g)].map((m) => ({ image: localImage(m[1]), alt: decode(m[2]) }))
}

function cityOf(slug) {
  return slug.split('-')[0]
}

async function main() {
  await fs.mkdir(DATA, { recursive: true })
  console.log('Fetching WP REST API…')
  const [pages, posts, media] = await Promise.all([
    getAllPages('pages?_fields=id,slug,link,title,content,excerpt,featured_media,modified'),
    getAllPages('posts?_fields=id,slug,link,title,date,excerpt,content,featured_media'),
    getAllPages('media?_fields=id,source_url,title,alt_text,mime_type,media_details.width,media_details.height'),
  ])
  const mediaById = new Map(media.map((m) => [m.id, m]))

  // ---------- Studios ----------
  const studioPages = pages.filter((p) => /-studio(-\d+)?$|dtl-room$/.test(p.slug)).sort((a, b) => a.slug.localeCompare(b.slug, undefined, { numeric: true }))
  const studios = []
  for (const p of studioPages) {
    console.log('  studio', p.slug)
    const html = await get(`${ORIGIN}/${p.slug}/`)
    const main = mainOf(html)
    const slides = await extractSlides(html)
    const specs = extractSpecs(main)
    const highlights = extractIconBoxes(main).map((b) => b.description || b.title)
    const useCases = extractCtas(main).filter((c) => c.title)
    const useCasesHeading = extractHeadings(main).find((h) => /^(This|These) studio/.test(h)) || ''
    const plan = [...main.matchAll(/elementor-widget-image"[\s\S]*?<img[^>]+src="([^"]+)"/g)].map((m) => localImage(m[1])).find((u) => /Drawing|Layout|Plan|Deposite-Studio-2|st1-Deposite|Hiwar/i.test(u)) || ''
    const area = (specs.length ? specs : highlights).map((s) => s.match(/^([\d.,]+)\s*sq\.?\s*mt/i)?.[1]).find(Boolean) || ''
    studios.push({
      slug: p.slug,
      title: decode(p.title.rendered),
      city: cityOf(p.slug),
      areaSqm: area ? Number(area.replace(',', '.')) : null,
      specs,
      highlights,
      useCasesHeading,
      useCases: useCases.map(({ title, description, image }) => ({ title, description, image })),
      gallery: slides,
      carousel: extractCarousel(main),
      floorPlan: plan,
      contact: extractContact(main),
      address: extractMap(main),
      sourceUrl: p.link,
      modified: p.modified,
    })
  }

  // ---------- Cities ----------
  const cities = []
  for (const slug of ['london', 'dublin', 'paris', 'istanbul']) {
    console.log('  city', slug)
    const html = await get(`${ORIGIN}/${slug}/`)
    const main = mainOf(html)
    const lines = stripLines(main)
    const ctas = extractCtas(main)
    // studio cards: heading followed by description paragraph (Elementor heading + text-editor pairs)
    const blurbs = [...main.matchAll(/elementor-heading-title[^>]*>(?:<a[^>]*>)?\s*((?:London|Dublin|Paris|Istanbul)[^<]*?)\s*(?:<\/a>)?<\/h\d>[\s\S]*?elementor-widget-text-editor[\s\S]*?<p>([\s\S]*?)<\/p>/g)].map((m) => ({ title: strip(m[1]), description: strip(m[2]) }))
    const services = []
    const sIdx = lines.indexOf('Our Services')
    if (sIdx >= 0) for (let i = sIdx + 1; i + 1 < lines.length; i += 2) if (/Service|Hire/.test(lines[i])) services.push({ title: lines[i], description: lines[i + 1] })
    cities.push({
      slug,
      name: slug[0].toUpperCase() + slug.slice(1),
      heading: lines.find((l) => /^Our TV/.test(l)) || '',
      studioCards: ctas.filter((c) => c.href).map((c) => ({ slug: c.href.replace(ORIGIN, '').replace(/\//g, ''), image: c.image })),
      studioBlurbs: blurbs,
      services,
      sourceUrl: `${ORIGIN}/${slug}/`,
    })
  }

  // ---------- Home ----------
  console.log('  home')
  const homeHtml = await get(`${ORIGIN}/`)
  const homeMain = mainOf(homeHtml)
  const homeLines = stripLines(homeMain)
  const counters = [...homeMain.matchAll(/elementor-counter-title">([^<]+)<[\s\S]*?number-prefix">([^<]*)<[\s\S]*?data-to-value="([^"]+)"[\s\S]*?number-suffix">([^<]*)</g)].map((m) => ({ label: strip(m[1]), prefix: m[2].trim(), value: Number(m[3]), suffix: m[4].trim() }))
  const clients = [...homeMain.matchAll(/<img[^>]+src="([^"]+)"[^>]*alt="([^"]*)"[^>]*>/g)]
    .filter((m) => /logo|Barclays|newscentral|tv-one|zaitouna|afrosport|DMA|WhatsApp-Image-2023-06-06/i.test(m[1]))
    .map((m) => ({ name: decode(m[2]) || path.basename(m[1]), logo: localImage(m[1]) }))
  const home = {
    title: homeLines[0],
    tagline: homeLines[1],
    intro: homeLines.slice(3, 5),
    whyChoose: extractIconBoxes(homeMain),
    facilities: extractCtas(homeMain).filter((c) => /\/(london|dublin|paris|istanbul)\//.test(c.href)).map((c) => ({ city: c.title, country: c.description, image: c.image })),
    sustainability: homeLines.slice(homeLines.indexOf('Sustainability') + 1, homeLines.indexOf('Get In Touch', homeLines.indexOf('Sustainability'))),
    bestPractices: homeLines.slice(homeLines.indexOf('Practices') + 1, homeLines.indexOf('Sustainablility')),
    counters,
    clients,
  }

  // ---------- About ----------
  console.log('  about')
  const aboutMain = mainOf(await get(`${ORIGIN}/about-us/`))
  const aboutLines = stripLines(aboutMain)
  const section = (from, to) => aboutLines.slice(aboutLines.indexOf(from) + 1, to ? aboutLines.indexOf(to) : undefined)
  const about = {
    heading: aboutLines[0],
    who: section('Who is Vision Studios', 'years of experience').filter((l) => l !== 'Who is Vision Studios'),
    experience: section('years of experience', 'Our Vision'),
    vision: section('Our Vision', 'Our Mission'),
    mission: section('Our Mission', 'More About US !!'),
    team: aboutLines.slice(aboutLines.indexOf('More About US !!') + 2, aboutLines.findIndex((l) => l.startsWith('are experts'))),
    cards: extractCtas(aboutMain).filter((c) => c.title),
    images: [...aboutMain.matchAll(/background-image: url\(([^)]+)\)/g)].map((m) => localImage(m[1])),
  }

  // ---------- Contact ----------
  console.log('  contact')
  const contactMain = mainOf(await get(`${ORIGIN}/contact-us/`))
  const cl = stripLines(contactMain)
  const regions = []
  for (let i = 0; i < cl.length; i++) if (/^\+\d/.test(cl[i]) && i > 0) regions.push({ region: cl[i - 1], phone: cl[i] })
  const contact = { heading: cl[0], intro: cl.slice(1, 3), regions, email: 'booking@vision-studios.net' }

  // ---------- News ----------
  const news = posts.map((p) => {
    const content = p.content.rendered
    const images = [...new Set([...content.matchAll(/<img[^>]+src="([^"]+)"/g)].map((m) => localImage(m[1])))]
    const fm = mediaById.get(p.featured_media)
    return {
      slug: p.slug,
      title: decode(p.title.rendered),
      date: p.date.slice(0, 10),
      excerpt: strip(p.excerpt.rendered).replace(/\s*\[…\]$/, '…'),
      image: fm ? localImage(fm.source_url) : images[0] || '',
      images,
      videos: [...new Set([...content.matchAll(/https:\/\/(?:www\.)?youtu(?:be\.com|\.be)\/[^"'\s<]+/g)].map((m) => m[0]))],
      paragraphs: blocks(content).filter((l, i, arr) => l.length > 2 && !/^https?:/.test(l) && arr.indexOf(l) === i && l !== decode(p.title.rendered)),
      sourceUrl: p.link,
    }
  })

  const site = {
    name: 'Vision Studios',
    origin: ORIGIN,
    email: 'booking@vision-studios.net',
    social: {
      instagram: homeHtml.match(/https:\/\/www\.instagram\.com\/[^"']+/)?.[0] || '',
      linkedin: homeHtml.match(/https:\/\/www\.linkedin\.com\/[^"']+/)?.[0] || '',
    },
    hq: { address: 'Vision Studios, Kendal Avenue, London W3 0XA', locality: 'Kendal Avenue', region: 'London', postalCode: 'W3 0XA', country: 'UK' },
    openingHours: 'Mon–Sun 09:00–17:00',
    logo: localImage(`${ORIGIN}/wp-content/uploads/2024/05/logo_vision-removebg-preview-e1695908610989-150x150-1.png`),
    fetchedAt: new Date().toISOString(),
  }

  const write = (name, obj) => fs.writeFile(path.join(DATA, name), JSON.stringify(obj, null, 2) + '\n')
  await Promise.all([
    write('site.json', site), write('studios.json', studios), write('cities.json', cities), write('home.json', home),
    write('about.json', about), write('contact.json', contact), write('news.json', news),
    write('media-index.json', media.map((m) => ({ id: m.id, url: m.source_url, title: decode(m.title.rendered), alt: m.alt_text, mime: m.mime_type }))),
  ])
  console.log(`Wrote data/: ${studios.length} studios, ${cities.length} cities, ${news.length} posts, ${home.clients.length} clients`)

  if (SKIP_IMAGES) return
  console.log(`Downloading ${wanted.size} images…`)
  let n = 0
  const entries = [...wanted.entries()]
  await Promise.all(Array.from({ length: 8 }, async () => {
    while (entries.length) {
      const [url, local] = entries.shift()
      const dest = path.join(IMG_DIR, local.replace(/^remote\//, ''))
      try {
        await fs.access(dest); n++; continue
      } catch {}
      try {
        const res = await fetch(url, { headers: { 'user-agent': UA } })
        if (!res.ok) throw new Error(res.status)
        await fs.mkdir(path.dirname(dest), { recursive: true })
        await fs.writeFile(dest, Buffer.from(await res.arrayBuffer()))
        n++
      } catch (e) {
        console.warn('  failed', url, e.message)
      }
    }
  }))
  console.log(`Images on disk: ${n}/${wanted.size}`)
}

main().catch((e) => { console.error(e); process.exit(1) })

#!/usr/bin/env node
// Renders ./dist from ./data/*.json + ./src/templates. No dependencies.
import fs from 'node:fs/promises'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { renderHome } from '../src/templates/home.mjs'
import { renderCity } from '../src/templates/city.mjs'
import { renderStudio } from '../src/templates/studio.mjs'
import { renderNewsIndex, renderPost, renderAbout, renderContact, renderGallery, render404 } from '../src/templates/pages.mjs'

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const DIST = path.join(ROOT, 'dist')
const BASE_URL = (process.env.SITE_URL || '').replace(/\/$/, '')
// Sub-path when hosted under e.g. https://user.github.io/repo/ — set BASE_PATH=/repo. Empty for a root domain.
const BASE_PATH = (process.env.BASE_PATH || '').replace(/\/$/, '')
/** Prefix every root-relative URL in rendered HTML with BASE_PATH. */
const rebase = (html) => BASE_PATH ? html.replace(/((?:href|src|content|data-src|url)="|url=)\/(?!\/)/g, `$1${BASE_PATH}/`).replace(/"(\/static\/[^"]+)"/g, `"${BASE_PATH}$1"`) : html

async function readJson(name) { return JSON.parse(await fs.readFile(path.join(ROOT, 'data', name), 'utf8')) }

async function main() {
  const names = ['site', 'studios', 'cities', 'home', 'about', 'contact', 'news', 'curated']
  const data = Object.fromEntries(await Promise.all(names.map(async (n) => [n, await readJson(n + '.json')])))
  data.news.sort((a, b) => b.date.localeCompare(a.date))
  // Order studios: London, Dublin, Paris, Istanbul; numeric within city.
  const order = ['london', 'dublin', 'paris', 'istanbul']
  data.studios.sort((a, b) => order.indexOf(a.city) - order.indexOf(b.city) || a.slug.localeCompare(b.slug, undefined, { numeric: true }))
  data.cities.sort((a, b) => order.indexOf(a.slug) - order.indexOf(b.slug))

  await fs.rm(DIST, { recursive: true, force: true })
  await fs.mkdir(DIST, { recursive: true })
  await fs.cp(path.join(ROOT, 'src/static'), path.join(DIST, 'static'), { recursive: true })

  const pages = []
  const emit = async (p, html) => {
    const file = path.join(DIST, p === '/' ? 'index.html' : p.endsWith('/') ? p + 'index.html' : p)
    await fs.mkdir(path.dirname(file), { recursive: true })
    await fs.writeFile(file, rebase(html))
    if (p.endsWith('/')) pages.push(p)
  }
  const ctx = (p) => ({ data, path: p, baseUrl: BASE_URL, basePath: BASE_PATH })

  await emit('/', renderHome(ctx('/')))
  for (const c of data.cities) await emit(`/${c.slug}/`, renderCity(c, ctx(`/${c.slug}/`)))
  for (const s of data.studios) await emit(`/studios/${s.slug}/`, renderStudio(s, ctx(`/studios/${s.slug}/`)))
  await emit('/news/', renderNewsIndex(ctx('/news/')))
  for (const n of data.news) await emit(`/news/${n.slug}/`, renderPost(n, ctx(`/news/${n.slug}/`)))
  await emit('/about/', renderAbout(ctx('/about/')))
  await emit('/contact/', renderContact(ctx('/contact/')))
  await emit('/gallery/', renderGallery(ctx('/gallery/')))
  await emit('/404.html', render404(ctx('/404.html')))

  // Legacy URLs from vision-studios.net → new structure (static HTML redirects).
  const redirects = {}
  for (const s of data.studios) redirects[`/${s.slug}/`] = `/studios/${s.slug}/`
  Object.assign(redirects, { '/about-us/': '/about/', '/contact-us/': '/contact/', '/blogs/': '/news/', '/category/news/': '/news/' })
  for (const [from, to] of Object.entries(redirects)) {
    await fs.mkdir(path.join(DIST, from), { recursive: true })
    await fs.writeFile(path.join(DIST, from, 'index.html'), rebase(`<!DOCTYPE html><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=${to}"><link rel="canonical" href="${BASE_URL}${to}"><title>Redirecting…</title><a href="${to}">${to}</a>`))
  }
  await fs.writeFile(path.join(DIST, '_redirects'), Object.entries(redirects).map(([f, t]) => `${f} ${t} 301`).join('\n') + '\n')

  await fs.writeFile(path.join(DIST, 'sitemap.xml'), `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${pages.map((p) => `  <url><loc>${BASE_URL}${BASE_PATH}${p}</loc></url>`).join('\n')}\n</urlset>\n`)
  // GitHub Pages reads the custom domain from a CNAME file at the site root.
  if (process.env.CNAME_DOMAIN) await fs.writeFile(path.join(DIST, 'CNAME'), process.env.CNAME_DOMAIN + '\n')
  await fs.writeFile(path.join(DIST, 'robots.txt'), `User-agent: *\nAllow: /\nSitemap: ${BASE_URL}${BASE_PATH}/sitemap.xml\n`)
  console.log(`Built ${pages.length} pages + ${Object.keys(redirects).length} redirects → dist/`)
}

main().catch((e) => { console.error(e); process.exit(1) })

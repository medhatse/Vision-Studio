#!/usr/bin/env node
// Quick on-page SEO audit. Usage: node scripts/seo-audit.mjs https://vision-studios.net [/path ...]
const base = (process.argv[2] || 'http://localhost:8080').replace(/\/$/, '')
const paths = process.argv.slice(3).length ? process.argv.slice(3) : ['/', '/city/london/', '/city/istanbul/', '/studios/', '/studios/istanbul-studio-1/', '/studios/london-studio-1/', '/news/', '/about/', '/contact/', '/gallery/', '/does-not-exist/']
const attr = (html, re) => html.match(re)?.[1] || ''
let problems = 0
for (const p of paths) {
  const res = await fetch(base + p, { redirect: 'manual' })
  const html = await res.text()
  const head = html.split('</head>')[0]
  const title = attr(head, /<title>([\s\S]*?)<\/title>/).trim()
  const desc = attr(head, /<meta name="description" content="([^"]*)"/)
  const canonical = attr(head, /rel="canonical" href="([^"]+)"/)
  const robots = attr(head, /<meta name=['"]robots['"] content=['"]([^'"]*)['"]/)
  const og = (head.match(/property="og:/g) || []).length
  const h1 = (html.match(/<h1[\s>]/g) || []).length
  const imgs = html.match(/<img [^>]*>/g) || []
  const noAlt = imgs.filter((i) => !/\balt=/.test(i)).length // alt="" is valid for decorative images
  const types = []
  for (const m of html.matchAll(/<script type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/g)) {
    try { const d = JSON.parse(m[1]); for (const n of d['@graph'] || [d]) types.push([].concat(n['@type']).join('+')) } catch { types.push('INVALID-JSON') }
  }
  const issues = []
  if (res.status === 200) {
    if (!title) issues.push('no <title>'); else if (title.length > 65) issues.push(`title ${title.length} chars`)
    if (!desc) issues.push('no meta description'); else if (desc.length < 50 || desc.length > 160) issues.push(`description ${desc.length} chars`)
    if (!canonical) issues.push('no canonical')
    if (h1 !== 1) issues.push(`${h1} h1`)
    if (noAlt) issues.push(`${noAlt} img without alt`)
    if (!og) issues.push('no Open Graph')
    if (types.includes('INVALID-JSON')) issues.push('invalid JSON-LD')
    const thirdParty = [...head.matchAll(/<link[^>]+(?:stylesheet|preload)[^>]+href="(https?:\/\/[^"/]+)/g)].map((m) => m[1]).filter((o) => !base.startsWith(o))
    if (thirdParty.length && !/<link rel=['"]preconnect['"]/.test(head)) issues.push('third-party CSS without preconnect: ' + [...new Set(thirdParty)].join(', '))
    if (/fonts\.googleapis\.com|cdnjs|jsdelivr|unpkg/.test(head)) issues.push('render-blocking third-party resource in <head>')
  } else if (res.status === 404 && !/noindex/.test(robots)) issues.push('404 page not noindex')
  problems += issues.length
  console.log(`${res.status} ${p.padEnd(40)} h1=${h1} imgs=${imgs.length} ld=[${types.join(', ')}]${issues.length ? '\n      ! ' + issues.join('; ') : ''}`)
}
for (const f of ['/robots.txt', '/wp-sitemap.xml', '/sitemap_index.xml', '/sitemap.xml']) {
  const r = await fetch(base + f).catch(() => ({ status: 'ERR' }))
  console.log(`${r.status} ${f}`)
}
console.log(problems ? `\n${problems} issue(s) found` : '\nNo issues found')
process.exit(problems ? 1 : 0)

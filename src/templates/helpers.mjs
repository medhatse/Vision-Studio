// Shared layout + small components. All templates are plain functions returning HTML strings.
import { picture } from '../../scripts/images.mjs'

export const esc = (s = '') => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
export const mono = 'font-mono-tag uppercase tracking-[0.15em]'
export const eyebrow = (text) => `<p class="font-mono-tag text-xs uppercase tracking-[0.25em] text-accent mb-4">${esc(text)}</p>`
export const h2 = (text, extra = '') => `<h2 class="font-display uppercase text-[clamp(2rem,5vw,3.5rem)] leading-[0.95] ${extra}">${text}</h2>`
export const cityName = (slug) => slug[0].toUpperCase() + slug.slice(1)
export const fmtDate = (iso) => new Date(iso + 'T00:00:00Z').toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' })
export const telHref = (phone) => 'tel:' + phone.replace(/\(0\)|\s|\(|\)/g, '')

// ----- Inline SVG icons (same set as the WordPress theme) -----
const ICONS = {
  'arrow-up-right': '<path d="M7 17 17 7M8 7h9v9"/>',
  'arrow-right': '<path d="M5 12h14M13 6l6 6-6 6"/>',
  bars: '<path d="M4 6h16M4 12h16M4 18h16"/>',
  xmark: '<path d="M6 6l12 12M18 6 6 18"/>',
  check: '<path d="m5 12 5 5L20 7"/>',
  'circle-check': '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
  circle: '<circle cx="12" cy="12" r="9"/>',
  'tower-broadcast': '<circle cx="12" cy="9" r="2"/><path d="M12 11v10M7.8 4.8a6 6 0 0 0 0 8.4M16.2 4.8a6 6 0 0 1 0 8.4M5 2a10 10 0 0 0 0 14M19 2a10 10 0 0 1 0 14"/>',
  'camera-retro': '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M3 11h18M8 7V5h5v2"/><circle cx="14" cy="15" r="3"/>',
  'people-group': '<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M15 20v-1a4 4 0 0 1 6 0v1"/>',
  'pen-ruler': '<path d="M3 17l4 4 11-11-4-4L3 17zM14 6l4 4M7 13l4 4M10 10l2 2"/>',
  'satellite-dish': '<path d="M4 10a8 8 0 0 0 10 10M4 4a14 14 0 0 1 16 16M13 17l-6-6M9 8l7 7M15 5l4 4"/>',
  'microphone-lines': '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3M9 21h6M9 8h6M9 11h6"/>',
  'vector-square': '<rect x="3" y="3" width="4" height="4"/><rect x="17" y="3" width="4" height="4"/><rect x="3" y="17" width="4" height="4"/><rect x="17" y="17" width="4" height="4"/><path d="M7 5h10M5 7v10M19 7v10M7 19h10"/>',
  couch: '<path d="M5 11V8a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v3M3 13a2 2 0 0 1 4 0v3h10v-3a2 2 0 0 1 4 0v5H3v-5zM5 18v2M19 18v2"/>',
  tv: '<rect x="3" y="5" width="18" height="12" rx="2"/><path d="M8 21h8M12 17v4"/>',
  sliders: '<path d="M4 6h10M18 6h2M4 12h2M10 12h10M4 18h12M20 18h0"/><circle cx="16" cy="6" r="2"/><circle cx="8" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
  video: '<rect x="3" y="7" width="13" height="10" rx="2"/><path d="m16 11 5-3v8l-5-3z"/>',
  bolt: '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/>',
  'arrows-up-down-left-right': '<path d="M12 2v20M2 12h20M8 6l4-4 4 4M8 18l4 4 4-4M6 8l-4 4 4 4M18 8l4 4-4 4"/>',
  lightbulb: '<path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-4 10.5c.7.6 1 1.3 1 2.5h6c0-1.2.3-1.9 1-2.5A6 6 0 0 0 12 3z"/>',
  globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
  map: '<path d="M12 21s7-6.5 7-11a7 7 0 0 0-14 0c0 4.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
}
export const icon = (name, cls = '') => `<svg class="vs-icon ${cls}" viewBox="0 0 24 24" aria-hidden="true" focusable="false">${ICONS[name.replace(/^fa-/, '')] || ICONS.circle}</svg>`
export const arrow = icon('arrow-up-right', 'text-[10px]')
export const btnPrimary = (href, label) => `<a href="${href}" class="btn-primary inline-flex items-center gap-2 bg-accent text-black ${mono} text-xs px-6 py-4">${esc(label)} ${arrow}</a>`
export const btnWhite = (href, label) => `<a href="${href}" class="inline-flex items-center gap-2 bg-white text-black ${mono} text-xs px-6 py-4">${esc(label)} ${arrow}</a>`
export const btnGhost = (href, label) => `<a href="${href}" class="nav-link inline-flex items-center gap-2 ${mono} text-xs text-white/70">${esc(label)} ${arrow}</a>`

// ----- Responsive images -----
/** <img> with srcset/sizes/width/height. opts: { sizes, eager, cls, decorative } */
export function img(ctx, src, alt, opts = {}) {
  const p = picture(src, ctx.srcDir)
  const sizes = opts.sizes || '(min-width: 1024px) 33vw, 50vw'
  const attrs = [`src="${p.src}"`]
  if (p.srcset) attrs.push(`srcset="${p.srcset}"`, `sizes="${sizes}"`)
  if (p.width) attrs.push(`width="${p.width}"`, `height="${p.height}"`)
  attrs.push(`alt="${esc(alt)}"`, `decoding="async"`)
  attrs.push(opts.eager ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"')
  if (opts.cls) attrs.push(`class="${opts.cls}"`)
  return `<img ${attrs.join(' ')}/>`
}
export const heroImg = (ctx, src, alt) => img(ctx, src, alt, { sizes: '100vw', eager: true, cls: 'absolute inset-0 w-full h-full object-cover' })
export const preloadHero = (ctx, src) => { const p = picture(src, ctx.srcDir); return p.src ? `<link rel="preload" as="image" href="${p.src}"${p.srcset ? ` imagesrcset="${p.srcset}" imagesizes="100vw"` : ''} fetchpriority="high"/>` : '' }

const NAV = [['/studios/', 'Studios'], ['/#services', 'Services'], ['/gallery/', 'Gallery'], ['/news/', 'News'], ['/about/', 'About'], ['/contact/', 'Contact']]

export function header(ctx) {
  const links = NAV.map(([href, label]) => `<li><a href="${href}" class="nav-link${href !== '/#services' && ctx.path.startsWith(href) ? ' text-accent' : ''}">${label}</a></li>`).join('')
  const mobile = NAV.map(([href, label]) => `<li><a href="${href}" class="block py-1">${label}</a></li>`).join('')
  return `<a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:z-[70] focus:top-4 focus:left-4 focus:bg-accent focus:text-black focus:px-4 focus:py-2 font-mono-tag text-xs uppercase">Skip to content</a>
<header id="site-header" class="fixed top-0 inset-x-0 z-50 transition-colors duration-300">
<nav class="max-w-7xl mx-auto px-6 lg:px-10 h-20 flex items-center justify-between" aria-label="Primary">
<a href="/" class="flex items-center gap-3 shrink-0" aria-label="Vision Studios home"><img src="/static/img/logo-mark.png" alt="" width="430" height="455" class="h-8 w-auto"/><span class="font-display uppercase leading-none text-lg tracking-wide">Vision<span class="block text-xs lg:text-[10px] font-mono-tag tracking-[0.2em] text-white/50 -mt-0.5">Studios</span></span></a>
<ul class="hidden lg:flex items-center gap-10 font-mono-tag text-xs uppercase tracking-[0.15em] text-white/80">${links}</ul>
<div class="hidden lg:block"><a href="/contact/" class="btn-primary inline-flex items-center gap-2 bg-accent text-black font-mono-tag text-xs uppercase tracking-[0.15em] px-5 py-3 rounded-sm">Book a Studio${icon('arrow-right', 'text-[10px]')}</a></div>
<button id="menu-toggle" class="lg:hidden text-white text-2xl" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobile-menu"><span class="menu-icon-open">${icon('bars')}</span><span class="menu-icon-close" hidden>${icon('xmark')}</span></button>
</nav>
<div id="mobile-menu" class="lg:hidden max-h-0 overflow-hidden bg-black/95 border-t border-white/10"><ul class="flex flex-col px-6 py-4 gap-4 font-mono-tag text-sm uppercase tracking-[0.15em] text-white/85">${mobile}<li><a href="/contact/" class="inline-flex items-center gap-2 bg-accent text-black px-4 py-3 rounded-sm mt-2">Book a Studio ${icon('arrow-right', 'text-[10px]')}</a></li></ul></div>
</header>`
}

export function footer(ctx) {
  const { site, cities, contact } = ctx.data
  const cityLinks = cities.map((c) => `<li><a href="/${c.slug}/" class="hover:text-accent">${c.name}</a></li>`).join('')
  const phones = contact.regions.map((r) => `<li><a href="${telHref(r.phone)}" class="hover:text-accent">${esc(r.region)} · ${esc(r.phone)}</a></li>`).join('')
  const social = [['ig', site.social.instagram, 'Instagram'], ['in', site.social.linkedin, 'LinkedIn']].filter(([, href]) => href).map(([label, href, name]) => `<a href="${href}" target="_blank" rel="noopener" aria-label="${name}" class="w-9 h-9 flex items-center justify-center border border-white/20 text-xs lg:text-[10px] font-mono-tag uppercase hover:border-accent hover:text-accent">${label}</a>`).join('')
  return `<footer class="bg-black border-t border-white/10 pt-16 pb-8">
<div class="max-w-7xl mx-auto px-6 lg:px-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-12">
<div><img src="/static/img/logo-full.png" alt="Vision Studios" width="1024" height="370" loading="lazy" class="h-9 w-auto mb-4"/><p class="text-white/50 text-sm max-w-xs">Your professional TV and film production studio space in London, Dublin, Paris and Istanbul. 25+ years of experience across four capital cities.</p></div>
<div><p class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/55 mb-4">Studios</p><ul class="space-y-2 text-sm text-white/70">${cityLinks}</ul></div>
<div><p class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/55 mb-4">Company</p><ul class="space-y-2 text-sm text-white/70"><li><a href="/#services" class="hover:text-accent">Services</a></li><li><a href="/gallery/" class="hover:text-accent">Gallery</a></li><li><a href="/news/" class="hover:text-accent">News</a></li><li><a href="/about/" class="hover:text-accent">About Us</a></li><li><a href="/contact/" class="hover:text-accent">Contact</a></li></ul></div>
<div><p class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/55 mb-4">Get In Touch</p><ul class="space-y-2 text-sm text-white/70"><li><a href="mailto:${site.email}" class="hover:text-accent">${site.email}</a></li>${phones}<li class="text-white/55 pt-1">${esc(site.hq.address)}</li><li class="text-white/55">${esc(site.openingHours)}</li></ul><div class="flex gap-3 mt-4">${social}</div></div>
</div>
<div class="max-w-7xl mx-auto px-6 lg:px-10 mt-12 pt-6 border-t border-white/10 flex flex-wrap justify-between gap-4 text-xs text-white/50"><p>© ${new Date().getFullYear()} Vision Studios. All rights reserved.</p><p>London · Dublin · Paris · Istanbul</p></div>
</footer>`
}

// ----- Breadcrumbs -----
export function breadcrumbs(items) {
  if (!items || items.length < 2) return ''
  const last = items.length - 1
  return `<nav class="max-w-7xl mx-auto px-6 lg:px-10 pt-6 font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] text-white/55" aria-label="Breadcrumb"><ol class="flex flex-wrap gap-2">${items.map(([label, url], i) => `<li class="flex gap-2">${i ? '<span aria-hidden="true">/</span>' : ''}${i === last ? `<span class="text-white/70" aria-current="page">${esc(label)}</span>` : `<a href="${url}" class="hover:text-accent">${esc(label)}</a>`}</li>`).join('')}</ol></nav>`
}
const abs = (ctx, u) => (ctx.baseUrl || '') + (ctx.basePath || '') + u
export const breadcrumbLd = (ctx, items) => items && items.length > 1 ? { '@type': 'BreadcrumbList', itemListElement: items.map(([name, item], i) => ({ '@type': 'ListItem', position: i + 1, name, item: abs(ctx, item) })) } : null

export function orgLd(ctx) {
  const { site, contact } = ctx.data
  return [
    { '@type': 'Organization', '@id': abs(ctx, '/#organization'), name: site.name, url: abs(ctx, '/'), logo: abs(ctx, '/static/img/logo-mark.png'), email: site.email,
      address: { '@type': 'PostalAddress', streetAddress: site.hq.address, addressLocality: site.hq.region, postalCode: site.hq.postalCode, addressCountry: 'GB' },
      sameAs: Object.values(site.social).filter(Boolean),
      contactPoint: contact.regions.map((r) => ({ '@type': 'ContactPoint', telephone: r.phone, contactType: 'sales', areaServed: /Kingdom/.test(r.region) ? 'GB' : /Turkey/.test(r.region) ? 'TR' : 'IE', availableLanguage: ['en'] })) },
    { '@type': 'WebSite', '@id': abs(ctx, '/#website'), url: abs(ctx, '/'), name: site.name, publisher: { '@id': abs(ctx, '/#organization') }, inLanguage: 'en' },
  ]
}
export const parseCoords = (s = '') => { const m = s.match(/(-?[\d.]+)°?\s*([NS])?\s*\/\s*(-?[\d.]+)°?\s*([EW])?/); return m ? { '@type': 'GeoCoordinates', latitude: +m[1] * (m[2] === 'S' ? -1 : 1), longitude: +m[3] * (m[4] === 'W' ? -1 : 1) } : undefined }

export function mapFacade(query, title) {
  const src = `https://maps.google.com/maps?q=${encodeURIComponent(query)}&t=m&z=16&output=embed&iwloc=near`
  return `<div class="vs-map" data-src="${src}" data-title="${esc(title)}"><button type="button" class="inline-flex items-center gap-3 bg-white text-black font-mono-tag text-xs uppercase tracking-[0.15em] px-5 py-3">${icon('map')}Load map</button><a class="absolute bottom-3 font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] text-white/60 hover:text-accent" href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}" target="_blank" rel="noopener">Open in Google Maps</a></div>`
}

export function layout(ctx, { title, description, body, image, jsonLd, crumbs, noindex, type = 'website' }) {
  const site = ctx.data.site
  const canonical = abs(ctx, ctx.path)
  const p = image ? picture(image, ctx.srcDir) : null
  const ogImage = image ? abs(ctx, image) : ''
  const graph = [...orgLd(ctx), { '@type': 'WebPage', '@id': canonical + '#webpage', url: canonical, name: title, description, isPartOf: { '@id': abs(ctx, '/#website') }, primaryImageOfPage: ogImage ? { '@type': 'ImageObject', url: ogImage } : undefined, inLanguage: 'en' }, ...[].concat(jsonLd || []), breadcrumbLd(ctx, crumbs)].filter(Boolean)
  return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>${esc(title)}</title>
<meta name="description" content="${esc(description)}"/>
<meta name="robots" content="${noindex ? 'noindex, follow' : 'index, follow'}, max-image-preview:large, max-snippet:-1, max-video-preview:-1"/>
<link rel="canonical" href="${canonical}"/>
<link rel="icon" href="/static/img/logo-mark.png" type="image/png"/><link rel="apple-touch-icon" href="/static/img/logo-mark.png"/>
<link rel="preload" href="/static/fonts/Anton-400.woff2" as="font" type="font/woff2" crossorigin/><link rel="preload" href="/static/fonts/Inter-var.woff2" as="font" type="font/woff2" crossorigin/>
${image && ctx.preloadHero ? preloadHero(ctx, image) : ''}
<link href="/static/app.css" rel="stylesheet"/>
<meta property="og:locale" content="en_GB"/><meta property="og:type" content="${type}"/><meta property="og:site_name" content="${esc(site.name)}"/><meta property="og:title" content="${esc(title)}"/><meta property="og:description" content="${esc(description)}"/><meta property="og:url" content="${canonical}"/>${ogImage ? `<meta property="og:image" content="${ogImage}"/><meta property="og:image:width" content="${p.width || 1600}"/><meta property="og:image:height" content="${p.height || 900}"/><meta property="og:image:alt" content="${esc(title)}"/>` : ''}
<meta name="twitter:card" content="${ogImage ? 'summary_large_image' : 'summary'}"/><meta name="twitter:title" content="${esc(title)}"/><meta name="twitter:description" content="${esc(description)}"/>${ogImage ? `<meta name="twitter:image" content="${ogImage}"/>` : ''}
<script type="application/ld+json">${JSON.stringify({ '@context': 'https://schema.org', '@graph': graph })}</script>
</head>
<body class="bg-black text-white antialiased">
${header(ctx)}
<main id="content">${body}</main>
${footer(ctx)}
<div id="lightbox" class="fixed inset-0 z-[60] bg-black/95 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Image viewer"><button id="lightbox-close" class="absolute top-5 right-6 text-white/70 hover:text-white text-3xl" aria-label="Close">&times;</button><button id="lightbox-prev" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/60 hover:text-accent text-3xl px-3" aria-label="Previous">&#8249;</button><img id="lightbox-img" src="" alt="" class="max-h-[88vh] max-w-full object-contain"/><button id="lightbox-next" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/60 hover:text-accent text-3xl px-3" aria-label="Next">&#8250;</button><p id="lightbox-caption" class="absolute bottom-5 inset-x-0 text-center font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/50"></p></div>
<script src="/static/app.js" defer></script>
</body></html>`
}

/** Studio card used on home city cards, city pages and "other studios" strips. */
export function studioCard(s, ctx, { size = 'md' } = {}) {
  const tag = ctx.data.curated.studioTags[s.slug] || cityName(s.city)
  const area = s.areaSqm ? `${s.areaSqm} sq. mt. studio` : (s.highlights[0] || '')
  const cls = size === 'lg' ? 'aspect-[4/3]' : 'aspect-[3/4]'
  return `<a href="/studios/${s.slug}/" class="group fade-up img-zoom relative ${cls} block overflow-hidden bg-white/5">
${img(ctx, s.gallery[0] || '', `${s.title}, ${cityName(s.city)}`, { sizes: '(min-width: 768px) 25vw, 50vw', cls: 'absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500' })}
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>
<div class="absolute bottom-0 left-0 p-4"><p class="font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.15em] text-accent mb-1">${esc(tag)}</p><h3 class="font-display uppercase text-2xl leading-none">${esc(s.title)}</h3><p class="font-mono-tag text-xs lg:text-[10px] text-white/50 mt-1">${esc(area)}</p></div></a>`
}

export function newsCard(n, ctx) {
  return `<a href="/news/${n.slug}/" class="group fade-up block">
<div class="img-zoom relative aspect-[16/10] overflow-hidden bg-white/5">${img(ctx, n.image, n.title, { cls: 'absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500' })}</div>
<p class="font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] text-white/55 mt-4">${fmtDate(n.date)}</p>
<h3 class="font-display uppercase text-xl leading-tight mt-2 group-hover:text-accent transition-colors">${esc(n.title)}</h3>
<p class="text-white/60 text-sm mt-2 line-clamp-3">${esc(n.excerpt)}</p></a>`
}

export function ctaBand(ctx, { heading = 'Let\'s Make<br/><span class="text-accent">Something Worth<br/>Watching.</span>', text = 'Tell us about your production. We\'ll match you with a studio, a crew and a quote — usually within a working day.' } = {}) {
  return `<section id="contact" class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-10 items-center">
<h2 class="fade-up font-display uppercase text-[clamp(2.4rem,7vw,4.5rem)] leading-[0.95]">${heading}</h2>
<div class="fade-up"><p class="text-white/70 max-w-md mb-6">${text}</p><div class="flex flex-wrap gap-4">${btnWhite('/contact/', 'Start a Booking')}${btnGhost('mailto:' + ctx.data.site.email, ctx.data.site.email)}</div></div>
</div></section>`
}

// Shared layout + small components. All templates are plain functions returning HTML strings.
export const esc = (s = '') => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
export const mono = 'font-mono-tag uppercase tracking-[0.15em]'
export const eyebrow = (text) => `<p class="font-mono-tag text-xs uppercase tracking-[0.25em] text-accent mb-4">${esc(text)}</p>`
export const h2 = (text, extra = '') => `<h2 class="font-display uppercase text-[clamp(2rem,5vw,3.5rem)] leading-[0.95] ${extra}">${text}</h2>`
export const arrow = '<i class="fa-solid fa-arrow-up-right text-[10px]"></i>'
export const btnPrimary = (href, label) => `<a href="${href}" class="btn-primary inline-flex items-center gap-2 bg-accent text-black ${mono} text-xs px-6 py-4">${esc(label)} ${arrow}</a>`
export const btnWhite = (href, label) => `<a href="${href}" class="inline-flex items-center gap-2 bg-white text-black ${mono} text-xs px-6 py-4">${esc(label)} ${arrow}</a>`
export const btnGhost = (href, label) => `<a href="${href}" class="nav-link inline-flex items-center gap-2 ${mono} text-xs text-white/70">${esc(label)} ${arrow}</a>`
export const cityName = (slug) => slug[0].toUpperCase() + slug.slice(1)
export const fmtDate = (iso) => new Date(iso + 'T00:00:00Z').toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' })
export const telHref = (phone) => 'tel:' + phone.replace(/\(0\)|\s|\(|\)/g, '')

const NAV = [
  ['/#studios', 'Studios'],
  ['/#services', 'Services'],
  ['/gallery/', 'Gallery'],
  ['/news/', 'News'],
  ['/about/', 'About'],
  ['/contact/', 'Contact'],
]

export function header(ctx) {
  const links = NAV.map(([href, label]) => `<li><a href="${href}" class="nav-link${ctx.path.startsWith(href.replace('/#', '/x')) ? ' text-accent' : ''}">${label}</a></li>`).join('')
  const mobile = NAV.map(([href, label]) => `<li><a href="${href}" class="block py-1">${label}</a></li>`).join('')
  return `<header id="site-header" class="fixed top-0 inset-x-0 z-50 transition-colors duration-300">
<nav class="max-w-7xl mx-auto px-6 lg:px-10 h-20 flex items-center justify-between">
<a href="/" class="flex items-center gap-3 shrink-0" aria-label="Vision Studios home"><img src="/static/img/logo-mark.png" alt="" class="h-8 w-auto"/><span class="font-display uppercase leading-none text-lg tracking-wide">Vision<span class="block text-[10px] font-mono-tag tracking-[0.2em] text-white/50 -mt-0.5">Studios</span></span></a>
<ul class="hidden lg:flex items-center gap-10 font-mono-tag text-xs uppercase tracking-[0.15em] text-white/80">${links}</ul>
<div class="hidden lg:block"><a href="/contact/" class="btn-primary inline-flex items-center gap-2 bg-accent text-black font-mono-tag text-xs uppercase tracking-[0.15em] px-5 py-3 rounded-sm">Book a Studio<i class="fa-solid fa-arrow-right text-[10px]"></i></a></div>
<button id="menu-toggle" class="lg:hidden text-white text-2xl" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobile-menu"><i class="fa-solid fa-bars"></i></button>
</nav>
<div id="mobile-menu" class="lg:hidden max-h-0 overflow-hidden bg-black/95 border-t border-white/10"><ul class="flex flex-col px-6 py-4 gap-4 font-mono-tag text-sm uppercase tracking-[0.15em] text-white/85">${mobile}<li><a href="/contact/" class="inline-flex items-center gap-2 bg-accent text-black px-4 py-3 rounded-sm mt-2">Book a Studio <i class="fa-solid fa-arrow-right text-[10px]"></i></a></li></ul></div>
</header>`
}

export function footer(ctx) {
  const { site, cities, contact, curated } = ctx.data
  const cityLinks = cities.map((c) => `<li><a href="/${c.slug}/" class="hover:text-accent">${c.name}</a></li>`).join('')
  const phones = contact.regions.map((r) => `<li><a href="${telHref(r.phone)}" class="hover:text-accent">${esc(r.region)} · ${esc(r.phone)}</a></li>`).join('')
  const social = [
    ['ig', site.social.instagram, 'Instagram'],
    ['in', site.social.linkedin, 'LinkedIn'],
  ].filter(([, href]) => href).map(([label, href, name]) => `<a href="${href}" target="_blank" rel="noopener" aria-label="${name}" class="w-9 h-9 flex items-center justify-center border border-white/20 text-[10px] font-mono-tag uppercase hover:border-accent hover:text-accent">${label}</a>`).join('')
  return `<footer class="bg-black border-t border-white/10 pt-16 pb-8">
<div class="max-w-7xl mx-auto px-6 lg:px-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-12">
<div><img src="/static/img/logo-full.png" alt="Vision Studios" class="h-9 w-auto mb-4"/><p class="text-white/50 text-sm max-w-xs">Your professional TV and film production studio space in London, Dublin, Paris and Istanbul. 25+ years of experience across four capital cities.</p></div>
<div><p class="font-mono-tag text-[11px] uppercase tracking-[0.2em] text-white/40 mb-4">Studios</p><ul class="space-y-2 text-sm text-white/70">${cityLinks}</ul></div>
<div><p class="font-mono-tag text-[11px] uppercase tracking-[0.2em] text-white/40 mb-4">Company</p><ul class="space-y-2 text-sm text-white/70"><li><a href="/#services" class="hover:text-accent">Services</a></li><li><a href="/gallery/" class="hover:text-accent">Gallery</a></li><li><a href="/news/" class="hover:text-accent">News</a></li><li><a href="/about/" class="hover:text-accent">About Us</a></li><li><a href="/contact/" class="hover:text-accent">Contact</a></li></ul></div>
<div><p class="font-mono-tag text-[11px] uppercase tracking-[0.2em] text-white/40 mb-4">Get In Touch</p><ul class="space-y-2 text-sm text-white/70"><li><a href="mailto:${site.email}" class="hover:text-accent">${site.email}</a></li>${phones}<li class="text-white/40 pt-1">${esc(site.hq.address)}</li><li class="text-white/40">${esc(site.openingHours)}</li></ul><div class="flex gap-3 mt-4">${social}</div></div>
</div>
<div class="max-w-7xl mx-auto px-6 lg:px-10 mt-12 pt-6 border-t border-white/10 flex flex-wrap justify-between gap-4 text-xs text-white/30"><p>© ${new Date().getFullYear()} Vision Studios. All rights reserved.</p><p>London · Dublin · Paris · Istanbul</p></div>
</footer>`
}

export function layout(ctx, { title, description, body, image, jsonLd }) {
  const site = ctx.data.site
  const canonical = (ctx.baseUrl || '') + ctx.path
  const ogImage = image ? (ctx.baseUrl || '') + image : ''
  return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>${esc(title)}</title>
<meta name="description" content="${esc(description)}"/>
<link rel="canonical" href="${canonical}"/>
<meta property="og:type" content="website"/><meta property="og:site_name" content="${esc(site.name)}"/><meta property="og:title" content="${esc(title)}"/><meta property="og:description" content="${esc(description)}"/>${ogImage ? `<meta property="og:image" content="${ogImage}"/>` : ''}
<meta name="twitter:card" content="summary_large_image"/>
<link rel="icon" href="/static/img/logo-mark.png"/>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet"/>
<link href="/static/style.css" rel="stylesheet"/>
${jsonLd ? `<script type="application/ld+json">${JSON.stringify(jsonLd)}</script>` : ''}
</head>
<body class="bg-black text-white antialiased">
${header(ctx)}
<main>${body}</main>
${footer(ctx)}
<div id="lightbox" class="fixed inset-0 z-[60] bg-black/95 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Image viewer"><button id="lightbox-close" class="absolute top-5 right-6 text-white/70 hover:text-white text-3xl" aria-label="Close">&times;</button><button id="lightbox-prev" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/60 hover:text-accent text-3xl px-3" aria-label="Previous">&#8249;</button><img id="lightbox-img" src="" alt="" class="max-h-[88vh] max-w-full object-contain"/><button id="lightbox-next" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/60 hover:text-accent text-3xl px-3" aria-label="Next">&#8250;</button><p id="lightbox-caption" class="absolute bottom-5 inset-x-0 text-center font-mono-tag text-[11px] uppercase tracking-[0.2em] text-white/50"></p></div>
<script src="/static/app.js"></script>
</body></html>`
}

/** Studio card used on home city cards, city pages and "other studios" strips. */
export function studioCard(s, ctx, { size = 'md' } = {}) {
  const tag = ctx.data.curated.studioTags[s.slug] || cityName(s.city)
  const area = s.areaSqm ? `${s.areaSqm} sq. mt. studio` : (s.highlights[0] || '')
  const img = s.gallery[0] || ''
  const cls = size === 'lg' ? 'aspect-[4/3]' : 'aspect-[3/4]'
  return `<a href="/studios/${s.slug}/" class="group fade-up img-zoom relative ${cls} block overflow-hidden bg-white/5">
<img src="${img}" alt="${esc(s.title)}" loading="lazy" class="absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"/>
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>
<div class="absolute bottom-0 left-0 p-4"><p class="font-mono-tag text-[10px] uppercase tracking-[0.15em] text-accent mb-1">${esc(tag)}</p><h3 class="font-display uppercase text-2xl leading-none">${esc(s.title)}</h3><p class="font-mono-tag text-[10px] text-white/50 mt-1">${esc(area)}</p></div></a>`
}

export function newsCard(n) {
  return `<a href="/news/${n.slug}/" class="group fade-up block">
<div class="img-zoom relative aspect-[16/10] overflow-hidden bg-white/5"><img src="${n.image}" alt="${esc(n.title)}" loading="lazy" class="absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"/></div>
<p class="font-mono-tag text-[10px] uppercase tracking-[0.2em] text-white/40 mt-4">${fmtDate(n.date)}</p>
<h3 class="font-display uppercase text-xl leading-tight mt-2 group-hover:text-accent transition-colors">${esc(n.title)}</h3>
<p class="text-white/60 text-sm mt-2 line-clamp-3">${esc(n.excerpt)}</p></a>`
}

export function ctaBand(ctx, { heading = 'Let\'s Make<br/><span class="text-accent">Something Worth<br/>Watching.</span>', text = 'Tell us about your production. We\'ll match you with a studio, a crew and a quote — usually within a working day.' } = {}) {
  return `<section id="contact" class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-10 items-center">
<h2 class="fade-up font-display uppercase text-[clamp(2.4rem,7vw,4.5rem)] leading-[0.95]">${heading}</h2>
<div class="fade-up"><p class="text-white/70 max-w-md mb-6">${text}</p><div class="flex flex-wrap gap-4">${btnWhite('/contact/', 'Start a Booking')}${btnGhost('mailto:' + ctx.data.site.email, ctx.data.site.email)}</div></div>
</div></section>`
}

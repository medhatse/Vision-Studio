import { esc, eyebrow, h2, btnPrimary, btnWhite, btnGhost, layout, newsCard, ctaBand, icon, img, heroImg } from './helpers.mjs'

export function renderHome(ctx) {
  const { site, studios, cities, home, news, curated } = ctx.data
  const hero = curated.hero

  const stats = curated.stats.map((s) => `<div class="fade-up"><div class="font-display text-4xl sm:text-5xl">${esc(s.value)}</div><div class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.15em] text-white/50 mt-1">${esc(s.label)}</div></div>`).join('')

  const cityCards = cities.map((c) => {
    const cs = studios.filter((s) => s.city === c.slug)
    const meta = curated.cities[c.slug]
    const flagship = Math.max(...cs.map((s) => s.areaSqm || 0))
    return `<a href="/${c.slug}/" class="group fade-up img-zoom relative aspect-[3/4] block overflow-hidden bg-white/5">
${img(ctx, meta.image, `Vision Studios ${c.name}, ${meta.country}`, { sizes: '(min-width: 768px) 25vw, 50vw', cls: 'absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500' })}
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>
<div class="absolute bottom-0 left-0 p-4"><p class="font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.15em] text-accent mb-1">${esc(meta.tag)}</p><h3 class="font-display uppercase text-2xl leading-none">${esc(c.name)}</h3><p class="font-mono-tag text-xs lg:text-[10px] text-white/50 mt-1">${cs.length} studio${cs.length > 1 ? 's' : ''} · up to ${flagship} sq. mt.</p></div></a>`
  }).join('')

  const services = curated.services.map((s, i) => `<div class="fade-up service-card bg-black p-8 flex flex-col gap-4">${icon(s.icon, 'text-2xl text-accent')}<h3 class="font-display uppercase text-lg">${esc(s.title)}</h3><p class="text-white/60 text-sm leading-relaxed">${esc(s.description)}</p><p class="font-mono-tag text-xs lg:text-[10px] text-white/50 mt-auto pt-2">${String(i + 1).padStart(2, '0')} / ${String(curated.services.length).padStart(2, '0')}</p></div>`).join('')

  const gallery = curated.gallery.map((g) => `<button type="button" data-lightbox="home" data-src="${g.image}" data-caption="${esc(g.alt)}" class="fade-up img-zoom relative aspect-[4/3] overflow-hidden bg-white/5 md:col-span-${g.span} text-left">${img(ctx, g.image, g.alt, { sizes: g.span === 2 ? '(min-width: 768px) 50vw, 100vw' : '(min-width: 768px) 25vw, 100vw', cls: 'absolute inset-0 w-full h-full object-cover grayscale' })}</button>`).join('')

  const why = curated.why.map((w, i) => `<div class="flex gap-6 py-5"><span class="font-mono-tag text-accent text-sm pt-1">${String(i + 1).padStart(2, '0')}</span><div><h3 class="font-display uppercase text-lg">${esc(w.title)}</h3><p class="text-white/60 text-sm mt-1">${esc(w.description)}</p></div></div>`).join('')

  const whyChoose = home.whyChoose.map((w) => `<div class="fade-up border-t border-white/10 pt-5"><h3 class="font-display uppercase text-lg">${esc(w.title)}</h3><p class="text-white/60 text-sm mt-2 leading-relaxed">${esc(w.description)}</p></div>`).join('')

  const clients = home.clients.map((c) => {
    const name = curated.clientNames[c.name] || c.name
    return `<div class="client-logo flex items-center justify-center h-28 p-6 border-white/10 [&:not(:nth-child(5n))]:border-r [&:not(:nth-last-child(-n+5))]:border-b sm:[&:not(:nth-child(3n))]:border-r">${img(ctx, c.logo, name + ' logo', { sizes: '200px', cls: 'max-h-14 max-w-full object-contain opacity-70 grayscale transition-all duration-300' })}</div>`
  }).join('')

  const counters = home.counters.map((c) => `<div class="fade-up"><div class="font-display text-4xl sm:text-5xl"><span data-count="${c.value}">0</span>${esc(c.prefix)}</div><div class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.15em] text-white/50 mt-1">${esc(c.label.replace(/​/g, ''))}</div></div>`).join('')

  const latest = news.slice(0, 3).map((n) => newsCard(n, ctx)).join('')

  const body = `
<section id="top" class="relative min-h-screen flex flex-col justify-end overflow-hidden">
${heroImg(ctx, hero.image, hero.imageAlt)}
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/70 to-black/30"></div><div class="grain-overlay"></div>
<div class="relative z-10 max-w-7xl w-full mx-auto px-6 lg:px-10 pt-40 pb-16">
<div class="flex items-center justify-between font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/60 mb-6"><span>${esc(hero.eyebrowLeft)}</span><span class="hidden sm:inline text-accent">${esc(hero.eyebrowCenter)}</span><span class="hidden sm:inline">${esc(hero.eyebrowRight)}</span></div>
<h1 class="font-display uppercase leading-[0.95] text-[clamp(2.4rem,8vw,5.5rem)]">Broadcast &amp;<br/>Production Studios<br/><span class="text-white/55">London · Dublin ·</span> Paris ·<br class="hidden sm:block"/><span class="text-white/55">Istanbul</span></h1>
<p class="mt-6 max-w-xl text-white/70 text-base sm:text-lg">${esc(home.tagline)}. Fully equipped, modern studios for live news, TV shows, corporate advertising, virtual events and podcasts.</p>
<div class="mt-8 flex flex-wrap gap-4">${btnWhite('#studios', 'View Studios')}${btnPrimary('/contact/', 'Book a Studio')}</div>
</div></section>

<section class="bg-black border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 py-14 grid grid-cols-2 sm:grid-cols-4 gap-8">${stats}</div></section>

<section id="studios" class="bg-black py-24 lg:py-32"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-14 flex items-end justify-between flex-wrap gap-4"><div>${eyebrow('Our Studios')}${h2('Four Cities. One Production Standard.', 'max-w-3xl')}</div><p class="font-mono-tag text-xs uppercase tracking-[0.15em] text-white/55">${studios.length} studios · ${cities.length} cities</p></div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">${cityCards}</div>
</div></section>

<section id="services" class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-14">${eyebrow('Services')}${h2('End-to-End Production, Under One Roof.', 'max-w-3xl')}</div>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-white/10">${services}</div>
</div></section>

<section id="gallery" class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex items-end justify-between mb-10 flex-wrap gap-4"><div>${eyebrow('Gallery')}${h2('From Behind The Lens.')}</div>${btnGhost('/gallery/', 'View All')}</div>
<div class="grid md:grid-cols-4 gap-3">${gallery}</div>
</div></section>

<section class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-14">
<div class="fade-up">${eyebrow('Why Vision')}${h2('The Reasons Directors Keep Coming Back.')}<p class="mt-6 text-white/60 max-w-md">${esc(ctx.data.about.mission[0])}</p><div class="mt-8">${btnGhost('/about/', 'More About Us')}</div></div>
<div class="fade-up divide-y divide-white/10">${why}</div>
</div></section>

<section class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-12 max-w-3xl">${eyebrow('Why Choose Vision Studios?')}<p class="text-white/60">${esc(home.intro[1])}</p></div>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-8">${whyChoose}</div>
<div class="grid grid-cols-3 gap-8 mt-16 pt-10 border-t border-white/10">${counters}</div>
</div></section>

<section id="trusted-by" class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex items-center justify-between mb-8 flex-wrap gap-2"><p class="font-mono-tag text-xs uppercase tracking-[0.25em] text-white/50">Trusted By Broadcasters &amp; Brands</p><p class="font-mono-tag text-xs uppercase tracking-[0.25em] text-white/50">Some of Our Clients</p></div>
<div class="fade-up grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 border border-white/10">${clients}</div>
</div></section>

<section id="news" class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex items-end justify-between mb-10 flex-wrap gap-4"><div>${eyebrow('Our News')}${h2('Recently On Set.')}</div>${btnGhost('/news/', 'Explore Our News')}</div>
<div class="grid md:grid-cols-3 gap-8">${latest}</div>
</div></section>

${ctaBand(ctx)}`

  ctx.preloadHero = true
  const jsonLd = { '@type': 'ItemList', name: 'Vision Studios cities', itemListElement: cities.map((c, i) => ({ '@type': 'ListItem', position: i + 1, name: c.name, url: (ctx.baseUrl || '') + (ctx.basePath || '') + '/' + c.slug + '/' })) }
  return layout(ctx, { title: 'Broadcast & Production Studios for Hire | Vision Studios', description: home.intro[1].slice(0, 155), body, image: hero.image, jsonLd })
}

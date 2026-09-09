import { esc, eyebrow, h2, layout, ctaBand, telHref, btnGhost, img, heroImg, breadcrumbs, mapFacade, parseCoords, cityName } from './helpers.mjs'

export function renderCity(city, ctx) {
  const { studios, cities, curated } = ctx.data
  const meta = curated.cities[city.slug]
  const cs = studios.filter((s) => s.city === city.slug)
  const blurbs = new Map(city.studioBlurbs.map((b) => [b.title, b.description]))
  const hero = meta.image
  // London/Istanbul have no services block on the source site; reuse the shared copy from Dublin/Paris.
  const services = city.services.length ? city.services : (cities.find((c) => c.services.length)?.services || [])

  const studioList = cs.map((s, i) => `<article class="fade-up grid md:grid-cols-2 gap-8 items-center py-12 ${i ? 'border-t border-white/10' : ''}">
<a href="/studios/${s.slug}/" class="group img-zoom relative aspect-[16/10] block overflow-hidden bg-white/5 ${i % 2 ? 'md:order-2' : ''}">${img(ctx, s.gallery[0], `${s.title}, ${city.name}`, { sizes: '(min-width: 768px) 50vw, 100vw', cls: 'absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500' })}</a>
<div><p class="font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] text-accent mb-2">${esc(curated.studioTags[s.slug] || '')}</p><h3 class="font-display uppercase text-3xl leading-none"><a href="/studios/${s.slug}/" class="hover:text-accent">${esc(s.title)}</a></h3>
<p class="text-white/60 text-sm mt-4 leading-relaxed">${esc(blurbs.get(s.title) || '')}</p>
<ul class="mt-5 grid sm:grid-cols-2 gap-x-6 gap-y-1 font-mono-tag text-xs lg:text-[11px] text-white/50">${s.highlights.slice(0, 4).map((h) => `<li class="flex gap-2"><span class="text-accent">—</span>${esc(h)}</li>`).join('')}</ul>
<div class="mt-6">${btnGhost('/studios/' + s.slug + '/', 'Studio details & booking')}</div></div></article>`).join('')

  const svc = services.map((s, i) => `<div class="fade-up service-card bg-black p-8"><p class="font-mono-tag text-xs lg:text-[10px] text-white/50 mb-3">0${i + 1}</p><h3 class="font-display uppercase text-lg">${esc(s.title)}</h3><p class="text-white/60 text-sm leading-relaxed mt-3">${esc(s.description)}</p></div>`).join('')

  const others = cities.filter((c) => c.slug !== city.slug).map((c) => `<a href="/${c.slug}/" class="nav-link font-mono-tag text-xs uppercase tracking-[0.15em] text-white/70 hover:text-accent">${c.name}</a>`).join('<span class="text-white/20">·</span>')

  const crumbs = [['Home', '/'], ['Studios', '/studios/'], [city.name, `/${city.slug}/`]]
  const body = `
<section class="relative min-h-[70vh] flex flex-col justify-end overflow-hidden">
${heroImg(ctx, hero, `Vision Studios ${city.name}`)}
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/70 to-black/30"></div><div class="grain-overlay"></div>
<div class="relative z-10 max-w-7xl w-full mx-auto px-6 lg:px-10 pt-40 pb-16">
<div class="flex items-center justify-between font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/60 mb-6"><span>${esc(meta.tag)}</span><span class="hidden sm:inline text-accent">${esc(meta.country)}</span><span class="hidden sm:inline">${esc(meta.coords)}</span></div>
<h1 class="font-display uppercase leading-[0.95] text-[clamp(2.4rem,8vw,5.5rem)]">${esc(city.heading.replace(/^Our /, '')).replace(' in ', '<br/><span class="text-white/55">in </span>')}</h1>
<p class="mt-6 max-w-xl text-white/70">${cs.length} studio${cs.length > 1 ? 's' : ''} · ${esc(meta.address)}</p>
</div></section>
${breadcrumbs(crumbs)}

<section class="bg-black py-16 lg:py-24"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-4">${eyebrow('Our Studios In ' + city.name)}${h2('Choose Your Space.', 'max-w-3xl')}</div>
${studioList}
</div></section>

<section class="bg-black py-24 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-12">${eyebrow('Our Services')}${h2('Everything Around The Studio.', 'max-w-3xl')}</div>
<div class="grid md:grid-cols-3 gap-px bg-white/10">${svc}</div>
</div></section>

<section class="bg-black py-16 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-10">
<div class="fade-up">${eyebrow('Get In Touch · ' + city.name)}<p class="font-display uppercase text-2xl"><a href="${telHref(meta.phone)}" class="hover:text-accent">${esc(meta.phone)}</a></p><p class="text-white/60 text-sm mt-3">${esc(meta.address)}</p><p class="text-white/60 text-sm mt-1"><a href="mailto:${ctx.data.site.email}" class="hover:text-accent">${ctx.data.site.email}</a></p><div class="mt-6 flex items-center gap-4 flex-wrap"><span class="font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] text-white/50">Other cities</span>${others}</div></div>
<div class="fade-up aspect-[16/9] bg-white/5 overflow-hidden">${mapFacade(meta.mapQuery, 'Map of Vision Studios ' + city.name)}</div>
</div></section>

${ctaBand(ctx, { heading: `Book A Studio<br/><span class="text-accent">In ${esc(city.name)}.</span>` })}`

  ctx.preloadHero = true
  const abs = (u) => (ctx.baseUrl || '') + (ctx.basePath || '') + u
  const jsonLd = [
    { '@type': 'LocalBusiness', '@id': abs(`/${city.slug}/#location`), name: `Vision Studios ${city.name}`, url: abs(`/${city.slug}/`), image: abs(hero), telephone: meta.phone, email: ctx.data.site.email, parentOrganization: { '@id': abs('/#organization') }, address: { '@type': 'PostalAddress', streetAddress: meta.address, addressLocality: city.name, addressCountry: meta.country }, geo: parseCoords(meta.coords) },
    { '@type': 'CollectionPage', name: `${city.name} TV & Production Studios for Hire`, url: abs(`/${city.slug}/`), mainEntity: { '@type': 'ItemList', itemListElement: cs.map((s, i) => ({ '@type': 'ListItem', position: i + 1, name: s.title, url: abs(`/studios/${s.slug}/`) })) } },
  ]
  return layout(ctx, { title: `${city.name} TV & Production Studios for Hire | Vision Studios`, description: `Fully equipped broadcast and production studios for hire in ${city.name}, ${meta.country}: ${cs.map((s) => s.title).join(', ')}.`, body, image: hero, jsonLd, crumbs })
}

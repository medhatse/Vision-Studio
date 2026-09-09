import { esc, eyebrow, h2, layout, newsCard, ctaBand, fmtDate, telHref, btnGhost, btnWhite } from './helpers.mjs'

const pageHero = (title, sub, image, eyebrowText) => `<section class="relative min-h-[55vh] flex flex-col justify-end overflow-hidden">
<img src="${image}" alt="" class="absolute inset-0 w-full h-full object-cover" fetchpriority="high"/><div class="absolute inset-0 bg-gradient-to-t from-black via-black/75 to-black/40"></div><div class="grain-overlay"></div>
<div class="relative z-10 max-w-7xl w-full mx-auto px-6 lg:px-10 pt-40 pb-14">${eyebrow(eyebrowText)}<h1 class="font-display uppercase leading-[0.95] text-[clamp(2.4rem,7vw,5rem)]">${title}</h1>${sub ? `<p class="mt-6 max-w-2xl text-white/70">${sub}</p>` : ''}</div></section>`

export function renderNewsIndex(ctx) {
  const { news } = ctx.data
  const [first, ...rest] = news
  const body = `${pageHero('Our News.', 'Productions, live events and behind-the-scenes stories from our studios in London, Dublin, Paris and Istanbul.', first.image, `${news.length} stories`)}
<section class="bg-black py-20"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<a href="/news/${first.slug}/" class="group fade-up grid lg:grid-cols-2 gap-10 items-center mb-20"><div class="img-zoom relative aspect-[16/10] overflow-hidden bg-white/5"><img src="${first.image}" alt="${esc(first.title)}" class="absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"/></div><div>${eyebrow('Latest · ' + fmtDate(first.date))}<h2 class="font-display uppercase text-[clamp(1.8rem,4vw,3rem)] leading-[0.95] group-hover:text-accent transition-colors">${esc(first.title)}</h2><p class="text-white/60 mt-5 max-w-lg">${esc(first.excerpt)}</p></div></a>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-14">${rest.map(newsCard).join('')}</div></div></section>
${ctaBand(ctx)}`
  return layout(ctx, { title: 'News | Vision Studios', description: 'Latest productions, live broadcasts and behind-the-scenes stories from Vision Studios.', body, image: first.image })
}

export function renderPost(n, ctx) {
  const { news } = ctx.data
  const idx = news.indexOf(n)
  const prev = news[idx + 1], next = news[idx - 1]
  const paragraphs = n.paragraphs.map((p) => /^[^.]{3,70}$/.test(p) && !/[.!?…]$/.test(p) && p.split(' ').length < 12 ? `<h2 class="font-display uppercase text-2xl mt-10 mb-3">${esc(p)}</h2>` : `<p class="text-white/75 leading-relaxed mb-5">${esc(p)}</p>`).join('')
  const images = n.images.filter((i) => i !== n.image)
  const gallery = images.length ? `<div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-12">${images.map((img, i) => `<button type="button" data-lightbox="post" data-src="${img}" data-caption="${esc(n.title)}" class="img-zoom relative aspect-[4/3] overflow-hidden bg-white/5"><img src="${img}" alt="${esc(n.title)} photo ${i + 1}" loading="lazy" class="absolute inset-0 w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500"/></button>`).join('')}</div>` : ''
  const videos = n.videos.map((v) => { const id = v.match(/(?:youtu\.be\/|v=)([\w-]+)/)?.[1]; return id ? `<div class="aspect-video mt-12 bg-white/5"><iframe class="w-full h-full" src="https://www.youtube-nocookie.com/embed/${id}" title="Video" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>` : '' }).join('')
  const body = `${pageHero(esc(n.title), '', n.image, fmtDate(n.date))}
<article class="bg-black py-16"><div class="max-w-3xl mx-auto px-6 lg:px-10">${paragraphs}${videos}${gallery}
<div class="mt-16 pt-8 border-t border-white/10 flex justify-between gap-6 font-mono-tag text-xs uppercase tracking-[0.15em]">${prev ? `<a href="/news/${prev.slug}/" class="nav-link text-white/60 hover:text-accent">← ${esc(prev.title.slice(0, 40))}${prev.title.length > 40 ? '…' : ''}</a>` : '<span></span>'}${next ? `<a href="/news/${next.slug}/" class="nav-link text-white/60 hover:text-accent text-right">${esc(next.title.slice(0, 40))}${next.title.length > 40 ? '…' : ''} →</a>` : ''}</div>
<p class="mt-8"><a href="/news/" class="font-mono-tag text-xs uppercase tracking-[0.15em] text-accent">All news</a></p></div></article>
${ctaBand(ctx)}`
  const jsonLd = { '@context': 'https://schema.org', '@type': 'NewsArticle', headline: n.title, datePublished: n.date, image: n.image, publisher: { '@type': 'Organization', name: 'Vision Studios' } }
  return layout(ctx, { title: `${n.title} | Vision Studios News`, description: n.excerpt.slice(0, 160), body, image: n.image, jsonLd })
}

export function renderAbout(ctx) {
  const { about, home, curated } = ctx.data
  const block = (label, text) => `<div class="fade-up">${eyebrow(label)}<p class="text-white/70 leading-relaxed">${esc(text)}</p></div>`
  const cards = about.cards.map((c) => `<div class="fade-up relative aspect-[4/5] overflow-hidden bg-white/5 group"><img src="${c.image}" alt="" loading="lazy" class="absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-700"/><div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-black/10"></div><div class="absolute bottom-0 p-6"><h3 class="font-display uppercase text-2xl">${esc(c.title)}</h3><p class="text-white/70 text-sm mt-3 leading-relaxed">${esc(c.description)}</p></div></div>`).join('')
  const team = about.team.map((t) => `<li class="flex items-center gap-3 font-display uppercase text-xl"><i class="fa-solid fa-check text-accent text-sm"></i>${esc(t)}</li>`).join('')
  const counters = home.counters.map((c) => `<div><div class="font-display text-4xl sm:text-5xl"><span data-count="${c.value}">0</span>${esc(c.prefix)}</div><div class="font-mono-tag text-[11px] uppercase tracking-[0.15em] text-white/50 mt-1">${esc(c.label.replace(/​/g, ''))}</div></div>`).join('')
  const body = `${pageHero('Who Is<br/><span class="text-accent">Vision Studios.</span>', esc(about.who[0]), about.images[0], 'About Us')}
<section class="bg-black py-20"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid md:grid-cols-3 gap-12">${block('25+ Years of Experience', about.experience[0])}${block('Our Vision', about.vision[0])}${block('Our Mission', about.mission[0])}</div></section>
<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-14 items-center"><div class="fade-up">${eyebrow('More About Us')}${h2('Experts In Their Fields.')}<p class="text-white/60 mt-6 max-w-md">In addition to our state-of-the-art equipment and well-maintained facilities, we offer a wide range of services to meet all your production needs. Our team of:</p><ul class="mt-6 space-y-3">${team}</ul><p class="text-white/60 mt-6 max-w-md">…are experts in their fields and will ensure that your broadcast runs smoothly from start to finish.</p></div><div class="grid grid-cols-3 gap-8 fade-up">${counters}</div></div></section>
<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10"><div class="fade-up mb-12">${eyebrow('Unleash Your Creative Vision')}${h2('Bespoke. Showcase. Community.')}</div><div class="grid md:grid-cols-3 gap-4">${cards}</div></div></section>
<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid md:grid-cols-2 gap-12"><div class="fade-up">${eyebrow('Sustainability')}<h3 class="font-display uppercase text-2xl">${esc(home.sustainability[0])}</h3><p class="text-white/60 mt-4 leading-relaxed">${esc(home.sustainability[1])}</p></div><div class="fade-up">${eyebrow('We Follow Best Practices')}<h3 class="font-display uppercase text-2xl">Quality, Safety, Professionalism.</h3><p class="text-white/60 mt-4 leading-relaxed">${esc(home.bestPractices[0])}</p><ul class="mt-5 flex flex-wrap gap-3 font-mono-tag text-[10px] uppercase tracking-[0.2em] text-white/50">${home.bestPractices.slice(1).map((b) => `<li class="border border-white/15 px-3 py-2">${esc(b)}</li>`).join('')}</ul></div></div></section>
${ctaBand(ctx)}`
  return layout(ctx, { title: 'About Us | Vision Studios', description: about.who[0], body, image: about.images[0] })
}

export function renderContact(ctx) {
  const { contact, site, cities, curated } = ctx.data
  const regions = contact.regions.map((r) => `<div class="fade-up border border-white/10 p-6"><p class="font-mono-tag text-[10px] uppercase tracking-[0.2em] text-accent mb-3">${esc(r.region)}</p><p class="font-display uppercase text-2xl"><a href="${telHref(r.phone)}" class="hover:text-accent">${esc(r.phone)}</a></p><p class="text-white/60 text-sm mt-2"><a href="mailto:${site.email}" class="hover:text-accent">${site.email}</a></p></div>`).join('')
  const locations = cities.map((c) => { const m = curated.cities[c.slug]; return `<div class="fade-up py-5 border-t border-white/10 grid sm:grid-cols-[8rem_1fr_auto] gap-3 items-start"><h3 class="font-display uppercase text-xl"><a href="/${c.slug}/" class="hover:text-accent">${c.name}</a></h3><p class="text-white/60 text-sm">${esc(m.address)}</p><a href="${telHref(m.phone)}" class="font-mono-tag text-xs text-white/70 hover:text-accent whitespace-nowrap">${esc(m.phone)}</a></div>` }).join('')
  const body = `${pageHero('Contact<br/><span class="text-accent">Vision Studios.</span>', esc(contact.intro[1]), curated.cities.london.image, 'Get In Touch')}
<section class="bg-black py-20"><div class="max-w-7xl mx-auto px-6 lg:px-10"><p class="fade-up text-white/60 max-w-3xl mb-12 leading-relaxed">${esc(contact.intro[0])}</p><div class="grid md:grid-cols-3 gap-4">${regions}</div></div></section>
<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-14">
<div class="fade-up">${eyebrow('Send A Message')}${h2('Start A<br/><span class="text-accent">Conversation.</span>')}<p class="text-white/60 mt-6 max-w-md">Tell us about your production and where you would like to shoot. We reply within a working day.</p><div class="mt-10">${eyebrow('Our Locations')}${locations}</div><div class="mt-8 flex gap-3">${site.social.instagram ? `<a href="${site.social.instagram}" target="_blank" rel="noopener" class="nav-link font-mono-tag text-xs uppercase tracking-[0.15em] text-white/70">Instagram</a>` : ''}${site.social.linkedin ? `<a href="${site.social.linkedin}" target="_blank" rel="noopener" class="nav-link font-mono-tag text-xs uppercase tracking-[0.15em] text-white/70">LinkedIn</a>` : ''}</div></div>
<form class="fade-up booking-form grid gap-4 self-start" data-to="${site.email}">
<label class="block"><span class="font-mono-tag text-[10px] uppercase tracking-[0.2em] text-white/50">Name *</span><input name="name" type="text" required class="mt-2 w-full bg-black border border-white/20 px-4 py-3 text-sm focus:border-accent outline-none"/></label>
<label class="block"><span class="font-mono-tag text-[10px] uppercase tracking-[0.2em] text-white/50">Email *</span><input name="email" type="email" required class="mt-2 w-full bg-black border border-white/20 px-4 py-3 text-sm focus:border-accent outline-none"/></label>
<label class="block"><span class="font-mono-tag text-[10px] uppercase tracking-[0.2em] text-white/50">Studio / city</span><select name="studio" class="mt-2 w-full bg-black border border-white/20 px-4 py-3 text-sm focus:border-accent outline-none"><option value="">Not sure yet</option>${ctx.data.studios.map((s) => `<option>${esc(s.title)}</option>`).join('')}</select></label>
<label class="block"><span class="font-mono-tag text-[10px] uppercase tracking-[0.2em] text-white/50">Message *</span><textarea name="message" rows="6" required class="mt-2 w-full bg-black border border-white/20 px-4 py-3 text-sm focus:border-accent outline-none"></textarea></label>
<div class="flex flex-wrap items-center gap-4"><button type="submit" class="btn-primary inline-flex items-center gap-2 bg-accent text-black font-mono-tag text-xs uppercase tracking-[0.15em] px-6 py-4">Send <i class="fa-solid fa-arrow-up-right text-[10px]"></i></button><p class="text-white/40 text-xs">Opens your email client with the message pre-filled.</p></div>
</form></div></section>`
  return layout(ctx, { title: 'Contact Us | Vision Studios', description: contact.intro[1], body, image: curated.cities.london.image })
}

export function renderGallery(ctx) {
  const { studios, curated } = ctx.data
  const items = studios.flatMap((s) => s.gallery.map((g, i) => ({ src: g, studio: s, i })))
  const filters = ['all', ...new Set(studios.map((s) => s.city))]
  const body = `${pageHero('From Behind<br/><span class="text-accent">The Lens.</span>', `${items.length} photographs from ${studios.length} studios across four cities.`, curated.gallery[2].image, 'Gallery')}
<section class="bg-black py-16"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex flex-wrap gap-2 mb-10" id="gallery-filters">${filters.map((f, i) => `<button type="button" data-filter="${f}" class="gallery-filter font-mono-tag text-[10px] uppercase tracking-[0.2em] px-4 py-2 border ${i ? 'border-white/15 text-white/60' : 'border-accent text-accent'}">${f === 'all' ? 'All cities' : f}</button>`).join('')}</div>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" id="gallery-grid">${items.map(({ src, studio, i }) => `<button type="button" data-city="${studio.city}" data-lightbox="all" data-src="${src}" data-caption="${esc(studio.title)}" class="gallery-item fade-up img-zoom relative aspect-[4/3] overflow-hidden bg-white/5 group"><img src="${src}" alt="${esc(studio.title)} photo ${i + 1}" loading="lazy" class="absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"/><span class="absolute bottom-2 left-3 font-mono-tag text-[10px] uppercase tracking-[0.15em] text-white/70 opacity-0 group-hover:opacity-100 transition-opacity">${esc(studio.title)}</span></button>`).join('')}</div>
</div></section>${ctaBand(ctx)}`
  return layout(ctx, { title: 'Gallery | Vision Studios', description: 'Photographs from every Vision Studios space in London, Dublin, Paris and Istanbul.', body, image: curated.gallery[2].image })
}

export function render404(ctx) {
  const body = `<section class="min-h-[80vh] flex items-center"><div class="max-w-7xl mx-auto px-6 lg:px-10 pt-40 pb-20">${eyebrow('404')}<h1 class="font-display uppercase text-[clamp(2.4rem,8vw,5.5rem)] leading-[0.95]">Off Air.<br/><span class="text-white/40">Page Not Found.</span></h1><div class="mt-8">${btnWhite('/', 'Back to the studios')}</div></div></section>`
  return layout(ctx, { title: 'Page not found | Vision Studios', description: 'Page not found.', body })
}

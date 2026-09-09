import { esc, eyebrow, h2, layout, studioCard, telHref, cityName, btnPrimary, btnGhost } from './helpers.mjs'

const ICONS = [/sq\.? ?mt|area/i, /decorat|isolat|stage|customi/i, /video wall|LED screen|screen/i, /gallery|control room/i, /camera/i, /generator|UPS|electric/i, /mixer|audio|microphone/i, /jib|jip|tripod/i, /light/i]
const ICON_CLASS = ['fa-vector-square', 'fa-couch', 'fa-tv', 'fa-sliders', 'fa-video', 'fa-bolt', 'fa-microphone-lines', 'fa-arrows-up-down-left-right', 'fa-lightbulb']
const iconFor = (text) => { const i = ICONS.findIndex((re) => re.test(text)); return ICON_CLASS[i >= 0 ? i : 0] }

export function renderStudio(s, ctx) {
  const { studios, curated, site } = ctx.data
  const meta = curated.cities[s.city]
  const tag = curated.studioTags[s.slug] || ''
  const phone = s.contact.phone && s.contact.phone.length > 8 ? s.contact.phone : meta.phone
  // Prefer the Google-Maps query the source site uses (well-formed), fall back to the city address.
  const address = s.address && !/vm cloud/i.test(s.address) ? s.address.replace(/\s+/g, ' ') : (s.contact.address || meta.address)
  const mapQuery = s.address && !/vm cloud/i.test(s.address) ? s.address : meta.mapQuery
  const gallery = s.gallery.length ? s.gallery : [meta.image]

  const slides = gallery.map((g, i) => `<div class="hero-slide absolute inset-0 transition-opacity duration-1000 ${i ? 'opacity-0' : 'opacity-100'}" data-slide="${i}"><img src="${g}" alt="${esc(s.title)} — view ${i + 1}" class="w-full h-full object-cover" ${i ? 'loading="lazy"' : 'fetchpriority="high"'}/></div>`).join('')
  const dots = gallery.map((_, i) => `<button type="button" class="hero-dot w-8 h-[2px] ${i ? 'bg-white/30' : 'bg-accent'}" data-goto="${i}" aria-label="Show image ${i + 1}"></button>`).join('')

  const highlights = s.highlights.map((h) => `<div class="fade-up flex items-start gap-4 p-6 bg-black"><i class="fa-solid ${iconFor(h)} text-accent text-xl mt-1 w-6 text-center"></i><p class="text-sm text-white/80 leading-snug">${esc(h)}</p></div>`).join('')
  const specs = (s.specs.length ? s.specs : s.highlights).map((sp) => `<li class="flex gap-3 py-2.5 border-b border-white/10 text-sm text-white/75"><span class="text-accent font-mono-tag text-xs pt-0.5">—</span>${esc(sp.replace(/\.$/, ''))}</li>`).join('')

  const useCases = s.useCases.length ? `<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-10">${eyebrow('What To Shoot Here')}${h2(esc(s.useCasesHeading || 'Built for a wide range of productions.'), 'max-w-4xl text-[clamp(1.6rem,3.5vw,2.5rem)]')}</div>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-white/10">${s.useCases.map((u, i) => `<div class="fade-up service-card bg-black p-8"><p class="font-mono-tag text-[10px] text-white/30 mb-3">0${i + 1}</p><h3 class="font-display uppercase text-lg">${esc(u.title)}</h3><p class="text-white/60 text-sm leading-relaxed mt-3">${esc(u.description)}</p></div>`).join('')}</div></div></section>` : ''

  const thumbs = gallery.map((g, i) => `<button type="button" data-lightbox="studio" data-src="${g}" data-caption="${esc(s.title)} — ${i + 1} / ${gallery.length}" class="fade-up img-zoom relative aspect-[4/3] overflow-hidden bg-white/5"><img src="${g}" alt="${esc(s.title)} photo ${i + 1}" loading="lazy" class="absolute inset-0 w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500"/></button>`).join('')

  const plan = s.floorPlan ? `<div class="fade-up"><p class="font-mono-tag text-xs uppercase tracking-[0.25em] text-accent mb-4">Floor Plan</p><button type="button" data-lightbox="plan" data-src="${s.floorPlan}" data-caption="${esc(s.title)} floor plan" class="block w-full bg-white/5 p-4"><img src="${s.floorPlan}" alt="${esc(s.title)} floor plan" loading="lazy" class="w-full h-auto"/></button></div>` : ''

  const siblings = studios.filter((o) => o.city === s.city && o.slug !== s.slug)
  const otherCities = studios.filter((o) => o.city !== s.city).sort(() => 0).slice(0, 4 - Math.min(siblings.length, 4))
  const related = [...siblings.slice(0, 4), ...(siblings.length < 4 ? otherCities : [])].slice(0, 4)

  const body = `
<section class="relative min-h-[80vh] flex flex-col justify-end overflow-hidden" id="hero-slider">
${slides}
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-black/20 pointer-events-none"></div><div class="grain-overlay"></div>
<div class="relative z-10 max-w-7xl w-full mx-auto px-6 lg:px-10 pt-40 pb-14">
<div class="flex items-center justify-between font-mono-tag text-[11px] uppercase tracking-[0.2em] text-white/60 mb-6"><a href="/${s.city}/" class="hover:text-accent">← ${cityName(s.city)} studios</a><span class="hidden sm:inline text-accent">${esc(tag)}</span><span class="hidden sm:inline">${esc(meta.coords)}</span></div>
<h1 class="font-display uppercase leading-[0.95] text-[clamp(2.4rem,8vw,5.5rem)]">${esc(s.title)}</h1>
<p class="mt-5 max-w-xl text-white/70">${s.areaSqm ? `${s.areaSqm} sq. mt. studio area · ` : ''}${esc(cityName(s.city))}, ${esc(meta.country)}</p>
<div class="mt-8 flex flex-wrap items-center gap-6">${btnPrimary('#book', 'Book This Studio')}<div class="flex items-center gap-2">${dots}</div></div>
</div></section>

<section class="bg-black border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 py-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-white/10 border border-white/10">${highlights}</div></section>

<section class="bg-black py-20"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-5 gap-14">
<div class="lg:col-span-3 fade-up">${eyebrow('Description')}${h2('Full Specification.', 'mb-8')}${s.specs.length ? `<ul>${specs}</ul>` : `<ul>${specs}</ul><p class="text-white/40 text-sm mt-6">Need a longer equipment list for this studio? <a href="mailto:${site.email}" class="text-accent">Email us</a> and we will send the full inventory.</p>`}</div>
<div class="lg:col-span-2 space-y-10">${plan}
<div class="fade-up border border-white/10 p-6"><p class="font-mono-tag text-xs uppercase tracking-[0.25em] text-accent mb-4">Contact</p><p class="font-display uppercase text-2xl"><a href="${telHref(phone)}" class="hover:text-accent">${esc(phone)}</a></p><p class="text-white/60 text-sm mt-2"><a href="mailto:${site.email}" class="hover:text-accent">${site.email}</a></p><p class="text-white/60 text-sm mt-3 leading-relaxed">${esc(address)}</p></div>
</div></div></section>

<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex items-end justify-between mb-10 flex-wrap gap-4"><div>${eyebrow('Gallery')}${h2(esc(s.title) + ' In Pictures.')}</div><p class="font-mono-tag text-xs uppercase tracking-[0.15em] text-white/40">${gallery.length} photos</p></div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3">${thumbs}</div></div></section>

${useCases}

<section id="book" class="bg-black py-24 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-14">
<div class="fade-up">${eyebrow('Book ' + esc(s.title))}${h2('Tell Us About<br/><span class="text-accent">Your Production.</span>')}<p class="mt-6 text-white/60 max-w-md">Send us your dates and a short brief. We reply with availability, a crew recommendation and a quote — usually within a working day.</p>
<div class="mt-8 aspect-[16/10] bg-white/5 overflow-hidden"><iframe title="Map of ${esc(s.title)}" src="https://maps.google.com/maps?q=${encodeURIComponent(mapQuery)}&t=m&z=16&output=embed&iwloc=near" loading="lazy" class="w-full h-full grayscale invert-[.9] contrast-[.9]" referrerpolicy="no-referrer-when-downgrade"></iframe></div></div>
<form class="fade-up booking-form grid sm:grid-cols-2 gap-4" data-studio="${esc(s.title)}" data-to="${site.email}">
${field('name', 'Your name', 'text', true)}${field('email', 'Your email', 'email', true)}${field('company', 'Your company name', 'text')}${field('phone', 'Your phone no.', 'tel')}
${field('checkin', 'Check-in', 'date')}${field('checkout', 'Check-out', 'date')}
<label class="sm:col-span-2 block"><span class="font-mono-tag text-[10px] uppercase tracking-[0.2em] text-white/50">Where did you hear about us</span><select name="source" class="mt-2 w-full bg-black border border-white/20 px-4 py-3 text-sm focus:border-accent outline-none"><option>Google</option><option>Social network</option><option>Referral</option><option>Other</option></select></label>
<label class="sm:col-span-2 block"><span class="font-mono-tag text-[10px] uppercase tracking-[0.2em] text-white/50">Your message (optional)</span><textarea name="message" rows="4" class="mt-2 w-full bg-black border border-white/20 px-4 py-3 text-sm focus:border-accent outline-none"></textarea></label>
<div class="sm:col-span-2 flex flex-wrap items-center gap-4"><button type="submit" class="btn-primary inline-flex items-center gap-2 bg-accent text-black font-mono-tag text-xs uppercase tracking-[0.15em] px-6 py-4">Send Booking Request <i class="fa-solid fa-arrow-up-right text-[10px]"></i></button><p class="text-white/40 text-xs">Opens your email client with the request pre-filled for ${site.email}.</p></div>
</form></div></section>

<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex items-end justify-between mb-10 flex-wrap gap-4"><div>${eyebrow('More Studios')}${h2('Other Spaces You Might Like.')}</div>${btnGhost('/#studios', 'All cities')}</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">${related.map((r) => studioCard(r, ctx)).join('')}</div></div></section>`

  const jsonLd = { '@context': 'https://schema.org', '@type': 'Place', name: `Vision Studios — ${s.title}`, address, telephone: phone, image: gallery[0], url: (ctx.baseUrl || '') + ctx.path }
  return layout(ctx, { title: `${s.title} | Vision Studios ${cityName(s.city)}`, description: `${s.title} for hire in ${cityName(s.city)}: ${(s.specs.length ? s.specs : s.highlights).slice(0, 4).join(' ')}`.slice(0, 160), body, image: gallery[0], jsonLd })
}

function field(name, label, type, required = false) {
  return `<label class="block"><span class="font-mono-tag text-[10px] uppercase tracking-[0.2em] text-white/50">${label}${required ? ' *' : ''}</span><input name="${name}" type="${type}" ${required ? 'required' : ''} class="mt-2 w-full bg-black border border-white/20 px-4 py-3 text-sm focus:border-accent outline-none"/></label>`
}

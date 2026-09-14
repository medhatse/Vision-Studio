// Builds wordpress/vision-studios-theme/data/tr.json (Turkish content for the Istanbul pages) from data/tr/*.json.
import { readFileSync, writeFileSync } from 'node:fs'
const read = (f) => JSON.parse(readFileSync(new URL(`../data/tr/${f}`, import.meta.url), 'utf8'))
const city = read('city.json'), studios = read('studios.json'), ui = read('ui.json')
const faqLines = (own = []) => [...own, ...city.shared_faq].map(([q, a]) => `${q} | ${a}`).join('\n')
const warn = (what, s) => { if (s.length > 158) console.warn(`  ! ${what}: description is ${s.length} chars (max 158)`) }
const out = {
  city: { name: city.name, heading: city.heading, tagline: city.tagline, description: city.intro.join('\n\n'), faq: faqLines(city.faq), seo_title: city.seo_title, seo_description: city.seo_description },
  studios: {},
  ui,
}
warn('city', city.seo_description)
for (const [slug, s] of Object.entries(studios)) {
  warn(slug, s.seo_description)
  out.studios[slug] = {
    title: s.title, tagline: s.tagline, excerpt: s.excerpt, content: s.intro.join('\n\n'),
    highlights: s.highlights.join('\n'), specs: s.specs.join('\n'),
    use_cases_heading: s.use_cases_heading, use_cases: s.use_cases.map(([t, d]) => `${t} | ${d}`).join('\n'),
    faq: faqLines(s.faq), price_from: s.price_from, seo_title: s.seo_title, seo_description: s.seo_description,
  }
}
const target = new URL('../wordpress/vision-studios-theme/data/tr.json', import.meta.url)
writeFileSync(target, JSON.stringify(out, null, 1) + '\n')
console.log(`Wrote data/tr.json: ${Object.keys(out.studios).length} studios, ${Object.keys(ui).length} UI strings`)

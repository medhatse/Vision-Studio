/** Tailwind build for the static site (dist/static/app.css). */
module.exports = {
  content: ['./src/templates/**/*.mjs', './src/static/app.js', './scripts/build.mjs'],
  safelist: ['bg-black/90', 'backdrop-blur', 'border-b', 'border-white/10', 'md:col-span-1', 'md:col-span-2', 'opacity-0', 'opacity-100', 'bg-accent', 'bg-white/30', 'border-accent', 'text-accent', 'border-white/15', 'text-white/60', 'md:order-2', 'is-visible', 'is-active', 'grayscale', 'invert-[.9]', 'contrast-[.9]'],
  theme: { extend: { colors: { accent: '#f2703a' } } },
}

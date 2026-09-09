/** Tailwind build for the WordPress theme. Run: npm run theme:css */
module.exports = {
  content: ['./vision-studios-theme/**/*.php', './vision-studios-theme/assets/app.js'],
  safelist: ['bg-black/90', 'backdrop-blur', 'border-b', 'border-white/10', 'md:col-span-1', 'md:col-span-2', 'opacity-0', 'opacity-100', 'bg-accent', 'bg-white/30', 'border-accent', 'text-accent', 'border-white/15', 'text-white/60', 'md:order-2', 'is-visible'],
  theme: { extend: { colors: { accent: '#f2703a' } } },
  corePlugins: { preflight: true },
}

// Vision Studios — frontend interactions (no dependencies)
document.addEventListener('DOMContentLoaded', () => {
  // Header background on scroll
  const header = document.getElementById('site-header')
  const onScroll = () => {
    const scrolled = window.scrollY > 40
    header.classList.toggle('bg-black/90', scrolled)
    header.classList.toggle('backdrop-blur', scrolled)
    header.classList.toggle('border-b', scrolled)
    header.classList.toggle('border-white/10', scrolled)
  }
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })

  // Mobile menu toggle
  const menuToggle = document.getElementById('menu-toggle')
  const mobileMenu = document.getElementById('mobile-menu')
  let menuOpen = false
  const setMenu = (open) => {
    menuOpen = open
    menuToggle.innerHTML = open ? '<i class="fa-solid fa-xmark"></i>' : '<i class="fa-solid fa-bars"></i>'
    menuToggle.setAttribute('aria-expanded', String(open))
    mobileMenu.style.maxHeight = open ? mobileMenu.scrollHeight + 'px' : '0px'
  }
  menuToggle.addEventListener('click', () => setMenu(!menuOpen))
  mobileMenu.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => setMenu(false)))

  // Fade-up on scroll
  const faders = document.querySelectorAll('.fade-up')
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => { if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target) } })
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' })
    faders.forEach((el) => observer.observe(el))
  } else {
    faders.forEach((el) => el.classList.add('is-visible'))
  }

  // Animated counters
  const counters = document.querySelectorAll('[data-count]')
  if (counters.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return
        io.unobserve(entry.target)
        const target = Number(entry.target.dataset.count)
        const start = performance.now()
        const tick = (now) => {
          const t = Math.min(1, (now - start) / 1600)
          entry.target.textContent = Math.round(target * (1 - Math.pow(1 - t, 3))).toLocaleString('en-GB')
          if (t < 1) requestAnimationFrame(tick)
        }
        requestAnimationFrame(tick)
      })
    }, { threshold: 0.5 })
    counters.forEach((c) => io.observe(c))
  }

  // Studio hero slider
  const slider = document.getElementById('hero-slider')
  if (slider) {
    const slides = [...slider.querySelectorAll('.hero-slide')]
    const dots = [...slider.querySelectorAll('.hero-dot')]
    let current = 0, timer
    const show = (i) => {
      current = (i + slides.length) % slides.length
      slides.forEach((s, j) => { s.classList.toggle('opacity-100', j === current); s.classList.toggle('opacity-0', j !== current) })
      dots.forEach((d, j) => { d.classList.toggle('bg-accent', j === current); d.classList.toggle('bg-white/30', j !== current) })
    }
    const play = () => { clearInterval(timer); if (slides.length > 1) timer = setInterval(() => show(current + 1), 5000) }
    dots.forEach((d) => d.addEventListener('click', () => { show(Number(d.dataset.goto)); play() }))
    play()
  }

  // Lightbox (any [data-lightbox] button; groups by the attribute value)
  const lb = document.getElementById('lightbox')
  if (lb) {
    const img = document.getElementById('lightbox-img')
    const cap = document.getElementById('lightbox-caption')
    let group = [], index = 0
    const open = (items, i) => { group = items; index = i; render(); lb.classList.add('is-open'); document.body.style.overflow = 'hidden' }
    const close = () => { lb.classList.remove('is-open'); document.body.style.overflow = '' }
    const render = () => { const it = group[index]; img.src = it.dataset.src; img.alt = it.dataset.caption || ''; cap.textContent = it.dataset.caption || '' }
    const step = (d) => { if (!group.length) return; index = (index + d + group.length) % group.length; render() }
    document.querySelectorAll('[data-lightbox]').forEach((btn) => btn.addEventListener('click', () => {
      const items = [...document.querySelectorAll(`[data-lightbox="${btn.dataset.lightbox}"]`)].filter((el) => el.offsetParent !== null)
      open(items, items.indexOf(btn))
    }))
    document.getElementById('lightbox-close').addEventListener('click', close)
    document.getElementById('lightbox-prev').addEventListener('click', () => step(-1))
    document.getElementById('lightbox-next').addEventListener('click', () => step(1))
    lb.addEventListener('click', (e) => { if (e.target === lb) close() })
    document.addEventListener('keydown', (e) => {
      if (!lb.classList.contains('is-open')) return
      if (e.key === 'Escape') close(); if (e.key === 'ArrowRight') step(1); if (e.key === 'ArrowLeft') step(-1)
    })
  }

  // Gallery filters
  const filters = document.querySelectorAll('.gallery-filter')
  if (filters.length) {
    filters.forEach((f) => f.addEventListener('click', () => {
      filters.forEach((o) => { o.classList.remove('border-accent', 'text-accent'); o.classList.add('border-white/15', 'text-white/60') })
      f.classList.add('border-accent', 'text-accent'); f.classList.remove('border-white/15', 'text-white/60')
      document.querySelectorAll('.gallery-item').forEach((item) => { item.hidden = f.dataset.filter !== 'all' && item.dataset.city !== f.dataset.filter })
    }))
  }

  // Booking / contact forms → pre-filled email (no backend in this static build)
  document.querySelectorAll('form.booking-form').forEach((form) => form.addEventListener('submit', (e) => {
    e.preventDefault()
    const fd = new FormData(form)
    const studio = form.dataset.studio || fd.get('studio') || 'General enquiry'
    const lines = [...fd.entries()].filter(([, v]) => v).map(([k, v]) => `${k[0].toUpperCase()}${k.slice(1)}: ${v}`)
    const subject = encodeURIComponent(`Booking request — ${studio}`)
    const body = encodeURIComponent(`Studio: ${studio}\n${lines.join('\n')}\n\nSent from ${location.href}`)
    window.location.href = `mailto:${form.dataset.to}?subject=${subject}&body=${body}`
  }))
})

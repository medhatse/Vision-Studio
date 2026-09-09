// Responsive image variants for the static build. No dependencies: reads WebP/JPEG/PNG dimensions from the
// file header and resizes with `cwebp` when it is installed (macOS: brew install webp; CI: apt-get install webp).
import fs from 'node:fs'
import path from 'node:path'
import { execFileSync } from 'node:child_process'

export const WIDTHS = [640, 1024, 1600]
const registry = new Map() // web path -> { file, width, height }
let hasCwebp = null

export function dimensions(file) {
  const b = fs.readFileSync(file)
  if (b.slice(0, 4).toString() === 'RIFF' && b.slice(8, 12).toString() === 'WEBP') {
    const chunk = b.slice(12, 16).toString()
    if (chunk === 'VP8X') return { width: 1 + b.readUIntLE(24, 3), height: 1 + b.readUIntLE(27, 3) }
    if (chunk === 'VP8L') { const bits = b.readUInt32LE(21); return { width: 1 + (bits & 0x3fff), height: 1 + ((bits >> 14) & 0x3fff) } }
    if (chunk === 'VP8 ') return { width: b.readUInt16LE(26) & 0x3fff, height: b.readUInt16LE(28) & 0x3fff }
  }
  if (b[0] === 0x89 && b.slice(1, 4).toString() === 'PNG') return { width: b.readUInt32BE(16), height: b.readUInt32BE(20) }
  if (b[0] === 0xff && b[1] === 0xd8) {
    let i = 2
    while (i < b.length) {
      if (b[i] !== 0xff) { i++; continue }
      const marker = b[i + 1]
      if (marker >= 0xc0 && marker <= 0xcf && ![0xc4, 0xc8, 0xcc].includes(marker)) return { height: b.readUInt16BE(i + 5), width: b.readUInt16BE(i + 7) }
      i += 2 + b.readUInt16BE(i + 2)
    }
  }
  return { width: 0, height: 0 }
}

/** Register an image (web path like /static/img/remote/2024/05/x.webp) and get src/srcset/width/height. */
export function picture(webPath, srcDir) {
  if (!webPath || !webPath.startsWith('/static/img/')) return { src: webPath, srcset: '', width: 0, height: 0 }
  let entry = registry.get(webPath)
  if (!entry) {
    const file = path.join(srcDir, webPath)
    const { width, height } = fs.existsSync(file) ? dimensions(file) : { width: 0, height: 0 }
    entry = { file, width, height, webPath }
    registry.set(webPath, entry)
  }
  const ext = path.extname(webPath)
  const base = webPath.slice(0, -ext.length)
  const widths = WIDTHS.filter((w) => w < entry.width)
  const srcset = [...widths.map((w) => `${base}-w${w}.webp ${w}w`), `${webPath} ${entry.width}w`].join(', ')
  return { src: webPath, srcset: entry.width ? srcset : '', width: entry.width, height: entry.height }
}

/** Write every registered variant into dist (skips existing files). Returns count generated. */
export function generateVariants(distDir, log = console.log) {
  if (hasCwebp === null) { try { execFileSync('cwebp', ['-version'], { stdio: 'ignore' }); hasCwebp = true } catch { hasCwebp = false } }
  if (!hasCwebp) { log('cwebp not found — serving original images only (install the "webp" package for responsive variants)'); return 0 }
  let n = 0
  for (const e of registry.values()) {
    if (!e.width) continue
    const ext = path.extname(e.webPath)
    for (const w of WIDTHS.filter((w) => w < e.width)) {
      const out = path.join(distDir, e.webPath.replace(/^\//, '').slice(0, -ext.length) + `-w${w}.webp`)
      if (fs.existsSync(out)) continue
      fs.mkdirSync(path.dirname(out), { recursive: true })
      try { execFileSync('cwebp', ['-quiet', '-q', '78', '-resize', String(w), '0', e.file, '-o', out], { stdio: 'ignore' }); n++ } catch (err) { log('  variant failed', e.webPath, w) }
    }
  }
  return n
}

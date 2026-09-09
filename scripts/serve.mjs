#!/usr/bin/env node
// Zero-dependency static server for ./dist (development preview only).
import http from 'node:http'
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', 'dist')
const PORT = Number(process.env.PORT || 4173)
const TYPES = { '.html': 'text/html; charset=utf-8', '.css': 'text/css', '.js': 'text/javascript', '.json': 'application/json', '.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.webp': 'image/webp', '.svg': 'image/svg+xml', '.ico': 'image/x-icon', '.xml': 'application/xml', '.txt': 'text/plain' }

http.createServer((req, res) => {
  let p = decodeURIComponent(new URL(req.url, 'http://x').pathname)
  if (p.endsWith('/')) p += 'index.html'
  let file = path.join(ROOT, p)
  if (!file.startsWith(ROOT)) { res.writeHead(403); return res.end() }
  if (fs.existsSync(file) && fs.statSync(file).isDirectory()) { res.writeHead(301, { location: p + '/' }); return res.end() }
  if (!fs.existsSync(file)) { file = path.join(ROOT, '404.html'); res.statusCode = 404 }
  if (!fs.existsSync(file)) { res.writeHead(404); return res.end('Not found') }
  res.setHeader('content-type', TYPES[path.extname(file)] || 'application/octet-stream')
  fs.createReadStream(file).pipe(res)
}).listen(PORT, '127.0.0.1', () => console.log(`Serving dist/ at http://localhost:${PORT}`))

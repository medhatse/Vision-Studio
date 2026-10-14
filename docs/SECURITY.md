# Security Policy & Guidelines

This document outlines the security posture, vulnerability reporting procedures, and architectural defense-in-depth principles implemented in `medhatse/Vision-Studio`.

---

## 1. Reporting Security Vulnerabilities

We take the security of our website and themes seriously. If you identify a potential security vulnerability in this repository, please report it privately:

- **Primary Contact**: Open a private GitHub Security Advisory via the repository's **Security** tab.
- **Direct Contact**: Reach out to the repository owner ([@medhatse](https://github.com/medhatse)) or email `booking@vision-studios.net`.
- **Public Disclosure**: Please allow sufficient time for investigation and mitigation before disclosing any details publicly.

---

## 2. Security Architecture & Threat Mitigation

### 2.1 Static Site Generator Security (Track 1)
- **Zero Server-Side Attack Surface**: The production static site emits flat HTML, CSS, JavaScript, and WebP media files into `dist/`. There is no active server-side runtime (Node.js, PHP, Python) running in production to target with remote code execution (RCE) or SQL injection.
- **Zero npm Runtime Dependencies**: `package.json` specifies zero runtime `dependencies` or `devDependencies`. This completely eliminates traditional Node.js supply-chain compromise vectors (compromised packages, dependency confusion, transitive malicious packages).
- **Self-Hosted Assets (GDPR Compliance)**:
  - Typography (`Anton`, `Inter`, `Space Mono`) is self-hosted in `src/static/fonts/` in WOFF2 format. No connections are made to Google Fonts, ensuring compliance with European Union GDPR standards.
  - Icons are rendered as inline SVG vectors, removing dependencies on external CDN providers (e.g. Font Awesome).
- **Click-to-Load Map Facade**: Google Maps iframes are not embedded during initial page render; an interactive facade requires explicit user interaction before establishing third-party connections.
- **Form Handling**: Contact and studio booking forms use client-side `mailto:` generation by default, eliminating server-side mail injection vulnerabilities or spam relays.

### 2.2 WordPress Theme Security (Track 2)
- **Input Sanitization & Output Escaping**: All custom post meta and options use native WordPress sanitization (`sanitize_text_field`, `wp_kses_post`, `absint`) upon persistence and contextual output escaping (`esc_html`, `esc_attr`, `esc_url`) during template rendering.
- **Nonce Verification**: All admin meta boxes and customizer forms implement WordPress security nonces (`wp_nonce_field` and `wp_verify_nonce`) to prevent Cross-Site Request Forgery (CSRF).
- **Header Hardening (`inc/compat.php`)**: Strips unnecessary WordPress information disclosure headers, including Windows Live Writer manifests (`wlwmanifest_link`), Really Simple Discovery (`rsd_link`), and shortlinks.
- **SVG Upload Security**: When SVG uploads are enabled, the theme enforces capability checks restricted to authorized administrators.

### 2.3 Local Docker Security
- **Loopback Binding**: In `wordpress/docker-compose.yml`, the WordPress service binds explicitly to `127.0.0.1:8080`, preventing local containers from exposing ports to external local area networks.
- **Segregated Networks**: MariaDB database containers communicate across an internal Docker bridge network without exposing database ports to the host machine.
- **Development Credentials**: Credentials defined in `docker-compose.yml` (`wordpress / wordpress`) are designated strictly for isolated local development and must never be reused in production environments.

---

## 3. Secret Management & Repository Hygiene

- **No Secrets in Source Control**: This repository does not contain or require API keys, database passwords, private keys, or personal access tokens.
- **Automated Scanning**: Changes are verified with secret scanning tools prior to merging into protected branches.
- **Public Data Ingestion**: The data fetcher script (`scripts/fetch-data.mjs`) only queries publicly available REST API endpoints and public media files.

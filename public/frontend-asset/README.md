# 24/7 IN N OUT — Static Tailwind Website

Production-style static frontend for **24/7 IN N OUT CONVENIENCE STORE - VAPE - PHONE REPAIR - SMOOTHIES**.

## Verified business information used
- Address: 6168 Oxon Hill Rd, Oxon Hill, MD 20745, United States
- Primary Google Business Profile category visible in the supplied profile: Convenience store
- Services/categories named publicly by the business: vape, phone repair, smoothies
- Current phone number and business hours were **not** hard-coded because they were not reliably verifiable from the supplied profile snapshot. The Contact page links to Google for live details.
- The adult-only page follows the current federal Tobacco 21 rule and keeps vape/tobacco content informational.

## Pages
- `index.html` — Home
- `convenience-store.html` — Convenience store SEO landing page
- `phone-repair.html` — Phone repair SEO landing page
- `smoothies.html` — Smoothies SEO landing page
- `vape-tobacco.html` — 21+ informational page with age confirmation
- `about.html` — About
- `gallery.html` — Gallery
- `faq.html` — FAQ + FAQ schema
- `contact.html` — Address, map, Google profile and Laravel-ready form
- `404.html` — Not found page

## Frontend architecture
- Semantic HTML5
- Exactly one `<h1>` on each content page
- No `<h2>`–`<h6>` tags; lower-level headings use `role="heading"` and `aria-level` on spans, matching the requested rule while preserving accessibility cues
- Tailwind CSS utilities, compiled locally into `assets/css/site.css`
- Minimal vanilla JavaScript for dark mode, mobile navigation and age confirmation
- Dark mode persists through `localStorage` and respects system preference on first visit
- Lightweight local WebP/SVG assets; no JS framework
- JSON-LD LocalBusiness/ConvenienceStore structured data; FAQ page also includes FAQPage schema
- Lazy-loaded non-critical images and map

## Laravel Blade conversion
Recommended split:
- `resources/views/layouts/app.blade.php` — head + global shell
- `resources/views/partials/header.blade.php`
- `resources/views/partials/footer.blade.php`
- Each HTML page becomes a Blade view with its current `<main>` content
- Replace static links with `route()` and use `@yield('title')` / `@yield('meta_description')`
- Wire the Contact form to a named POST route with CSRF, validation, rate limiting and mail delivery
- Move Tailwind compilation to Laravel Vite and keep the same utility classes

## Before public launch
1. Confirm the final domain and add absolute canonical + Open Graph URLs.
2. Confirm the current phone number and operating hours from the business owner/GBP before adding them to schema.
3. Replace service illustrations with original, high-resolution store/repair/smoothie photos as they become available.
4. Confirm the exact repair menu, smoothie menu and prices before publishing those details.
5. Add analytics/Search Console only after the production domain is connected.

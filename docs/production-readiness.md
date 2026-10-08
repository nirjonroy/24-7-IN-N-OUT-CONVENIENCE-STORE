# Production Readiness Checklist

This checklist is for the final deployment pass. Do not commit secrets into the repository.

## Environment

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Set `APP_URL` to the final HTTPS canonical domain.
- Set `SESSION_SECURE_COOKIE=true` when HTTPS is active.
- Configure database credentials in `.env`.
- Configure production mail credentials in `.env`.
- Configure `LOG_LEVEL=error` or an appropriate production level.

## Application Setup

- Run `composer install --no-dev --optimize-autoloader`.
- Run `php artisan key:generate` only if the production app key is not already set.
- Run `php artisan migrate --force`.
- Run `php artisan storage:link`.
- Run the legacy redirect seeder once after migrations: `php artisan db:seed --class=LegacyFrontendRedirectSeeder --force`.

## Frontend Assets

- Build assets before deployment or on the server: `npm ci && npm run build`.
- Confirm `public/build/manifest.json` exists.
- Keep `public/frontend-asset/` until the approved final cleanup step.

## Cache Commands

- Run `php artisan config:cache`.
- Run `php artisan route:cache`.
- Run `php artisan view:cache`.
- Run `php artisan optimize` only after confirming the above commands pass.

## Storage And Permissions

- Ensure `storage/` and `bootstrap/cache/` are writable by the web server user.
- Ensure public uploads are served through `public/storage`.
- Do not expose `.env`, `storage/logs`, or source directories publicly.

## SEO

- Set the canonical base URL in SEO Settings to the final HTTPS domain.
- Confirm `/sitemap.xml` uses the production domain.
- Confirm `/robots.txt` allows production indexing and keeps `Disallow: /frontend-asset/` until cleanup.
- Confirm old `.html` URLs redirect in one hop to clean URLs.

## HTTPS And Redirects

- Use one primary HTTPS redirect layer, preferably the host/CDN or Apache, not several competing rules.
- Match the HTTPS host with the SEO canonical domain.
- Do not add www/non-www redirects until the final canonical host is chosen.

## Queues And Scheduler

- Current contact notifications use the configured mail transport synchronously.
- If queues are introduced later, configure a queue worker and supervisor.
- No scheduler requirement was identified in Step 17.

## Verification

- Run `php artisan test`.
- Run `php artisan route:list`.
- Manually verify Home, Store, Smoothies, Adult Retail, Phone Repair, About, FAQ, Gallery, Contact, a generic CMS page, 404, sitemap, robots, and legacy redirects.

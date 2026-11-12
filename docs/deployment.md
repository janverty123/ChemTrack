# Deploying ChemTrack

This guide takes ChemTrack from the GitHub repository to a live HTTPS site on PHP hosting with MySQL or MariaDB, matching the architecture decision in [architecture](project/architecture.md). It does not choose a host for you; check the requirements below before buying a plan.

## 1. Host requirements

| Need | Minimum |
| --- | --- |
| PHP | 8.3 or newer, with `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd` (JPEG/PNG/WebP), `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip` |
| Database | MySQL 8 or MariaDB 10.6+ and a database user limited to the ChemTrack database |
| Web server | Apache (with `mod_rewrite`) or Nginx, with the **document root set to `public/`** |
| HTTPS | A valid certificate (most hosts include free Let's Encrypt) |
| Access | SSH and Composer are strongly preferred; a cron entry for the scheduler |
| Mail | An SMTP sender (host mailbox, Brevo, Mailgun, Amazon SES, Gmail Workspace SMTP, …) |
| Storage | At least 1 GB free for uploaded module PDFs and photos; keep videos on YouTube/Drive, not on the host |

## 2. Build the front-end assets (on your computer)

Most shared hosts have no Node.js, and `public/build` is not committed. Build it locally and upload it with the code:

```bash
pnpm install --frozen-lockfile
pnpm run build          # creates public/build
```

## 3. Upload and install

```bash
# on the server, inside the project folder (outside public_html if the host allows)
composer install --no-dev --optimize-autoloader
cp .env.production.example .env
php artisan key:generate          # ONCE only. Back up the resulting APP_KEY somewhere safe.
```

Edit `.env` and fill in `APP_URL`, the `DB_*` values and the `MAIL_*` sender. Then:

```bash
php artisan migrate --force
php artisan filament:assets
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan chemtrack:create-teacher teacher@your-school.example --name="Ms Reyes"
```

`chemtrack:create-teacher` asks for a password (12+ characters). Teacher accounts are created only this way; students join through invitations from their teacher.

Make `storage/` and `bootstrap/cache/` writable by the web server user (usually `chmod -R 775 storage bootstrap/cache`).

**Document root.** Point the domain at the `public/` folder. If the host forces the files into `public_html` and you cannot change the document root, upload the whole project there: the root `.htaccess` routes every request into `public/`, so `.env`, `storage/` (private PDFs and photos) and the source code are never served directly.

## 4. Scheduler (cron)

Add one cron entry so expired signup records (which contain an email address) are removed daily:

```
* * * * * cd /path/to/chemtrack && php artisan schedule:run >> /dev/null 2>&1
```

No queue worker is needed: mail is sent during the request.

## 5. HTTPS, proxies and Cloudflare

- **Host serves HTTPS directly (most shared hosting):** keep `TRUSTED_PROXIES=127.0.0.1,::1` and `SESSION_SECURE_COOKIE=true`.
- **Behind Cloudflare:** set SSL/TLS mode to **Full (strict)**, never *Flexible*. Put Cloudflare's published IPv4 and IPv6 ranges (https://www.cloudflare.com/ips/) in `TRUSTED_PROXIES`, comma separated, then run `php artisan config:cache` again. Do not cache `/learn`, `/teacher`, `/settings` or `/account`.
- Do not set `TRUSTED_PROXIES=*` unless the server only accepts traffic from the proxy; otherwise visitors can spoof their IP and bypass sign-in rate limits.

## 6. Go-live checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, real `APP_URL` with `https://`
- [ ] `APP_KEY` backed up offline (losing or regenerating it makes private student identities unreadable)
- [ ] A test email arrives from the real sender (sign up with a test invitation)
- [ ] Teacher account created, MFA enabled under **Settings → Account security**
- [ ] `https://your-domain/up` returns 200; `/does-not-exist` shows the ChemTrack 404 page
- [ ] Cron entry added
- [ ] Database backups scheduled at the host, and **one restore tested**
- [ ] Approved module PDFs, video links and Google Form links added by the teacher

## 7. Updating later

```bash
php artisan down
git pull                      # or upload the new files, including a freshly built public/build
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

## Notes

- Visitors see the branded maintenance page while the site is down.
- Old MySQL 5.7 and MariaDB releases older than 10.10 may silently add auto-update behaviour to some timestamps. Migration `2026_11_12_000001` removes it, so `attempts.started_at` and similar values are not overwritten.
- Local development is unchanged: see [development setup](development/README.md) and `Start-ChemTrack.cmd`.

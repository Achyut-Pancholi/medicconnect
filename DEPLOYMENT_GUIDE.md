# MediConnect — Deployment Guide

## Supported Platforms

This guide covers deployment to three free/low-cost cloud platforms:

1. **Railway** (recommended — managed MySQL built-in)
2. **Render**
3. **Fly.io**

---

## Prerequisites (all platforms)

```bash
composer install --optimize-autoloader --no-dev
npm run build      # only if using Vite assets
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 1. Railway

### Setup

1. Install Railway CLI: `npm install -g railway`
2. `railway login`
3. `railway init` inside the project root
4. Add a **MySQL** plugin from the Railway dashboard
5. Copy the `DATABASE_URL` provided by Railway

### Environment Variables

Set these in the Railway dashboard → Variables:

```
APP_ENV=production
APP_KEY=<run: php artisan key:generate --show>
APP_URL=https://<your-app>.up.railway.app

DB_CONNECTION=mysql
DB_HOST=<railway-mysql-host>
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=<railway-mysql-password>

FILESYSTEM_DISK=local
SESSION_DRIVER=database
CACHE_STORE=database
```

### Deploy

```bash
railway up
railway run php artisan migrate --seed
```

---

## 2. Render

### Setup

1. Push code to GitHub
2. Create a new **Web Service** on [render.com](https://render.com), connect GitHub repo
3. Set Build Command: `composer install --optimize-autoloader --no-dev && php artisan config:cache`
4. Set Start Command: `php artisan serve --host=0.0.0.0 --port=$PORT`
5. Add a **PostgreSQL** or external MySQL database and set its credentials in the **Environment** tab

### Key Environment Variables

```
APP_ENV=production
APP_KEY=<generated>
APP_URL=https://<your-service>.onrender.com
DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=medicconnect
DB_USERNAME=...
DB_PASSWORD=...
```

### Post-Deploy

Use the Render Shell tab:

```bash
php artisan migrate --seed
```

---

## 3. Fly.io

### Setup

```bash
# Install flyctl
curl -L https://fly.io/install.sh | sh
fly auth login
fly launch        # follow prompts
```

### `fly.toml` (auto-generated, key section)

```toml
[env]
  APP_ENV = "production"
  LOG_CHANNEL = "stderr"

[[services]]
  internal_port = 8080
  protocol = "tcp"
```

### Deploy

```bash
fly secrets set APP_KEY=$(php artisan key:generate --show)
fly secrets set DB_HOST=... DB_DATABASE=... DB_USERNAME=... DB_PASSWORD=...

fly deploy
fly ssh console -C "php artisan migrate --seed"
```

---

## Production Checklist

```
[ ] APP_DEBUG=false
[ ] APP_ENV=production
[ ] Run: php artisan config:cache
[ ] Run: php artisan route:cache
[ ] Run: php artisan view:cache
[ ] Cron set up for scheduler (see README.md)
[ ] Storage symlink: php artisan storage:link
[ ] HTTPS enforced (SSL provided by platform)
[ ] Backup strategy for MySQL
```

---

## Storage (Lab Reports)

Lab reports are stored on the **private** local disk (`storage/app/private/lab_reports/`). On cloud platforms ensure the storage directory is persistent (Railway & Fly.io support persistent volumes).

For production, consider migrating to **S3-compatible object storage** (AWS S3, Cloudflare R2) by updating `config/filesystems.php` disk driver to `s3`.

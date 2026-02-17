# Deploying to Coolify

This guide covers deploying the Catch Jiu Jitsu Portal to [Coolify](https://coolify.io/).

## Quick Setup

1. **Create a new Application** in Coolify from your Git repository
2. **Build Pack**: Select **Dockerfile**
3. **Ports Exposes**: Set to **80**
4. **Dockerfile location**: `./Dockerfile` (project root)

## Environment Variables

Set these in Coolify's **Environment Variables** (Developer view):

### Required
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-generated-key-here
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=<your-mysql-host>
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### Optional (if using Redis)
```
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=<redis-host>
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### LINE / Gemini (optional)
```
LINE_CHANNEL_ACCESS_TOKEN=
LINE_CHANNEL_SECRET=
GEMINI_API_KEY=
```

## Database

1. Create a **MySQL** database in Coolify (Database → Add MySQL)
2. Use the internal hostname as `DB_HOST` (e.g. `coolify-mysql-xxx`)
3. Run migrations via **Post-deployment commands** (see below)

## Post-Deployment Commands

In Coolify, under **Post-deployment** / **Execute Commands**, add:

```bash
php artisan migrate --force && php artisan storage:link && php artisan config:cache && php artisan route:cache
```

Or for first-time setup (generates key if missing):

```bash
php artisan key:generate --force 2>/dev/null; php artisan migrate --force && php artisan storage:link && php artisan optimize
```

## Trusted Proxies

Laravel runs behind Coolify's Traefik reverse proxy. Ensure `TrustProxies` middleware is configured (Laravel 12 does this by default). If you see wrong URLs in redirects, add to `.env`:

```
APP_TRUSTED_PROXIES=*
```

## URLs

- **Portal**: `https://your-domain.com/portal/`
- **Check-in kiosk**: `https://your-domain.com/portal/checkin.html`
- **API**: `https://your-domain.com/api/`

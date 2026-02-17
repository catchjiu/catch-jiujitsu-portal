# Deploy to live server (check-in kiosk + live data)

## 1. Backend (Laravel) – already added

- **Route:** `GET /api/checkin?code=...` (public, no auth)
- **Controller:** `App\Http\Controllers\CheckInController`
- **User:** `getHoursThisYearAttribute()` added for “hours this year”

Upload your Laravel project to the live server as you normally do (e.g. `laravel/` folder). Ensure:

- `.env` is set for production (DB, `APP_URL`, etc.)
- `php artisan route:cache` (optional)
- Storage link: `php artisan storage:link` (for avatars)

## 2. Build the frontend (React + check-in)

On your machine, from the project root (folder with `package.json`, not inside `laravel`):

```bash
cd "c:\MAMP\htdocs\new portal"
npm install
npm run build
```

This creates a `dist/` folder with `index.html`, `checkin.html`, and an `assets/` folder.

### If the app is at the site root

Use the build as-is (default base `/`). Upload the **contents** of `dist/` into your Laravel `public/` directory (so `checkin.html` is at `public/checkin.html`).  
Check-in URL: **https://yourdomain.com/checkin.html**  
API will be same origin, so `/api/checkin` works.

### If the app is in a subfolder (e.g. `/app/` or `/kiosk/`)

Build with a base path, then upload into that subfolder under `public/`:

```bash
# Windows (PowerShell)
$env:VITE_BASE_PATH="/app/"; npm run build

# macOS / Linux
VITE_BASE_PATH=/app/ npm run build
```

Upload the **contents** of `dist/` into `laravel/public/app/` (so you have `public/app/checkin.html`, `public/app/assets/`, etc.).  
Check-in URL: **https://yourdomain.com/app/checkin.html**

Update the Admin “Check-In Kiosk” link to match (e.g. `/app/checkin.html` instead of `/checkin.html`).

## 3. Upload to the server

- Upload the **Laravel** project (including the new `CheckInController`, route, and `User` change) to your live server.
- Upload the **built frontend** as above:
  - Root: copy `dist/*` into `laravel/public/`
  - Subfolder: copy `dist/*` into `laravel/public/app/` (or your chosen folder).

## 4. Test live data

1. Open **https://yourdomain.com/checkin.html** (or **https://yourdomain.com/app/checkin.html**).
2. Type a real member ID (e.g. `1`) and press Enter.
3. You should see that member’s name, belt, stats, and active/expired from your live database.

QR codes should encode the member ID (or a code that your system maps to a member). The API accepts:

- Numeric: `1`, `42`
- Prefixed: `CATCH-1`, `CATCH-42`

## 5. Optional: API base URL

If the check-in page is on a different domain than the Laravel API, set the API base in the frontend env before building:

```bash
# In .env (project root, next to package.json)
VITE_CHECKIN_API=https://your-laravel-domain.com
```

Then run `npm run build` again. The check-in page will call `https://your-laravel-domain.com/api/checkin?code=...`.  
Ensure Laravel allows CORS for that origin if needed.

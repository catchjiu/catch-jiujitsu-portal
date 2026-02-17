# Deploy to catchjiujitsu.com – do this now

Build is done. Follow these steps (you need to upload; the app can’t access your server).

---

## Part 1: Frontend (check-in kiosk + main app)

**From your PC**, upload these to your **web document root** on the server (the same folder where `checkin.html` already lives, e.g. `public_html/public` or `public_html`):

| Local folder (on your PC) | Upload to server |
|---------------------------|------------------|
| `c:\MAMP\htdocs\new portal\dist\checkin.html` | Same folder → `checkin.html` (overwrite) |
| `c:\MAMP\htdocs\new portal\dist\index.html` | Same folder → `index.html` (overwrite) |
| `c:\MAMP\htdocs\new portal\dist\assets\` (entire folder) | Same folder → `assets/` (merge/overwrite files inside) |

**In practice:**  
1. Open your FTP client (or File Manager in your hosting panel).  
2. Go to the **document root** for catchjiujitsu.com (where you previously put checkin.html).  
3. Upload the **contents** of the local `dist` folder: the two HTML files and the `assets` folder (with all JS inside). Overwrite existing files.

---

## Part 2: Laravel (check-in API)

**From your PC**, upload these to your **Laravel app folder** on the server (e.g. `public_html/laravel12` or wherever the Laravel app lives):

| Local file | Server path (example) |
|------------|------------------------|
| `laravel\routes\web.php` | `routes/web.php` (overwrite) |
| `laravel\app\Http\Controllers\CheckInController.php` | `app/Http/Controllers/CheckInController.php` (new or overwrite) |
| `laravel\app\Models\User.php` | `app/Models/User.php` (overwrite) |

**Then on the server** (SSH or your host’s “Run PHP” / terminal):

```bash
cd /path/to/your/laravel
php artisan route:clear
php artisan config:clear
```

(Replace `/path/to/your/laravel` with the real path, e.g. `~/public_html/laravel12`.)

---

## Part 3: Test

1. **API:** Open in browser: **https://catchjiujitsu.com/api/checkin?code=1**  
   (Use a real user ID.) You should see JSON, not a 404 page.

2. **Kiosk:** Open **https://catchjiujitsu.com/checkin.html**, click the scan area, type that ID, press Enter. You should see the welcome screen or “Member not found” only if that ID doesn’t exist.

---

**Summary:** Upload `dist` contents to the site’s document root; upload the three Laravel files to the Laravel app; run `php artisan route:clear` (and `config:clear`) on the server; then test the API URL and the check-in page.

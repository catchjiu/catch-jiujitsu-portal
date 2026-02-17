# Check-in API – deploy and test on live

## If /checkin or /api/checkin returns 404 (but /login works)

**Cause:** Laravel is using **cached routes** from before the check-in route existed.

**Fix on the server** (SSH or your host’s “Run PHP” / terminal):

```bash
cd /path/to/your/laravel
php artisan route:clear
php artisan config:clear
```

Then open **https://catchjiujitsu.com/checkin** again. To confirm the route is registered:

```bash
php artisan route:list
```

You should see `GET|HEAD checkin ... CheckInController@show`.

---

## If the kiosk shows **"Member not found"** for valid IDs

The request often never reaches Laravel. Use the steps below.

## 1. Deploy these to the live server

- **Route:** `laravel/routes/web.php` – must include:
  ```php
  Route::get('/api/checkin', [CheckInController::class, 'lookup'])->name('checkin.lookup');
  ```
- **Controller:** `laravel/app/Http/Controllers/CheckInController.php`
- **User model:** `laravel/app/Models/User.php` – must include `getHoursThisYearAttribute()` (hours trained this year)

## 2. Clear route cache on the server

After uploading, SSH or use your host’s “Run PHP” / terminal and run:

```bash
cd /path/to/your/laravel   # e.g. public_html/laravel12 or wherever Laravel lives
php artisan route:clear
php artisan route:list
```

In `route:list` you should see: `GET|HEAD api/checkin ... lookup`

## 3. Test the API in the browser

Open:

**https://catchjiujitsu.com/api/checkin?code=1**

(Use a real user ID from your DB instead of `1` if you prefer.)

- **If you see JSON** (e.g. `{"id":1,"name":"...","rank":"Blue",...}` or `{"message":"Member not found"}`)  
  → The API is live. If you still get "Member not found" in the kiosk for that ID, the user really isn’t in the database or the ID is wrong.

- **If you see 404 or an HTML error page**  
  → The request is not reaching Laravel. Check:
  - Document root for the domain: it must point at Laravel’s **public** folder (e.g. `public_html/laravel12/public`), not the parent.
  - You deployed the updated `web.php` and `CheckInController.php` and ran `php artisan route:clear`.

## 4. Check Laravel logs

If the API URL returns 500 or the kiosk shows "Check-in service unavailable", check:

`laravel/storage/logs/laravel.log`

Errors from the check-in lookup are logged there (e.g. missing method on `User`).

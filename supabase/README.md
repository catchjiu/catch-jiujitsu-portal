# Supabase Setup for Catch Jiu Jitsu

## 1. Create Supabase Project

1. Go to [supabase.com](https://supabase.com) and create a project
2. Note your project URL and database password (Settings → Database)

## 2. Create Schema

1. In Supabase Dashboard → **SQL Editor**
2. Run the contents of `schema.sql` to create all tables

## 3. Import Data from MySQL Dump (pgloader)

Your source is a MySQL dump (`newcatchjiujitsu-353030306a07.sql`). Use pgloader to migrate to Supabase:

### Step 1: Install pgloader

```bash
# Ubuntu/Debian
sudo apt install pgloader

# macOS
brew install pgloader
```

### Step 2: Configure credentials

```bash
cd supabase
cp .env.migrate.example .env.migrate
# Edit .env.migrate with your MySQL and Supabase credentials
```

Get Supabase connection details from **Dashboard → Settings → Database** (Connection string / Host).

### Step 3: Run migration

**If MySQL already has the data** (e.g. from Laravel/MAMP):

```bash
./run_pgloader.sh
```

**If you need to restore the dump first**:

```bash
./run_pgloader.sh --restore
```

This will:
1. Create the MySQL database if needed
2. Restore the dump into MySQL
3. Run pgloader to migrate data into Supabase

### Alternative: Manual conversion

- Export from MySQL as CSV and use Supabase Table Editor → Import
- Or use [mysql-to-postgres](https://github.com/nickshanks/mysql-to-postgres)

## 4. Connect Laravel to Supabase

Add to `laravel/.env`:

```env
# For PostgreSQL (Supabase) - use pgsql instead of mysql
DB_CONNECTION=pgsql
DB_HOST=db.YOUR_PROJECT_REF.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-db-password
```

Get the connection string from Supabase: **Settings → Database → Connection string (URI)**.

## 5. Row Level Security (RLS)

Supabase enables RLS by default. If Laravel manages auth and you want direct DB access disabled for the API, you can leave RLS on. If Laravel needs full access, you may need to add policies or disable RLS for application tables. For a Laravel-only backend, using the service role key bypasses RLS.

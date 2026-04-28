#!/usr/bin/env bash
# Migrate MySQL data to Supabase using pgloader
#
# Prerequisites:
#   1. MySQL running with the data (restore dump first if needed)
#   2. schema.sql already run in Supabase SQL Editor
#   3. pgloader installed: sudo apt install pgloader  (Ubuntu/Debian)
#
# Usage:
#   ./run_pgloader.sh              # uses .env.migrate or env vars
#   ./run_pgloader.sh --restore    # restore MySQL dump first, then migrate

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
DUMP_FILE="$PROJECT_DIR/newcatchjiujitsu-353030306a07.sql"

# Load config from .env.migrate if it exists
if [[ -f "$SCRIPT_DIR/.env.migrate" ]]; then
  set -a
  source "$SCRIPT_DIR/.env.migrate"
  set +a
fi

# MySQL connection (defaults match Laravel .env)
MYSQL_HOST="${MYSQL_HOST:-127.0.0.1}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
MYSQL_DATABASE="${MYSQL_DATABASE:-bjj_portal}"
MYSQL_USER="${MYSQL_USER:-catch_user}"
MYSQL_PASSWORD="${MYSQL_PASSWORD:-}"

# Supabase connection (get from Supabase Dashboard → Settings → Database)
# If direct connection (port 5432) fails with ENETUNREACH, try pooler (port 6543)
SUPABASE_USE_POOLER="${SUPABASE_USE_POOLER:-false}"
SUPABASE_HOST="${SUPABASE_HOST:-}"
SUPABASE_PORT="${SUPABASE_PORT:-5432}"
SUPABASE_DATABASE="${SUPABASE_DATABASE:-postgres}"
SUPABASE_USER="${SUPABASE_USER:-postgres}"
SUPABASE_PASSWORD="${SUPABASE_PASSWORD:-}"

# Pooler connection (when SUPABASE_USE_POOLER=true)
# Get exact host from Dashboard → Settings → Database → Connection string → Transaction
SUPABASE_POOLER_HOST="${SUPABASE_POOLER_HOST:-aws-0-ap-southeast-1.pooler.supabase.com}"
SUPABASE_POOLER_PORT="${SUPABASE_POOLER_PORT:-6543}"
SUPABASE_POOLER_USER="${SUPABASE_POOLER_USER:-postgres.cduuhkayhennbphjhvzx}"

if [[ "$SUPABASE_USE_POOLER" == "true" || "$SUPABASE_USE_POOLER" == "1" ]]; then
  SUPABASE_HOST="$SUPABASE_POOLER_HOST"
  SUPABASE_PORT="$SUPABASE_POOLER_PORT"
  SUPABASE_USER="$SUPABASE_POOLER_USER"
fi

# Validate
if [[ -z "$SUPABASE_HOST" || -z "$SUPABASE_PASSWORD" ]]; then
  echo "Error: Set SUPABASE_HOST and SUPABASE_PASSWORD (or create .env.migrate)"
  echo ""
  echo "Example .env.migrate:"
  echo "  MYSQL_HOST=127.0.0.1"
  echo "  MYSQL_DATABASE=bjj_portal"
  echo "  MYSQL_USER=catch_user"
  echo "  MYSQL_PASSWORD=your_mysql_password"
  echo "  SUPABASE_HOST=db.xxxxx.supabase.co"
  echo "  SUPABASE_PASSWORD=your_supabase_db_password"
  exit 1
fi

if [[ -z "$MYSQL_PASSWORD" ]]; then
  echo "Error: MYSQL_PASSWORD is required"
  exit 1
fi

# Optional: test connectivity before migrating
if [[ "${1:-}" == "--test" ]]; then
  echo "==> Testing MySQL connection..."
  mysql -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT 1;" "$MYSQL_DATABASE" 2>/dev/null && echo "MySQL: OK" || echo "MySQL: FAILED"
  echo "==> Testing Supabase connection (port $SUPABASE_PORT)..."
  (timeout 5 bash -c "echo >/dev/tcp/$SUPABASE_HOST/$SUPABASE_PORT" 2>/dev/null) && echo "Supabase: reachable" || echo "Supabase: UNREACHABLE (try SUPABASE_USE_POOLER=true for port 6543)"
  exit 0
fi

# Optional: restore MySQL dump first
if [[ "${1:-}" == "--restore" ]]; then
  echo "==> Restoring MySQL dump..."
  if [[ ! -f "$DUMP_FILE" ]]; then
    echo "Error: Dump file not found: $DUMP_FILE"
    exit 1
  fi
  # Create database if not exists, then restore
  mysql -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DATABASE\`;"
  mysql -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < "$DUMP_FILE"
  echo "==> MySQL dump restored."
fi

# Generate pgloader config with substituted values
LOAD_FILE="$SCRIPT_DIR/mysql_to_supabase.load"
TMP_LOAD=$(mktemp)
export MYSQL_HOST MYSQL_PORT MYSQL_DATABASE MYSQL_USER MYSQL_PASSWORD
export SUPABASE_HOST SUPABASE_PORT SUPABASE_DATABASE SUPABASE_USER SUPABASE_PASSWORD
envsubst '$MYSQL_HOST $MYSQL_PORT $MYSQL_DATABASE $MYSQL_USER $MYSQL_PASSWORD $SUPABASE_HOST $SUPABASE_PORT $SUPABASE_DATABASE $SUPABASE_USER $SUPABASE_PASSWORD' < "$LOAD_FILE" > "$TMP_LOAD"

echo "==> Running pgloader..."
pgloader "$TMP_LOAD"
rm -f "$TMP_LOAD"
echo "==> Migration complete."

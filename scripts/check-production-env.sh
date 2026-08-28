#!/usr/bin/env bash
# Quick production env sanity check for MOLIDO backend
set -e
ENV_FILE="${1:-backend/.env}"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "FAIL: $ENV_FILE not found"
  exit 1
fi

ok=0
warn=0

check() {
  local key="$1"
  local val
  val=$(grep -E "^${key}=" "$ENV_FILE" | tail -1 | cut -d= -f2- | tr -d '"' | tr -d "'")
  if [[ -z "$val" ]]; then
    echo "WARN: $key is empty"
    warn=$((warn+1))
  else
    echo "OK: $key is set"
    ok=$((ok+1))
  fi
}

echo "Checking $ENV_FILE ..."
check APP_KEY
check APP_URL
check DB_DATABASE
check DB_USERNAME

app_env=$(grep -E "^APP_ENV=" "$ENV_FILE" | cut -d= -f2 | tr -d '"' || true)
app_debug=$(grep -E "^APP_DEBUG=" "$ENV_FILE" | cut -d= -f2 | tr -d '"' || true)

if [[ "$app_env" == "production" ]]; then
  echo "OK: APP_ENV=production"
  ok=$((ok+1))
else
  echo "WARN: APP_ENV is '$app_env' (expected production)"
  warn=$((warn+1))
fi

if [[ "$app_debug" == "false" ]]; then
  echo "OK: APP_DEBUG=false"
  ok=$((ok+1))
else
  echo "WARN: APP_DEBUG should be false in production"
  warn=$((warn+1))
fi

echo "---"
echo "OK=$ok WARN=$warn"
exit 0

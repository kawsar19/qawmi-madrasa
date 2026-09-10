#!/usr/bin/env bash
# ডেমো টানেল — নিজের মেশিন থেকে একটা পাবলিক URL।
#
#   ./deploy/tunnel.sh                 # সুপার অ্যাডমিন (central) দেখাবে
#   ./deploy/tunnel.sh belashifm       # ওই মাদরাসার প্যানেল দেখাবে
#
# Cloudflare quick tunnel দেয় একটাই random hostname, wildcard নয়। অ্যাপ
# tenant চেনে hostname দেখে (InitializeTenancyByDomain), আর
# PreventAccessFromCentralDomains একটা hostname-কে central আর tenant দুটোই
# হতে দেয় না — তাই এক টানেলে একটাই জিনিস দেখানো যায়।
#
# স্ক্রিপ্ট শেষ হলে .env আর domains টেবিল আগের অবস্থায় ফিরে যায়।
set -euo pipefail

cd "$(dirname "$0")/.."

PORT="${PORT:-8000}"
SLUG="${1:-}"
ENV_BACKUP=".env.tunnel-backup"
HOT_BACKUP="public/hot.tunnel-backup"

command -v cloudflared >/dev/null || { echo "cloudflared নেই: brew install cloudflared"; exit 1; }

# ---- আগের অবস্থায় ফেরানো ---------------------------------------------------
cleanup() {
    echo ""
    echo "==> গোছানো হচ্ছে"
    [ -n "${TUNNEL_PID:-}" ] && kill "$TUNNEL_PID" 2>/dev/null || true
    [ -n "${SERVE_PID:-}"  ] && kill "$SERVE_PID"  2>/dev/null || true
    [ -n "${CAFFEINE_PID:-}" ] && kill "$CAFFEINE_PID" 2>/dev/null || true

    if [ -f "$ENV_BACKUP" ]; then
        mv "$ENV_BACKUP" .env
        echo "    .env ফেরত দেওয়া হয়েছে"
    fi
    if [ -f "$HOT_BACKUP" ]; then
        mv "$HOT_BACKUP" public/hot
        echo "    Vite dev mode ফেরত দেওয়া হয়েছে"
    fi
    if [ -n "$SLUG" ] && [ -n "${OLD_DOMAIN:-}" ]; then
        php artisan tinker --execute="
            \Stancl\Tenancy\Database\Models\Domain::where('domain', '${PUBLIC_HOST:-}')
                ->update(['domain' => '$OLD_DOMAIN']);
        " >/dev/null 2>&1 || true
        echo "    domain ফেরত: $OLD_DOMAIN"
    fi
    # .env ফেরত না গেলেও টানেলের মান যেন পড়ে না থাকে।
    if grep -q 'trycloudflare\.com' .env 2>/dev/null; then
        sed -i '' 's|^CENTRAL_DOMAINS=.*|CENTRAL_DOMAINS=app.localhost,localhost,127.0.0.1|' .env
        sed -i '' "s|^APP_URL=.*|APP_URL=http://app.localhost:$PORT|" .env
        sed -i '' '/^ASSET_URL=/d' .env
    fi
    php artisan config:clear >/dev/null 2>&1 || true
}
trap cleanup EXIT INT TERM

# আগের কোনো crash-এর ব্যাকআপ পড়ে থাকলে সেটাই আসল .env — নাহলে এখনকার
# (হয়তো টানেলের মান বসানো) .env দিয়ে সেটা ঢেকে ফেলব।
if [ -f "$ENV_BACKUP" ]; then
    echo "==> আগের ব্যাকআপ পাওয়া গেছে, .env ফেরত দেওয়া হচ্ছে"
    mv "$ENV_BACKUP" .env
fi

# ব্যাকআপ নিজেও যদি আগের কোনো crash-এ টানেলের মান নিয়ে জমে থাকে, সেটা
# বারবার ফিরে আসবে। মরা টানেলের URL মুছে লোকাল অবস্থায় ফেরাই।
if grep -q 'trycloudflare\.com' .env; then
    echo "==> .env-এ পুরনো টানেলের মান ছিল, লোকাল অবস্থায় ফেরানো হচ্ছে"
    sed -i '' 's|^CENTRAL_DOMAINS=.*|CENTRAL_DOMAINS=app.localhost,localhost,127.0.0.1|' .env
    sed -i '' "s|^APP_URL=.*|APP_URL=http://app.localhost:$PORT|" .env
    sed -i '' '/^ASSET_URL=/d' .env
fi

cp .env "$ENV_BACKUP"

# ---- পথ পরিষ্কার --------------------------------------------------------------
# `composer dev` / `npm run dev` চললে দুটো সমস্যা: ওরা $PORT দখল করে রাখে,
# আর Vite নতুন করে public/hot লিখে দেয় — তখন @vite আবার 127.0.0.1:5173 এ
# পাঠায় আর ফোনে স্টাইল আসে না। তাই টানেলের আগে ওগুলো থামাতেই হবে।
if lsof -ti:5173 >/dev/null 2>&1 || lsof -ti:"$PORT" >/dev/null 2>&1; then
    echo "==> dev server চলছে — টানেলের জন্য থামাতে হবে"
    printf "    থামিয়ে এগোব? [y/N] "
    read -r reply
    case "$reply" in
        [yY]*)
            pkill -f "artisan serve"  2>/dev/null || true
            pkill -f "@laravel/multiplex" 2>/dev/null || true
            pkill -f "npm run dev"    2>/dev/null || true
            pkill -f "vite"           2>/dev/null || true
            sleep 3
            echo "    থামানো হয়েছে"
            ;;
        *)
            echo "    বাতিল। নিজে থামিয়ে আবার চালান।"
            exit 1
            ;;
    esac
fi

# ---- অ্যাসেট ------------------------------------------------------------------
# public/hot থাকলে @vite ফাইলগুলো Vite dev server (127.0.0.1:5173) থেকে
# আনে। ফোনে 127.0.0.1 মানে ফোন নিজেই — সেখানে কিছু নেই, তাই পেজ আসে
# স্টাইল ছাড়া। টানেলের সময় তাই বিল্ড করা ফাইলই ব্যবহার করি।
if [ -f public/hot ]; then
    mv public/hot "$HOT_BACKUP"
    echo "==> Vite dev mode সরানো হলো (টানেলে বিল্ড করা অ্যাসেট লাগবে)"
fi

if [ ! -f public/build/manifest.json ]; then
    echo "==> অ্যাসেট বিল্ড হচ্ছে (একবারই, একটু সময় লাগবে)..."
    npm run build >/dev/null 2>&1 || { echo "npm run build ব্যর্থ"; exit 1; }
fi

# ---- ঘুম আটকানো -------------------------------------------------------------
# স্ক্রিপ্ট চলাকালীন মেশিন ঘুমালে টানেল আর সার্ভার দুটোই মরে যায় এবং
# ফিরে আসে না। -i idle sleep, -m ডিস্ক, -s চার্জারে থাকা অবস্থায়।
# ঢাকনা নামানো (clamshell) এতে আটকায় না — সেটা macOS ছাড়ে না।
caffeinate -ims &
CAFFEINE_PID=$!
echo "==> ঘুম বন্ধ রাখা হচ্ছে (ঢাকনা খোলা রাখুন)"

# ---- টানেল চালু, URL ধরা ----------------------------------------------------
echo "==> টানেল খোলা হচ্ছে..."
LOG=$(mktemp)
cloudflared tunnel --url "http://localhost:$PORT" --no-autoupdate >"$LOG" 2>&1 &
TUNNEL_PID=$!

PUBLIC_URL=""
for _ in $(seq 1 30); do
    PUBLIC_URL=$(grep -oE 'https://[a-z0-9-]+\.trycloudflare\.com' "$LOG" | head -1 || true)
    [ -n "$PUBLIC_URL" ] && break
    sleep 1
done
[ -z "$PUBLIC_URL" ] && { echo "URL পাওয়া গেল না:"; cat "$LOG"; exit 1; }

PUBLIC_HOST="${PUBLIC_URL#https://}"
echo "==> $PUBLIC_URL"

# ---- .env: টানেলের hostname চেনানো ------------------------------------------
# APP_URL https, নাহলে asset() http:// লিংক বানায় আর ব্রাউজার mixed content
# হিসেবে CSS/JS ব্লক করে — পেজ আসে সাদা।
set_env() {  # key value
    if grep -q "^$1=" .env; then
        # | delimiter, কারণ value-তে / আছে
        sed -i '' "s|^$1=.*|$1=$2|" .env
    else
        printf '%s=%s\n' "$1" "$2" >> .env
    fi
}

set_env APP_URL "$PUBLIC_URL"
set_env ASSET_URL "$PUBLIC_URL"

if [ -z "$SLUG" ]; then
    # central: টানেল hostname-কে central domain বানাই
    set_env CENTRAL_DOMAINS "$PUBLIC_HOST,app.localhost,localhost,127.0.0.1"
    ENTRY="$PUBLIC_URL/login"
    WHAT="সুপার অ্যাডমিন"
else
    # tenant: central তালিকা থেকে টানেল hostname বাদ, আর ওই মাদরাসার
    # domain সাময়িকভাবে টানেলের hostname করে দিই
    set_env CENTRAL_DOMAINS "app.localhost,localhost,127.0.0.1"

    OLD_DOMAIN=$(php artisan tinker --execute="
        \$t = \App\Models\Central\Tenant::where('slug', '$SLUG')->first();
        echo \$t ? optional(\$t->domains->first())->domain : '';
    " 2>/dev/null | tail -1 | tr -d '[:space:]')

    [ -z "$OLD_DOMAIN" ] && { echo "'$SLUG' নামে মাদরাসা পাওয়া যায়নি"; exit 1; }

    php artisan tinker --execute="
        \Stancl\Tenancy\Database\Models\Domain::where('domain', '$OLD_DOMAIN')
            ->update(['domain' => '$PUBLIC_HOST']);
    " >/dev/null
    ENTRY="$PUBLIC_URL/panel/login"
    WHAT="মাদরাসা: $SLUG"
fi

php artisan config:clear >/dev/null

# ---- সার্ভার ----------------------------------------------------------------
# --host 0.0.0.0 নয়: cloudflared একই মেশিন থেকেই কানেক্ট করে।
php artisan serve --port="$PORT" >/dev/null 2>&1 &
SERVE_PID=$!
sleep 2

cat <<INFO

  ────────────────────────────────────────────────
   $WHAT
   $ENTRY

   পাসওয়ার্ড: password
   বন্ধ করতে: Ctrl+C
  ────────────────────────────────────────────────

INFO

wait $TUNNEL_PID

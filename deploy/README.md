# Fly.io deployment

এক মেশিন, এক volume, SQLite। বিস্তারিত ধাপ নিচে।

## প্রথমবার

```bash
# CLI ইনস্টল (macOS)
brew install flyctl                   # অথবা: curl -L https://fly.io/install.sh | sh

fly auth signup                       # অথবা fly auth login
fly launch --no-deploy --copy-config  # fly.toml আগে থেকেই আছে
fly volumes create qawmi_data --size 3 --region sin
fly secrets set APP_KEY="$(php artisan key:generate --show)"
fly secrets set CENTRAL_DOMAINS="qawmi-madrasa.fly.dev"
fly secrets set APP_URL="https://qawmi-madrasa.fly.dev"
fly deploy
```

`fly launch` অ্যাপের নাম বদলালে fly.toml-এর `app` আর উপরের দুই secret-ও
সেই নামে বদলান।

## নিজের ডোমেইন যোগ করলে

```bash
fly certs add example.com
fly certs add '*.example.com'         # tenant subdomain-এর জন্য wildcard
fly secrets set CENTRAL_DOMAINS="app.example.com" APP_URL="https://app.example.com" \
                SESSION_DOMAIN=".example.com"
```

DNS-এ: `app` আর `*` দুটোরই CNAME → `<app>.fly.dev`, এবং `fly certs show`
যে acme challenge record চায় সেটি বসান। wildcard certificate-এর জন্য
DNS-01 challenge লাগে, তাই CNAME-only যথেষ্ট নয়।

## দৈনন্দিন

```bash
fly deploy                 # নতুন কোড পাঠান; entrypoint নিজে migrate করে
fly logs                   # লাইভ লগ
fly ssh console            # কন্টেইনারে ঢুকুন
fly ssh console -C "php /var/www/html/artisan tenant:create"
```

## ব্যাকআপ

volume হারালে সব যায়, তাই কপি বাইরে নিন:

```bash
fly ssh console -C "php /var/www/html/artisan db:backup"
fly ssh sftp get /data/storage/backups/<file>.sqlite ./backup.sqlite
```

Fly ডিফল্টে volume-এর দৈনিক snapshot রাখে (৫ দিন), কিন্তু সেটা
আপনার নিজের কপির বিকল্প নয়।

## মনে রাখার মতো

- **কখনো `min_machines_running` বাড়াবেন না** — volume একটাই মেশিনে
  attach হয়, আর SQLite ফাইল দুই মেশিন শেয়ার করতে পারে না।
- `fly deploy` কোড বদলায়, `/data` ছোঁয় না — ডেটা নিরাপদ।
- `.fly.dev`-এ wildcard subdomain পাওয়া যায় না, তাই tenant প্যানেল
  টেস্ট করতে নিজের ডোমেইন লাগবে।

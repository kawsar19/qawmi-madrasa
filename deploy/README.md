# ডেমো: নিজের মেশিন থেকে পাবলিক URL

কার্ড বা ডোমেইন ছাড়া কাউকে অ্যাপ দেখাতে হলে সবচেয়ে সহজ পথ — Cloudflare
quick tunnel। খরচ নেই, অ্যাকাউন্টও লাগে না।

```bash
brew install cloudflared        # একবারই
./deploy/tunnel.sh              # সুপার অ্যাডমিন প্যানেল
./deploy/tunnel.sh belashifm    # ওই মাদরাসার প্যানেল
```

স্ক্রিপ্ট নিজেই টানেল খোলে, URL ধরে, `.env`-এ বসায়, অ্যাসেট বিল্ড করে,
সার্ভার চালু করে, আর Ctrl+C-তে সব আগের অবস্থায় ফিরিয়ে দেয় (`.env`,
`public/hot` ও `domains` টেবিল সহ)।

`npm run dev` চালু থাকলে `public/hot` তৈরি হয় এবং `@vite` তখন CSS/JS
আনে `127.0.0.1:5173` থেকে। ফোনে `127.0.0.1` মানে ফোন নিজেই — তাই পেজ আসে
স্টাইল ছাড়া। স্ক্রিপ্ট তাই টানেলের সময় `public/hot` সরিয়ে বিল্ড করা
অ্যাসেট ব্যবহার করে, শেষে ফেরত দেয়।

> কোড বদলে আবার ডেমো দেখাতে চাইলে `npm run build` চালিয়ে নিন — টানেলে
> বিল্ড করা ফাইলই যায়, hot reload নয়।

## সীমাবদ্ধতা

- **এক টানেলে একটাই জিনিস।** quick tunnel একটাই random hostname দেয়,
  wildcard নয়। `PreventAccessFromCentralDomains` একটা hostname-কে central
  আর tenant দুটোই হতে দেয় না — তাই সুপার অ্যাডমিন আর মাদরাসা প্যানেল
  একসাথে দেখানো যায় না। দুটো দেখাতে হলে দুবার চালান।
- **প্রতিবার URL বদলায়**, তাই স্থায়ী লিংক দেওয়া যায় না।
- **মেশিন ঘুমালে সাইট বন্ধ।** স্ক্রিপ্ট নিজে `caffeinate` চালিয়ে idle
  sleep আটকায়, কিন্তু **ঢাকনা নামালে (clamshell) macOS ঘুমাবেই** — সেটা
  কোনো সেটিং দিয়ে বন্ধ করা যায় না। ঢাকনা খোলা রাখুন, চার্জারে রাখুন।
  ঘুমিয়ে গেলে টানেল ফিরে আসে না; আবার চালাতে হয় এবং URL বদলে যায়।

  ঢাকনা নামিয়েই চালাতে চাইলে একটাই উপায় — external monitor + কিবোর্ড/মাউস
  + চার্জার লাগানো (clamshell mode)। তখন ঢাকনা বন্ধ করলেও মেশিন জেগে থাকে।
- uptime-এর কোনো নিশ্চয়তা Cloudflare দেয় না — ডেমোর জন্য, প্রোডাকশনের
  জন্য নয়।

স্থায়ী করতে চাইলে একটা ডোমেইন কিনে named tunnel করলে wildcard subdomain
(`*.example.com`) সহ সবই চলবে, এবং তখনো হোস্টিং খরচ শূন্য।

---

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

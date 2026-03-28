# Fix DDEV SSL Certificate Issue

## Problem
Browser shows: `NetworkError when attempting to fetch resource` because DDEV uses self-signed SSL certificates.

## Solution: Trust DDEV Certificate

### Option 1: Install mkcert (Recommended)
```bash
# Install mkcert
cd /var/www/billoria.ad/cmsapi
ddev mkcert -install

# Restart DDEV
ddev restart
```

### Option 2: Manual Certificate Trust (Firefox)

1. Open `https://billoria-ad-api.ddev.site` in Firefox
2. Click "Advanced" on the security warning
3. Click "Accept the Risk and Continue"
4. Refresh your frontend app

### Option 3: Manual Certificate Trust (Chrome/Edge)

1. Open `https://billoria-ad-api.ddev.site` in Chrome
2. Click anywhere on the page and type: `thisisunsafe`
3. Refresh your frontend app

### Option 4: Use HTTP (Not Recommended)

Edit `.env.local`:
```env
NEXT_PUBLIC_API_BASE_URL=http://billoria-ad-api.ddev.site:33000
```

**Note**: Port 33000 is required for HTTP on DDEV.

## Verify Fix

After trusting the certificate, test in browser console:
```javascript
fetch('https://billoria-ad-api.ddev.site/api/v1/billboard/list?limit=1')
  .then(r => r.json())
  .then(d => console.log('✓ API Working:', d))
```

## Restart Next.js

After any `.env.local` changes:
```bash
cd /var/www/billoria.ad/frontendapp
# Stop current server (Ctrl+C)
pnpm dev
```

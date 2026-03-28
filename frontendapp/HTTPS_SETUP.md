# Why HTTPS is Better and How to Fix Certificate Issues

## ✅ Updated to HTTPS

Your `.env.local` now uses HTTPS:
```env
NEXT_PUBLIC_API_BASE_URL=https://billoria-ad-api.ddev.site
```

## Why HTTPS > HTTP:

| Feature | HTTPS | HTTP |
|---------|-------|------|
| **Security** | ✅ Encrypted | ❌ Plain text |
| **Credentials** | ✅ Cookies work | ⚠️ Limited |
| **Modern APIs** | ✅ Required | ⚠️ Deprecated |
| **Production** | ✅ Standard | ❌ Blocked |

## If Browser Still Shows Certificate Error:

### Option 1: Restart Browser (Recommended)
```bash
# Close ALL browser windows and restart
# mkcert is already installed system-wide
```

### Option 2: Trust in Browser (One-Time)

**Firefox:**
1. Visit `https://billoria-ad-api.ddev.site`
2. Click **Advanced**
3. Click **Accept the Risk and Continue**
4. Close that tab and refresh your frontend

**Chrome/Edge:**
1. Visit `https://billoria-ad-api.ddev.site`
2. Type `thisisunsafe` (anywhere on the page, no input box needed)
3. Close that tab and refresh your frontend

### Option 3: Verify mkcert Installation
```bash
# Check if CA is installed
mkcert -install

# Should show:
# The local CA is already installed in the system trust store! 👍
# The local CA is already installed in the Firefox and/or Chrome/Chromium trust store! 👍
```

## Smart Fallback System

The API client now tries in order:
1. **HTTPS** (primary, secure) ← You're here now!
2. **HTTPS alternate** (backup)  
3. **HTTP with port 33000** (last resort if HTTPS fails)

## After Setup:

1. **Restart Next.js:**
   ```bash
   cd /var/www/billoria.ad/frontendapp
   # Press Ctrl+C
   pnpm dev
   ```

2. **Hard refresh browser:** `Ctrl + Shift + R`

3. **Check console** - you should see:
   ```
   Trying API URL: https://billoria-ad-api.ddev.site/api/v1/billboard/list
   ✓ Success with: https://billoria-ad-api.ddev.site...
   ```

4. If HTTPS fails, it automatically falls back to HTTP (you'll see it try each URL)

## Testing HTTPS Works:
```bash
curl -s "https://billoria-ad-api.ddev.site/api/v1/billboard/list?limit=1" | jq '.success'
# Should return: true
```

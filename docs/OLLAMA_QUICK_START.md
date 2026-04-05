# Ollama Quick Start — Billoria.ad

Fast setup guide to get AI features running in 5 minutes.

## Prerequisites

- DDEV environment running
- Ollama installed on your system

## Step 1: Install & Start Ollama (2 min)

### Linux

```bash
curl -fsSL https://ollama.com/install.sh | sh
ollama serve
```

### macOS

```bash
brew install ollama
ollama serve
```

Leave this terminal running.

## Step 2: Pull AI Model (3 min)

In a new terminal:

```bash
ollama pull llama3.2
```

This downloads ~5GB. For faster testing, use: `ollama pull phi3` (2.3GB)

## Step 3: Configure Drupal (1 min)

```bash
cd /var/www/billoria.ad/cmsapi
ddev ssh
php scripts/configure-ollama-ai.php
```

This will:

- Enable AI modules
- Configure Ollama connection
- Test the integration

## Step 4: Test It! (30 sec)

### Backend Test

```bash
curl -X POST http://billoria-ad-api.ddev.site/api/v1/ai/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello! Tell me about Billoria."}'
```

### Frontend Test

1. Start Next.js: `cd frontendapp && ddev start`
2. Visit: <http://billoria-ad.ddev.site:3000>
3. Click the chat button (💬) in the bottom-right
4. Type: "Hello!" and press Enter

You should see AI-powered responses! 🎉

## What You Get

✅ **AI Chatbot** — Floating chat widget on all pages  
✅ **Smart Search** — Natural language search understanding  
✅ **Content Generation** — Auto-generate billboard descriptions  
✅ **Recommendations** — AI-powered billboard suggestions  

## API Endpoints Available

```
POST /api/v1/ai/chat                  # Chatbot
POST /api/v1/ai/billboard-description # Generate descriptions
POST /api/v1/ai/enhance-search        # Smart search
POST /api/v1/ai/recommendations       # Suggestions
GET  /api/v1/ai/status               # Health check (admin)
```

## Troubleshooting

**"Cannot connect to Ollama"**
→ Make sure `ollama serve` is running

**"Model not found"**
→ Run: `ollama pull llama3.2`

**Frontend not showing chatbot**
→ Check that `<Chatbot />` is in your layout file

**Slow responses?**
→ Use smaller model: `ollama pull phi3` then update config to use "phi3"

## Next Steps

- Read full docs: [`docs/OLLAMA_INTEGRATION.md`](./OLLAMA_INTEGRATION.md)
- Customize prompts in `cmsapi/web/modules/custom/billoria_core/src/Service/OllamaAiService.php`
- Add new AI features following the customization guide

## Need Help?

- Check Ollama: `ollama list` (should show llama3.2)
- Check API status: `curl http://billoria-ad-api.ddev.site/api/v1/ai/status`
- View logs: `ddev drush watchdog-show --severity=Error`

---

For detailed documentation, see: **[OLLAMA_INTEGRATION.md](./OLLAMA_INTEGRATION.md)**

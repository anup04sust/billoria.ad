# Ollama AI Integration — Billoria.ad

Complete guide for the Ollama AI integration in the Billoria billboard marketplace platform.

## Overview

The Ollama integration provides AI-powered features including:

- **AI Chatbot** — Conversational assistant for users
- **Content Generation** — Auto-generate billboard descriptions
- **Smart Search** — Natural language search query enhancement
- **Recommendations** — AI-powered billboard suggestions

## Architecture

```
Frontend (Next.js)
    ↓ HTTP/REST
Backend (Drupal)
    ↓ HTTP
Ollama Service
    ↓ LLM
AI Models (llama3.2, etc.)
```

**Components:**

- `billoria_core.ollama_ai` — Drupal service handling Ollama communication
- `AiApiController` — REST API endpoints
- `lib/api/ai.ts` — Frontend API client
- `components/shared/Chatbot.tsx` — Chat UI component

## Installation

### 1. Install Ollama

**Linux:**

```bash
curl -fsSL https://ollama.com/install.sh | sh
```

**macOS:**

```bash
brew install ollama
```

**Start Ollama:**

```bash
ollama serve
```

### 2. Pull AI Model

```bash
# Recommended: Llama 3.2 (4.9GB)
ollama pull llama3.2

# Alternative models:
ollama pull mistral        # 4.1GB
ollama pull phi3          # 2.3GB (faster, less capable)
```

### 3. Enable Drupal AI Modules

```bash
cd cmsapi
ddev drush pm:enable ai ai_provider_ollama -y
ddev drush cr
```

### 4. Configure Integration

```bash
cd cmsapi
ddev ssh
php scripts/configure-ollama-ai.php
```

This script will:

- Check Ollama availability
- Configure connection settings
- Test the AI integration
- Clear Drupal cache

### 5. Verify Installation

**Check service status:**

```bash
curl http://billoria-ad-api.ddev.site/api/v1/ai/status
```

**Test chatbot:**

```bash
curl -X POST http://billoria-ad-api.ddev.site/api/v1/ai/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello, how can you help me?"}'
```

## API Endpoints

### POST `/api/v1/ai/chat`

AI chatbot conversation endpoint.

**Request:**

```json
{
  "message": "I need a billboard in Dhaka",
  "context": {
    "type": "chatbot"
  },
  "history": [
    {
      "role": "user",
      "content": "What services do you offer?"
    },
    {
      "role": "assistant",
      "content": "We help you find and book billboards..."
    }
  ]
}
```

**Response:**

```json
{
  "success": true,
  "message": "I can help you find billboards in Dhaka! What type of location are you looking for?",
  "model": "llama3.2",
  "timestamp": 1234567890
}
```

### POST `/api/v1/ai/billboard-description`

Generate professional billboard descriptions.

**Request:**

```json
{
  "title": "Prime Location Gulshan 2",
  "location": "Gulshan Avenue, Dhaka",
  "size": "20x10 feet",
  "type": "LED"
}
```

**Response:**

```json
{
  "success": true,
  "message": "This premium LED billboard at Gulshan 2 offers exceptional visibility..."
}
```

**Permissions:** Requires `create billboard content`

### POST `/api/v1/ai/enhance-search`

Enhance natural language search queries.

**Request:**

```json
{
  "query": "I want a big LED billboard near airport for 3 months under 50000 taka"
}
```

**Response:**

```json
{
  "success": true,
  "message": "...",
  "structured_data": {
    "location": "near airport",
    "size": "large",
    "type": "LED",
    "budget": "50000 taka",
    "intent": "billboard rental"
  }
}
```

### POST `/api/v1/ai/recommendations`

Get AI-powered billboard recommendations.

**Request:**

```json
{
  "criteria": {
    "budget": 50000,
    "location": "Dhaka",
    "target_audience": "corporate professionals",
    "campaign_duration": "3 months"
  },
  "billboards": []
}
```

### GET `/api/v1/ai/status`

Check AI service health (admin only).

**Response:**

```json
{
  "available": true,
  "host": "localhost",
  "port": "11434",
  "models": [
    {"name": "llama3.2", "size": 4921057792}
  ],
  "configured_model": "llama3.2"
}
```

**Permissions:** Requires `administer billoria`

## Frontend Integration

### Using the Chatbot Component

The `Chatbot` component is already integrated and appears on all pages:

```tsx
import { Chatbot } from '@/components/shared/Chatbot';

export default function Layout({ children }) {
  return (
    <>
      {children}
      <Chatbot />
    </>
  );
}
```

### Using AI API Directly

```typescript
import { aiAPI } from '@/lib/api/ai';

// Send chat message
const response = await aiAPI.chat('How do I book a billboard?');

// Generate description
const description = await aiAPI.generateDescription({
  title: 'Downtown Billboard',
  location: 'Main Street, Dhaka',
  size: '15x8 feet',
  type: 'static',
});

// Enhance search
const enhanced = await aiAPI.enhanceSearch('LED billboards in Dhaka');
console.log(enhanced.structured_data);

// Get recommendations
const recommendations = await aiAPI.getRecommendations({
  budget: 75000,
  location: 'Dhaka',
  type: 'LED',
});
```

## Configuration

### Drupal Configuration

Edit configuration at: `/admin/config/billoria` or directly in code:

```php
$config = \Drupal::configFactory()->getEditable('billoria_core.settings');
$config->set('ollama.host', 'localhost');
$config->set('ollama.port', '11434');
$config->set('ollama.model', 'llama3.2');
$config->set('ollama.timeout', 30);
$config->save();
```

### Environment Variables

Create `cmsapi/.env.local`:

```env
# Optional: Override Ollama host for remote deployment
OLLAMA_HOST=localhost
OLLAMA_PORT=11434
OLLAMA_MODEL=llama3.2
```

### Frontend Environment

Already configured in `frontendapp/.env.local`:

```env
NEXT_PUBLIC_API_URL=http://billoria-ad-api.ddev.site
```

## Customization

### Custom System Prompts

Edit `OllamaAiService::buildSystemPrompt()`:

```php
protected function buildSystemPrompt(array $context): string {
  $base = "You are Billoria AI, an expert in outdoor advertising in Bangladesh. ";
  
  // Add custom context-specific prompts
  if ($context['type'] === 'chatbot') {
    return $base . "Help users find billboards, explain pricing, and guide bookings.";
  }
  
  return $base;
}
```

### Add New AI Features

1. **Add method to `OllamaAiService.php`:**

```php
public function suggestCampaignStrategy(array $campaign_data): array {
  $prompt = "Suggest an outdoor advertising strategy for: " . json_encode($campaign_data);
  return $this->chat($prompt, ['type' => 'strategy']);
}
```

1. **Add controller endpoint:**

```php
public function suggestStrategy(Request $request): JsonResponse {
  $data = json_decode($request->getContent(), TRUE);
  $response = $this->ollamaService->suggestCampaignStrategy($data);
  return new JsonResponse($response);
}
```

1. **Add route in `billoria_core.routing.yml`:**

```yaml
billoria_core.api.ai_strategy:
  path: '/api/v1/ai/campaign-strategy'
  defaults:
    _controller: '\Drupal\billoria_core\Controller\AiApiController::suggestStrategy'
  requirements:
    _access: 'TRUE'
  methods: [POST]
```

1. **Add frontend method:**

```typescript
// lib/api/ai.ts
async suggestStrategy(campaignData: any) {
  const response = await apiFetch('/api/v1/ai/campaign-strategy', {
    method: 'POST',
    body: JSON.stringify(campaignData),
  });
  return response.json();
}
```

## Troubleshooting

### Ollama Not Responding

**Problem:** API returns "Cannot connect to Ollama service"

**Solutions:**

1. Check if Ollama is running: `curl http://localhost:11434/api/tags`
2. Start Ollama: `ollama serve`
3. Verify port: Default is 11434
4. Check firewall/network settings

### Slow AI Responses

**Problem:** Responses take too long (>30 seconds)

**Solutions:**

1. Use a smaller model: `ollama pull phi3`
2. Increase timeout in config (caution: affects UX)
3. Use GPU acceleration if available
4. Limit conversation history length (currently 5 messages)

### Model Not Found

**Problem:** "Model not available" error

**Solutions:**

```bash
# List installed models
ollama list

# Pull missing model
ollama pull llama3.2

# Update configuration
ddev drush config-set billoria_core.settings ollama.model llama3.2
```

### Permission Denied

**Problem:** "Access denied" on AI endpoints

**Solutions:**

- `/ai/chat` and `/ai/enhance-search` are public (no auth required)
- `/ai/billboard-description` requires `create billboard content` permission
- `/ai/status` requires `administer billoria` permission

Grant permissions:

```bash
ddev drush role:perm:add authenticated 'create billboard content'
```

### Frontend TypeError

**Problem:** `Cannot read property 'message' of undefined`

**Solution:** Always check `response.success` before accessing data:

```typescript
const response = await aiAPI.chat(message);
if (response.success && response.message) {
  console.log(response.message);
} else {
  console.error(response.error || 'Unknown error');
}
```

## Performance Optimization

### 1. Model Selection

| Model | Size | Speed | Quality | Use Case |
|-------|------|-------|---------|----------|
| phi3 | 2.3GB | Fast | Good | Chatbot, quick responses |
| llama3.2 | 4.9GB | Medium | Excellent | Recommended default |
| mistral | 4.1GB | Medium | Very Good | Alternative to llama3.2 |

### 2. Response Caching

For billboard descriptions (which rarely change):

```php
// In OllamaAiService.php
public function generateBillboardDescription(array $billboard_data): array {
  $cache_key = 'billboard_desc_' . md5(json_encode($billboard_data));
  $cached = \Drupal::cache()->get($cache_key);
  
  if ($cached) {
    return $cached->data;
  }
  
  $response = $this->chat(...);
  \Drupal::cache()->set($cache_key, $response, time() + 3600 * 24 * 7); // 7 days
  return $response;
}
```

### 3. Stream Responses (Advanced)

For real-time chatbot typing effect, modify to use streaming:

```php
$response = $this->httpClient->post($url, [
  'json' => [
    'model' => $config['model'],
    'messages' => $messages,
    'stream' => TRUE, // Enable streaming
  ],
  'stream' => TRUE,
]);
```

## Security Considerations

1. **Rate Limiting** — Already implemented via `ApiSecurityService`
2. **Input Validation** — Always sanitize user input
3. **Permission Checks** — Sensitive endpoints are protected
4. **Error Handling** — Never expose technical errors to users
5. **Resource Limits** — Ollama timeout prevents hanging requests

## Production Deployment

### Docker Compose Setup

```yaml
services:
  ollama:
    image: ollama/ollama:latest
    volumes:
      - ollama_data:/root/.ollama
    ports:
      - "11434:11434"
    deploy:
      resources:
        limits:
          memory: 8G
        reservations:
          memory: 4G

volumes:
  ollama_data:
```

### Remote Ollama Configuration

Update Drupal config for remote Ollama:

```php
$config->set('ollama.host', 'ollama.yourhost.com');
$config->set('ollama.port', '11434');
```

### Monitoring

Add health checks:

```bash
# Cron job to check AI availability
*/5 * * * * curl -f http://localhost:11434/api/tags || systemctl restart ollama
```

## Future Enhancements

Potential features to implement:

- [ ] Multi-language support (Bengali, English)
- [ ] Voice input/output integration
- [ ] Image analysis for billboard photos
- [ ] Campaign performance prediction
- [ ] Sentiment analysis for billboard reviews
- [ ] Auto-tagging of billboard features
- [ ] Smart pricing suggestions

## Support

For issues or questions:

- Check logs: `/var/log/drupal/billoria_ai.log`
- Drupal logs: `ddev drush watchdog-show --severity=Error`
- Test API: Via Postman/cURL examples above

## Resources

- [Ollama Documentation](https://github.com/ollama/ollama)
- [Drupal AI Module](https://www.drupal.org/project/ai)
- [Llama 3.2 Model Card](https://ollama.com/library/llama3.2)

---

**Last Updated:** April 2026  
**Maintained by:** Billoria Development Team

<?php

namespace Drupal\billoria_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Ollama AI Service for Billoria platform.
 *
 * Provides AI-powered features:
 * - Chatbot responses
 * - Billboard description generation
 * - Search query understanding
 * - Content recommendations
 */
class OllamaAiService
{

    /**
     * HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * Config factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructs an OllamaAiService object.
     */
    public function __construct(
        ClientInterface $http_client,
        ConfigFactoryInterface $config_factory,
        LoggerChannelFactoryInterface $logger_factory
    ) {
        $this->httpClient = $http_client;
        $this->configFactory = $config_factory;
        $this->logger = $logger_factory->get('billoria_ai');
    }

    /**
     * Get Ollama API configuration.
     *
     * @return array
     *   Configuration array with host, port, and model.
     */
    protected function getConfig(): array
    {
        $config = $this->configFactory->get('billoria_core.settings');
        return [
            'host' => $config->get('ollama.host') ?? 'localhost',
            'port' => $config->get('ollama.port') ?? '11434',
            'model' => $config->get('ollama.model') ?? 'llama3.2',
            'timeout' => $config->get('ollama.timeout') ?? 30,
        ];
    }

    /**
     * Send a chat message to Ollama and get response.
     *
     * @param string $message
     *   User message.
     * @param array $context
     *   Optional context for the conversation.
     * @param array $history
     *   Optional conversation history.
     *
     * @return array
     *   Response array with 'success', 'message', and optional 'error'.
     */
    public function chat(string $message, array $context = [], array $history = []): array
    {
        $config = $this->getConfig();
        $url = "http://{$config['host']}:{$config['port']}/api/chat";

        // Build system prompt based on context
        $system_prompt = $this->buildSystemPrompt($context);

        // Build messages array
        $messages = [];
        if ($system_prompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $system_prompt,
            ];
        }

        // Add conversation history
        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        try {
            $response = $this->httpClient->post($url, [
                'json' => [
                    'model' => $config['model'],
                    'messages' => $messages,
                    'stream' => FALSE,
                ],
                'timeout' => $config['timeout'],
            ]);

            $body = json_decode($response->getBody()->getContents(), TRUE);

            if (isset($body['message']['content'])) {
                return [
                    'success' => TRUE,
                    'message' => $body['message']['content'],
                    'model' => $body['model'] ?? $config['model'],
                    'timestamp' => time(),
                ];
            }

            return [
                'success' => FALSE,
                'error' => 'Invalid response format from Ollama',
            ];
        } catch (GuzzleException $e) {
            $this->logger->error('Ollama API error: @message', [
                '@message' => $e->getMessage(),
            ]);

            return [
                'success' => FALSE,
                'error' => 'AI service temporarily unavailable. Please try again.',
                'technical_error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate billboard description from basic details.
     *
     * @param array $billboard_data
     *   Billboard data (title, location, size, features).
     *
     * @return array
     *   Generated description with success status.
     */
    public function generateBillboardDescription(array $billboard_data): array
    {
        $prompt = "Generate a professional, SEO-friendly description for an outdoor advertising billboard with these details:\n\n";
        $prompt .= "Title: " . ($billboard_data['title'] ?? 'Billboard') . "\n";
        $prompt .= "Location: " . ($billboard_data['location'] ?? 'Not specified') . "\n";
        $prompt .= "Size: " . ($billboard_data['size'] ?? 'Not specified') . "\n";
        $prompt .= "Type: " . ($billboard_data['type'] ?? 'Static') . "\n";
        $prompt .= "\nThe description should be:\n";
        $prompt .= "- 2-3 paragraphs (150-200 words)\n";
        $prompt .= "- Highlight visibility, traffic, and strategic advantages\n";
        $prompt .= "- Professional tone for business clients\n";
        $prompt .= "- Include relevant keywords naturally\n";

        $context = [
            'type' => 'billboard_description',
            'platform' => 'billoria',
        ];

        return $this->chat($prompt, $context);
    }

    /**
     * Enhance search query understanding.
     *
     * @param string $query
     *   User search query.
     *
     * @return array
     *   Enhanced query understanding with extracted parameters.
     */
    public function enhanceSearchQuery(string $query): array
    {
        $prompt = "Analyze this billboard search query and extract structured search parameters:\n\n";
        $prompt .= "Query: \"$query\"\n\n";
        $prompt .= "Extract and return as JSON:\n";
        $prompt .= "- location: (city, area, or highway name)\n";
        $prompt .= "- size: (small, medium, large, or specific dimensions)\n";
        $prompt .= "- type: (LED, static, digital)\n";
        $prompt .= "- budget: (budget range if mentioned)\n";
        $prompt .= "- features: (specific features or requirements)\n";
        $prompt .= "- intent: (what the user is trying to find)\n\n";
        $prompt .= "Return ONLY valid JSON, no explanation.";

        $context = [
            'type' => 'search_enhancement',
            'platform' => 'billoria',
        ];

        $response = $this->chat($prompt, $context);

        if ($response['success']) {
            // Try to parse JSON from response
            $json_start = strpos($response['message'], '{');
            $json_end = strrpos($response['message'], '}');

            if ($json_start !== FALSE && $json_end !== FALSE) {
                $json_string = substr($response['message'], $json_start, $json_end - $json_start + 1);
                $parsed = json_decode($json_string, TRUE);

                if ($parsed) {
                    $response['structured_data'] = $parsed;
                }
            }
        }

        return $response;
    }

    /**
     * Get AI-powered billboard recommendations.
     *
     * @param array $user_criteria
     *   User preferences and requirements.
     * @param array $available_billboards
     *   Available billboards to recommend from.
     *
     * @return array
     *   Recommendations with reasoning.
     */
    public function recommendBillboards(array $user_criteria, array $available_billboards): array
    {
        $prompt = "Based on these user requirements:\n\n";
        $prompt .= json_encode($user_criteria, JSON_PRETTY_PRINT);
        $prompt .= "\n\nAnd these available billboards:\n\n";
        $prompt .= json_encode($available_billboards, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide top 3 recommendations with brief reasoning for each.";

        $context = [
            'type' => 'recommendation',
            'platform' => 'billoria',
        ];

        return $this->chat($prompt, $context);
    }

    /**
     * Build system prompt based on context.
     *
     * @param array $context
     *   Context information.
     *
     * @return string
     *   System prompt.
     */
    protected function buildSystemPrompt(array $context): string
    {
        $type = $context['type'] ?? 'general';

        $base = "You are Billoria AI Assistant, helping users with outdoor advertising and billboard marketplace services in Bangladesh. ";
        $base .= "Be professional, concise, and helpful. ";

        switch ($type) {
            case 'chatbot':
                return $base . "Answer user questions about finding billboards, booking processes, pricing, and platform features. "
                    . "If asked about specific technical details you don't know, suggest contacting support. "
                    . "Keep responses under 100 words unless detailed explanation is requested.";

            case 'billboard_description':
                return $base . "Generate compelling, professional billboard descriptions for outdoor advertising inventory.";

            case 'search_enhancement':
                return $base . "Extract structured search parameters from natural language queries.";

            case 'recommendation':
                return $base . "Provide data-driven outdoor advertising recommendations based on user criteria.";

            default:
                return $base . "Provide helpful information about billboard advertising and the Billoria platform.";
        }
    }

    /**
     * Check if Ollama is available and responding.
     *
     * @return array
     *   Status array with 'available', 'models', and error info.
     */
    public function checkStatus(): array
    {
        $config = $this->getConfig();
        $url = "http://{$config['host']}:{$config['port']}/api/tags";

        try {
            $response = $this->httpClient->get($url, [
                'timeout' => 5,
            ]);

            $body = json_decode($response->getBody()->getContents(), TRUE);

            return [
                'available' => TRUE,
                'host' => $config['host'],
                'port' => $config['port'],
                'models' => $body['models'] ?? [],
                'configured_model' => $config['model'],
            ];
        } catch (GuzzleException $e) {
            return [
                'available' => FALSE,
                'error' => 'Cannot connect to Ollama service',
                'technical_error' => $e->getMessage(),
                'host' => $config['host'],
                'port' => $config['port'],
            ];
        }
    }
}

<?php

namespace Drupal\billoria_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\billoria_core\Service\OllamaAiService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * AI API Controller for Ollama integration.
 */
class AiApiController extends ControllerBase
{

    /**
     * The Ollama AI service.
     *
     * @var \Drupal\billoria_core\Service\OllamaAiService
     */
    protected $ollamaService;

    /**
     * Constructs an AiApiController object.
     *
     * @param \Drupal\billoria_core\Service\OllamaAiService $ollama_service
     *   The Ollama AI service.
     */
    public function __construct(OllamaAiService $ollama_service)
    {
        $this->ollamaService = $ollama_service;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('billoria_core.ollama_ai')
        );
    }

    /**
     * Chat endpoint for AI conversations.
     *
     * POST /api/v1/ai/chat
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The request object.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *   JSON response with AI message.
     */
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), TRUE);

        if (empty($data['message'])) {
            return new JsonResponse([
                'success' => FALSE,
                'error' => 'Message is required',
            ], 400);
        }

        $message = $data['message'];
        $context = $data['context'] ?? ['type' => 'chatbot'];
        $history = $data['history'] ?? [];

        // Validate history format
        if (!is_array($history)) {
            return new JsonResponse([
                'success' => FALSE,
                'error' => 'Invalid history format',
            ], 400);
        }

        $response = $this->ollamaService->chat($message, $context, $history);

        return new JsonResponse($response, $response['success'] ? 200 : 500);
    }

    /**
     * Generate billboard description.
     *
     * POST /api/v1/ai/billboard-description
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The request object.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *   JSON response with generated description.
     */
    public function generateBillboardDescription(Request $request): JsonResponse
    {
        // Check permission
        if (
            !$this->currentUser()->hasPermission('create billboard content') &&
            !$this->currentUser()->hasPermission('administer billboards')
        ) {
            return new JsonResponse([
                'success' => FALSE,
                'error' => 'Access denied',
            ], 403);
        }

        $data = json_decode($request->getContent(), TRUE);

        if (empty($data['title']) && empty($data['location'])) {
            return new JsonResponse([
                'success' => FALSE,
                'error' => 'At least title or location is required',
            ], 400);
        }

        $billboard_data = [
            'title' => $data['title'] ?? '',
            'location' => $data['location'] ?? '',
            'size' => $data['size'] ?? '',
            'type' => $data['type'] ?? 'static',
        ];

        $response = $this->ollamaService->generateBillboardDescription($billboard_data);

        return new JsonResponse($response, $response['success'] ? 200 : 500);
    }

    /**
     * Enhance search query.
     *
     * POST /api/v1/ai/enhance-search
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The request object.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *   JSON response with enhanced query understanding.
     */
    public function enhanceSearch(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), TRUE);

        if (empty($data['query'])) {
            return new JsonResponse([
                'success' => FALSE,
                'error' => 'Query is required',
            ], 400);
        }

        $response = $this->ollamaService->enhanceSearchQuery($data['query']);

        return new JsonResponse($response, $response['success'] ? 200 : 500);
    }

    /**
     * Get billboard recommendations.
     *
     * POST /api/v1/ai/recommendations
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The request object.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *   JSON response with recommendations.
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), TRUE);

        if (empty($data['criteria'])) {
            return new JsonResponse([
                'success' => FALSE,
                'error' => 'User criteria is required',
            ], 400);
        }

        $billboards = $data['billboards'] ?? [];
        $response = $this->ollamaService->recommendBillboards(
            $data['criteria'],
            $billboards
        );

        return new JsonResponse($response, $response['success'] ? 200 : 500);
    }

    /**
     * Check Ollama service status.
     *
     * GET /api/v1/ai/status
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *   JSON response with service status.
     */
    public function checkStatus(): JsonResponse
    {
        // Restrict to admin users
        if (!$this->currentUser()->hasPermission('administer billoria')) {
            return new JsonResponse([
                'available' => FALSE,
                'error' => 'Access denied',
            ], 403);
        }

        $status = $this->ollamaService->checkStatus();

        return new JsonResponse($status, $status['available'] ? 200 : 503);
    }
}

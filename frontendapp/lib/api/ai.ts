/**
 * AI API Client - Ollama Integration
 * 
 * Provides access to AI-powered features:
 * - Chatbot conversations
 * - Billboard description generation
 * - Search query enhancement
 * - Billboard recommendations
 */

const API_BASE_URL =
    process.env.NEXT_PUBLIC_API_BASE_URL ||
    process.env.NEXT_PUBLIC_API_URL ||
    'https://billoria-ad-api.ddev.site';

const FALLBACK_URLS = [
    'https://billoria-ad-api.ddev.site',
    'https://api.billoria-ad.ddev.site',
    'http://billoria-ad-api.ddev.site:33000',
];

async function apiFetch(path: string, options: RequestInit = {}): Promise<Response> {
    const urls = [...new Set([API_BASE_URL, ...FALLBACK_URLS])];
    let lastError: Error | null = null;

    for (const baseUrl of urls) {
        try {
            const response = await fetch(`${baseUrl}${path}`, {
                ...options,
                mode: 'cors',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    ...options.headers,
                },
            });
            if (response.ok || response.status === 400 || response.status === 403 || response.status === 422 || response.status === 500) {
                return response;
            }
        } catch (err) {
            lastError = err as Error;
        }
    }
    throw lastError || new Error('All API URLs failed');
}

// ─── Types ────────────────────────────────────────────────────────────────────

export interface ChatMessage {
    role: 'user' | 'assistant' | 'system';
    content: string;
}

export interface ChatResponse {
    success: boolean;
    message?: string;
    model?: string;
    timestamp?: number;
    error?: string;
    technical_error?: string;
}

export interface BillboardDescriptionRequest {
    title?: string;
    location?: string;
    size?: string;
    type?: 'static' | 'LED' | 'digital';
}

export interface SearchEnhancementResponse extends ChatResponse {
    structured_data?: {
        location?: string;
        size?: string;
        type?: string;
        budget?: string;
        features?: string[];
        intent?: string;
    };
}

export interface RecommendationRequest {
    criteria: Record<string, any>;
    billboards?: any[];
}

export interface AIStatus {
    available: boolean;
    host?: string;
    port?: string;
    models?: any[];
    configured_model?: string;
    error?: string;
    technical_error?: string;
}

// ─── API Methods ──────────────────────────────────────────────────────────────

export const aiAPI = {
    /**
     * Send a chat message to the AI assistant
     * 
     * @param message - User's message
     * @param context - Optional context information
     * @param history - Optional conversation history
     */
    async chat(
        message: string,
        context?: Record<string, any>,
        history?: ChatMessage[]
    ): Promise<ChatResponse> {
        try {
            const response = await apiFetch('/api/v1/ai/chat', {
                method: 'POST',
                body: JSON.stringify({
                    message,
                    context: context || { type: 'chatbot' },
                    history: history || [],
                }),
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('AI chat error:', error);
            return {
                success: false,
                error: 'Failed to connect to AI service. Please try again.',
            };
        }
    },

    /**
     * Generate a billboard description using AI
     * 
     * @param billboardData - Billboard details
     */
    async generateDescription(
        billboardData: BillboardDescriptionRequest
    ): Promise<ChatResponse> {
        try {
            const response = await apiFetch('/api/v1/ai/billboard-description', {
                method: 'POST',
                body: JSON.stringify(billboardData),
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('AI description generation error:', error);
            return {
                success: false,
                error: 'Failed to generate description. Please try again.',
            };
        }
    },

    /**
     * Enhance search query with AI understanding
     * 
     * @param query - User's search query
     */
    async enhanceSearch(query: string): Promise<SearchEnhancementResponse> {
        try {
            const response = await apiFetch('/api/v1/ai/enhance-search', {
                method: 'POST',
                body: JSON.stringify({ query }),
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('AI search enhancement error:', error);
            return {
                success: false,
                error: 'Failed to enhance search query.',
            };
        }
    },

    /**
     * Get AI-powered billboard recommendations
     * 
     * @param criteria - User's criteria
     * @param billboards - Available billboards (optional)
     */
    async getRecommendations(
        criteria: Record<string, any>,
        billboards?: any[]
    ): Promise<ChatResponse> {
        try {
            const response = await apiFetch('/api/v1/ai/recommendations', {
                method: 'POST',
                body: JSON.stringify({
                    criteria,
                    billboards: billboards || [],
                }),
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('AI recommendations error:', error);
            return {
                success: false,
                error: 'Failed to get recommendations.',
            };
        }
    },

    /**
     * Check AI service status (admin only)
     */
    async checkStatus(): Promise<AIStatus> {
        try {
            const response = await apiFetch('/api/v1/ai/status', {
                method: 'GET',
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('AI status check error:', error);
            return {
                available: false,
                error: 'Failed to check AI service status.',
            };
        }
    },
};

// ─── Utility Functions ────────────────────────────────────────────────────────

/**
 * Format conversation history for API calls
 */
export function formatChatHistory(messages: Array<{
    text: string;
    isBot: boolean;
}>): ChatMessage[] {
    return messages.map(msg => ({
        role: msg.isBot ? 'assistant' : 'user',
        content: msg.text,
    }));
}

/**
 * Parse AI response for specific data patterns
 */
export function parseAIResponse(response: string): {
    text: string;
    data?: Record<string, any>;
} {
    // Try to extract JSON data if present
    const jsonMatch = response.match(/\{[\s\S]*\}/);

    if (jsonMatch) {
        try {
            const data = JSON.parse(jsonMatch[0]);
            const text = response.replace(jsonMatch[0], '').trim();
            return { text, data };
        } catch {
            return { text: response };
        }
    }

    return { text: response };
}

export default aiAPI;

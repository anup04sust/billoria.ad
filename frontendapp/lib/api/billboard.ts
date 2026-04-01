import type { Billboard } from '@/types/billboard';

// Support multiple API URLs
const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 
                     process.env.NEXT_PUBLIC_API_URL || 
                     'https://billoria-ad-api.ddev.site';

// Fallback URLs to try if primary fails (HTTPS first, then HTTP)
const FALLBACK_URLS = [
  'https://billoria-ad-api.ddev.site',
  'https://api.billoria-ad.ddev.site',
  'http://billoria-ad-api.ddev.site:33000',
];

async function fetchWithFallback(path: string, options: RequestInit = {}): Promise<Response> {
  const urls = [API_BASE_URL, ...FALLBACK_URLS].filter((url, index, self) => 
    self.indexOf(url) === index // Remove duplicates
  );

  let lastError: Error | null = null;

  for (const baseUrl of urls) {
    try {
      const url = `${baseUrl}${path}`;
      console.log(`Trying API URL: ${url}`);
      
      const response = await fetch(url, {
        ...options,
        mode: 'cors',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          ...options.headers,
        },
      });
      
      if (response.ok) {
        console.log(`✓ Success with: ${url}`);
        return response;
      }
      
      console.warn(`✗ Failed with ${response.status}: ${url}`);
      const errorText = await response.text();
      console.warn(`Response:`, errorText);
    } catch (error) {
      console.warn(`✗ Error with ${baseUrl}:`, error);
      lastError = error as Error;
    }
  }

  throw lastError || new Error('All API URLs failed');
}

interface BillboardListParams {
  division?: number;
  district?: number;
  area_zone?: number;
  media_format?: number;
  availability_status?: number;
  owner_organization?: number;
  min_price?: number;
  max_price?: number;
  is_premium?: boolean;
  is_active?: boolean;
  page?: number;
  limit?: number;
}

interface BillboardListResponse {
  success: boolean;
  message: string;
  data: {
    billboards: Billboard[];
    pager?: {
      page: number;
      limit: number;
      total: number;
      pages: number;
    };
  };
  timestamp: number;
}

interface BillboardResponse {
  success: boolean;
  message: string;
  data: Billboard;
  timestamp: number;
}

export const billboardAPI = {
  /**
   * Get list of billboards with optional filters
   */
  async list(params?: BillboardListParams): Promise<BillboardListResponse> {
    const queryParams = new URLSearchParams();
    
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          queryParams.append(key, String(value));
        }
      });
    }

    const path = `/api/v1/billboard/list${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;
    
    const response = await fetchWithFallback(path, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      cache: 'no-store', // Force fresh data on each request
    });

    if (!response.ok) {
      throw new Error(`Failed to fetch billboards: ${response.statusText}`);
    }

    return response.json();
  },

  /**
   * Get single billboard by ID
   */
  async get(nid: string | number): Promise<BillboardResponse> {
    const response = await fetchWithFallback(`/api/v1/billboard/${nid}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`Failed to fetch billboard: ${response.statusText}`);
    }

    return response.json();
  },

  /**
   * Get single billboard by UUID (for public URLs)
   */
  async getByUuid(uuid: string): Promise<BillboardResponse> {
    const response = await fetchWithFallback(`/api/v1/billboard/uuid/${uuid}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`Failed to fetch billboard: ${response.statusText}`);
    }

    return response.json();
  },

  /**
   * Get all billboards with coordinates (for map display)
   */
  async listForMap(): Promise<Billboard[]> {
    try {
      console.log('Fetching billboards for map...');
      
      const response = await this.list({
        limit: 500, // Increased limit for testing with bulk data
      });

      console.log('API Response:', response);

      // Extract billboards from the nested data structure
      const billboards = response.data.billboards || [];
      console.log('Billboards from API:', billboards.length);

      // Filter only billboards with valid coordinates
      const withCoords = billboards.filter(
        (billboard) => billboard.latitude && billboard.longitude
      );
      console.log('Billboards with coordinates:', withCoords.length);

      return withCoords;
    } catch (error) {
      console.error('Error in listForMap:', error);
      throw error;
    }
  },
};

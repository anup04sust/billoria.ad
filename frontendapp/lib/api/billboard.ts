import type { Billboard } from '@/types/billboard';
import { authAPI } from './auth';

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

  // Auto-attach CSRF token for mutation requests.
  const method = (options.method || 'GET').toUpperCase();
  const mutationHeaders: Record<string, string> = {};
  if (['POST', 'PATCH', 'PUT', 'DELETE'].includes(method)) {
    const csrfToken = authAPI.getCsrfToken();
    if (csrfToken) {
      mutationHeaders['X-CSRF-Token'] = csrfToken;
    }
  }

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
          ...mutationHeaders,
          ...options.headers,
        },
      });
      
      // Return any server response (including 4xx/5xx) — only retry on network errors.
      console.log(`✓ Got response ${response.status} from: ${url}`);
      return response;
    } catch (error) {
      console.warn(`✗ Network error with ${baseUrl}:`, error);
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

export interface TaxonomyOption {
  id: number;
  label: string;
  weight: number;
  divisionId?: number;
  districtId?: number;
  upazilaId?: number;
  cityCorporationId?: number;
}

export interface FieldDefinition {
  type: string;
  label: string;
  required: boolean;
  maxlength?: number;
  placeholder?: string;
  description?: string;
  min?: number;
  max?: number;
  default?: string | boolean;
  options?: string[];
}

export interface TabDefinition {
  id: string;
  label: string;
  fields: string[];
}

export interface FieldConfigData {
  fields: Record<string, FieldDefinition>;
  options: Record<string, TaxonomyOption[]>;
  tabs: TabDefinition[];
}

export interface FieldConfigResponse {
  success: boolean;
  message: string;
  data: FieldConfigData;
  timestamp: number;
}

export interface CreateBillboardData {
  title: string;
  owner_organization: number;
  media_format: number;
  latitude: number;
  longitude: number;
  placement_type?: number;
  display_size?: string;
  width_ft?: number;
  height_ft?: number;
  division?: number;
  district?: number;
  upazila_thana?: number;
  city_corporation?: number;
  area_zone?: number;
  road_name?: number;
  road_type?: number;
  facing_direction?: string;
  traffic_direction?: number;
  visibility_class?: number;
  illumination_type?: number;
  rate_card_price?: number;
  currency?: string;
  commercial_score?: number;
  traffic_score?: number;
  booking_mode?: number;
  availability_status?: number;
  owner_contact_number?: string;
  is_premium?: boolean;
  is_active?: boolean;
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

  /**
   * Get billboards owned by current user's organization(s)
   */
  async myBillboards(params?: Omit<BillboardListParams, 'owner_organization'>): Promise<BillboardListResponse> {
    const queryParams = new URLSearchParams();
    
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          queryParams.append(key, String(value));
        }
      });
    }

    const path = `/api/v1/billboard/my-billboards${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;
    
    const response = await fetchWithFallback(path, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      cache: 'no-store',
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Failed to fetch my billboards: ${response.statusText} - ${errorText}`);
    }

    return response.json();
  },

  /**
   * Autocomplete billboard titles
   */
  async titleSuggest(query: string): Promise<{ id: number; title: string }[]> {
    const response = await fetchWithFallback(`/api/v1/billboard/title-suggest?q=${encodeURIComponent(query)}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
    });

    if (!response.ok) return [];

    const data = await response.json();
    return data?.data?.suggestions || [];
  },

  /**
   * Get billboard field configurations and taxonomy options
   */
  async fieldConfig(): Promise<FieldConfigResponse> {
    const response = await fetchWithFallback('/api/v1/billboard/field-config', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`Failed to fetch field config: ${response.statusText}`);
    }

    return response.json();
  },

  /**
   * Create a new billboard
   */
  async create(data: CreateBillboardData): Promise<BillboardResponse> {
    const response = await fetchWithFallback('/api/v1/billboard/create', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(data),
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Failed to create billboard: ${response.statusText} - ${errorText}`);
    }

    return response.json();
  },

  /**
   * Update an existing billboard
   */
  async update(nid: number, data: Partial<CreateBillboardData>): Promise<BillboardResponse> {
    const response = await fetchWithFallback(`/api/v1/billboard/${nid}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(data),
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Failed to update billboard: ${response.statusText} - ${errorText}`);
    }

    return response.json();
  },

  /**
   * Publish a billboard (validates required fields server-side)
   */
  async publish(nid: number): Promise<BillboardResponse> {
    const response = await fetchWithFallback(`/api/v1/billboard/${nid}/publish`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
    });

    if (!response.ok) {
      const body = await response.json().catch(() => null);
      const msg = body?.error || `Failed to publish billboard: ${response.statusText}`;
      throw new Error(msg);
    }

    return response.json();
  },
};

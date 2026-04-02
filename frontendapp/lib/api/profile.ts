const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_BASE_URL ||
  process.env.NEXT_PUBLIC_API_URL ||
  'https://billoria-ad-api.ddev.site';

async function apiFetch(path: string, options: RequestInit = {}): Promise<Response> {
  return fetch(`${API_BASE_URL}${path}`, {
    ...options,
    mode: 'cors',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...options.headers,
    },
  });
}

// ── Types ─────────────────────────────────────────────────────────────────────

export interface ProfileUser {
  id: number;
  username: string;           // Login username (email)
  name: string | null;        // Display name / Full name
  email: string;
  mobileNumber: string | null;
  emailVerified: boolean;
  phoneVerified: boolean;
  trustScore: number;
  roles: string[];
  designation: string | null;
  department: string | null;
}

export interface ProfileOrganization {
  id: number;
  name: string;
  type: 'brand' | 'agency' | 'owner';
  isActive: boolean;
  logo: string | null;
  officialEmail: string | null;
  officialPhone: string | null;
  website: string | null;
  divisions: { id: number; name: string }[];
  districts: { id: number; name: string }[];
  fullAddress: string | null;
  businessRegNumber: string | null;
  tin: string | null;
  establishmentYear: number | null;
  nationwideService: boolean;
  internationalService: boolean;
  verificationStatus: 'pending' | 'verified' | 'rejected';
  trustScore: number;
  profileCompletion: number;
  verificationDocuments?: { url: string; filename: string; description: string }[];
  verificationDocsStatus?: 'pending_review' | 'verified' | 'rejected' | null;
  brandDetails?: {
    parentCompany: string | null;
    annualBudgetRange: string | null;
    bookingDuration: string | null;
    preferredRegions: { id: number; name: string }[];
  };
  agencyDetails?: {
    agencyServices: string[];
    portfolioSize: string | null;
    ownsInventory: boolean;
    operationsContact: string | null;
    financeContact: string | null;
    preferredRegions: { id: number; name: string }[];
  };
}

export interface UserProfile {
  user: ProfileUser;
  organizations: ProfileOrganization[];
}

export interface TaxonomyTermOption {
  id: number;
  name: string;
  parentId?: number | null;
}

// ── Update payloads ───────────────────────────────────────────────────────────

export interface UpdateUserPayload {
  name?: string | null;         // Full name / Display name
  mobileNumber?: string | null;
  designation?: string | null;
  department?: string | null;
}

export interface UpdateOrgPayload {
  name?: string | null;
  officialEmail?: string | null;
  officialPhone?: string | null;
  website?: string | null;
  fullAddress?: string | null;
  businessRegNumber?: string | null;
  tin?: string | null;
  establishmentYear?: number | null;
  divisions?: number[];
  districts?: number[];
  preferredRegions?: number[];
  nationwideService?: boolean;
  internationalService?: boolean;
  // brand
  parentCompany?: string | null;
  annualBudgetRange?: string | null;
  bookingDuration?: string | null;
  // agency
  portfolioSize?: string | null;
  ownsInventory?: boolean;
  operationsContact?: string | null;
  financeContact?: string | null;
  agencyServices?: string[];
}

// ── API ───────────────────────────────────────────────────────────────────────

export class ProfileAuthError extends Error {
  constructor() { super('PROFILE_AUTH'); }
}

export const profileAPI = {
  async get(): Promise<UserProfile> {
    const res = await apiFetch('/api/v1/user/profile');
    if (res.status === 401 || res.status === 403) throw new ProfileAuthError();
    if (!res.ok) throw new Error(`Profile fetch failed: ${res.status}`);
    const json = await res.json() as { success: boolean; data: UserProfile };
    return json.data;
  },

  async updateUser(payload: UpdateUserPayload): Promise<ProfileUser> {
    const csrfToken = (await import('@/lib/api/auth')).authAPI.getCsrfToken() ?? '';
    const res = await apiFetch('/api/v1/user/profile', {
      method: 'PATCH',
      headers: { 'X-CSRF-Token': csrfToken },
      body: JSON.stringify(payload),
    });
    if (res.status === 401 || res.status === 403) throw new ProfileAuthError();
    if (!res.ok) {
      const err = await res.json().catch(() => ({})) as { message?: string };
      throw new Error(err.message ?? `Update failed: ${res.status}`);
    }
    const json = await res.json() as { success: boolean; data: { user: ProfileUser } };
    return json.data.user;
  },

  async updateOrganization(nid: number, payload: UpdateOrgPayload): Promise<ProfileOrganization> {
    const csrfToken = (await import('@/lib/api/auth')).authAPI.getCsrfToken() ?? '';
    const res = await apiFetch(`/api/v1/organization/${nid}`, {
      method: 'PATCH',
      headers: { 'X-CSRF-Token': csrfToken },
      body: JSON.stringify(payload),
    });
    if (res.status === 401 || res.status === 403) throw new ProfileAuthError();
    if (!res.ok) {
      const err = await res.json().catch(() => ({})) as { message?: string };
      throw new Error(err.message ?? `Update failed: ${res.status}`);
    }
    const json = await res.json() as { success: boolean; data: { organization: ProfileOrganization } };
    return json.data.organization;
  },

  async uploadOrganizationLogo(nid: number, file: File): Promise<ProfileOrganization> {
    const csrfToken = (await import('@/lib/api/auth')).authAPI.getCsrfToken() ?? '';
    const form = new FormData();
    form.append('logo', file);

    const res = await fetch(`${API_BASE_URL}/api/v1/organization/${nid}/logo`, {
      method: 'POST',
      credentials: 'include',
      body: form,
      headers: {
        'X-CSRF-Token': csrfToken,
      },
    });
    if (res.status === 401 || res.status === 403) throw new ProfileAuthError();
    if (!res.ok) {
      const err = await res.json().catch(() => ({})) as { message?: string };
      throw new Error(err.message ?? `Logo upload failed: ${res.status}`);
    }
    const json = await res.json() as { success: boolean; data: { organization: ProfileOrganization } };
    return json.data.organization;
  },

  async deleteOrganizationLogo(nid: number): Promise<ProfileOrganization> {
    const csrfToken = (await import('@/lib/api/auth')).authAPI.getCsrfToken() ?? '';
    const res = await apiFetch(`/api/v1/organization/${nid}/logo`, {
      method: 'DELETE',
      headers: { 'X-CSRF-Token': csrfToken },
    });
    if (res.status === 401 || res.status === 403) throw new ProfileAuthError();
    if (!res.ok) {
      const err = await res.json().catch(() => ({})) as { message?: string };
      throw new Error(err.message ?? `Logo removal failed: ${res.status}`);
    }
    const json = await res.json() as { success: boolean; data: { organization: ProfileOrganization } };
    return json.data.organization;
  },

  async uploadVerificationDocuments(nid: number, formData: FormData): Promise<{ success: boolean; message: string; data?: ProfileOrganization }> {
    const csrfToken = (await import('@/lib/api/auth')).authAPI.getCsrfToken() ?? '';
    
    const res = await fetch(`${API_BASE_URL}/api/v1/organization/${nid}/verification-documents`, {
      method: 'POST',
      credentials: 'include',
      body: formData,
      headers: {
        'X-CSRF-Token': csrfToken,
        // Don't set Content-Type - browser will set it with boundary for multipart/form-data
      },
    });
    
    if (res.status === 401 || res.status === 403) throw new ProfileAuthError();
    
    // Check if response is JSON
    const contentType = res.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await res.text();
      console.error('Non-JSON response:', text);
      throw new Error(`Server returned non-JSON response (${res.status}): ${text.substring(0, 200)}`);
    }
    
    const json = await res.json() as { success: boolean; message: string; data?: { organization: ProfileOrganization } };
    
    if (!res.ok || !json.success) {
      throw new Error(json.message ?? `Document upload failed: ${res.status}`);
    }
    
    return {
      success: json.success,
      message: json.message,
      data: json.data?.organization,
    };
  },

  async getTaxonomyTerms(vocabulary: 'division' | 'district' | 'area_zone'): Promise<TaxonomyTermOption[]> {
    const relationField = vocabulary === 'district'
      ? 'field_division'
      : vocabulary === 'area_zone'
        ? 'field_district'
        : null;
    const include = relationField ? `&include=${relationField}` : '';
    const res = await apiFetch(`/jsonapi/taxonomy_term/${vocabulary}?sort=name&page[limit]=250${include}`, {
      headers: {
        Accept: 'application/vnd.api+json, application/json',
      },
    });
    if (!res.ok) {
      throw new Error(`Failed to load ${vocabulary} terms: ${res.status}`);
    }

    const json = await res.json() as {
      data: Array<{
        id?: string;
        attributes?: {
          drupal_internal__tid?: number;
          name?: string;
        };
        relationships?: {
          field_division?: {
            data?: {
              id?: string;
              meta?: {
                drupal_internal__target_id?: string | number;
              };
            } | null;
          };
          field_district?: {
            data?: {
              id?: string;
              meta?: {
                drupal_internal__target_id?: string | number;
              };
            } | null;
          };
        };
      }>;
      included?: Array<{
        id?: string;
        attributes?: {
          drupal_internal__tid?: number;
        };
      }>;
    };

    const includedTidByUuid = new Map<string, number>();
    for (const item of json.included ?? []) {
      if (item.id && item.attributes?.drupal_internal__tid) {
        includedTidByUuid.set(item.id, item.attributes.drupal_internal__tid);
      }
    }

    return (json.data ?? [])
      .map((item) => ({
        id: item.attributes?.drupal_internal__tid ?? 0,
        name: item.attributes?.name ?? '',
        parentId: (() => {
          if (!relationField) {
            return null;
          }
          const relationData = relationField === 'field_division'
            ? item.relationships?.field_division?.data
            : item.relationships?.field_district?.data;

          return Number(
            relationData?.meta?.drupal_internal__target_id ??
            (relationData?.id ? includedTidByUuid.get(relationData.id) ?? 0 : 0)
          ) || null;
        })(),
      }))
      .filter((item) => item.id > 0 && item.name);
  },
};

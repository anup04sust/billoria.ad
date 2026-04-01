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
  name: string;
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
  officialEmail: string | null;
  officialPhone: string | null;
  website: string | null;
  division: { id: number; name: string } | null;
  district: { id: number; name: string } | null;
  fullAddress: string | null;
  businessRegNumber: string | null;
  tin: string | null;
  establishmentYear: number | null;
  verificationStatus: 'pending' | 'verified' | 'rejected';
  trustScore: number;
  profileCompletion: number;
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

// ── Update payloads ───────────────────────────────────────────────────────────

export interface UpdateUserPayload {
  mobileNumber?: string | null;
  designation?: string | null;
  department?: string | null;
}

export interface UpdateOrgPayload {
  officialEmail?: string | null;
  officialPhone?: string | null;
  website?: string | null;
  fullAddress?: string | null;
  businessRegNumber?: string | null;
  tin?: string | null;
  establishmentYear?: number | null;
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
};

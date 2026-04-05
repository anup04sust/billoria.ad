const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ||
                     process.env.NEXT_PUBLIC_API_URL ||
                     'https://billoria-ad-api.ddev.site';

export interface SiteSettings {
  site_name: string;
  site_slogan: string;
  site_mail: string;
  front_page: string;
}

interface SiteSettingsResponse {
  success: boolean;
  data: SiteSettings;
  timestamp: number;
}

/**
 * Fetch site settings from Drupal (server-side safe — no auth needed).
 */
export async function getSiteSettings(): Promise<SiteSettings> {
  const defaults: SiteSettings = {
    site_name: 'Billoria',
    site_slogan: 'Billboard Marketplace Platform',
    site_mail: '',
    front_page: '/',
  };

  try {
    const res = await fetch(`${API_BASE_URL}/api/v1/site-settings`, {
      next: { revalidate: 3600 }, // Cache for 1 hour
    });

    if (!res.ok) return defaults;

    const json: SiteSettingsResponse = await res.json();
    return json.success ? json.data : defaults;
  } catch {
    return defaults;
  }
}

export interface Billboard {
  id: string;
  uuid: string;
  title: string;
  billboard_id?: string;
  status: 'published' | 'unpublished';
  created: string;
  updated: number;
  media_format?: {
    id: string;
    label: string;
  };
  placement_type?: {
    id: string;
    label: string;
  };
  display_size?: string;
  width_ft?: string;
  height_ft?: string;
  division?: {
    id: string;
    label: string;
  };
  district?: {
    id: string;
    label: string;
  };
  area_zone?: {
    id: string;
    label: string;
  };
  road_name?: {
    id: string;
    label: string;
  };
  road_type?: {
    id: string;
    label: string;
  };
  latitude?: string;
  longitude?: string;
  facing_direction?: string;
  rate_card_price?: string;
  currency?: string;
  commercial_score?: string;
  traffic_score?: string;
  booking_mode?: {
    id: string;
    label: string;
  };
  availability_status?: {
    id: string;
    label: string;
  };
  owner_organization?: {
    id: string;
    label: string;
  };
  is_premium?: string;
  is_active?: string;
  hero_image?: {
    original: string;
    large: string;
    medium: string;
    thumbnail: string;
    alt: string;
    title: string;
    width: string;
    height: string;
    mime_type: string;
    size: number;
  };
  gallery?: Array<{
    original: string;
    large: string;
    thumbnail: string;
    alt: string;
    title: string;
    width: string;
    height: string;
    mime_type: string;
    size: number;
  }>;
}

export interface BillboardListResponse {
  success: boolean;
  message: string;
  data: Billboard[];
  pager?: {
    page: number;
    limit: number;
    total: number;
    pages: number;
  };
  timestamp: number;
}

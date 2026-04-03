const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_BASE_URL ||
  process.env.NEXT_PUBLIC_API_URL ||
  'http://billoria-ad-api.ddev.site';

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

export interface Notification {
  nid: number;
  uid: number;
  type: string;
  title: string;
  message: string;
  metadata: Record<string, unknown>;
  priority: 'low' | 'normal' | 'high' | 'urgent';
  created: number;
  is_read: boolean;
  expires_at: number | null;
}

export interface NotificationListResponse {
  success: boolean;
  data: {
    notifications: Notification[];
    unreadCount: number;
    pagination: {
      limit: number;
      offset: number;
      hasMore: boolean;
    };
  };
  timestamp: number;
}

export interface UnreadCountResponse {
  success: boolean;
  data: {
    unreadCount: number;
  };
  timestamp: number;
}

export interface MarkReadResponse {
  success: boolean;
  message: string;
  data: {
    unreadCount: number;
  };
  timestamp: number;
}

// ── API Functions ─────────────────────────────────────────────────────────────

/**
 * Fetch notifications for the current user.
 * 
 * @param options - Query parameters
 * @returns Notification list response
 */
export async function getNotifications(options: {
  limit?: number;
  offset?: number;
  unreadOnly?: boolean;
  type?: string;
} = {}): Promise<NotificationListResponse> {
  const params = new URLSearchParams();
  if (options.limit) params.set('limit', String(options.limit));
  if (options.offset) params.set('offset', String(options.offset));
  if (options.unreadOnly) params.set('unread_only', 'true');
  if (options.type) params.set('type', options.type);

  const queryString = params.toString();
  const url = `/api/v1/notifications${queryString ? `?${queryString}` : ''}`;

  const response = await apiFetch(url);

  if (!response.ok) {
    throw new Error(`Failed to fetch notifications: ${response.statusText}`);
  }

  return response.json();
}

/**
 * Get the count of unread notifications.
 * 
 * @returns Unread count response
 */
export async function getUnreadCount(): Promise<UnreadCountResponse> {
  const response = await apiFetch('/api/v1/notifications/unread-count');

  if (!response.ok) {
    throw new Error(`Failed to fetch unread count: ${response.statusText}`);
  }

  return response.json();
}

/**
 * Mark a notification as read.
 * 
 * @param nid - Notification ID
 * @returns Mark read response
 */
export async function markAsRead(nid: number): Promise<MarkReadResponse> {
  const response = await apiFetch(`/api/v1/notifications/${nid}/mark-read`, {
    method: 'POST',
  });

  if (!response.ok) {
    throw new Error(`Failed to mark notification as read: ${response.statusText}`);
  }

  return response.json();
}

/**
 * Mark all notifications as read.
 * 
 * @returns Mark all read response
 */
export async function markAllAsRead(): Promise<{
  success: boolean;
  message: string;
  data: {
    updatedCount: number;
    unreadCount: number;
  };
  timestamp: number;
}> {
  const response = await apiFetch('/api/v1/notifications/mark-all-read', {
    method: 'POST',
  });

  if (!response.ok) {
    throw new Error(`Failed to mark all notifications as read: ${response.statusText}`);
  }

  return response.json();
}

/**
 * Delete a notification.
 * 
 * @param nid - Notification ID
 * @returns Delete response
 */
export async function deleteNotification(nid: number): Promise<{
  success: boolean;
  message: string;
  data: {
    unreadCount: number;
  };
  timestamp: number;
}> {
  const response = await apiFetch(`/api/v1/notifications/${nid}`, {
    method: 'DELETE',
  });

  if (!response.ok) {
    throw new Error(`Failed to delete notification: ${response.statusText}`);
  }

  return response.json();
}

// Export a single object with all notification API functions
export const notificationAPI = {
  getNotifications,
  getUnreadCount,
  markAsRead,
  markAllAsRead,
  deleteNotification,
};

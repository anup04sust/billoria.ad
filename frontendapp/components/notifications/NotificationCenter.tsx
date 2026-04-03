'use client';

import { useState, useEffect, useRef } from 'react';
import { notificationAPI, type Notification } from '@/lib/api/notifications';
import { trackEvent, AnalyticsEvents } from '@/lib/firebase/analytics';
import { authAPI } from '@/lib/api/auth';
import './notification-center.css';

// ── Icons ─────────────────────────────────────────────────────────────────────
const IconBell = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" />
  </svg>
);

// ── Helpers ───────────────────────────────────────────────────────────────────
function getNotificationsRoute(roles: string[]): string {
  if (roles.includes('platform_admin')) return '/admin/dashboard/notifications';
  if (roles.includes('agency'))          return '/agency/dashboard/notifications';
  if (roles.includes('billboard_owner')) return '/owner/dashboard/notifications';
  if (roles.includes('brand_user'))      return '/brand/dashboard/notifications';
  return '/dashboard/notifications'; // fallback
}

/**
 * Notification center component for dashboard topbar.
 * 
 * Features:
 * - Shows unread notification count badge
 * - Dropdown list of recent notifications
 * - Marks notifications as read when clicked
 * - Auto-refreshes every minute
 * - Click outside to close dropdown
 */
export function NotificationCenter() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [isOpen, setIsOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [notificationsUrl, setNotificationsUrl] = useState('/dashboard/notifications');
  const dropdownRef = useRef<HTMLDivElement>(null);

  // Get user and set notifications URL
  useEffect(() => {
    const user = authAPI.getCurrentUser();
    if (user?.roles) {
      setNotificationsUrl(getNotificationsRoute(user.roles));
    }
  }, []);

  // Fetch notifications
  const fetchNotifications = async () => {
    setIsLoading(true);
    try {
      const response = await notificationAPI.getNotifications({ limit: 10 });
      
      if (response.success) {
        setNotifications(response.data.notifications);
        setUnreadCount(response.data.unreadCount);
      }
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
    } finally {
      setIsLoading(false);
    }
  };

  // Mark notification as read
  const handleMarkAsRead = async (nid: number) => {
    try {
      const response = await notificationAPI.markAsRead(nid);
      
      if (response.success) {
        setNotifications(prev =>
          prev.map(n => (n.nid === nid ? { ...n, is_read: true } : n))
        );
        setUnreadCount(response.data.unreadCount);
        
        // Track analytics
        await trackEvent(AnalyticsEvents.NOTIFICATION_CLICKED, {
          notification_id: nid,
        });
      }
    } catch (error) {
      console.error('Failed to mark notification as read:', error);
    }
  };

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    }

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
      return () => document.removeEventListener('mousedown', handleClickOutside);
    }
  }, [isOpen]);

  // Fetch notifications on mount and periodically
  useEffect(() => {
    fetchNotifications();
    
    const interval = setInterval(fetchNotifications, 60000); // Every minute
    
    return () => clearInterval(interval);
  }, []);

  // Format timestamp
  const formatTime = (timestamp: number): string => {
    const date = new Date(timestamp * 1000);
    const now = new Date();
    const diff = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  };

  // Get notification icon based on type
  const getNotificationIcon = (type: string): string => {
    switch (type) {
      case 'booking': return '📅';
      case 'payment': return '💰';
      case 'verification': return '✅';
      case 'system': return '⚙️';
      case 'announcement': return '📢';
      case 'welcome': return '👋';
      case 'promotion': return '🎉';
      default: return '🔔';
    }
  };

  return (
    <div className="nc-wrap" ref={dropdownRef}>
      <button
        className="nc-bell-btn"
        type="button"
        aria-label="Notifications"
        aria-expanded={isOpen}
        onClick={() => setIsOpen(!isOpen)}
      >
        <IconBell />
        {unreadCount > 0 && (
          <span className="nc-badge">
            {unreadCount > 9 ? '9+' : unreadCount}
          </span>
        )}
      </button>

      {isOpen && (
        <div className="nc-dropdown">
          <div className="nc-header">
            <h3 className="nc-title">Notifications</h3>
            {unreadCount > 0 && (
              <span className="nc-count">{unreadCount} new</span>
            )}
          </div>
          
          <div className="nc-list">
            {isLoading ? (
              <div className="nc-empty">Loading...</div>
            ) : notifications.length === 0 ? (
              <div className="nc-empty">
                <span className="nc-empty-icon">🔔</span>
                <p>No notifications yet</p>
              </div>
            ) : (
              notifications.map((notification) => (
                <button
                  key={notification.nid}
                  onClick={() => {
                    handleMarkAsRead(notification.nid);
                    setIsOpen(false);
                  }}
                  className={`nc-item ${!notification.is_read ? 'nc-item--unread' : ''}`}
                >
                  <div className="nc-item__icon">
                    {getNotificationIcon(notification.type)}
                  </div>
                  <div className="nc-item__content">
                    <p className="nc-item__title">{notification.title}</p>
                    <p className="nc-item__message">{notification.message}</p>
                    <span className="nc-item__time">{formatTime(notification.created)}</span>
                  </div>
                  {!notification.is_read && (
                    <span className="nc-item__dot" />
                  )}
                </button>
              ))
            )}
          </div>
          
          {notifications.length > 0 && (
            <div className="nc-footer">
              <a href={notificationsUrl} className="nc-footer-link">
                View all notifications
              </a>
            </div>
          )}
        </div>
      )}
    </div>
  );
}

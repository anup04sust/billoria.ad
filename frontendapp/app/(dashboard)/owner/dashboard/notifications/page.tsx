'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { authAPI } from '@/lib/api/auth';
import { notificationAPI, type Notification } from '@/lib/api/notifications';
import '@/components/dashboard/dashboard-sidebar.css';
import '@/components/dashboard/dashboard-topbar.css';
import '@/components/dashboard/dashboard.css';
import '../../agency/dashboard/notifications/notifications-page.css';

export default function OwnerNotificationsPage() {
  const router = useRouter();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [filter, setFilter] = useState<string>('all');

  useEffect(() => {
    if (!authAPI.isLoggedIn()) router.replace('/login');
  }, [router]);

  useEffect(() => {
    fetchNotifications();
  }, [filter]);

  const fetchNotifications = async () => {
    setIsLoading(true);
    try {
      const params: any = { limit: 50 };
      if (filter === 'unread') {
        params.unreadOnly = true;
      } else if (filter !== 'all') {
        params.type = filter;
      }

      const response = await notificationAPI.getNotifications(params);
      
      if (response.success) {
        setNotifications(response.data.notifications);
      }
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleMarkAsRead = async (nid: number) => {
    try {
      const response = await notificationAPI.markAsRead(nid);
      
      if (response.success) {
        setNotifications(prev =>
          prev.map(n => (n.nid === nid ? { ...n, is_read: true } : n))
        );
      }
    } catch (error) {
      console.error('Failed to mark as read:', error);
    }
  };

  const handleMarkAllAsRead = async () => {
    try {
      const response = await notificationAPI.markAllAsRead();
      
      if (response.success) {
        setNotifications(prev =>
          prev.map(n => ({ ...n, is_read: true }))
        );
      }
    } catch (error) {
      console.error('Failed to mark all as read:', error);
    }
  };

  const handleDelete = async (nid: number) => {
    try {
      const response = await notificationAPI.deleteNotification(nid);
      
      if (response.success) {
        setNotifications(prev => prev.filter(n => n.nid !== nid));
      }
    } catch (error) {
      console.error('Failed to delete notification:', error);
    }
  };

  const formatTime = (timestamp: number): string => {
    const date = new Date(timestamp * 1000);
    const now = new Date();
    const diff = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)} minutes ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
    if (diff < 604800) return `${Math.floor(diff / 86400)} days ago`;
    
    return date.toLocaleDateString('en-US', { 
      year: 'numeric',
      month: 'short', 
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

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

  const unreadCount = notifications.filter(n => !n.is_read).length;

  return (
    <div className="db-shell">
      <DashboardSidebar role="owner" />
      <div className="db-main">
        <DashboardTopbar
          role="owner"
          title="Notifications"
          subtitle="View and manage your notifications"
        />
        <div className="db-content">

          {/* Header with actions */}
          <div className="np-header">
            <div className="np-filters">
              <button
                className={`np-filter-btn ${filter === 'all' ? 'np-filter-btn--active' : ''}`}
                onClick={() => setFilter('all')}
              >
                All
              </button>
              <button
                className={`np-filter-btn ${filter === 'unread' ? 'np-filter-btn--active' : ''}`}
                onClick={() => setFilter('unread')}
              >
                Unread
              </button>
              <button
                className={`np-filter-btn ${filter === 'booking' ? 'np-filter-btn--active' : ''}`}
                onClick={() => setFilter('booking')}
              >
                Bookings
              </button>
              <button
                className={`np-filter-btn ${filter === 'payment' ? 'np-filter-btn--active' : ''}`}
                onClick={() => setFilter('payment')}
              >
                Payments
              </button>
              <button
                className={`np-filter-btn ${filter === 'system' ? 'np-filter-btn--active' : ''}`}
                onClick={() => setFilter('system')}
              >
                System
              </button>
            </div>

            {unreadCount > 0 && (
              <button
                className="np-mark-all-btn"
                onClick={handleMarkAllAsRead}
              >
                Mark all as read
              </button>
            )}
          </div>

          {/* Notifications list */}
          <div className="np-list">
            {isLoading ? (
              <div className="np-empty">
                <div className="np-spinner" />
                <p>Loading notifications...</p>
              </div>
            ) : notifications.length === 0 ? (
              <div className="np-empty">
                <span className="np-empty-icon">🔔</span>
                <h3>No notifications</h3>
                <p>You're all caught up! Check back later for updates.</p>
              </div>
            ) : (
              notifications.map((notification) => (
                <div
                  key={notification.nid}
                  className={`np-item ${!notification.is_read ? 'np-item--unread' : ''}`}
                >
                  <div className="np-item__icon">
                    {getNotificationIcon(notification.type)}
                  </div>
                  <div className="np-item__content">
                    <div className="np-item__header">
                      <h4 className="np-item__title">{notification.title}</h4>
                      <span className="np-item__time">{formatTime(notification.created)}</span>
                    </div>
                    <p className="np-item__message">{notification.message}</p>
                    {!notification.is_read && (
                      <span className="np-item__badge">New</span>
                    )}
                  </div>
                  <div className="np-item__actions">
                    {!notification.is_read && (
                      <button
                        className="np-action-btn"
                        onClick={() => handleMarkAsRead(notification.nid)}
                        title="Mark as read"
                      >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75">
                          <polyline points="20 6 9 17 4 12" />
                        </svg>
                      </button>
                    )}
                    <button
                      className="np-action-btn np-action-btn--delete"
                      onClick={() => handleDelete(notification.nid)}
                      title="Delete"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                      </svg>
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>

        </div>
      </div>
    </div>
  );
}

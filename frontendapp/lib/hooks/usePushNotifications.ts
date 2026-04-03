'use client';

import { useState, useEffect, useCallback } from 'react';
import { getToken, onMessage, Messaging } from 'firebase/messaging';
import { getFirebaseMessaging, isFirebaseConfigured } from '@/lib/firebase/config';

const VAPID_KEY = process.env.NEXT_PUBLIC_FIREBASE_VAPID_KEY;
const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'https://billoria-ad-api.ddev.site';

interface PushNotificationState {
  supported: boolean;
  permission: NotificationPermission;
  token: string | null;
  isLoading: boolean;
  error: string | null;
}

interface UsePushNotificationsReturn extends PushNotificationState {
  requestPermission: () => Promise<boolean>;
  unregister: () => Promise<void>;
}

/**
 * React hook for managing push notifications with Firebase Cloud Messaging.
 * 
 * @param userId - The Drupal user ID (pass null if not logged in)
 * @returns Push notification state and control functions
 */
export function usePushNotifications(userId: string | null): UsePushNotificationsReturn {
  const [state, setState] = useState<PushNotificationState>({
    supported: false,
    permission: 'default',
    token: null,
    isLoading: true,
    error: null,
  });

  // Check if notifications are supported
  useEffect(() => {
    const checkSupport = async () => {
      const supported = 
        typeof window !== 'undefined' &&
        'Notification' in window &&
        'serviceWorker' in navigator &&
        isFirebaseConfigured();
      
      setState(prev => ({
        ...prev,
        supported,
        permission: supported ? Notification.permission : 'denied',
        isLoading: false,
        error: supported ? null : 'Push notifications are not supported in this browser',
      }));
    };
    
    checkSupport();
  }, []);

  // Register service worker
  useEffect(() => {
    if (!state.supported || typeof window === 'undefined') return;

    const registerServiceWorker = async () => {
      try {
        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
        console.log('Service Worker registered:', registration);
        
        // Send Firebase config to service worker
        if (registration.active) {
          registration.active.postMessage({
            type: 'FIREBASE_CONFIG',
            config: {
              apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY,
              authDomain: process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN,
              projectId: process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID,
              storageBucket: process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET,
              messagingSenderId: process.env.NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID,
              appId: process.env.NEXT_PUBLIC_FIREBASE_APP_ID,
            },
          });
        }
      } catch (error) {
        console.error('Service Worker registration failed:', error);
        setState(prev => ({
          ...prev,
          error: 'Failed to register service worker',
        }));
      }
    };

    registerServiceWorker();
  }, [state.supported]);

  // Listen for foreground messages
  useEffect(() => {
    if (!state.supported || !state.token) return;

    let unsubscribe: (() => void) | undefined;

    const setupMessageListener = async () => {
      const messaging = await getFirebaseMessaging();
      if (!messaging) return;

      unsubscribe = onMessage(messaging, (payload) => {
        console.log('Foreground message received:', payload);
        
        // Show notification when app is in foreground
        if (Notification.permission === 'granted') {
          const title = payload.notification?.title || 'Billoria Notification';
          const options = {
            body: payload.notification?.body || '',
            icon: payload.notification?.icon || '/billoria-logo.svg',
            badge: '/billoria-eye-logo.svg',
            data: payload.data,
          };
          
          new Notification(title, options);
        }
      });
    };

    setupMessageListener();

    return () => {
      if (unsubscribe) unsubscribe();
    };
  }, [state.supported, state.token]);

  /**
   * Request notification permission and register FCM token.
   */
  const requestPermission = useCallback(async (): Promise<boolean> => {
    if (!state.supported) {
      setState(prev => ({ ...prev, error: 'Notifications are not supported' }));
      return false;
    }

    if (!userId) {
      setState(prev => ({ ...prev, error: 'User must be logged in' }));
      return false;
    }

    if (!VAPID_KEY || VAPID_KEY === 'your_vapid_key_here') {
      setState(prev => ({ ...prev, error: 'Firebase VAPID key not configured' }));
      return false;
    }

    setState(prev => ({ ...prev, isLoading: true, error: null }));

    try {
      // Request permission
      const permission = await Notification.requestPermission();
      
      if (permission !== 'granted') {
        setState(prev => ({
          ...prev,
          permission,
          isLoading: false,
          error: 'Notification permission denied',
        }));
        return false;
      }

      // Get FCM token
      const messaging = await getFirebaseMessaging();
      if (!messaging) {
        throw new Error('Failed to initialize Firebase Messaging');
      }

      const token = await getToken(messaging, {
        vapidKey: VAPID_KEY,
      });

      if (!token) {
        throw new Error('Failed to get FCM token');
      }

      // Register token with Drupal backend
      const response = await fetch(`${API_BASE_URL}/api/v1/notifications/fcm/register`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          token,
          deviceType: 'web',
          deviceName: navigator.userAgent.includes('Mobile') ? 'Mobile Browser' : 'Desktop Browser',
        }),
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        console.error('FCM registration failed:', response.status, errorData);
        throw new Error(errorData.error || `Failed to register FCM token (${response.status})`);
      }

      const result = await response.json();
      console.log('FCM token registered:', result);

      setState(prev => ({
        ...prev,
        permission: 'granted',
        token,
        isLoading: false,
        error: null,
      }));

      return true;
    } catch (error) {
      console.error('Error requesting notification permission:', error);
      setState(prev => ({
        ...prev,
        isLoading: false,
        error: error instanceof Error ? error.message : 'Failed to enable notifications',
      }));
      return false;
    }
  }, [state.supported, userId]);

  /**
   * Unregister FCM token from backend.
   */
  const unregister = useCallback(async (): Promise<void> => {
    if (!state.token) return;

    try {
      const response = await fetch(`${API_BASE_URL}/api/v1/notifications/fcm/unregister`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          token: state.token,
        }),
      });

      if (response.ok) {
        setState(prev => ({
          ...prev,
          token: null,
        }));
        console.log('FCM token unregistered successfully');
      } else {
        console.error('Failed to unregister FCM token:', response.status);
      }
    } catch (error) {
      console.error('Error unregistering FCM token:', error);
    }
  }, [state.token]);

  return {
    ...state,
    requestPermission,
    unregister,
  };
}

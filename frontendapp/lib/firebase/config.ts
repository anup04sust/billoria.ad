import { initializeApp, getApps, FirebaseApp } from 'firebase/app';
import { getMessaging, Messaging, isSupported } from 'firebase/messaging';
import { getAnalytics, Analytics, isSupported as isAnalyticsSupported } from 'firebase/analytics';

const firebaseConfig = {
  apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY,
  authDomain: process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN,
  projectId: process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID,
  storageBucket: process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: process.env.NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID,
  appId: process.env.NEXT_PUBLIC_FIREBASE_APP_ID,
  measurementId: process.env.NEXT_PUBLIC_FIREBASE_MEASUREMENT_ID,
};

let app: FirebaseApp | undefined;
let messaging: Messaging | undefined;
let analytics: Analytics | undefined;

/**
 * Initialize Firebase app (singleton pattern).
 */
export function getFirebaseApp(): FirebaseApp | null {
  if (typeof window === 'undefined') return null;
  
  if (!app && getApps().length === 0) {
    try {
      app = initializeApp(firebaseConfig);
    } catch (error) {
      console.error('Firebase initialization error:', error);
      return null;
    }
  }
  
  return app || getApps()[0] || null;
}

/**
 * Get Firebase Messaging instance.
 */
export async function getFirebaseMessaging(): Promise<Messaging | null> {
  if (typeof window === 'undefined') return null;
  
  try {
    const supported = await isSupported();
    if (!supported) {
      console.warn('Firebase Messaging is not supported in this browser');
      return null;
    }
    
    const app = getFirebaseApp();
    if (!app) return null;
    
    if (!messaging) {
      messaging = getMessaging(app);
    }
    
    return messaging;
  } catch (error) {
    console.error('Firebase Messaging error:', error);
    return null;
  }
}

/**
 * Get Firebase Analytics instance.
 */
export async function getFirebaseAnalytics(): Promise<Analytics | null> {
  // Analytics only works in browser, not in SSR
  if (typeof window === 'undefined') return null;
  
  try {
    const supported = await isAnalyticsSupported();
    if (!supported) {
      console.warn('Firebase Analytics is not supported in this browser');
      return null;
    }
    
    const app = getFirebaseApp();
    if (!app) return null;
    
    if (!analytics) {
      analytics = getAnalytics(app);
    }
    
    return analytics;
  } catch (error) {
    console.error('Firebase Analytics error:', error);
    return null;
  }
}

/**
 * Check if Firebase is properly configured.
 */
export function isFirebaseConfigured(): boolean {
  return !!(
    firebaseConfig.apiKey &&
    firebaseConfig.projectId &&
    firebaseConfig.messagingSenderId &&
    firebaseConfig.apiKey !== 'your_api_key_here'
  );
}

// Firebase Service Worker for background push notifications
// This file must be in the /public directory and served at the root URL

// Import Firebase scripts
importScripts('https://www.gstatic.com/firebasejs/10.13.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.13.2/firebase-messaging-compat.js');

// Initialize Firebase in service worker
// Note: These values are read from the page that registers this service worker
// We'll pass them via query parameters from the registration code

let firebaseApp;

// Listen for messages from the main thread to configure Firebase
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'FIREBASE_CONFIG') {
    const config = event.data.config;
    
    if (!firebaseApp) {
      firebaseApp = firebase.initializeApp(config);
    }
  }
});

// Handle background messages
self.addEventListener('push', (event) => {
  console.log('[Service Worker] Push received:', event);
});

// This will be called when a notification is clicked
self.addEventListener('notificationclick', (event) => {
  console.log('[Service Worker] Notification clicked:', event);
  
  event.notification.close();
  
  // Get the URL to open from the notification data
  const urlToOpen = event.notification.data?.url || '/';
  
  // Open the URL or focus existing tab
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      // Check if there's already a window open
      for (const client of clientList) {
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus();
        }
      }
      // If not, open a new window
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
});

// Initialize messaging when Firebase is configured
if (firebaseApp) {
  const messaging = firebase.messaging();
  
  // Handle background messages
  messaging.onBackgroundMessage((payload) => {
    console.log('[Service Worker] Background message received:', payload);
    
    const notificationTitle = payload.notification?.title || 'Billoria Notification';
    const notificationOptions = {
      body: payload.notification?.body || '',
      icon: payload.notification?.icon || '/billoria-logo.svg',
      badge: '/billoria-eye-logo.svg',
      data: {
        url: payload.data?.click_action || payload.fcmOptions?.link || '/',
        ...payload.data,
      },
    };
    
    return self.registration.showNotification(notificationTitle, notificationOptions);
  });
}

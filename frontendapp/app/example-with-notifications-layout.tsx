// Example: app/layout.tsx with push notifications
import { PushNotificationPrompt } from '@/components/notifications/PushNotificationPrompt';
import { NotificationCenter } from '@/components/notifications/NotificationCenter';

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body>
        <header>
          <nav className="flex items-center justify-between p-4">
            <div className="logo">Billoria</div>
            
            <div className="flex items-center gap-4">
              {/* Add notification center to navbar */}
              <NotificationCenter />
              
              {/* Other nav items */}
              <a href="/dashboard">Dashboard</a>
              <a href="/profile">Profile</a>
            </div>
          </nav>
        </header>
        
        <main>{children}</main>
        
        <footer>
          <p>&copy; 2026 Billoria</p>
        </footer>
        
        {/* Auto-prompt for push notifications when user logs in */}
        <PushNotificationPrompt />
      </body>
    </html>
  );
}

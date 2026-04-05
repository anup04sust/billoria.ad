import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { AuthSync } from "@/components/auth/AuthSync";
import { PushNotificationPrompt } from "@/components/notifications/PushNotificationPrompt";
import { AnalyticsProvider } from "@/components/analytics/AnalyticsProvider";
import { getSiteSettings } from "@/lib/api/settings";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export async function generateMetadata(): Promise<Metadata> {
  const settings = await getSiteSettings();
  return {
    title: {
      default: settings.site_name,
      template: `%s | ${settings.site_name}`,
    },
    description: settings.site_slogan,
    icons: {
      icon: '/favicon.svg',
    },
  };
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html
      lang="en"
      className={`${geistSans.variable} ${geistMono.variable} h-full antialiased`}
    >
      <body className="min-h-full flex flex-col" suppressHydrationWarning>
        <AuthSync />
        <AnalyticsProvider />
        {children}
        <PushNotificationPrompt />
      </body>
    </html>
  );
}

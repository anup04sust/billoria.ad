'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { authAPI } from '@/lib/api/auth';

export function getDashboardRoute(roles: string[]): string {
  if (roles.includes('platform_admin')) return '/admin/dashboard';
  if (roles.includes('agency'))          return '/agency/dashboard';
  if (roles.includes('billboard_owner')) return '/owner/dashboard';
  if (roles.includes('brand_user'))      return '/brand/dashboard';
  return '/billboards'; // fallback for unknown roles
}

export default function DashboardRedirectPage() {
  const router = useRouter();

  useEffect(() => {
    const user = authAPI.getCurrentUser();
    if (!user) {
      router.replace('/login');
    } else {
      router.replace(getDashboardRoute(user.roles));
    }
  }, [router]);

  return null;
}

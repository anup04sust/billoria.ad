'use client';

import Link from 'next/link';
import Image from 'next/image';
import { useEffect, useState } from 'react';
import { usePathname, useRouter } from 'next/navigation';
import { authAPI } from '@/lib/api/auth';
import { IconGrid, IconBillboardAlt, IconCalendar, IconBarChart, IconSearch, IconBriefcase, IconCurrency, IconUsers, IconSettings, IconLogout, IconList, IconPlus, IconChevronDown } from '@/lib/icons/ui-icons';
import './dashboard-sidebar.css';

interface NavItem {
  label: string;
  href?: string;
  icon: React.ReactNode;
  submenu?: NavItem[];
}

interface DashboardSidebarProps {
  role: 'agency' | 'brand' | 'owner' | 'admin';
}

const ICONS = {
  grid: <IconGrid />,
  billboard: <IconBillboardAlt />,
  calendar: <IconCalendar />,
  chart: <IconBarChart />,
  search: <IconSearch />,
  briefcase: <IconBriefcase />,
  coin: <IconCurrency />,
  users: <IconUsers />,
  settings: <IconSettings />,
  logout: <IconLogout />,
  list: <IconList />,
  plus: <IconPlus />,
  chevronDown: <IconChevronDown />,
};

const NAV_BY_ROLE: Record<DashboardSidebarProps['role'], NavItem[]> = {
  agency: [
    { label: 'Overview', href: '/agency/dashboard', icon: ICONS.grid },
    { 
      label: 'Own BillBoard',
      icon: ICONS.billboard,
      submenu: [
        { label: 'List', href: '/agency/billboards', icon: ICONS.list },
        { label: 'Add New', href: '/agency/billboards/create', icon: ICONS.plus },
      ]
    },
    { label: 'Campaigns', href: '/agency/campaigns', icon: ICONS.briefcase },
    { label: 'Bookings', href: '/agency/bookings', icon: ICONS.calendar },
    { label: 'Analytics', href: '/agency/analytics', icon: ICONS.chart },
    { label: 'Clients', href: '/agency/clients', icon: ICONS.users },
    { label: 'Profile', href: '/agency/profile', icon: ICONS.users },
    { label: 'Settings', href: '/agency/settings', icon: ICONS.settings },
  ],
  brand: [
    { label: 'Overview', href: '/brand/dashboard', icon: ICONS.grid },
    { label: 'Find Billboards', href: '/billboards', icon: ICONS.search },
    { label: 'My Campaigns', href: '/brand/campaigns', icon: ICONS.briefcase },
    { label: 'Bookings', href: '/brand/bookings', icon: ICONS.calendar },
    { label: 'Spend', href: '/brand/spend', icon: ICONS.coin },
    { label: 'Profile', href: '/brand/profile', icon: ICONS.users },
    { label: 'Settings', href: '/brand/settings', icon: ICONS.settings },
  ],
  owner: [
    { label: 'Overview', href: '/owner/dashboard', icon: ICONS.grid },
    {
      label: 'My Billboards',
      icon: ICONS.billboard,
      submenu: [
        { label: 'List', href: '/owner/billboards', icon: ICONS.list },
        { label: 'Add New', href: '/owner/billboards/create', icon: ICONS.plus },
      ]
    },
    { label: 'Booking Requests', href: '/owner/requests', icon: ICONS.calendar },
    { label: 'Earnings', href: '/owner/earnings', icon: ICONS.coin },
    { label: 'Analytics', href: '/owner/analytics', icon: ICONS.chart },
    { label: 'Settings', href: '/owner/settings', icon: ICONS.settings },
  ],
  admin: [
    { label: 'Overview', href: '/admin/dashboard', icon: ICONS.grid },
    { label: 'Billboards', href: '/admin/billboards', icon: ICONS.billboard },
    { label: 'Users', href: '/admin/users', icon: ICONS.users },
    { label: 'Bookings', href: '/admin/bookings', icon: ICONS.calendar },
    { label: 'Analytics', href: '/admin/analytics', icon: ICONS.chart },
    { label: 'Settings', href: '/admin/settings', icon: ICONS.settings },
  ],
};

const ROLE_LABEL: Record<DashboardSidebarProps['role'], string> = {
  agency: 'Agency',
  brand: 'Brand',
  owner: 'Billboard Owner',
  admin: 'Administrator',
};

export function DashboardSidebar({ role }: DashboardSidebarProps) {
  const pathname = usePathname();
  const router = useRouter();
  const [user, setUser] = useState<ReturnType<typeof authAPI.getCurrentUser>>(null);
  const [expandedMenus, setExpandedMenus] = useState<Set<string>>(new Set());
  const navItems = NAV_BY_ROLE[role];

  useEffect(() => {
    setUser(authAPI.getCurrentUser());
    
    // Auto-expand menu if current path matches a submenu item
    const newExpanded = new Set<string>();
    navItems.forEach((item) => {
      if (item.submenu) {
        const isActive = item.submenu.some(sub => 
          sub.href && (pathname === sub.href || pathname.startsWith(sub.href + '/'))
        );
        if (isActive) {
          newExpanded.add(item.label);
        }
      }
    });
    setExpandedMenus(newExpanded);
  }, [pathname, navItems]);

  const toggleMenu = (label: string) => {
    setExpandedMenus(prev => {
      const newSet = new Set(prev);
      if (newSet.has(label)) {
        newSet.delete(label);
      } else {
        newSet.add(label);
      }
      return newSet;
    });
  };

  async function handleLogout() {
    await authAPI.logout();
    router.push('/login');
  }

  return (
    <aside className="db-sidebar">
      {/* Logo */}
      <div className="db-sidebar__logo">
        <Link href="/">
          <Image src="/billoria-logo-white.svg" alt="Billoria" width={208} height={35} priority />
        </Link>
      </div>

      {/* Role badge */}
      <div className="db-sidebar__role-badge">
        <span className="db-sidebar__role-dot" />
        {ROLE_LABEL[role]}
      </div>

      {/* Navigation */}
      <nav className="db-sidebar__nav" aria-label="Dashboard navigation">
        {navItems.map((item) => {
          // Item with submenu
          if (item.submenu) {
            const isExpanded = expandedMenus.has(item.label);
            const hasActiveChild = item.submenu.some(sub => 
              sub.href && (pathname === sub.href || pathname.startsWith(sub.href + '/'))
            );
            
            return (
              <div key={item.label} className="db-sidebar__menu-group">
                <button
                  className={`db-sidebar__link db-sidebar__link--parent ${hasActiveChild ? 'db-sidebar__link--active' : ''}`}
                  onClick={() => toggleMenu(item.label)}
                  type="button"
                >
                  <span className="db-sidebar__link-icon">{item.icon}</span>
                  <span>{item.label}</span>
                  <span className={`db-sidebar__link-chevron ${isExpanded ? 'db-sidebar__link-chevron--open' : ''}`}>
                    {ICONS.chevronDown}
                  </span>
                </button>
                
                {isExpanded && (
                  <div className="db-sidebar__submenu">
                    {item.submenu.map((subItem) => {
                      if (!subItem.href) return null;
                      const active = pathname === subItem.href;
                      return (
                        <Link
                          key={subItem.href}
                          href={subItem.href}
                          className={`db-sidebar__sublink ${active ? 'db-sidebar__sublink--active' : ''}`}
                        >
                          <span className="db-sidebar__sublink-icon">{subItem.icon}</span>
                          <span>{subItem.label}</span>
                        </Link>
                      );
                    })}
                  </div>
                )}
              </div>
            );
          }
          
          // Regular item without submenu
          if (!item.href) return null;
          const active = pathname === item.href || pathname.startsWith(item.href + '/');
          return (
            <Link
              key={item.href}
              href={item.href}
              className={`db-sidebar__link ${active ? 'db-sidebar__link--active' : ''}`}
            >
              <span className="db-sidebar__link-icon">{item.icon}</span>
              <span>{item.label}</span>
              {active && <span className="db-sidebar__link-bar" />}
            </Link>
          );
        })}
      </nav>

      {/* User footer */}
      <div className="db-sidebar__footer">
        <div className="db-sidebar__user">
          <div className="db-sidebar__avatar">
            {user?.name?.charAt(0).toUpperCase() ?? 'U'}
          </div>
          <div className="db-sidebar__user-info">
            <span className="db-sidebar__user-name">{user?.name ?? 'User'}</span>
            <span className="db-sidebar__user-role">{ROLE_LABEL[role]}</span>
          </div>
        </div>
        <button
          className="db-sidebar__logout"
          onClick={handleLogout}
          type="button"
          aria-label="Sign out"
        >
          {ICONS.logout}
        </button>
      </div>
    </aside>
  );
}

import Link from 'next/link';
import './breadcrumb.css';
import { IconHome, IconChevronRight } from '@/lib/icons/ui-icons';

export interface BreadcrumbItem {
  label: string;
  href?: string;
}

interface BreadcrumbProps {
  items: BreadcrumbItem[];
}

export function Breadcrumb({ items }: BreadcrumbProps) {
  if (items.length === 0) return null;

  return (
    <nav className="breadcrumb" aria-label="Breadcrumb">
      <ol className="breadcrumb__list">
        <li className="breadcrumb__item">
          <Link href="/" className="breadcrumb__link">
            <span className="breadcrumb__home"><IconHome /></span>
            Home
          </Link>
        </li>
        {items.map((item, i) => {
          const isLast = i === items.length - 1;
          return (
            <li key={i} className="breadcrumb__item">
              <span className="breadcrumb__sep"><IconChevronRight /></span>
              {isLast || !item.href ? (
                <span className="breadcrumb__current">{item.label}</span>
              ) : (
                <Link href={item.href} className="breadcrumb__link">{item.label}</Link>
              )}
            </li>
          );
        })}
      </ol>
    </nav>
  );
}

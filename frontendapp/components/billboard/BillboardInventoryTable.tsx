import Link from 'next/link';
import type { Billboard } from '@/types/billboard';
import { IconRect, IconEye, IconEdit } from '@/lib/icons/ui-icons';
import './billboard-inventory.css';

interface BillboardInventoryTableProps {
  billboards: Billboard[];
  basePath?: string;
}

const STATUS_COLORS: Record<string, string> = {
  available: 'bi-status--green',
  booked: 'bi-status--blue',
  maintenance: 'bi-status--amber',
  inactive: 'bi-status--gray',
};

const REVIEW_STATUS_COLORS: Record<string, string> = {
  draft: 'bi-status--gray',
  pending_review: 'bi-status--amber',
  approved: 'bi-status--green',
  revision_requested: 'bi-status--blue',
  rejected: 'bi-status--red',
};

const REVIEW_STATUS_LABELS: Record<string, string> = {
  draft: 'Draft',
  pending_review: 'Pending Review',
  approved: 'Approved',
  revision_requested: 'Revision Requested',
  rejected: 'Rejected',
};

export function BillboardInventoryTable({ billboards, basePath }: BillboardInventoryTableProps) {
  return (
    <div className="bi-table-container">
      <table className="bi-table">
        <thead>
          <tr>
            <th>Billboard</th>
            <th>ID</th>
            <th>Location</th>
            <th>Format</th>
            <th>Status</th>
            <th>Review</th>
            <th>Rate Card</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {billboards.map((billboard) => (
            <tr key={billboard.id}>
              <td>
                <div className="bi-table__billboard">
                  {billboard.hero_image ? (
                    <img 
                      src={billboard.hero_image.thumbnail} 
                      alt={billboard.title}
                      className="bi-table__thumb"
                    />
                  ) : (
                    <div className="bi-table__thumb bi-table__thumb--placeholder">
                      <IconRect />
                    </div>
                  )}
                  <span className="bi-table__title">{billboard.title}</span>
                </div>
              </td>
              <td>{billboard.billboard_id || billboard.id}</td>
              <td>{billboard.area_zone?.label || billboard.district?.label || 'N/A'}</td>
              <td>{billboard.media_format?.label || 'N/A'}</td>
              <td>
                <span className={`bi-status ${STATUS_COLORS[billboard.availability_status?.label?.toLowerCase() || 'inactive']}`}>
                  {billboard.availability_status?.label || 'Unknown'}
                </span>
              </td>
              <td>
                <span className={`bi-status ${REVIEW_STATUS_COLORS[billboard.review_status || 'draft']}`}>
                  {REVIEW_STATUS_LABELS[billboard.review_status || 'draft'] || billboard.review_status}
                </span>
              </td>
              <td>৳{Number(billboard.rate_card_price || 0).toLocaleString()}</td>
              <td>
                <div className="bi-table__actions">
                  <Link 
                    href={`${basePath ? basePath : '/billboards'}/${billboard.uuid}`}
                    className="bi-table__btn bi-table__btn--icon"
                    title="View"
                  >
                    <IconEye />
                  </Link>
                  <Link 
                    href={`${basePath ? basePath : '/billboards'}/${billboard.uuid}/edit`}
                    className="bi-table__btn bi-table__btn--icon"
                    title="Edit"
                  >
                    <IconEdit />
                  </Link>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

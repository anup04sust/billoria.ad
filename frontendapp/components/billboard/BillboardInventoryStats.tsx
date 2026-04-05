import type { Billboard } from '@/types/billboard';
import { IconBillboard, IconCheckCircle, IconCalendar, IconWrench, IconArchive } from '@/lib/icons/ui-icons';
import './billboard-inventory.css';

interface BillboardInventoryStatsProps {
  billboards: Billboard[];
}

export function BillboardInventoryStats({ billboards }: BillboardInventoryStatsProps) {
  const published = billboards.filter(b => b.status === 'published').length;
  const available = billboards.filter(b => 
    b.availability_status?.label?.toLowerCase() === 'available'
  ).length;
  const booked = billboards.filter(b => 
    b.availability_status?.label?.toLowerCase() === 'booked'
  ).length;
  const maintenance = billboards.filter(b => 
    b.availability_status?.label?.toLowerCase() === 'maintenance'
  ).length;
  const archived = billboards.filter(b => 
    b.availability_status?.label?.toLowerCase() === 'archived' ||
    b.availability_status?.label?.toLowerCase() === 'inactive'
  ).length;

  return (
    <div className="db-stats">
      <div className="db-stat">
        <div className="db-stat__icon-wrap db-stat__icon-wrap--red">
          <IconBillboard />
        </div>
        <div className="db-stat__content">
          <div className="db-stat__value">{published}</div>
          <div className="db-stat__label">Total Published</div>
        </div>
      </div>

      <div className="db-stat">
        <div className="db-stat__icon-wrap db-stat__icon-wrap--green">
          <IconCheckCircle />
        </div>
        <div className="db-stat__content">
          <div className="db-stat__value">{available}</div>
          <div className="db-stat__label">Available</div>
        </div>
      </div>

      <div className="db-stat">
        <div className="db-stat__icon-wrap db-stat__icon-wrap--blue">
          <IconCalendar />
        </div>
        <div className="db-stat__content">
          <div className="db-stat__value">{booked}</div>
          <div className="db-stat__label">Booked</div>
        </div>
      </div>

      <div className="db-stat">
        <div className="db-stat__icon-wrap db-stat__icon-wrap--amber">
          <IconWrench />
        </div>
        <div className="db-stat__content">
          <div className="db-stat__value">{maintenance}</div>
          <div className="db-stat__label">Under Maintenance</div>
        </div>
      </div>

      <div className="db-stat">
        <div className="db-stat__icon-wrap db-stat__icon-wrap--gray">
          <IconArchive />
        </div>
        <div className="db-stat__content">
          <div className="db-stat__value">{archived}</div>
          <div className="db-stat__label">Archived</div>
        </div>
      </div>

    </div>
  );
}

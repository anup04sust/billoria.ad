import { IconAlertCircle } from '@/lib/icons/ui-icons';
import './billboard-inventory.css';

interface BillboardInventoryLoadingProps {
  message?: string;
}

export function BillboardInventoryLoading({ message = 'Loading billboards...' }: BillboardInventoryLoadingProps) {
  return (
    <div className="bi-loading">
      <div className="bi-spinner"></div>
      <p>{message}</p>
    </div>
  );
}

interface BillboardInventoryErrorProps {
  message?: string;
}

export function BillboardInventoryError({ message = 'Failed to load billboards. Please try again.' }: BillboardInventoryErrorProps) {
  return (
    <div className="bi-error">
      <IconAlertCircle />
      <p>{message}</p>
    </div>
  );
}

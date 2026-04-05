# Billboard Inventory Components

Reusable, component-based billboard listing system for all dashboard roles (Agency, Owner, Admin, Brand).

## Components

### `BillboardInventoryList`
Main orchestrator component that combines all sub-components.

```tsx
<BillboardInventoryList 
  billboards={billboards}
  organizationName="My Agency Name"
  showStats={true}
/>
```

**Props:**
- `billboards: Billboard[]` - Array of billboard data
- `organizationName?: string` - Organization name to display in results count
- `showStats?: boolean` - Show stats cards (default: true)

**Features:**
- Search by title, ID, location
- Filter by availability status
- Sort by date, name, price
- Grid/Table view toggle
- Responsive design

---

### `BillboardInventoryStats`
Dashboard statistics cards showing totals, availability, bookings, and portfolio value.

```tsx
<BillboardInventoryStats billboards={billboards} />
```

---

### `BillboardInventoryToolbar`
Search, filter, sort, and view mode controls.

```tsx
<BillboardInventoryToolbar
  searchQuery={searchQuery}
  onSearchChange={setSearchQuery}
  statusFilter={statusFilter}
  onStatusFilterChange={setStatusFilter}
  sortBy={sortBy}
  onSortChange={setSortBy}
  viewMode={viewMode}
  onViewModeChange={setViewMode}
/>
```

---

### `BillboardInventoryCard`
Individual billboard card for grid view.

```tsx
<BillboardInventoryCard billboard={billboard} />
```

---

### `BillboardInventoryTable`
Table view rendering all billboards.

```tsx
<BillboardInventoryTable billboards={billboards} />
```

---

### `BillboardInventoryLoading` / `BillboardInventoryError`
Pre-styled loading and error states.

```tsx
<BillboardInventoryLoading message="Loading..." />
<BillboardInventoryError message="Failed to load" />
```

---

## Usage Examples

### Agency Dashboard
Shows billboards owned by agency organization.

```tsx
'use client';

import { useEffect, useState } from 'react';
import { BillboardInventoryList, BillboardInventoryLoading, BillboardInventoryError } from '@/components/billboard/inventory';
import { billboardAPI } from '@/lib/api/billboard';
import { profileAPI } from '@/lib/api/profile';

export default function AgencyBillboardsPage() {
  const [billboards, setBillboards] = useState([]);
  const [organization, setOrganization] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchData() {
      try {
        const profile = await profileAPI.get();
        const org = profile.data.organizations.find(o => o.type === 'agency');
        
        const response = await billboardAPI.list({
          owner_organization: org.id,
          limit: 500,
        });
        
        setBillboards(response.data.billboards);
        setOrganization(org);
      } catch (err) {
        setError('Failed to load billboards');
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, []);

  if (loading) return <BillboardInventoryLoading />;
  if (error) return <BillboardInventoryError message={error} />;

  return (
    <BillboardInventoryList 
      billboards={billboards}
      organizationName={organization?.name}
      showStats={true}
    />
  );
}
```

---

### Owner Dashboard
Shows billboards owned by billboard owner organization.

```tsx
// Same pattern as agency, but filter for type === 'owner'
const ownerOrg = profile.data.organizations.find(o => o.type === 'owner');
```

---

### Admin Dashboard
Shows ALL billboards in the system.

```tsx
// No organization filter - fetch all
const response = await billboardAPI.list({ limit: 1000 });
```

---

### Brand Dashboard (Future)
Could show favorited/bookmarked billboards.

```tsx
// Custom logic to fetch user's favorites
const favorites = await favoritesAPI.list();
```

---

## File Structure

```
components/billboard/
├── BillboardInventoryList.tsx       # Main component
├── BillboardInventoryStats.tsx      # Stats cards
├── BillboardInventoryToolbar.tsx    # Filters/search/sort
├── BillboardInventoryCard.tsx       # Grid card
├── BillboardInventoryTable.tsx      # Table row
├── BillboardInventoryStates.tsx     # Loading/error states
├── billboard-inventory.css          # Shared styles
└── inventory/
    └── index.ts                     # Barrel export
```

---

## Styling

All components use shared CSS from `billboard-inventory.css` with BEM-style class names:

- `.bi-toolbar` - Toolbar container
- `.bi-search` - Search input wrapper
- `.bi-filters` - Filter controls
- `.bi-grid` - Grid layout
- `.bi-card` - Individual card
- `.bi-table` - Table view
- `.bi-status` - Status badges
- `.bi-loading` / `.bi-error` - State containers

**Color variants:**
- `.bi-status--green` - Available
- `.bi-status--blue` - Booked
- `.bi-status--amber` - Maintenance
- `.bi-status--gray` - Inactive

---

## Customization

### Hide Stats
```tsx
<BillboardInventoryList 
  billboards={billboards}
  showStats={false}
/>
```

### Custom Empty State
Modify `BillboardInventoryList.tsx` to accept custom empty state component.

### Additional Filters
Extend `BillboardInventoryToolbar` to add division/district filters.

---

## Implementation Checklist

When implementing for a new dashboard:

1. ✅ Import components from `@/components/billboard/inventory`
2. ✅ Fetch billboard data with appropriate filters
3. ✅ Handle loading/error states
4. ✅ Pass billboards array to `BillboardInventoryList`
5. ✅ Include dashboard layout (Sidebar + Topbar)
6. ✅ Verify auth redirect for logged-out users

---

## Related Files

- [/app/(dashboard)/agency/billboards/page.tsx](../../app/(dashboard)/agency/billboards/page.tsx)
- [/app/(dashboard)/owner/billboards/page.tsx](../../app/(dashboard)/owner/billboards/page.tsx)
- [/app/(dashboard)/admin/billboards/page.tsx](../../app/(dashboard)/admin/billboards/page.tsx)
- [/lib/api/billboard.ts](../../lib/api/billboard.ts)
- [/types/billboard.ts](../../types/billboard.ts)

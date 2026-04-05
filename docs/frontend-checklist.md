# Billoria Frontend Development Checklist

**Phase 1: Foundation, Layout & Billboard Listing UI**  
**Estimated Timeline:** ~15 hours  
**Last Updated:** March 27, 2026

---

## 🎯 **CSS Approach: Semantic BEM Classes**

**Philosophy:** Clean, maintainable, Drupal-inspired class names (NOT messy inline Tailwind utilities)

### ❌ **Avoid This:**
```tsx
<div className="flex items-center justify-between p-4 bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
```

### ✅ **Use This:**
```tsx
<div className="billboard-card">

// In styles/components/billboard-card.css
.billboard-card {
  @apply flex items-center justify-between;
  @apply p-4 bg-white rounded-lg shadow-md;
  @apply hover:shadow-lg transition-shadow;
}
```

---

## 📦 **1. Project Setup & Configuration** (⏱️ 30 mins)

### 1.1 Core Dependencies
- [ ] Install Tailwind & utilities
  ```bash
  cd /var/www/billoria.ad/frontendapp
  pnpm add tailwindcss postcss autoprefixer
  pnpm add class-variance-authority clsx tailwind-merge
  pnpm add lucide-react
  ```

### 1.2 Data Fetching & Forms
- [ ] Install TanStack Query, Axios, Form libraries
  ```bash
  pnpm add @tanstack/react-query @tanstack/react-query-devtools
  pnpm add axios
  pnpm add react-hook-form zod @hookform/resolvers
  pnpm add zustand js-cookie
  pnpm add @types/js-cookie -D
  ```

### 1.3 Utilities
- [ ] Install date/utility libraries
  ```bash
  pnpm add date-fns lodash-es
  pnpm add @types/lodash-es -D
  ```

### 1.4 Map Integration (OpenStreetMap)
- [ ] Install Leaflet and React-Leaflet
  ```bash
  pnpm add leaflet react-leaflet
  pnpm add @types/leaflet -D
  ```
- [ ] Install marker clustering (for better performance with many billboards)
  ```bash
  pnpm add react-leaflet-cluster
  ```

### 1.4 Tailwind Configuration
- [ ] Create `tailwind.config.ts`
  - Billoria brand colors (primary, secondary, accent)
  - Custom screens/breakpoints
  - Custom spacing/radius
  - Configure content paths
  - Extend theme with CSS variables

### 1.5 PostCSS Configuration
- [ ] Create `postcss.config.mjs`
  - Configure Tailwind
  - Configure Autoprefixer

### 1.6 Global Styles
- [ ] Update `app/globals.css`
  - Import Tailwind directives
  - Define CSS custom properties
  - Base HTML styles
  - Typography styles
  - Utility classes with @apply

---

## 📁 **2. Project Structure Setup** (⏱️ 20 mins)

### 2.1 Create Folder Structure
- [ ] Create directories:
  ```
  frontendapp/
  ├── app/
  │   ├── (auth)/           # Auth pages (login, register, verify)
  │   ├── (marketing)/      # Public pages (home, about, contact)
  │   ├── (dashboard)/      # Protected dashboard pages
  │   └── billboards/       # Billboard search/listing
  ├── components/
  │   ├── layout/           # Header, Footer, Sidebar
  │   ├── billboard/        # Billboard-specific components
  │   ├── forms/            # Form components
  │   └── shared/           # Reusable components
  ├── lib/
  │   ├── api/              # API client & functions
  │   ├── hooks/            # Custom React hooks
  │   ├── store/            # Zustand stores
  │   ├── utils/            # Utility functions
  │   └── providers/        # Context providers
  ├── types/                # TypeScript types
  │   ├── api.ts
  │   ├── billboard.ts
  │   ├── user.ts
  │   └── taxonomy.ts
  ├── styles/
  │   ├── components/       # Component-specific CSS
  │   ├── utilities/        # Utility CSS classes
  │   └── pages/            # Page-specific CSS
  └── config/
      ├── site.ts           # Site metadata
      └── navigation.ts     # Navigation config
  ```

### 2.2 TypeScript Types
- [ ] Create `types/api.ts` - Base API response types
- [ ] Create `types/billboard.ts` - Billboard entity types
- [ ] Create `types/user.ts` - User/Organization types
- [ ] Create `types/taxonomy.ts` - Taxonomy term types

### 2.3 Configuration Files
- [ ] Create `lib/constants.ts` - API URLs, pagination limits, etc.
- [ ] Create `config/site.ts` - Site name, description, social links
- [ ] Create `config/navigation.ts` - Menu items configuration

---

## 🔌 **3. API Client Setup** (⏱️ 45 mins)

### 3.1 Axios Instance
- [ ] Create `lib/api/client.ts`
  - Base URL: `https://billoria-ad-api.ddev.site`
  - Request interceptors (add CSRF token, auth headers)
  - Response interceptors (handle errors, format responses)
  - Cookie handling for session-based auth
  - Timeout configuration

### 3.2 Billboard API Functions
- [ ] Create `lib/api/billboards.ts`
  - `getBillboards(filters, page, limit)` - List with pagination
  - `getBillboard(id)` - Get single billboard details
  - `createBillboard(data)` - Create billboard (auth required)
  - `updateBillboard(id, data)` - Update billboard (auth required)
  - `deleteBillboard(id)` - Delete billboard (auth required)
  - Type all request/response data

### 3.3 Taxonomy API Functions
- [ ] Create `lib/api/taxonomies.ts`
  - `getDivisions()` - Get all divisions
  - `getDistricts(divisionId?)` - Get districts (optionally filtered)
  - `getAreaZones(districtId?)` - Get areas/zones
  - `getMediaFormats()` - Get billboard media formats
  - `getPlacementTypes()` - Get placement types
  - `getAvailabilityStatuses()` - Get availability statuses

### 3.4 Test API Connections
- [ ] Verify CORS headers from Drupal backend
- [ ] Test GET `/api/v1/billboard/list`
- [ ] Confirm image URLs are resolving correctly
- [ ] Test error handling (404, 500, network errors)

---

## 💾 **4. State Management** (⏱️ 30 mins)

### 4.1 Filter Store (Zustand)
- [ ] Create `lib/store/filterStore.ts`
  - State: `division`, `district`, `area_zone`, `media_format`, `priceRange`, `availability_status`, `is_premium`, `is_active`
  - Actions: `setFilter`, `updateFilter`, `clearFilters`, `resetFilters`
  - Persist to localStorage (optional)

### 4.2 Auth Store (Zustand)
- [ ] Create `lib/store/authStore.ts`
  - State: `user`, `organization`, `isAuthenticated`, `isLoading`
  - Actions: `login`, `logout`, `setUser`, `checkAuth`
  - Persist authentication state

### 4.3 React Query Provider
- [ ] Create `lib/providers/QueryProvider.tsx`
  - Configure QueryClient with default options
  - Set staleTime, cacheTime, retry logic
  - Wrap with QueryClientProvider
  - Add React Query DevTools (dev only)

### 4.4 Root Layout Integration
- [ ] Update `app/layout.tsx` to include QueryProvider

---

## 🏗️ **5. Base Layout Structure** (⏱️ 1 hour)

### 5.1 Root Layout
- [ ] Update `app/layout.tsx`
  - HTML lang="en"
  - Metadata configuration (title, description, OG tags)
  - Font optimization (Inter, local fonts)
  - Body className
  - QueryProvider wrapper

### 5.2 Header Component
- [ ] Create `components/layout/Header.tsx`
- [ ] Create `styles/components/header.css`
- [ ] Features:
  - Logo with link to homepage
  - Primary navigation: Home, Search Billboards, About, Contact
  - User menu (Login/Register or Avatar + Dashboard)
  - Mobile hamburger menu
  - Sticky header on scroll
- [ ] **Classes:**
  - `.site-header`, `.site-header--sticky`
  - `.site-header__container`
  - `.site-header__logo`, `.site-header__logo-img`
  - `.site-header__nav`, `.site-header__nav-list`, `.site-header__nav-item`, `.site-header__nav-link`
  - `.site-header__actions`
  - `.site-header__mobile-toggle`

### 5.3 Footer Component
- [ ] Create `components/layout/Footer.tsx`
- [ ] Create `styles/components/footer.css`
- [ ] Features:
  - Company info (logo, tagline)
  - Footer links (About, Terms, Privacy, Contact)
  - Social media icons
  - Copyright notice
- [ ] **Classes:**
  - `.site-footer`
  - `.site-footer__container`
  - `.site-footer__content`, `.site-footer__section`
  - `.site-footer__logo`, `.site-footer__tagline`
  - `.site-footer__links`, `.site-footer__link`
  - `.site-footer__social`, `.site-footer__social-link`
  - `.site-footer__copyright`

### 5.4 Marketing Layout
- [ ] Create `app/(marketing)/layout.tsx`
  - Include Header
  - Render children
  - Include Footer
  - Used for: homepage, about, contact, billboard search

### 5.5 Container & Grid Utilities
- [ ] Create `styles/utilities/layout.css`
  - `.container` - Max-width responsive container
  - `.container--narrow`, `.container--wide`
  - `.grid-auto` - Auto-fit responsive grid
  - `.grid-billboard` - Billboard card grid (1→2→3 cols)
  - `.section` - Standard section padding
  - `.section--hero` - Hero section style

---

## 🗺️ **6. Landing Page with Full-Screen Map** (⏱️ 2 hours)

### 6.1 Map Component Setup
- [ ] Create `components/map/BillboardMap.tsx`
- [ ] Create `styles/components/billboard-map.css`
- [ ] Import Leaflet CSS in component or globals.css:
  ```ts
  import 'leaflet/dist/leaflet.css'
  ```
- [ ] Fix Leaflet default marker icons (Next.js compatibility issue)
  ```ts
  import L from 'leaflet'
  delete L.Icon.Default.prototype._getIconUrl
  L.Icon.Default.mergeOptions({
    iconUrl: '/images/marker-icon.png',
    iconRetinaUrl: '/images/marker-icon-2x.png',
    shadowUrl: '/images/marker-shadow.png',
  })
  ```

### 6.2 Billboard Map Component
- [ ] Props: `billboards`, `center`, `zoom`, `onMarkerClick`
- [ ] **Structure:**
  ```tsx
  <div className="billboard-map">
    <MapContainer center={center} zoom={zoom} className="billboard-map__container">
      <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
      <MarkerClusterGroup>
        {billboards.map(billboard => (
          <Marker position={[lat, lng]} key={id}>
            <Popup className="billboard-map__popup">
              <BillboardMapCard billboard={billboard} />
            </Popup>
          </Marker>
        ))}
      </MarkerClusterGroup>
    </MapContainer>
  </div>
  ```
- [ ] **Classes:**
  - `.billboard-map` (full viewport height container)
  - `.billboard-map__container` (Leaflet map container)
  - `.billboard-map__popup` (Custom popup styling)
  - `.billboard-map__controls` (Zoom, filter buttons overlay)

### 6.3 Custom Billboard Marker Icons
- [ ] Create custom marker icons for different statuses
  - Available: Green marker
  - Booked: Red marker
  - Premium: Gold/star marker
- [ ] Use SVG icons or Leaflet DivIcon
- [ ] Add marker icon legend

### 6.4 Billboard Map Card (Popup Content)
- [ ] Create `components/billboard/BillboardMapCard.tsx`
- [ ] Compact card with:
  - Thumbnail image
  - Title
  - Location
  - Price
  - Status badge
  - "View Details" link
- [ ] **Classes:**
  - `.billboard-map-card`
  - `.billboard-map-card__image`
  - `.billboard-map-card__content`
  - `.billboard-map-card__title`
  - `.billboard-map-card__price`
  - `.billboard-map-card__link`

### 6.5 Landing Page Implementation
- [ ] Update `app/(marketing)/page.tsx` (Homepage)
- [ ] Create `styles/pages/home.css`
- [ ] **Layout:**
  ```html
  <div class="home-page">
    <div class="home-page__map-section">
      <BillboardMap billboards={billboards} fullscreen />
    </div>
    <div class="home-page__overlay">
      <div class="home-page__hero">
        <h1 class="home-page__title">Find Your Perfect Billboard</h1>
        <p class="home-page__subtitle">Explore premium advertising spaces across Bangladesh</p>
        <div class="home-page__search">
          <QuickSearchBar />
        </div>
      </div>
      <button class="home-page__explore-btn">Explore All Locations</button>
    </div>
  </div>
  ```
- [ ] Full viewport height map (100vh)
- [ ] Overlay with hero text and search bar
- [ ] Responsive: Map below overlay on mobile

### 6.6 Map Controls & Interactions
- [ ] Zoom controls (default Leaflet controls)
- [ ] Location/GPS button (center on user location)
- [ ] Fullscreen toggle button
- [ ] Cluster expansion on click
- [ ] Marker click opens popup with billboard card
- [ ] Popup "View Details" navigates to billboard detail page

### 6.7 Quick Search Bar (Overlay)
- [ ] Create `components/map/QuickSearchBar.tsx`
- [ ] Location autocomplete (divisions/districts)
- [ ] Price range slider
- [ ] "Search" button
- [ ] Filters update map markers in real-time
- [ ] **Classes:**
  - `.quick-search`
  - `.quick-search__input`
  - `.quick-search__filters`
  - `.quick-search__button`

### 6.8 Performance Optimization
- [ ] Use marker clustering for 100+ billboards
- [ ] Lazy load map on viewport (react-intersection-observer)
- [ ] Debounce map move events
- [ ] Only fetch billboards in current map bounds
- [ ] Cache map tiles

### 6.9 Mobile Responsiveness
- [ ] Full-screen map on mobile
- [ ] Hero overlay at bottom on mobile (slide up panel)
- [ ] Touch-friendly markers (larger tap targets)
- [ ] Disable zoom on scroll (prevent accidental zoom)

---

## 🧩 **7. Common UI Components** (⏱️ 1.5 hours)

### 6.1 Button Component
- [ ] Create `components/shared/Button.tsx`
- [ ] Create `styles/components/button.css`
- [ ] Props: `variant`, `size`, `disabled`, `loading`, `icon`
- [ ] Variants: `primary`, `secondary`, `outline`, `ghost`, `link`
- [ ] Sizes: `sm`, `md`, `lg`
- [ ] **Classes:**
  - `.btn`, `.btn--primary`, `.btn--secondary`, `.btn--outline`, `.btn--ghost`
  - `.btn--sm`, `.btn--md`, `.btn--lg`
  - `.btn--loading`, `.btn--disabled`
  - `.btn__icon`, `.btn__text`

### 6.2 Card Component
- [ ] Create `components/shared/Card.tsx`
- [ ] Create `styles/components/card.css`
- [ ] Props: `variant`, `hoverable`, `clickable`
- [ ] **Classes:**
  - `.card`, `.card--hoverable`, `.card--clickable`
  - `.card__header`, `.card__body`, `.card__footer`
  - `.card__title`, `.card__description`

### 6.3 Badge Component
- [ ] Create `components/shared/Badge.tsx`
- [ ] Create `styles/components/badge.css`
- [ ] Props: `variant`, `size`
- [ ] Variants: `success`, `warning`, `error`, `info`, `default`
- [ ] **Classes:**
  - `.badge`, `.badge--success`, `.badge--warning`, `.badge--error`, `.badge--info`
  - `.badge--sm`, `.badge--md`, `.badge--lg`

### 6.4 Loading Spinner
- [ ] Create `components/shared/LoadingSpinner.tsx`
- [ ] Create `styles/components/spinner.css`
- [ ] Props: `size`, `color`
- [ ] **Classes:**
  - `.spinner`, `.spinner--sm`, `.spinner--md`, `.spinner--lg`
  - `.spinner__circle`

### 6.5 Empty State Component
- [ ] Create `components/shared/EmptyState.tsx`
- [ ] Create `styles/components/empty-state.css`
- [ ] Props: `icon`, `title`, `message`, `action`
- [ ] **Classes:**
  - `.empty-state`
  - `.empty-state__icon`, `.empty-state__title`, `.empty-state__message`
  - `.empty-state__action`

### 6.6 Error State Component
- [ ] Create `components/shared/ErrorState.tsx`
- [ ] Props: `error`, `retry`
- [ ] Show friendly error message
- [ ] Retry button

---

## 🎴 **7. Billboard Card Component** (⏱️ 2 hours)

### 7.1 Billboard Card
- [ ] Create `components/billboard/BillboardCard.tsx`
- [ ] Create `styles/components/billboard-card.css`
- [ ] Props: `billboard`, `variant` (grid | list)
- [ ] **Structure:**
  ```html
  <article class="billboard-card">
    <div class="billboard-card__image">
      <Image class="billboard-card__img" />
      <Badge class="billboard-card__badge">Premium</Badge>
    </div>
    <div class="billboard-card__content">
      <h3 class="billboard-card__title">Title</h3>
      <p class="billboard-card__location">
        <Icon /> Gulshan, Dhaka
      </p>
      <div class="billboard-card__meta">
        <span class="billboard-card__size">20x30 ft</span>
        <span class="billboard-card__format">Bridge Banner</span>
      </div>
      <div class="billboard-card__footer">
        <span class="billboard-card__price">BDT 150,000</span>
        <Badge class="billboard-card__status">Available</Badge>
      </div>
    </div>
    <Link class="billboard-card__link" />
  </article>
  ```
- [ ] **Classes:**
  - `.billboard-card`, `.billboard-card--list`, `.billboard-card--featured`
  - `.billboard-card__image`, `.billboard-card__img`, `.billboard-card__badge`
  - `.billboard-card__content`
  - `.billboard-card__title`, `.billboard-card__location`, `.billboard-card__meta`
  - `.billboard-card__size`, `.billboard-card__format`
  - `.billboard-card__footer`, `.billboard-card__price`, `.billboard-card__status`
  - `.billboard-card__link` (absolute positioned overlay)

### 7.2 Responsive Image Loading
- [ ] Use Next.js `<Image>` component
- [ ] Responsive srcSet using API's `thumbnail`, `medium`, `large`
- [ ] sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
- [ ] Lazy loading enabled
- [ ] Blur placeholder from `hero_image.thumbnail`

### 7.3 Card Hover Effects
- [ ] Subtle shadow increase on hover
- [ ] Image zoom effect (scale 1.05)
- [ ] Smooth transitions (300ms)
- [ ] Focus visible for accessibility

### 7.4 Billboard Status Badge Logic
- [ ] Available: Green badge
- [ ] Partially Booked: Yellow badge
- [ ] Booked: Red badge
- [ ] Inactive: Gray badge
- [ ] Premium: Gold/orange special badge

---

## 🎛️ **8. Billboard Filters Component** (⏱️ 2 hours)

### 8.1 Filter Sidebar/Panel
- [ ] Create `components/billboard/BillboardFilters.tsx`
- [ ] Create `styles/components/billboard-filters.css`
- [ ] Props: `filters`, `onFilterChange`, `onClear`
- [ ] **Classes:**
  - `.billboard-filters`
  - `.billboard-filters__header`, `.billboard-filters__title`
  - `.billboard-filters__section`, `.billboard-filters__section-title`
  - `.billboard-filters__group`
  - `.billboard-filters__actions`

### 8.2 Location Filters (Cascading Dropdowns)
- [ ] Create `components/billboard/LocationFilter.tsx`
- [ ] Division dropdown (fetch from taxonomy API)
- [ ] District dropdown (enabled when division selected)
- [ ] Area/Zone dropdown (enabled when district selected)
- [ ] Auto-fetch child options on parent change
- [ ] Clear button for each level

### 8.3 Price Range Filter
- [ ] Create `components/billboard/PriceRangeFilter.tsx`
- [ ] Dual-range slider component
- [ ] Min/Max input fields (BDT currency)
- [ ] Debounce input changes (500ms)
- [ ] Format numbers with commas (150,000)
- [ ] Show current range values

### 8.4 Media Format Filter
- [ ] Create `components/billboard/MediaFormatFilter.tsx`
- [ ] Checkbox group (multi-select)
- [ ] Fetch formats from taxonomy API
- [ ] "Select All" / "Clear All" options
- [ ] Show count of selected formats

### 8.5 Availability Status Filter
- [ ] Radio button group: All, Available, Partially Booked, Booked, Inactive
- [ ] Default: "All"

### 8.6 Premium/Active Toggles
- [ ] Create `components/shared/Switch.tsx` component
- [ ] "Show only premium" toggle
- [ ] "Show only active" toggle
- [ ] **Classes:**
  - `.switch`, `.switch__input`, `.switch__slider`
  - `.switch--checked`, `.switch--disabled`

### 8.7 Filter Actions
- [ ] "Clear All Filters" button (reset to defaults)
- [ ] "Apply Filters" button (on mobile)
- [ ] Show filter count badge when filters active

### 8.8 Mobile Filter Drawer
- [ ] Create slide-out drawer for mobile
- [ ] Filter toggle button in header
- [ ] Apply/Cancel buttons in drawer footer
- [ ] Smooth slide animation

---

## 📋 **9. Billboard List Page** (⏱️ 2.5 hours)

### 9.1 Page Structure
- [ ] Create `app/(marketing)/billboards/page.tsx`
- [ ] Create `styles/pages/billboards-list.css`
- [ ] **Layout Structure:**
  ```html
  <div class="billboards-page">
    <aside class="billboards-page__sidebar">
      <BillboardFilters />
    </aside>
    <main class="billboards-page__main">
      <div class="billboards-page__header">
        <h1 class="billboards-page__title">Billboards</h1>
        <div class="billboards-page__controls">
          <SortDropdown />
          <ViewToggle />
        </div>
      </div>
      <div class="billboards-page__results-info">
        Showing X of Y billboards
      </div>
      <div class="billboards-page__grid">
        <!-- Billboard Cards -->
      </div>
      <div class="billboards-page__pagination">
        <Pagination />
      </div>
    </main>
  </div>
  ```

### 9.2 TanStack Query Hook
- [ ] Create `lib/hooks/useBillboards.ts`
- [ ] `useQuery` with filters as query key
- [ ] Automatic refetch when filters change
- [ ] Return: `{ data, isLoading, error, refetch }`
- [ ] Enable pagination (keepPreviousData: true)

### 9.3 Results Header
- [ ] Total count display: "Showing 12 of 145 billboards"
- [ ] Create `components/billboard/SortDropdown.tsx`
  - Options: Newest, Price (Low to High), Price (High to Low), Name (A-Z)
  - Update URL param on change
- [ ] Create `components/billboard/ViewToggle.tsx`
  - Toggle between Grid / List view
  - Save preference to localStorage

### 9.4 Billboard Grid
- [ ] Responsive CSS Grid layout
- [ ] 1 column (mobile) → 2 columns (tablet) → 3 columns (desktop)
- [ ] Auto-fit with minmax(300px, 1fr)
- [ ] Gap: 1.5rem
- [ ] **Classes:**
  - `.billboards-grid`, `.billboards-grid--list-view`

### 9.5 Loading State
- [ ] Create `components/billboard/BillboardCardSkeleton.tsx`
- [ ] Show 6-9 skeleton cards while loading
- [ ] Smooth fade-in animation when data loads
- [ ] **Classes:**
  - `.billboard-skeleton`, `.billboard-skeleton__image`, `.billboard-skeleton__content`

### 9.6 Empty State
- [ ] Show when no results
- [ ] Message: "No billboards found"
- [ ] Subtext: "Try adjusting your filters"
- [ ] "Clear Filters" button
- [ ] Illustration/icon (using Lucide icon)

### 9.7 Error State
- [ ] Show when API error occurs
- [ ] Friendly error message
- [ ] "Try Again" button (triggers refetch)
- [ ] Show error details in dev mode

### 9.8 Pagination Component
- [ ] Create `components/shared/Pagination.tsx`
- [ ] Create `styles/components/pagination.css`
- [ ] Props: `currentPage`, `totalPages`, `onPageChange`
- [ ] Show: Previous, page numbers, Next
- [ ] Collapse middle pages (1 ... 5 6 7 ... 20)
- [ ] Update URL param on page change
- [ ] Scroll to top on page change
- [ ] **Classes:**
  - `.pagination`, `.pagination__list`
  - `.pagination__item`, `.pagination__item--active`, `.pagination__item--disabled`
  - `.pagination__button`, `.pagination__ellipsis`

---

## 🔗 **10. URL State Management** (⏱️ 1 hour)

### 10.1 Sync Filters with URL
- [ ] Use Next.js `useSearchParams` and `useRouter`
- [ ] Update URL on filter change (without page reload)
- [ ] Parse URL params on page load
- [ ] Validate and sanitize URL params

### 10.2 Shareable URLs
- [ ] Full filter state in URL
- [ ] Example: `/billboards?division=1&district=107&min_price=100000&max_price=200000&page=2`
- [ ] Bookmarkable URLs
- [ ] Social sharing friendly

### 10.3 Browser History
- [ ] Back button restores previous filters
- [ ] Forward button works correctly
- [ ] Use `router.replace()` for filter updates (not push)

### 10.4 URL Utilities
- [ ] Create `lib/utils/url.ts`
- [ ] `parseFiltersFromUrl(searchParams)` - Parse URL to filter object
- [ ] `filtersToUrlParams(filters)` - Convert filters to URL params
- [ ] `updateUrl(filters)` - Update URL without reload

---

## 📱 **11. Responsive Design** (⏱️ 1 hour)

### 11.1 Mobile Layout (< 768px)
- [ ] Single column billboard grid
- [ ] Filters in slide-out drawer (hidden by default)
- [ ] Filter button in top bar
- [ ] Sticky filter button while scrolling
- [ ] Touch-friendly tap targets (min 44x44px)

### 11.2 Mobile Filter Drawer
- [ ] Slide from left/bottom
- [ ] Overlay backdrop
- [ ] Close on backdrop click
- [ ] Close button in header
- [ ] Apply/Cancel buttons in footer
- [ ] Smooth animations

### 11.3 Tablet Layout (768px - 1024px)
- [ ] 2-column grid
- [ ] Collapsible filter sidebar
- [ ] Show/hide toggle button
- [ ] Filter panel width: 280px

### 11.4 Desktop Layout (> 1024px)
- [ ] 3-column grid (4 columns on large screens > 1440px)
- [ ] Fixed filter sidebar (always visible)
- [ ] Filter panel width: 320px
- [ ] Sticky header on scroll

### 11.5 Responsive Images
- [ ] Use appropriate image size per breakpoint
- [ ] Mobile: thumbnail (400x300)
- [ ] Tablet: medium (800x600)
- [ ] Desktop: large (1200x800)

---

## ⚡ **12. Performance Optimization** (⏱️ 30 mins)

### 12.1 Image Optimization
- [ ] Next.js Image component with proper sizing
- [ ] Lazy loading (loading="lazy")
- [ ] Blur placeholder
- [ ] Optimize image formats (WebP with JPEG fallback)

### 12.2 Code Splitting
- [ ] Dynamic import for filters panel
  ```ts
  const BillboardFilters = dynamic(() => import('.../BillboardFilters'))
  ```
- [ ] Lazy load pagination component
- [ ] Lazy load lightbox/gallery

### 12.3 Debounce & Throttle
- [ ] Debounce price range slider (500ms)
- [ ] Debounce search input (300ms)
- [ ] Throttle scroll events if needed

### 12.4 Prefetching
- [ ] Prefetch billboard detail pages on card hover
- [ ] Prefetch next page of results

### 12.5 Memoization
- [ ] Memoize expensive filter calculations
- [ ] Use `useMemo` for filtered/sorted lists
- [ ] Use `useCallback` for event handlers

---

## 🎨 **13. CSS Organization** (⏱️ 30 mins)

### 13.1 File Structure
- [ ] Ensure structure:
  ```
  styles/
  ├── globals.css
  ├── components/
  │   ├── button.css
  │   ├── card.css
  │   ├── badge.css
  │   ├── billboard-card.css
  │   ├── billboard-filters.css
  │   ├── header.css
  │   ├── footer.css
  │   ├── pagination.css
  │   ├── spinner.css
  │   └── empty-state.css
  ├── utilities/
  │   ├── layout.css
  │   ├── spacing.css
  │   └── typography.css
  └── pages/
      └── billboards-list.css
  ```

### 13.2 BEM Naming Convention
- [ ] Block: `.billboard-card`
- [ ] Element: `.billboard-card__title`
- [ ] Modifier: `.billboard-card--featured`
- [ ] Document naming conventions in README

### 13.3 CSS Custom Properties
- [ ] Define in `globals.css`:
  ```css
  :root {
    --color-primary: theme('colors.blue.600');
    --color-secondary: theme('colors.gray.600');
    --color-success: theme('colors.green.600');
    --color-warning: theme('colors.yellow.600');
    --color-error: theme('colors.red.600');
    
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;
    
    --radius-sm: 0.25rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    
    --shadow-sm: theme('boxShadow.sm');
    --shadow-md: theme('boxShadow.md');
    --shadow-lg: theme('boxShadow.lg');
  }
  ```

### 13.4 Import Organization
- [ ] Import component CSS in component files
- [ ] Use CSS Modules alternative if needed
- [ ] Document import patterns

---

## 🧪 **14. Testing & Quality Assurance** (⏱️ 1 hour)

### 14.1 Functional Testing
- [ ] Load billboard list page (no filters)
- [ ] Apply single filter (division)
- [ ] Apply multiple filters (division + price range)
- [ ] Clear filters
- [ ] Test pagination (navigate pages)
- [ ] Test sorting (price low to high, newest, etc.)
- [ ] Test view toggle (grid ↔ list)
- [ ] Test empty state (filter with no results)
- [ ] Test error state (disconnect network)

### 14.2 Responsive Testing
- [ ] Mobile (375px - iPhone SE)
- [ ] Mobile (414px - iPhone 12 Pro)
- [ ] Tablet (768px - iPad)
- [ ] Desktop (1280px)
- [ ] Large Desktop (1920px)
- [ ] Test filter drawer on mobile
- [ ] Test collapsible sidebar on tablet

### 14.3 Browser Testing
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### 14.4 Performance Testing
- [ ] Lighthouse audit
  - Performance > 90
  - Accessibility > 90
  - Best Practices > 90
  - SEO > 90
- [ ] Check bundle size
- [ ] Test with slow 3G throttling
- [ ] Check for unnecessary re-renders

### 14.5 Accessibility Testing
- [ ] Keyboard navigation works
- [ ] Tab order is logical
- [ ] Focus indicators visible
- [ ] Screen reader testing (basic)
- [ ] ARIA labels where needed
- [ ] Color contrast meets WCAG AA
- [ ] Images have alt text

### 14.6 SEO Testing
- [ ] Page title includes keywords
- [ ] Meta description is compelling
- [ ] Heading hierarchy is correct (h1, h2, h3)
- [ ] Semantic HTML tags used
- [ ] URLs are clean and readable

---

## 📊 **Estimated Time Breakdown**

| Task Group | Estimated Time |
|------------|----------------|
| 1. Setup & Config | 30 mins |
| 2. Project Structure | 20 mins |
| 3. API Client | 45 mins |
| 4. State Management | 30 mins |
| 5. Layout Structure | 1 hour |
| 6. UI Components | 1.5 hours |
| 7. Billboard Card | 2 hours |
| 8. Filters | 2 hours |
| 9. List Page | 2.5 hours |
| 10. URL State | 1 hour |
| 11. Responsive | 1 hour |
| 12. Performance | 30 mins |
| 13. CSS Organization | 30 mins |
| 14. Testing | 1 hour |
| **TOTAL** | **~15 hours** |

---

## 🎨 **BEM Class Name Reference**

### Billboard Card
```css
.billboard-card
.billboard-card--list              /* List view variant */
.billboard-card--featured          /* Featured/premium */
.billboard-card__image
.billboard-card__img
.billboard-card__badge
.billboard-card__content
.billboard-card__title
.billboard-card__location
.billboard-card__meta
.billboard-card__size
.billboard-card__format
.billboard-card__footer
.billboard-card__price
.billboard-card__status
.billboard-card__link              /* Overlay link */
```

### Billboard Filters
```css
.billboard-filters
.billboard-filters__header
.billboard-filters__title
.billboard-filters__section
.billboard-filters__section-title
.billboard-filters__group
.billboard-filters__label
.billboard-filters__input
.billboard-filters__select
.billboard-filters__checkbox-group
.billboard-filters__range-slider
.billboard-filters__actions
```

### Header
```css
.site-header
.site-header--sticky
.site-header__container
.site-header__logo
.site-header__logo-img
.site-header__nav
.site-header__nav-list
.site-header__nav-item
.site-header__nav-link
.site-header__nav-link--active
.site-header__actions
.site-header__mobile-toggle
```

### Footer
```css
.site-footer
.site-footer__container
.site-footer__content
.site-footer__section
.site-footer__logo
.site-footer__tagline
.site-footer__links
.site-footer__link
.site-footer__social
.site-footer__social-link
.site-footer__copyright
```

### Button
```css
.btn
.btn--primary
.btn--secondary
.btn--outline
.btn--ghost
.btn--link
.btn--sm
.btn--md
.btn--lg
.btn--loading
.btn--disabled
.btn__icon
.btn__text
```

### Pagination
```css
.pagination
.pagination__list
.pagination__item
.pagination__item--active
.pagination__item--disabled
.pagination__button
.pagination__ellipsis
```

### Badge
```css
.badge
.badge--success
.badge--warning
.badge--error
.badge--info
.badge--default
.badge--sm
.badge--md
.badge--lg
```

---

## 📝 **Notes**

- Always use semantic class names, never inline Tailwind utilities in JSX
- Use `@apply` in CSS files to keep markup clean
- Follow BEM methodology consistently
- Test on real devices when possible
- Keep components small and focused
- Document complex components
- Use TypeScript strictly (no `any` types)

---

## 🚀 **Next Phase Preview**

After completing Phase 1, we'll move to:
- **Phase 2:** Billboard Detail Page with Gallery
- **Phase 3:** Interactive Map View with Markers
- **Phase 4:** Authentication (Login/Register/Verify)
- **Phase 5:** Dashboard & Billboard Management

---

**Last Updated:** March 27, 2026  
**Status:** Ready to start Phase 1  
**Developer:** Billoria Team

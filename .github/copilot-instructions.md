# Billoria.ad — Billboard Marketplace Platform

Headless Drupal 11 + Next.js platform for digitizing billboard discovery, booking, and workflows. Built by DreamSteps for the Bangladesh outdoor advertising market.

## Architecture

**Stack**: Drupal 11 (API/CMS) + Next.js 16 (Frontend) + DDEV (local env)

**Project Structure**:
- `cmsapi/` — Drupal backend (API, auth, content modeling, admin)
- `frontendapp/` — Next.js frontend (public UX, dashboards, search)
- `application-wiki/` — API documentation (QUICK_START.md, API endpoints, auth flows)
- `docs/` — Project specs and roadmaps

**Communication**: 
- Drupal is source of truth for users, roles, permissions, content, and workflows
- Frontend consumes JSON:API + custom REST endpoints via centralized API client in `lib/api/`
- Cookie-based sessions + JWT tokens for authentication
- CSRF tokens required for mutations (`/api/v1/csrf-token`)

**Local URLs**:
- DDEV Frontend: `http://billoria-ad.ddev.site:3000`
- Local Frontend: `http://localhost:3000`
- Backend(Drupal CMS) API: `http://billoria-ad-api.ddev.site`

## Build and Test

### Backend (Drupal)
```bash
cd cmsapi
ddev start                       # Start containers
ddev composer install            # Install dependencies
ddev drush cr                    # Clear cache
ddev drush updb                  # Run database updates
ddev drush user:login            # Generate admin login link
ddev snapshot --list             # List database backups
```

### Frontend (Next.js)
```bash
cd frontendapp
ddev start                       # Auto-starts Next.js on port 3000
# Or manually:
pnpm install                     # Install dependencies
pnpm dev                         # Start dev server
pnpm build                       # Production build
pnpm lint                        # Run ESLint
```

**Prerequisites**: DDEV v1.25.1+ (handles all containerization, no manual PHP/Node setup needed)

**Environment Variables**: Create `frontendapp/.env.local`:
```env
NEXT_PUBLIC_API_URL=http://billoria-ad-api.ddev.site
```

## Code Style

### TypeScript (Frontend)
- **Components**: PascalCase function declarations with typed props
  ```typescript
  interface BillboardMapProps { billboards?: Billboard[]; }
  export function BillboardMap({ billboards = [] }: BillboardMapProps) { ... }
  ```
- **Files**: Component file + co-located CSS (`BillboardMap.tsx` + `billboard-map.css`)
- **Imports**: Use `@/` path alias (`import type { Billboard } from '@/types/billboard'`)
- **Client Components**: Mark with `'use client'` directive only when using hooks/browser APIs
- **Server Components**: Default for App Router pages, use `async function` for data fetching
- **Utilities**: Use `cn()` from `lib/utils/cn.ts` for conditional classes

### PHP (Backend)
- **Controllers**: Extend `ControllerBase`, inject services via constructor, use `create()` factory
- **Services**: Registered in `module.services.yml` as `module_name.service_name`
- **Routing**: Define in `module.routing.yml` with `_format: json` for APIs
- **Permissions**: Check with `$this->currentUser()->hasPermission('permission_name')`

### CSS (Frontend)

### SVG Icons (Frontend)
- **Icon Library**: All SVG icons must be defined as React components in `lib/icons/ui-icons.tsx`
- **Never inline SVGs** directly in component JSX — always import from the icon library
  - ❌ `<svg viewBox="0 0 24 24">...</svg>` directly in a component
  - ✅ `import { IconEdit } from '@/lib/icons/ui-icons';` then `<IconEdit />`
- **Naming**: Use `Icon` prefix + PascalCase descriptor (`IconBillboard`, `IconCheckCircle`, `IconArchive`)
- **Adding new icons**: Export a new named function from `lib/icons/ui-icons.tsx`
  ```typescript
  export function IconExample() {
    return (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
        <path d="..." />
      </svg>
    );
  }
  ```
- **Usage**: Import and use as a component
  ```typescript
  import { IconEdit, IconBillboard } from '@/lib/icons/ui-icons';
  // In JSX:
  <IconEdit />
  ```

### CSS Styling (Frontend)
- **File Location**: Co-locate CSS with components (e.g., `BillboardMap.tsx` + `billboard-map.css` in same directory)
- **BEM Naming**: Use BEM methodology for class names (`.block__element--modifier`)
  ```css
  .epm-overlay { }              /* Block */
  .epm-modal { }                /* Block */
  .epm-header__title { }        /* Element */
  .epm-btn--primary { }         /* Modifier */
  ```
- **Prefix Convention**: Use abbreviated component prefix for scoping
  - `.epm-*` for EditPersonalModal
  - `.mfm-*` for MapFilterModal
  - `.pnp-*` for PushNotificationPrompt
- **Import Pattern**: Import CSS at top of component file
  ```typescript
  import './component-name.css';
  ```
- **CSS Variables**: Use defined CSS variables from `styles/globals.css` for brand colors
  ```css
  background: var(--color-primary);        /* #C1121F */
  background: var(--color-primary-dark);   /* #780000 */
  color: var(--color-text-primary);
  ```
- **Avoid Inline Tailwind**: Do NOT use inline Tailwind classes for modal/component styling
  - ❌ `className="fixed inset-0 bg-black/50"`
  - ✅ `className="epm-overlay"` + external CSS file
- **Tailwind Usage**: Reserve Tailwind for:
  - Utility-first page layouts
  - Rapid prototyping
  - Simple one-off spacing/sizing adjustments
- **Modal Pattern**: Follow established structure
  ```tsx
  <div className="prefix-overlay" />
  <div className="prefix-modal">
    <div className="prefix-header">...</div>
    <div className="prefix-body">...</div>
    <div className="prefix-footer">...</div>
  </div>
  ```

### Naming Conventions
- **API responses**: camelCase fields (`accountType`, `mobileNumber`, `billedId`)
- **Drupal fields**: snake_case with `field_` prefix (`field_mobile_number`, `field_org_type`)
- **Files**: kebab-case for CSS/config, PascalCase for components

### Example Files
- [frontendapp/components/map/BillboardMap.tsx](../frontendapp/components/map/BillboardMap.tsx) — Client component pattern
- [frontendapp/components/dashboard/EditPersonalModal.tsx](../frontendapp/components/dashboard/EditPersonalModal.tsx) + [edit-profile-modal.css](../frontendapp/components/dashboard/edit-profile-modal.css) — Modal component with BEM CSS
- [frontendapp/components/notifications/PushNotificationPrompt.tsx](../frontendapp/components/notifications/PushNotificationPrompt.tsx) + [push-notification-prompt.css](../frontendapp/components/notifications/push-notification-prompt.css) — CSS naming and structure reference
- [frontendapp/types/billboard.ts](../frontendapp/types/billboard.ts) — Type definitions
- [frontendapp/ONBOARDING_INTEGRATION.md](../frontendapp/ONBOARDING_INTEGRATION.md) — API integration guide
- `cmsapi/web/modules/custom/billoria_core/src/Controller/BillboardApiController.php` — API controller pattern

## Conventions

### API Integration
- **Centralize API calls** in `lib/api/` modules (e.g., `authAPI.register()`, `billboardAPI.list()`)
- **Standard response format**: `{ success: boolean, message: string, data: T, timestamp: number }`
- **Error responses**: `{ error: string }` with appropriate HTTP status
- **Never scatter raw fetch/axios calls** throughout components

### Security
- **Never trust frontend role checks** — always verify permissions in Drupal
- **CSRF tokens required** for all mutations (POST/PUT/DELETE)
- **Rate limiting** handled by backend EventSubscriber middleware
- **Roles**: `platform_admin`, `billboard_owner`, `agency`, `brand_user`, `vendor`

### Backend vs Frontend Responsibilities
- **Use Drupal for**: admin operations, content moderation, role/permission management, workflow definitions
- **Use Next.js for**: public-facing UX, search/discovery UI, dashboards, map interactions
- **Don't build duplicate admin logic in Next.js** if Drupal can handle it better

### MVP Scope (What to Build)
Build: Billboard listing, search/discovery, booking request flow, role-based auth, dashboard foundation

**Don't overbuild**: Full accounting, tax engines, legal automation, AI features until core marketplace works

See [docs/PROJECT_ROADMAP.md](../docs/PROJECT_ROADMAP.md) for phase planning

## Documentation

- **API Reference**: [application-wiki/QUICK_START.md](../application-wiki/QUICK_START.md), [API_OVERVIEW.md](../application-wiki/API_OVERVIEW.md)
- **Authentication**: [application-wiki/AUTHENTICATION.md](../application-wiki/AUTHENTICATION.md)
- **Billboard API**: [application-wiki/BILLBOARD_API.md](../application-wiki/BILLBOARD_API.md)
- **User Onboarding**: [docs/USER_ONBOARDING_SPEC.md](../docs/USER_ONBOARDING_SPEC.md)
- **Data Model**: [docs/Drupal_Content_Model_Sheet.md](../docs/Drupal_Content_Model_Sheet.md)
- **Project Status**: [docs/PROJECT_CHECKLIST.md](../docs/PROJECT_CHECKLIST.md)

## Common Tasks

**Add Drupal module**: `ddev composer require drupal/module_name && ddev drush pm:enable module_name && ddev drush cr`

**Run setup scripts**: `ddev ssh` then `php scripts/script-name.php` (70+ scripts in `cmsapi/scripts/`)

**Database backup**: `ddev snapshot` (automated hourly backups available)

**Access Drupal admin**: `ddev drush user:login` (generates one-time login link)

**Clear Next.js cache**: `rm -rf .next && pnpm dev`

**View container logs**: `ddev logs` (backend) or check Next.js terminal output (frontend)

## Important Notes

- **Package manager**: Use PNPM (via Corepack), not npm/yarn
- **Next.js version**: 16.2.1 has breaking changes from training data — consult Next.js docs before implementing features
- **API client**: Not yet implemented in `lib/api/` — create following patterns in [ONBOARDING_INTEGRATION.md](../frontendapp/ONBOARDING_INTEGRATION.md)
- **TODO markers**: Existing code has `// TODO: Replace with API call` comments marking incomplete integrations

- **Billboard API**: Use UUID (not NID) for all API endpoints and frontend integration. All billboard lookups, edits, and references must use the UUID field, not the numeric node ID (NID). This applies to both backend controller routes and all frontend API calls.

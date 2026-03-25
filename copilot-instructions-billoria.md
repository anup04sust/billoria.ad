# Copilot Instructions — Billoria.ad

## Project Overview

Billoria.ad is a headless Drupal + Next.js marketplace platform under DreamSteps for digitizing billboard discovery, booking, and operational workflows.

### Core business idea
The platform solves a major outdoor advertising problem: brands do not know which billboards are available, where they are located, what they cost, who controls them, or how to complete paperwork cleanly.

### Primary users
- Billboard owners
- Agencies
- Brand marketing teams
- Marketing vendors (design, print, install)
- Platform admins

### MVP goal
Build a nationwide-ready foundation, but implement a practical MVP focused on:
1. Billboard listing
2. Search and discovery
3. Booking request workflow
4. Role-based dashboard and communication foundation

### Long-term vision
- Paperwork automation
- Payment tracking
- Vendor bidding/tender flow
- Tax/legal workflow support
- Traffic/location intelligence
- AI-assisted billboard recommendation

---

## Architecture

### Stack
- **Backend CMS/API:** Drupal 11
- **Frontend:** Next.js
- **Approach:** Headless / decoupled
- **Local environment:** DDEV
- **Package manager:** PNPM preferred over npm

### Local domains
- Frontend: `https://billoria-ad.ddev.site`
- Drupal API: `https://api.billoria-ad.ddev.site`

### Repository structure
```text
billoria.ad/
├── cmsapi/              # Drupal backend
│   ├── .ddev/
│   ├── web/
│   ├── composer.json
│   └── ...
├── frontend/            # Next.js app
│   ├── .ddev/
│   ├── app/
│   ├── components/
│   ├── lib/
│   ├── package.json
│   └── ...
└── README.md
```

---

## Product rules

### Business assumptions
- Drupal is the source of truth for users, roles, permissions, structured content, and workflow data.
- Next.js is the source of truth for public UX, dashboards, search UI, and map-based discovery.
- Do not build duplicate admin logic in Next.js if Drupal admin can handle it better.
- Favor structured workflow over quick hacks.

### MVP scope
Build only:
- Billboard entity/content modeling
- Role-based registration/login foundation
- Listing management
- Billboard discovery UI
- Booking request flow
- Admin moderation foundation

Do not overbuild into:
- Full accounting system
- Full tax engine
- Full legal automation
- Full chat system unless explicitly prioritized
- AI features before marketplace basics work

### Phase 2 candidates
- Vendor bidding
- Payment integration
- Contract generation
- Tax reporting helpers
- Location scoring
- Traffic data integration
- Campaign intelligence

---

## Development principles

### General
- Prefer maintainable architecture over shortcuts.
- Use TypeScript on the frontend.
- Keep code readable for a long-lived product.
- Avoid unnecessary abstraction too early.
- Build MVP-ready code, but organize it so the system can grow.

### Drupal-side principles
- Use Drupal for:
  - roles and permissions
  - entity modeling
  - moderation/workflows
  - API exposure
  - admin operations
- Prefer configuration-first Drupal architecture where practical.
- Use custom modules for actual business logic.
- Do not force all business workflows into content types if custom entities are a better fit.

### Next.js-side principles
- Use App Router.
- Keep UI modular and role-aware.
- Use server-side checks for protected routes where possible.
- Keep API integration centralized under `lib/`.
- Do not scatter raw fetch calls throughout components.

---

## Roles and permissions model

Initial roles:
- `platform_admin`
- `billboard_owner`
- `agency`
- `brand_user`
- `vendor`

Rules:
- `platform_admin` manages approvals, system settings, and moderation.
- `billboard_owner` manages owned billboard inventory.
- `agency` may manage billboard inventory and booking negotiation on behalf of owners.
- `brand_user` searches, evaluates, and requests billboard bookings.
- `vendor` is reserved for future tender/bidding workflows.

Important:
- Never trust role decisions from the frontend alone.
- Drupal permissions must always enforce access control.
- Next.js should adapt UI based on role, but not act as the final security layer.

---

## Data modeling guidance

### Start with these major domain objects
- Billboard
- Agency profile
- Owner profile
- Booking request
- User/company profile
- Vendor profile
- Area taxonomy
- Billboard type taxonomy

### Billboard fields should likely include
- title
- unique billboard code
- owner reference
- agency reference
- district
- area
- full address
- coordinates
- width
- height
- type
- illumination
- facing direction
- road type
- traffic notes
- price model
- base price
- availability status
- media gallery
- verification status

### Booking request fields should likely include
- billboard reference
- requesting brand reference
- requested start date
- requested end date
- campaign type
- notes
- status
- assigned owner/agency
- communication trail reference

### Modeling notes
- Use Drupal content entities or nodes for content-heavy records such as billboards and profiles.
- Consider custom entities for workflow-heavy records such as bookings, bids, negotiations, or payment tracking.
- Keep future extensibility in mind for legal and tax workflows.

---

## API guidance

### Preferred approach
- Use Drupal JSON:API for standard content retrieval and relationship-driven data.
- Use custom Drupal endpoints for workflow actions such as:
  - registration
  - booking submission
  - approval actions
  - availability checks
  - role-aware dashboard summaries

### Frontend conventions
- Wrap all backend calls in reusable API helpers.
- Normalize API responses before passing them into UI components.
- Avoid mixing business logic into presentational components.

---

## Auth and registration guidance

### Strategy
- Drupal manages identity, roles, and access.
- Next.js provides login, registration, and protected dashboard UX.
- Use secure cookie-based session handling on the frontend side.
- Registration should support role-aware onboarding with approval where needed.

### Registration rules
- Allow self-registration for `brand_user`
- Allow self-registration for `vendor` only if future phase requires it
- Require approval for `billboard_owner` and `agency`
- Never expose self-registration for admin roles

### Security expectations
- Never trust client-supplied role assignment
- Never expose unsafe management endpoints publicly
- Validate role-based access on every protected backend action

---

## DDEV setup expectations

### This project runs as two DDEV projects
1. `cmsapi` for Drupal
2. `frontend` for Next.js

### Backend domain
`https://api.billoria-ad.ddev.site`

### Frontend domain
`https://billoria-ad.ddev.site`

### Backend config assumptions
- Drupal 11
- nginx-fpm
- MariaDB
- JSON:API enabled
- Headless-friendly CORS config

### Frontend config assumptions
- generic DDEV project
- Node.js enabled
- PNPM via Corepack
- Next.js dev server exposed via daemon

---

## Setup checklist

### Drupal backend setup
Inside `cmsapi/`:
1. Configure DDEV
2. Start DDEV
3. Create/install Drupal 11 project
4. Install Drush
5. Enable required modules
6. Configure trusted hosts
7. Configure local CORS for frontend domain
8. Create custom modules namespace for Billoria features

Example priorities:
- JSON:API
- Media
- Views
- Workflows
- Content Moderation
- custom auth/business modules

### Frontend setup
Inside `frontend/`:
1. Configure DDEV generic project
2. Enable Corepack
3. Activate PNPM
4. Create Next.js app with TypeScript
5. Configure environment variables
6. Build API client layer
7. Implement auth pages and role-aware route protection

Example environment variables:
```env
NEXT_PUBLIC_API_BASE_URL=https://api.billoria-ad.ddev.site
DRUPAL_BASE_URL=https://api.billoria-ad.ddev.site
```

---

## Coding conventions

### Naming
- Use clear, business-oriented names.
- Prefer `billboard`, `bookingRequest`, `ownerProfile`, `agencyProfile` over vague generic names.
- Keep Drupal machine names stable and predictable.

### Frontend structure suggestion
```text
frontend/
├── app/
├── components/
├── features/
│   ├── auth/
│   ├── billboards/
│   ├── booking/
│   └── dashboard/
├── lib/
│   ├── api/
│   ├── auth/
│   ├── utils/
│   └── config/
├── types/
└── styles/
```

### Drupal custom module structure suggestion
```text
cmsapi/web/modules/custom/
├── billoria_core/
├── billoria_auth/
├── billoria_billboard/
├── billoria_booking/
├── billoria_access/
└── billoria_vendor/
```

---

## What Copilot should optimize for

When generating code, optimize for:
- clarity
- long-term maintainability
- strong typing
- reusable patterns
- role-based access safety
- decoupled architecture
- Drupal compatibility
- Next.js App Router compatibility

Copilot should avoid:
- random third-party dependencies unless clearly justified
- mixing mock logic with production logic
- putting business rules only in frontend code
- overly clever abstractions
- using localStorage as the primary auth storage for sensitive sessions
- building features outside agreed MVP scope unless explicitly requested

---

## UI/UX direction

### Product tone
- Professional
- Clean
- Operational
- Data-first
- Marketplace + dashboard hybrid

### Frontend expectations
- Fast search experience
- Mobile-aware responsive UI
- Map-centric billboard browsing
- Clear dashboard separation by role
- Simple workflows with visible statuses

### Dashboard expectations
- Owner dashboard: manage listings and booking requests
- Agency dashboard: inventory and negotiation visibility
- Brand dashboard: saved billboards, requests, booking history
- Admin dashboard: approval, moderation, overview

---

## Suggested implementation order

### Backend-first foundation
1. User roles and permissions
2. Billboard content model
3. Booking request model
4. API exposure
5. Registration and auth workflow
6. Admin moderation

### Frontend after API contracts are clear
1. Public landing/search
2. Billboard listing page
3. Billboard detail page
4. Registration/login
5. Role-based dashboards
6. Booking request flow

---

## Future expansion notes

This project may later evolve into:
- multi-country outdoor media platform
- outdoor media procurement workflow engine
- AI-powered billboard recommendation system
- tender/vendor operations platform
- legal/compliance automation product

So generated code should not assume it will always remain a small local MVP.

---

## Copilot behavior guidance

When suggesting implementation:
- prefer practical MVP-first solutions
- mention tradeoffs clearly
- preserve separation of concerns
- respect the headless Drupal boundary
- keep config and code ready for DDEV-based local development

When uncertain:
- prefer stable, boring architecture over novelty
- do not invent business rules without clear basis
- leave extension points instead of hard-coding future assumptions

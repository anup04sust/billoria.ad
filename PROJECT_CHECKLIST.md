# Billoria Project Checklist

**Last Updated:** March 25, 2026

## How to Use This Checklist
- [ ] = Not Started
- [~] = In Progress
- [x] = Completed
- [!] = Blocked/Issues

---

## CMS Backend (Drupal)

### Initial Setup
- [x] **CMS-001**: Configure Drupal site settings (site name, email, timezone)
- [x] **CMS-002**: Install and configure required modules
  - [x] JSON:API / REST modules
  - [x] CORS configuration (via services.yml)
  - [x] Admin Toolbar
  - [x] Pathauto
  - [x] Token
  - [x] Other required modules: Serialization

### Content Architecture
- [ ] **CMS-003**: Define content types
  - [ ] Article/Blog posts
  - [ ] Pages
  - [ ] Products (if e-commerce)
  - [ ] Other: _____________
- [ ] **CMS-004**: Set up custom fields for content types
- [ ] **CMS-005**: Configure taxonomies/vocabularies
- [ ] **CMS-006**: Set up media entities (images, videos, files)

### API Configuration
- [ ] **CMS-007**: Enable and configure JSON:API
- [ ] **CMS-008**: Configure REST endpoints
- [ ] **CMS-009**: Set up API authentication (OAuth, JWT, or basic auth)
- [ ] **CMS-010**: Configure CORS settings for frontend domain

### Security & Permissions
- [ ] **CMS-011**: Create user roles (Admin, Editor, Author, etc.)
- [ ] **CMS-012**: Configure permissions for each role
- [ ] **CMS-013**: Set up content access controls
- [ ] **CMS-014**: Configure API access permissions

### Content & Testing
- [ ] **CMS-015**: Create sample content for testing
- [ ] **CMS-016**: Test API endpoints with Postman/curl
- [ ] **CMS-017**: Verify JSON responses are correct

---

## Frontend Application

### Project Setup
- [ ] **FE-001**: Initialize frontend project
  - Framework: _____________
  - Build tool: _____________
- [ ] **FE-002**: Set up project structure (components, pages, services, utils)
- [ ] **FE-003**: Configure environment variables
- [ ] **FE-004**: Set up linting and code formatting
- [ ] **FE-005**: Configure build and development scripts

### Core Infrastructure
- [ ] **FE-006**: Set up routing system
- [ ] **FE-007**: Create layout components (Header, Footer, Sidebar)
- [ ] **FE-008**: Implement navigation menu
- [ ] **FE-009**: Set up state management (if needed)
- [ ] **FE-010**: Configure error boundary/error handling

### API Integration
- [ ] **FE-011**: Create API client/service layer
- [ ] **FE-012**: Set up API base URL and configuration
- [ ] **FE-013**: Implement API authentication
- [ ] **FE-014**: Create data fetching hooks/utilities
- [ ] **FE-015**: Implement caching strategy (if needed)

### UI Components
- [ ] **FE-016**: Set up component library/design system
- [ ] **FE-017**: Create reusable UI components
  - [ ] Buttons
  - [ ] Forms
  - [ ] Cards
  - [ ] Modals
  - [ ] Loading states
  - [ ] Other: _____________
- [ ] **FE-018**: Implement responsive design
- [ ] **FE-019**: Set up CSS/styling approach

### Page Development
- [ ] **FE-020**: Create Home page
- [ ] **FE-021**: Create listing pages (blog, products, etc.)
- [ ] **FE-022**: Create detail pages (article, product, etc.)
- [ ] **FE-023**: Create search/filter functionality
- [ ] **FE-024**: Create 404/error pages
- [ ] **FE-025**: Other pages: _____________

### Advanced Features
- [ ] **FE-026**: Implement SEO optimization (meta tags, structured data)
- [ ] **FE-027**: Set up analytics tracking
- [ ] **FE-028**: Implement lazy loading for images/components
- [ ] **FE-029**: Add accessibility features (ARIA, keyboard navigation)
- [ ] **FE-030**: Set up internationalization (if needed)

---

## Integration & Testing

### CMS-Frontend Connection
- [ ] **INT-001**: Connect frontend to CMS API endpoints
- [ ] **INT-002**: Test data fetching from CMS
- [ ] **INT-003**: Verify CORS configuration works
- [ ] **INT-004**: Test authentication flow
- [ ] **INT-005**: Handle API errors gracefully

### Data Flow Testing
- [ ] **INT-006**: Test content type rendering
- [ ] **INT-007**: Test media/image loading
- [ ] **INT-008**: Test pagination functionality
- [ ] **INT-009**: Test filtering/search
- [ ] **INT-010**: Test real-time updates (if applicable)

### Performance & Quality
- [ ] **INT-011**: Run performance tests
- [ ] **INT-012**: Optimize API response times
- [ ] **INT-013**: Implement caching strategies
- [ ] **INT-014**: Cross-browser testing
- [ ] **INT-015**: Mobile responsiveness testing

---

## Deployment & DevOps

### Environment Setup
- [ ] **DEV-001**: Set up development environment
- [ ] **DEV-002**: Set up staging environment
- [ ] **DEV-003**: Set up production environment
- [ ] **DEV-004**: Configure CI/CD pipeline

### Deployment
- [ ] **DEV-005**: Deploy CMS to hosting
- [ ] **DEV-006**: Deploy frontend to hosting
- [ ] **DEV-007**: Configure domain and SSL
- [ ] **DEV-008**: Set up monitoring and logging
- [ ] **DEV-009**: Create backup strategy

---

## Documentation

- [ ] **DOC-001**: Document API endpoints
- [ ] **DOC-002**: Document frontend architecture
- [ ] **DOC-003**: Create deployment guide
- [ ] **DOC-004**: Write user manual for CMS editors
- [ ] **DOC-005**: Document environment setup

---

## Notes & Issues

### Current Focus
<!-- Add notes about what you're currently working on -->

### Blockers
<!-- List any blocking issues -->

### Questions/Decisions Needed
<!-- List any outstanding questions or decisions -->

### Recent Changes
<!-- Log recent important changes -->
- **2026-03-25**: Completed CMS-002 - Installed and configured required modules:
  - JSON:API (core module) for RESTful API access
  - REST and Serialization modules for web services
  - Admin Toolbar + Admin Toolbar Extra Tools for enhanced admin UI
  - Pathauto for automatic URL aliasing
  - Token for dynamic token replacement
  - CORS configured in services.yml to allow:
    - Origins: billoria-ad.ddev.site, localhost:3000
    - Methods: GET, POST, PATCH, DELETE, OPTIONS
    - Headers: Content-Type, Authorization, X-Requested-With, Accept


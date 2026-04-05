# Billoria Production-Ready Checklist

**Last Updated:** April 1, 2026  
**Architecture:** Decoupled Drupal 11 (Headless CMS) + Next.js 16 (Frontend)  
**Target:** Production deployment with enterprise-grade quality

## Legend

- [ ] Not Started
- [~] In Progress  
- [x] Completed
- [!] Blocked/Issue
- [⚠] Needs Review

---

# 🔧 PHASE 1: DEVELOPMENT FOUNDATION

## 1.1 Drupal Backend (CMS API)

### Core Setup

- [x] **BE-001**: Drupal 11.3.5 installed with PHP 8.4, MariaDB 11.8
- [x] **BE-002**: Core modules enabled (JSON:API, REST, Serialization)
- [x] **BE-003**: CORS configured for Next.js origins
- [x] **BE-004**: Multilingual support (English/Bengali)
- [x] **BE-005**: Admin Toolbar + Gin theme installed
- [x] **BE-006**: Pathauto + Token for URL management
- [ ] **BE-007**: Automated deployment scripts (Drush, config export)

### Custom Modules

- [x] **BE-008**: billoria_core module (11 services, 15 permissions, 5 roles)
- [x] **BE-009**: billoria_accounts module (4 controllers, registration/auth APIs)
- [ ] **BE-010**: Custom entity: Booking Request (replace database table)
- [ ] **BE-011**: Custom entity: Price Rule (dynamic pricing configuration)
- [ ] **BE-012**: Custom plugin: Payment Gateway integration
- [ ] **BE-013**: Update hooks for schema migrations

### Content Architecture

- [x] **BE-014**: Billboard content type (40+ fields)
- [x] **BE-015**: Organization content type (35 fields, brand/agency/owner types)
- [x] **BE-016**: 13 taxonomies with 743 terms (divisions, districts, upazilas, etc.)
- [x] **BE-017**: Field display configurations
- [x] **BE-018**: View modes for different contexts
- [ ] **BE-019**: Media entity configuration (image styles, crops, focal point)
- [ ] **BE-020**: File upload validation and sanitization

### API Layer

- [x] **BE-021**: Billboard CRUD APIs (/api/v1/billboard/*)
- [x] **BE-022**: Authentication APIs (login, logout, session)
- [x] **BE-023**: Registration + verification APIs
- [x] **BE-024**: Profile management APIs
- [x] **BE-025**: AI/Ollama integration APIs
- [x] **BE-026**: CSRF token endpoint
- [ ] **BE-027**: API versioning strategy (all routes use /v1/ or /v2/)
- [ ] **BE-028**: API rate limit documentation
- [ ] **BE-029**: Webhook endpoints for real-time updates
- [ ] **BE-030**: GraphQL endpoint (optional alternative to REST)

### Security Hardening

- [x] **BE-031**: CSRF protection implemented
- [x] **BE-032**: Rate limiting with Flood API
- [x] **BE-033**: Pattern detection (sequential emails, disposable domains)
- [x] **BE-034**: Role-based access control (RBAC) configured
- [ ] **BE-035**: Input validation service (Symfony Validator)
- [ ] **BE-036**: XSS prevention audit
- [ ] **BE-037**: SQL injection audit
- [ ] **BE-038**: Secure file upload handling
- [ ] **BE-039**: HTTPS enforcement in production
- [ ] **BE-040**: Security headers (CSP, HSTS, X-Frame-Options)
- [ ] **BE-041**: Secrets management (API keys, credentials)
- [ ] **BE-042**: Two-factor authentication (2FA)
- [ ] **BE-043**: API key rotation policy

### Performance Optimization

- [ ] **BE-044**: Database indexing on frequently queried fields
- [ ] **BE-045**: Cache API implementation for billboard listings
- [ ] **BE-046**: Redis/Memcached for session storage
- [ ] **BE-047**: CDN configuration for media files
- [ ] **BE-048**: Image optimization (lazy loading, responsive images)
- [ ] **BE-049**: Query optimization (EntityQuery vs Database API)
- [ ] **BE-050**: Aggregation and minification
- [ ] **BE-051**: OpCache configuration
- [ ] **BE-052**: MySQL query cache tuning
- [ ] **BE-053**: Varnish/reverse proxy setup

### Queue & Background Jobs

- [ ] **BE-054**: Queue API for email notifications
- [ ] **BE-055**: Cron job for expired booking cleanup
- [ ] **BE-056**: Cron job for reminder notifications
- [ ] **BE-057**: Async thumbnail generation
- [ ] **BE-058**: Batch operations for bulk updates

### Testing

- [ ] **BE-059**: PHPUnit tests for BillboardManager service
- [ ] **BE-060**: PHPUnit tests for BookingManager service
- [ ] **BE-061**: PHPUnit tests for PricingCalculator service
- [ ] **BE-062**: PHPUnit tests for VerificationService
- [ ] **BE-063**: Kernel tests for API controllers
- [ ] **BE-064**: Functional tests for registration workflow
- [ ] **BE-065**: Functional tests for booking workflow
- [ ] **BE-066**: API integration tests (all endpoints)
- [ ] **BE-067**: Security tests (CSRF, XSS, SQL injection)
- [ ] **BE-068**: Performance tests (load testing with 10K+ billboards)
- [ ] **BE-069**: Test coverage > 70%

---

## 1.2 Next.js Frontend

### Project Setup

- [x] **FE-001**: Next.js 16.2.1 with App Router initialized
- [x] **FE-002**: TypeScript 5 configured
- [x] **FE-003**: Tailwind CSS 4 + custom CSS modules
- [x] **FE-004**: PNPM package manager via Corepack
- [x] **FE-005**: ESLint configured
- [ ] **FE-006**: Prettier code formatter
- [ ] **FE-007**: Husky + lint-staged for pre-commit hooks
- [ ] **FE-008**: Environment variables documented (.env.example)

### Architecture & Structure

- [x] **FE-009**: App Router pages structure
- [x] **FE-010**: Component library organized (auth, billboard, dashboard, layout, map, shared)
- [x] **FE-011**: API client modules (auth, billboard, profile, ai)
- [x] **FE-012**: TypeScript type definitions (Billboard, User, etc.)
- [x] **FE-013**: Middleware for auth protection
- [ ] **FE-014**: Error boundaries for all routes
- [ ] **FE-015**: Loading skeletons/states for async operations
- [ ] **FE-016**: Global state management (React Context or Zustand)

### API Integration

- [x] **FE-017**: API client with fallback URLs
- [x] **FE-018**: CORS credentials handling
- [ ] **FE-019**: React Query/TanStack Query for data fetching
- [ ] **FE-020**: Optimistic updates for mutations
- [ ] **FE-021**: Automatic retry logic
- [ ] **FE-022**: Request/response interceptors
- [ ] **FE-023**: API error handling with user-friendly messages
- [ ] **FE-024**: Request deduplication
- [ ] **FE-025**: Shared API client (remove duplication)

### Authentication & Security

- [x] **FE-026**: Cookie-based authentication implemented
- [x] **FE-027**: Middleware route protection
- [x] **FE-028**: Role-based routing
- [ ] **FE-029**: Move CSRF tokens from localStorage to HttpOnly cookies
- [ ] **FE-030**: Secure session storage (no sensitive data in localStorage)
- [ ] **FE-031**: XSS prevention (DOMPurify for user-generated content)
- [ ] **FE-032**: CSRF token refresh on expiry
- [ ] **FE-033**: Auto-logout on token expiration
- [ ] **FE-034**: Secure password requirements validation
- [ ] **FE-035**: Content Security Policy (CSP) headers
- [ ] **FE-036**: Input sanitization for forms

### Forms & Validation

- [ ] **FE-037**: React Hook Form integration
- [ ] **FE-038**: Zod schema validation
- [ ] **FE-039**: Form error handling with user feedback
- [ ] **FE-040**: Real-time validation
- [ ] **FE-041**: Accessible form fields (ARIA labels)
- [ ] **FE-042**: File upload with validation
- [ ] **FE-043**: Multi-step forms for registration/booking

### UI/UX Components

- [x] **FE-044**: Header with navigation
- [x] **FE-045**: Footer with links
- [x] **FE-046**: Dashboard sidebar
- [x] **FE-047**: Chatbot widget (AI-powered)
- [x] **FE-048**: Search overlay
- [x] **FE-049**: Billboard map (Leaflet)
- [x] **FE-050**: Login/Register forms
- [ ] **FE-051**: Toast notifications (success/error)
- [ ] **FE-052**: Modal dialogs
- [ ] **FE-053**: Confirmation dialogs
- [ ] **FE-054**: Loading spinners/skeletons
- [ ] **FE-055**: Pagination component
- [ ] **FE-056**: Breadcrumb navigation
- [ ] **FE-057**: Empty states
- [ ] **FE-058**: 404/500 error pages

### Performance Optimization

- [ ] **FE-059**: Code splitting (dynamic imports)
- [ ] **FE-060**: Lazy loading for routes
- [ ] **FE-061**: Image optimization (next/image everywhere)
- [ ] **FE-062**: Font optimization (next/font)
- [ ] **FE-063**: Bundle size analysis (next/bundle-analyzer)
- [ ] **FE-064**: Tree shaking unused code
- [ ] **FE-065**: Memoization (useMemo, useCallback) for heavy computations
- [ ] **FE-066**: Virtual scrolling for large lists
- [ ] **FE-067**: Debouncing/throttling for search
- [ ] **FE-068**: Service Worker for offline support
- [ ] **FE-069**: PWA configuration (manifest.json)
- [ ] **FE-070**: Static page generation where possible
- [ ] **FE-071**: Incremental Static Regeneration (ISR)

### Testing

- [ ] **FE-072**: Vitest setup for unit tests
- [ ] **FE-073**: React Testing Library for component tests
- [ ] **FE-074**: Test API client modules
- [ ] **FE-075**: Test form validation logic
- [ ] **FE-076**: Test authentication flows
- [ ] **FE-077**: E2E tests with Playwright/Cypress
- [ ] **FE-078**: Visual regression tests (Percy/Chromatic)
- [ ] **FE-079**: Test coverage > 70%
- [ ] **FE-080**: Accessibility tests (axe-core)

### Accessibility (WCAG 2.1 AA)

- [ ] **FE-081**: Semantic HTML throughout
- [ ] **FE-082**: ARIA labels for interactive elements
- [ ] **FE-083**: Keyboard navigation support
- [ ] **FE-084**: Focus management
- [ ] **FE-085**: Color contrast ratio > 4.5:1
- [ ] **FE-086**: Screen reader compatibility
- [ ] **FE-087**: Skip to content links
- [ ] **FE-088**: Form labels and error associations
- [ ] **FE-089**: Accessible modals (focus trap)
- [ ] **FE-090**: Alt text for all images

### SEO & Meta

- [ ] **FE-091**: Update default metadata (remove "Create Next App")
- [ ] **FE-092**: Dynamic meta tags per page
- [ ] **FE-093**: Open Graph tags
- [ ] **FE-094**: Twitter Card tags
- [ ] **FE-095**: Canonical URLs
- [ ] **FE-096**: robots.txt
- [ ] **FE-097**: sitemap.xml (dynamic generation)
- [ ] **FE-098**: Schema.org structured data (LocalBusiness, Product)
- [ ] **FE-099**: Arabic/Bengali language meta tags

### Internationalization (i18n)

- [ ] **FE-100**: next-intl setup
- [ ] **FE-101**: Extract all hardcoded strings
- [ ] **FE-102**: Bengali translations
- [ ] **FE-103**: Language switcher
- [ ] **FE-104**: RTL support (if needed)
- [ ] **FE-105**: Date/time localization
- [ ] **FE-106**: Currency formatting (BDT)

### Analytics & Monitoring

- [ ] **FE-107**: Google Analytics 4 integration
- [ ] **FE-108**: Error tracking (Sentry)
- [ ] **FE-109**: Performance monitoring (Web Vitals)
- [ ] **FE-110**: User session recording (optional - Hotjar/LogRocket)
- [ ] **FE-111**: Custom event tracking (button clicks, form submissions)

---

# 🔒 PHASE 2: SECURITY HARDENING

## 2.1 Backend Security

- [ ] **SEC-BE-001**: Penetration testing completed
- [ ] **SEC-BE-002**: OWASP Top 10 vulnerabilities addressed
- [ ] **SEC-BE-003**: Dependency vulnerability scan (composer audit)
- [ ] **SEC-BE-004**: Database encryption at rest
- [ ] **SEC-BE-005**: Backup encryption
- [ ] **SEC-BE-006**: Secrets rotation policy
- [ ] **SEC-BE-007**: API key expiration
- [ ] **SEC-BE-008**: Session hijacking prevention
- [ ] **SEC-BE-009**: Brute force protection
- [ ] **SEC-BE-010**: Admin access IP whitelist
- [ ] **SEC-BE-011**: Disable directory listing
- [ ] **SEC-BE-012**: Remove default Drupal files (CHANGELOG.txt, README.txt)
- [ ] **SEC-BE-013**: Hide Drupal version in headers

## 2.2 Frontend Security

- [ ] **SEC-FE-001**: npm audit fix (no high/critical vulnerabilities)
- [ ] **SEC-FE-002**: Dependency scanning in CI/CD
- [ ] **SEC-FE-003**: Subresource Integrity (SRI) for CDN assets
- [ ] **SEC-FE-004**: No sensitive data in client-side code
- [ ] **SEC-FE-005**: Environment variables properly scoped (NEXT_PUBLIC_ only for public)
- [ ] **SEC-FE-006**: HTTPS enforcement
- [ ] **SEC-FE-007**: Security headers configured (Helmet.js)
- [ ] **SEC-FE-008**: Click-jacking prevention (X-Frame-Options)
- [ ] **SEC-FE-009**: MIME sniffing prevention (X-Content-Type-Options)

## 2.3 Infrastructure Security

- [ ] **SEC-INF-001**: SSL/TLS certificates (Let's Encrypt or commercial)
- [ ] **SEC-INF-002**: Firewall rules configured
- [ ] **SEC-INF-003**: DDoS protection (Cloudflare/AWS Shield)
- [ ] **SEC-INF-004**: Server hardening (disable unnecessary services)
- [ ] **SEC-INF-005**: SSH key-based authentication only
- [ ] **SEC-INF-006**: Regular security updates automated
- [ ] **SEC-INF-007**: Intrusion detection system (IDS)
- [ ] **SEC-INF-008**: Web Application Firewall (WAF)

---

# ⚡ PHASE 3: PERFORMANCE & OPTIMIZATION

## 3.1 Backend Performance

- [ ] **PERF-BE-001**: Database query optimization (EXPLAIN analysis)
- [ ] **PERF-BE-002**: Composite indexes on join columns
- [ ] **PERF-BE-003**: Redis caching layer
- [ ] **PERF-BE-004**: Varnish reverse proxy
- [ ] **PERF-BE-005**: OpCache configuration tuned
- [ ] **PERF-BE-006**: PHP-FPM pool optimization
- [ ] **PERF-BE-007**: MySQL connection pooling
- [ ] **PERF-BE-008**: Slow query log analysis
- [ ] **PERF-BE-009**: N+1 query elimination
- [ ] **PERF-BE-010**: API response time < 200ms (p95)
- [ ] **PERF-BE-011**: Image CDN (Cloudinary/Imgix)

## 3.2 Frontend Performance

- [ ] **PERF-FE-001**: Lighthouse score > 90 (all metrics)
- [ ] **PERF-FE-002**: First Contentful Paint (FCP) < 1.8s
- [ ] **PERF-FE-003**: Largest Contentful Paint (LCP) < 2.5s
- [ ] **PERF-FE-004**: Cumulative Layout Shift (CLS) < 0.1
- [ ] **PERF-FE-005**: Time to Interactive (TTI) < 3.8s
- [ ] **PERF-FE-006**: Total Blocking Time (TBT) < 200ms
- [ ] **PERF-FE-007**: JavaScript bundle < 200KB gzipped
- [ ] **PERF-FE-008**: CSS bundle < 50KB gzipped
- [ ] **PERF-FE-009**: Critical CSS inlined
- [ ] **PERF-FE-010**: Unused CSS removed
- [ ] **PERF-FE-011**: Font preloading
- [ ] **PERF-FE-012**: Image lazy loading (native + React)
- [ ] **PERF-FE-013**: WebP/AVIF image formats
- [ ] **PERF-FE-014**: Brotli compression enabled

---

# 🧪 PHASE 4: TESTING & QUALITY ASSURANCE

## 4.1 Backend Testing

- [ ] **TEST-BE-001**: Unit test coverage > 70%
- [ ] **TEST-BE-002**: Integration test coverage > 60%
- [ ] **TEST-BE-003**: API endpoint tests (Postman/Newman)
- [ ] **TEST-BE-004**: Load testing (Apache JMeter/Locust)
  - [ ] 100 concurrent users
  - [ ] 1000 requests/minute sustained
  - [ ] Response time under load < 500ms
- [ ] **TEST-BE-005**: Stress testing (find breaking point)
- [ ] **TEST-BE-006**: Database backup/restore testing
- [ ] **TEST-BE-007**: Disaster recovery simulation

## 4.2 Frontend Testing

- [ ] **TEST-FE-001**: Unit test coverage > 70%
- [ ] **TEST-FE-002**: Component test coverage > 60%
- [ ] **TEST-FE-003**: E2E critical user flows:
  - [ ] User registration → verification → login
  - [ ] Billboard search → view → booking request
  - [ ] Profile creation/editing
  - [ ] Dashboard navigation
- [ ] **TEST-FE-004**: Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] **TEST-FE-005**: Mobile responsive testing (iOS, Android)
- [ ] **TEST-FE-006**: Network throttling tests (3G, slow 4G)
- [ ] **TEST-FE-007**: Offline behavior testing

## 4.3 Integration Testing

- [ ] **TEST-INT-001**: End-to-end system testing
- [ ] **TEST-INT-002**: API contract testing
- [ ] **TEST-INT-003**: Third-party integration tests
- [ ] **TEST-INT-004**: Payment gateway sandbox testing
- [ ] **TEST-INT-005**: Email delivery testing
- [ ] **TEST-INT-006**: SMS/OTP delivery testing

---

# 🚀 PHASE 5: DEPLOYMENT & INFRASTRUCTURE

## 5.1 CI/CD Pipeline

- [ ] **DEPLOY-001**: GitHub Actions workflows configured
- [ ] **DEPLOY-002**: Automated testing on PR
- [ ] **DEPLOY-003**: Automated builds on merge
- [ ] **DEPLOY-004**: Staging environment deployment
- [ ] **DEPLOY-005**: Production deployment with approval
- [ ] **DEPLOY-006**: Rollback strategy documented
- [ ] **DEPLOY-007**: Blue-green deployment setup
- [ ] **DEPLOY-008**: Database migration automation
- [ ] **DEPLOY-009**: Config sync automation (Drupal)

## 5.2 Backend Infrastructure

- [ ] **DEPLOY-010**: Production server provisioned
- [ ] **DEPLOY-011**: Database server (MySQL/PostgreSQL)
- [ ] **DEPLOY-012**: Redis server for caching
- [ ] **DEPLOY-013**: File storage (S3/DigitalOcean Spaces)
- [ ] **DEPLOY-014**: Email service (SendGrid/Mailgun)
- [ ] **DEPLOY-015**: SMS gateway (Twilio/local provider)
- [ ] **DEPLOY-016**: CDN setup (Cloudflare/AWS CloudFront)
- [ ] **DEPLOY-017**: Domain DNS configured (billoria.ad)
- [ ] **DEPLOY-018**: SSL certificates installed
- [ ] **DEPLOY-019**: Load balancer configured (if needed)

## 5.3 Frontend Infrastructure

- [ ] **DEPLOY-020**: Vercel/Netlify deployment configured
  - Alternative: [ ] Docker + Nginx on VPS
- [ ] **DEPLOY-021**: Environment variables configured
- [ ] **DEPLOY-022**: Preview deployments enabled
- [ ] **DEPLOY-023**: Production domain linked
- [ ] **DEPLOY-024**: Asset optimization enabled
- [ ] **DEPLOY-025**: Edge caching configured
- [ ] **DEPLOY-026**: Serverless functions (if needed)

## 5.4 DevOps & Monitoring Setup

- [ ] **DEPLOY-027**: Server monitoring (Datadog/New Relic/Prometheus)
- [ ] **DEPLOY-028**: Application performance monitoring (APM)
- [ ] **DEPLOY-029**: Log aggregation (ELK Stack/Loki/CloudWatch)
- [ ] **DEPLOY-030**: Uptime monitoring (UptimeRobot/Pingdom)
- [ ] **DEPLOY-031**: Error tracking (Sentry/Rollbar)
- [ ] **DEPLOY-032**: Alerting configured (PagerDuty/Slack)
- [ ] **DEPLOY-033**: Status page (statuspage.io)
- [ ] **DEPLOY-034**: Backup automation (daily database, weekly files)
- [ ] **DEPLOY-035**: Backup restoration tested

---

# 📊 PHASE 6: MONITORING & OBSERVABILITY

## 6.1 Backend Monitoring

- [ ] **MON-BE-001**: Response time metrics
- [ ] **MON-BE-002**: Error rate tracking
- [ ] **MON-BE-003**: Database query performance
- [ ] **MON-BE-004**: Cache hit rate monitoring
- [ ] **MON-BE-005**: Queue job monitoring
- [ ] **MON-BE-006**: API endpoint usage analytics
- [ ] **MON-BE-007**: Disk space alerts
- [ ] **MON-BE-008**: Memory usage alerts
- [ ] **MON-BE-009**: CPU usage alerts
- [ ] **MON-BE-010**: Database connection pool monitoring

## 6.2 Frontend Monitoring

- [ ] **MON-FE-001**: Real User Monitoring (RUM)
- [ ] **MON-FE-002**: Core Web Vitals tracking
- [ ] **MON-FE-003**: JavaScript error tracking
- [ ] **MON-FE-004**: Network request failures
- [ ] **MON-FE-005**: Page load time by route
- [ ] **MON-FE-006**: API call latency tracking
- [ ] **MON-FE-007**: User session analytics
- [ ] **MON-FE-008**: Conversion funnel tracking

## 6.3 Business Metrics

- [ ] **MON-BIZ-001**: User registration rate
- [ ] **MON-BIZ-002**: Billboard listing growth
- [ ] **MON-BIZ-003**: Booking conversion rate
- [ ] **MON-BIZ-004**: Search query patterns
- [ ] **MON-BIZ-005**: Daily/Monthly active users (DAU/MAU)
- [ ] **MON-BIZ-006**: Revenue tracking (when applicable)
- [ ] **MON-BIZ-007**: Verification completion rate
- [ ] **MON-BIZ-008**: Average time to book

---

# ✅ PHASE 7: PRE-LAUNCH VALIDATION

## 7.1 Functional Testing

- [ ] **VALID-001**: All user flows tested end-to-end
- [ ] **VALID-002**: Role-based access control verified
- [ ] **VALID-003**: Form submissions working
- [ ] **VALID-004**: Email notifications received
- [ ] **VALID-005**: SMS OTP delivery confirmed
- [ ] **VALID-006**: Search functionality accurate
- [ ] **VALID-007**: Map rendering correctly
- [ ] **VALID-008**: Image uploads working
- [ ] **VALID-009**: File downloads working
- [ ] **VALID-010**: Multi-language switching

## 7.2 Security Audit

- [ ] **VALID-011**: Penetration test report reviewed
- [ ] **VALID-012**: Vulnerability scan completed
- [ ] **VALID-013**: No high/critical security issues
- [ ] **VALID-014**: Data privacy compliance (GDPR if applicable)
- [ ] **VALID-015**: Terms of Service + Privacy Policy live
- [ ] **VALID-016**: Cookie consent implemented
- [ ] **VALID-017**: Data retention policy defined
- [ ] **VALID-018**: Right to deletion implemented

## 7.3 Performance Validation

- [ ] **VALID-019**: Load test passed (target: 100 concurrent users)
- [ ] **VALID-020**: No memory leaks detected
- [ ] **VALID-021**: Database connection pooling stable
- [ ] **VALID-022**: Cache warming strategy tested
- [ ] **VALID-023**: CDN serving assets correctly
- [ ] **VALID-024**: Mobile performance acceptable (< 4s LCP on 4G)
- [ ] **VALID-025**: API rate limiting verified

## 7.4 Content & Data Validation

- [ ] **VALID-026**: Taxonomy terms complete (divisions, districts, etc.)
- [ ] **VALID-027**: Sample billboard listings created
- [ ] **VALID-028**: No broken links
- [ ] **VALID-029**: No 404 errors on critical pages
- [ ] **VALID-030**: SEO meta tags verified
- [ ] **VALID-031**: Sitemap generated and accessible
- [ ] **VALID-032**: robots.txt configured correctly
- [ ] **VALID-033**: Favicons and app icons in place

## 7.5 User Acceptance Testing (UAT)

- [ ] **VALID-034**: Stakeholder demo completed
- [ ] **VALID-035**: Beta user feedback collected
- [ ] **VALID-036**: Critical bugs fixed
- [ ] **VALID-037**: Nice-to-have bugs documented for v1.1
- [ ] **VALID-038**: User manual/FAQ prepared
- [ ] **VALID-039**: Admin training completed
- [ ] **VALID-040**: Customer support prepared

---

# 🚢 PHASE 8: GO-LIVE & LAUNCH

## 8.1 Pre-Launch Checklist

- [ ] **LAUNCH-001**: Staging environment final test
- [ ] **LAUNCH-002**: Production database migrated
- [ ] **LAUNCH-003**: Production environment variables configured
- [ ] **LAUNCH-004**: DNS records updated (A, AAAA, CNAME)
- [ ] **LAUNCH-005**: SSL certificates verified
- [ ] **LAUNCH-006**: CDN cache purged
- [ ] **LAUNCH-007**: Monitoring alerts configured
- [ ] **LAUNCH-008**: Backup verified within 24 hours
- [ ] **LAUNCH-009**: Rollback plan documented
- [ ] **LAUNCH-010**: Emergency contact list prepared

## 8.2 Launch Day Tasks

- [ ] **LAUNCH-011**: Deployment executed
- [ ] **LAUNCH-012**: Smoke tests passed
- [ ] **LAUNCH-013**: Critical user flows tested in production
- [ ] **LAUNCH-014**: Monitoring dashboards reviewed
- [ ] **LAUNCH-015**: No critical errors in logs
- [ ] **LAUNCH-016**: Team on standby for 24 hours
- [ ] **LAUNCH-017**: Status page updated (if applicable)

## 8.3 Post-Launch (First 7 Days)

- [ ] **LAUNCH-018**: Daily monitoring of error rates
- [ ] **LAUNCH-019**: Performance metrics within targets
- [ ] **LAUNCH-020**: User feedback collection started
- [ ] **LAUNCH-021**: Support tickets triaged
- [ ] **LAUNCH-022**: Hot-fixes deployed (if needed)
- [ ] **LAUNCH-023**: Incident retrospective (if issues occurred)
- [ ] **LAUNCH-024**: Marketing/announcement (if planned)

---

# 📋 ADDITIONAL CONSIDERATIONS

## Documentation

- [ ] **DOC-001**: API documentation complete (Swagger/OpenAPI)
- [ ] **DOC-002**: Developer onboarding guide
- [ ] **DOC-003**: Deployment runbook
- [ ] **DOC-004**: Troubleshooting guide
- [ ] **DOC-005**: Architecture diagrams updated
- [ ] **DOC-006**: Database schema documentation
- [ ] **DOC-007**: Component library/Storybook (optional)

## Legal & Compliance

- [ ] **LEGAL-001**: Terms of Service finalized
- [ ] **LEGAL-002**: Privacy Policy finalized
- [ ] **LEGAL-003**: Cookie Policy implemented
- [ ] **LEGAL-004**: Copyright notices in place
- [ ] **LEGAL-005**: License compliance (open-source dependencies)
- [ ] **LEGAL-006**: Trademark registration (if applicable)

## Business Readiness

- [ ] **BIZ-001**: Payment gateway integration (if phase 1)
- [ ] **BIZ-002**: Invoicing system ready
- [ ] **BIZ-003**: Customer support channels established
- [ ] **BIZ-004**: Marketing site/landing page
- [ ] **BIZ-005**: Social media presence
- [ ] **BIZ-006**: Analytics dashboards for stakeholders

---

# 🎯 PRIORITY MATRIX

## Critical (Must Fix Before Launch)

- Security vulnerabilities (HIGH/CRITICAL)
- Authentication/authorization issues
- Data loss risks
- Payment processing errors (if applicable)
- Core user flows broken

## High Priority (Should Fix)

- Performance issues affecting UX
- SEO meta tags missing
- Mobile responsiveness problems
- Accessibility violations (WCAG AA)

## Medium Priority (Nice to Have)

- UI polish and animations
- Advanced filtering features
- Analytics integrations
- Third-party integrations

## Low Priority (Defer to v1.1)

- Optional features from spec
- Advanced admin tools
- AI enhancements beyond chatbot
- Complex reporting features

---

# 📈 SUCCESS METRICS

## Technical KPIs

- [ ] API response time p95 < 200ms
- [ ] Frontend Lighthouse score > 90
- [ ] Uptime > 99.5%
- [ ] Error rate < 0.5%
- [ ] Test coverage > 70% (backend + frontend)

## Business KPIs (Post-Launch)

- [ ] 100+ billboard listings in first month
- [ ] 50+ registered users (brands/agencies/owners)
- [ ] 10+ booking requests processed
- [ ] < 5 second average search to result time
- [ ] > 60% profile completion rate

---

**Checklist Version:** 1.0  
**Created:** 2026-04-01  
**Next Review:** Weekly until launch, monthly post-launch

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
- **2026-03-26**: Completed CMS-002b - Installed AI modules:
  - AI Core (v1.3.1) - Framework for AI integrations
  - AI Translate (v1.3.1) - AI-powered translation capabilities
  - AI Provider: Ollama (v1.2.0-rc2) - Local LLM provider support
  - Key module (v1.22.0) - Secure API key management
  - Bengali translations imported for all AI modules
- **2026-03-26**: Completed CMS-002a - Configured multilingual support:
  - Enabled Language, Content Translation, Config Translation, and Interface Translation modules
  - Added Bengali (বাংলা) as secondary language
  - English set as primary/default language
  - URL path prefix configured: en=default (/), bn=/bn
  - Imported 510+ Bengali translations for Drupal core and contrib modules
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

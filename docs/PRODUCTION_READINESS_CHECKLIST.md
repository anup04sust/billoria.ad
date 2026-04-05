# Billoria.ad Production Readiness Checklist

**Version:** 1.0  
**Created:** April 1, 2026  
**Architecture:** Decoupled Drupal 11 (Headless) + Next.js 16 (Frontend)  
**Purpose:** Pre-deployment validation checklist for production launch

---

## 🎯 Quick Status Overview

| Category | Items | Completed | Status |
|----------|-------|-----------|--------|
| Backend Security | 15 | 0 | ⚠️ Not Started |
| Frontend Security | 12 | 0 | ⚠️ Not Started |
| Performance | 20 | 0 | ⚠️ Not Started |
| Testing | 18 | 0 | ⚠️ Not Started |
| Infrastructure | 25 | 0 | ⚠️ Not Started |
| Monitoring | 12 | 0 | ⚠️ Not Started |
| Documentation | 8 | 0 | ⚠️ Not Started |
| **TOTAL** | **110** | **0** | **0%** |

---

# 🔒 1. SECURITY CHECKLIST

## 1.1 Backend Security (Drupal)

### Critical Security Items

- [ ] **SEC-01**: Update Drupal core to latest stable version
- [ ] **SEC-02**: Run `composer audit` and fix all HIGH/CRITICAL vulnerabilities
- [ ] **SEC-03**: Remove development modules (Devel, Admin Toolbar Debug)
- [ ] **SEC-04**: Disable directory listing in .htaccess
- [ ] **SEC-05**: Remove/rename default Drupal files (CHANGELOG.txt, README.txt, INSTALL.txt)
- [ ] **SEC-06**: Configure `settings.php` for production:

  ```php
  $settings['trusted_host_patterns'] = ['^billoria\.ad$', '^www\.billoria\.ad$'];
  $config['system.logging']['error_level'] = 'hide';
  $settings['skip_permissions_hardening'] = FALSE;
  ```

- [ ] **SEC-07**: Set proper file permissions (755 dirs, 644 files, 444 settings.php)
- [ ] **SEC-08**: Enable `$settings['hash_salt']` with cryptographically secure random string
- [ ] **SEC-09**: Database credentials stored in environment variables (not settings.php)
- [ ] **SEC-10**: Enable HTTPS-only cookies in `services.yml`

### Authentication & Authorization

- [ ] **SEC-11**: Rate limiting configured (billoria_core EventSubscriber)
- [ ] **SEC-12**: CSRF protection enabled for all mutations
- [ ] **SEC-13**: Session timeout configured (30 minutes inactive)
- [ ] **SEC-14**: Password policy enforced (min 8 chars, complexity)
- [ ] **SEC-15**: Failed login attempts limited (5 attempts, 15-min lockout)

### API Security

- [ ] **SEC-16**: CORS configured with specific origins (no `*` wildcard)
- [ ] **SEC-17**: API rate limiting per IP/user
- [ ] **SEC-18**: Input validation on all API endpoints
- [ ] **SEC-19**: SQL injection prevention (use Drupal query builder)
- [ ] **SEC-20**: XSS prevention (sanitized output, no `Markup::create()` with user input)

## 1.2 Frontend Security (Next.js)

### Critical Security Items

- [ ] **SEC-21**: Run `pnpm audit` and fix vulnerabilities
- [ ] **SEC-22**: Move CSRF tokens from localStorage to httpOnly cookies
- [ ] **SEC-23**: Remove sensitive data from localStorage/sessionStorage
- [ ] **SEC-24**: Implement Content Security Policy (CSP) headers

  ```typescript
  // next.config.ts
  headers: async () => [{
    source: '/(.*)',
    headers: [
      { key: 'X-Content-Type-Options', value: 'nosniff' },
      { key: 'X-Frame-Options', value: 'DENY' },
      { key: 'X-XSS-Protection', value: '1; mode=block' },
      { key: 'Referrer-Policy', value: 'strict-origin-when-cross-origin' }
    ]
  }]
  ```

- [ ] **SEC-25**: Input sanitization with DOMPurify for user-generated content
- [ ] **SEC-26**: Environment variables properly scoped (`NEXT_PUBLIC_*` only for client)
- [ ] **SEC-27**: No API keys or secrets in client-side code
- [ ] **SEC-28**: Enable Strict Mode in React
- [ ] **SEC-29**: Implement rate limiting on form submissions
- [ ] **SEC-30**: File upload validation (type, size, malware scan)
- [ ] **SEC-31**: Auto-logout on token expiration
- [ ] **SEC-32**: Secure cookie settings (Secure, SameSite=Strict)

---

# ⚡ 2. PERFORMANCE CHECKLIST

## 2.1 Backend Performance (Drupal)

### Database Optimization

- [ ] **PERF-01**: Run `EXPLAIN` on slow queries (use Devel Query Log)
- [ ] **PERF-02**: Add composite indexes on frequently joined columns
- [ ] **PERF-03**: Enable MySQL query cache
- [ ] **PERF-04**: Optimize database tables (`OPTIMIZE TABLE`)
- [ ] **PERF-05**: Configure connection pooling

### Caching Strategy

- [ ] **PERF-06**: Install and configure Redis/Memcached
- [ ] **PERF-07**: Enable Dynamic Page Cache module
- [ ] **PERF-08**: Enable Internal Page Cache module
- [ ] **PERF-09**: Configure cache max-age for views/APIs
- [ ] **PERF-10**: Clear all caches before launch (`drush cr`)
- [ ] **PERF-11**: Configure Varnish reverse proxy (optional)

### PHP Optimization

- [ ] **PERF-12**: Enable OPcache with optimized settings

  ```ini
  opcache.memory_consumption=256
  opcache.max_accelerated_files=20000
  opcache.validate_timestamps=0
  ```

- [ ] **PERF-13**: PHP-FPM tuned (pm.max_children, pm.start_servers)
- [ ] **PERF-14**: Disable Xdebug in production

### API Performance

- [ ] **PERF-15**: API response time < 200ms (p95)
- [ ] **PERF-16**: Implement API response caching
- [ ] **PERF-17**: Use field filters to reduce payload size
- [ ] **PERF-18**: Pagination enabled for large datasets

## 2.2 Frontend Performance (Next.js)

### Build Optimization

- [ ] **PERF-19**: Production build completed (`pnpm build`)
- [ ] **PERF-20**: Bundle analysis reviewed (`@next/bundle-analyzer`)
- [ ] **PERF-21**: JavaScript bundle < 200KB gzipped (main chunk)
- [ ] **PERF-22**: CSS bundle < 50KB gzipped
- [ ] **PERF-23**: Tree shaking verified (no unused imports)
- [ ] **PERF-24**: Code splitting implemented (dynamic imports)

### Image & Asset Optimization

- [ ] **PERF-25**: All images use `next/image` component
- [ ] **PERF-26**: Image formats: WebP/AVIF with JPEG fallback
- [ ] **PERF-27**: Responsive images with proper sizes
- [ ] **PERF-28**: Lazy loading enabled for below-fold images
- [ ] **PERF-29**: Fonts optimized with `next/font`
- [ ] **PERF-30**: Font preloading configured
- [ ] **PERF-31**: Critical CSS inlined

### Runtime Performance

- [ ] **PERF-32**: React DevTools Profiler shows no performance issues
- [ ] **PERF-33**: Memoization applied (useMemo, useCallback) for heavy computations
- [ ] **PERF-34**: Virtual scrolling for long lists (react-window)
- [ ] **PERF-35**: Debouncing/throttling on search inputs
- [ ] **PERF-36**: Service Worker configured (optional PWA)

### Core Web Vitals Targets

- [ ] **PERF-37**: Largest Contentful Paint (LCP) < 2.5s
- [ ] **PERF-38**: First Input Delay (FID) < 100ms
- [ ] **PERF-39**: Cumulative Layout Shift (CLS) < 0.1
- [ ] **PERF-40**: Lighthouse Performance Score > 90

---

# 🧪 3. TESTING CHECKLIST

## 3.1 Backend Testing

### Unit & Integration Tests

- [ ] **TEST-01**: PHPUnit configured
- [ ] **TEST-02**: Service layer unit tests (70% coverage)
- [ ] **TEST-03**: API endpoint integration tests
- [ ] **TEST-04**: Database query tests
- [ ] **TEST-05**: Permission/access control tests
- [ ] **TEST-06**: Validation logic tests

### API Testing

- [ ] **TEST-07**: Postman/Newman collection created
- [ ] **TEST-08**: All API endpoints tested (200+ status codes)
- [ ] **TEST-09**: Error responses tested (400, 401, 403, 404, 500)
- [ ] **TEST-10**: Request validation tested (invalid payloads)
- [ ] **TEST-11**: Rate limiting tested (429 responses)

### Load Testing

- [ ] **TEST-12**: Load test with 100 concurrent users (JMeter/Locust)
- [ ] **TEST-13**: Sustained load: 1000 requests/minute for 10 minutes
- [ ] **TEST-14**: Response time under load < 500ms
- [ ] **TEST-15**: No memory leaks detected
- [ ] **TEST-16**: Database connection pool stable

## 3.2 Frontend Testing

### Unit & Component Tests

- [ ] **TEST-17**: Vitest configured
- [ ] **TEST-18**: Component tests with React Testing Library (60% coverage)
- [ ] **TEST-19**: Utility function tests
- [ ] **TEST-20**: API client module tests
- [ ] **TEST-21**: Form validation tests
- [ ] **TEST-22**: Authentication flow tests

### End-to-End Testing

- [ ] **TEST-23**: Playwright/Cypress configured
- [ ] **TEST-24**: Critical user flows tested:
  - [ ] Registration → Email verification → Login
  - [ ] Billboard search → View details → Booking request
  - [ ] Profile creation/editing
  - [ ] Password reset flow
  - [ ] Dashboard navigation
- [ ] **TEST-25**: Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] **TEST-26**: Mobile responsive testing (iOS Safari, Android Chrome)
- [ ] **TEST-27**: Network throttling tests (slow 3G, 4G)

### Accessibility Testing

- [ ] **TEST-28**: Axe-core accessibility tests
- [ ] **TEST-29**: Keyboard navigation works
- [ ] **TEST-30**: Screen reader compatible (NVDA/JAWS)
- [ ] **TEST-31**: WCAG 2.1 AA compliance validated
- [ ] **TEST-32**: Color contrast ratios > 4.5:1

---

# 🚀 4. INFRASTRUCTURE & DEPLOYMENT

## 4.1 Hosting Environment

### Backend Infrastructure

- [ ] **INFRA-01**: Production server provisioned (VPS/Cloud)
- [ ] **INFRA-02**: Server requirements met:
  - [ ] PHP 8.4+ with required extensions
  - [ ] MariaDB 11.8+ / MySQL 8.0+
  - [ ] Redis/Memcached for caching
  - [ ] Nginx/Apache web server
  - [ ] SSL certificate installed
- [ ] **INFRA-03**: Database server configured (separate instance recommended)
- [ ] **INFRA-04**: File storage configured (S3/DigitalOcean Spaces)
- [ ] **INFRA-05**: Email service integrated (SendGrid/Mailgun/AWS SES)
- [ ] **INFRA-06**: SMS gateway integrated (for OTP)

### Frontend Infrastructure

- [ ] **INFRA-07**: Deployment platform configured (Vercel/Netlify/Docker)
- [ ] **INFRA-08**: Environment variables configured:

  ```env
  NEXT_PUBLIC_API_URL=https://api.billoria.ad
  NEXT_PUBLIC_SITE_URL=https://billoria.ad
  ```

- [ ] **INFRA-09**: CDN configured (Cloudflare/AWS CloudFront)
- [ ] **INFRA-10**: Edge caching enabled
- [ ] **INFRA-11**: Asset compression (Brotli/Gzip) enabled

### Domain & DNS

- [ ] **INFRA-12**: Domain purchased (billoria.ad)
- [ ] **INFRA-13**: DNS records configured:
  - [ ] A record: billoria.ad → Frontend IP
  - [ ] A record: api.billoria.ad → Backend IP
  - [ ] CNAME: <www.billoria.ad> → billoria.ad
  - [ ] MX records for email (if applicable)
- [ ] **INFRA-14**: SSL certificates installed (Let's Encrypt/Commercial)
- [ ] **INFRA-15**: HTTPS redirect configured
- [ ] **INFRA-16**: HSTS headers enabled

### Security Infrastructure

- [ ] **INFRA-17**: Firewall configured (UFW/iptables)
- [ ] **INFRA-18**: DDoS protection enabled (Cloudflare)
- [ ] **INFRA-19**: Web Application Firewall (WAF) configured
- [ ] **INFRA-20**: SSH key-based authentication only (no password)
- [ ] **INFRA-21**: Fail2ban installed and configured
- [ ] **INFRA-22**: Regular security updates automated

## 4.2 CI/CD Pipeline

- [ ] **INFRA-23**: GitHub Actions workflows configured
- [ ] **INFRA-24**: Automated tests run on pull requests
- [ ] **INFRA-25**: Automated deployment to staging
- [ ] **INFRA-26**: Production deployment with manual approval
- [ ] **INFRA-27**: Rollback strategy documented and tested
- [ ] **INFRA-28**: Database migration automation (Drush)
- [ ] **INFRA-29**: Config sync automation

## 4.3 Backup & Recovery

- [ ] **INFRA-30**: Automated daily database backups
- [ ] **INFRA-31**: Automated weekly file backups
- [ ] **INFRA-32**: Backups encrypted and stored off-site
- [ ] **INFRA-33**: Backup retention policy: 30 days daily, 12 months monthly
- [ ] **INFRA-34**: Backup restoration tested successfully
- [ ] **INFRA-35**: Disaster recovery plan documented
- [ ] **INFRA-36**: RTO (Recovery Time Objective) < 4 hours
- [ ] **INFRA-37**: RPO (Recovery Point Objective) < 24 hours

---

# 📊 5. MONITORING & OBSERVABILITY

## 5.1 Application Monitoring

### Backend Monitoring

- [ ] **MON-01**: Application Performance Monitoring (APM) configured
- [ ] **MON-02**: Response time tracking per endpoint
- [ ] **MON-03**: Error rate monitoring
- [ ] **MON-04**: Database query performance monitoring
- [ ] **MON-05**: Cache hit/miss ratio tracking
- [ ] **MON-06**: Queue job monitoring (if using queues)

### Frontend Monitoring

- [ ] **MON-07**: Real User Monitoring (RUM) configured
- [ ] **MON-08**: Core Web Vitals tracking (Vercel Analytics/Google Analytics)
- [ ] **MON-09**: JavaScript error tracking (Sentry/Rollbar)
- [ ] **MON-10**: API call latency tracking
- [ ] **MON-11**: Page load time by route

## 5.2 Infrastructure Monitoring

- [ ] **MON-12**: Server monitoring (Datadog/New Relic/Prometheus)
- [ ] **MON-13**: CPU usage alerts (> 80% for 5 minutes)
- [ ] **MON-14**: Memory usage alerts (> 85%)
- [ ] **MON-15**: Disk space alerts (> 80%)
- [ ] **MON-16**: Database connection pool monitoring
- [ ] **MON-17**: SSL certificate expiry monitoring (30 days warning)

## 5.3 Uptime & Availability

- [ ] **MON-18**: Uptime monitoring (UptimeRobot/Pingdom)
- [ ] **MON-19**: Status page configured (status.billoria.ad)
- [ ] **MON-20**: Health check endpoints configured
- [ ] **MON-21**: Alerting configured (email/Slack/PagerDuty)
- [ ] **MON-22**: On-call rotation scheduled

## 5.4 Log Management

- [ ] **MON-23**: Centralized logging (ELK Stack/Loki/CloudWatch)
- [ ] **MON-24**: Error logs aggregated
- [ ] **MON-25**: Access logs retained (90 days minimum)
- [ ] **MON-26**: Log rotation configured
- [ ] **MON-27**: Sensitive data masked in logs

---

# 📚 6. DOCUMENTATION

## 6.1 Technical Documentation

- [ ] **DOC-01**: API documentation complete (Swagger/OpenAPI spec)
- [ ] **DOC-02**: Architecture diagrams updated
- [ ] **DOC-03**: Database schema documented
- [ ] **DOC-04**: Deployment runbook created
- [ ] **DOC-05**: Troubleshooting guide prepared
- [ ] **DOC-06**: Developer onboarding guide updated
- [ ] **DOC-07**: Environment setup instructions

## 6.2 User Documentation

- [ ] **DOC-08**: Admin user manual
- [ ] **DOC-09**: FAQ section created
- [ ] **DOC-10**: Help/Support page with contact info

## 6.3 Legal Documentation

- [ ] **DOC-11**: Terms of Service published
- [ ] **DOC-12**: Privacy Policy published
- [ ] **DOC-13**: Cookie Policy published
- [ ] **DOC-14**: Contact page with legal entity information
- [ ] **DOC-15**: GDPR compliance documentation (if applicable)

---

# ✅ 7. PRE-LAUNCH VALIDATION

## 7.1 Functional Validation

- [ ] **VALID-01**: All critical user flows tested in production-like environment
- [ ] **VALID-02**: Role-based access control verified for all roles
- [ ] **VALID-03**: Email notifications working (registration, verification, booking)
- [ ] **VALID-04**: SMS OTP delivery confirmed
- [ ] **VALID-05**: Search functionality returns accurate results
- [ ] **VALID-06**: Map rendering correctly with all billboards
- [ ] **VALID-07**: Image uploads working (all formats, size limits)
- [ ] **VALID-08**: Form validations working client-side and server-side
- [ ] **VALID-09**: Payment gateway tested (sandbox mode initially)
- [ ] **VALID-10**: Multi-language switching works (English ↔ Bengali)

## 7.2 Content Validation

- [ ] **VALID-11**: All taxonomy terms imported (divisions, districts, upazilas)
- [ ] **VALID-12**: Sample billboard listings created (20+ for demo)
- [ ] **VALID-13**: No broken internal links
- [ ] **VALID-14**: No 404 errors on critical pages
- [ ] **VALID-15**: Favicons and app icons uploaded
- [ ] **VALID-16**: SEO meta tags complete on all pages
- [ ] **VALID-17**: Sitemap.xml generated and submitted to Google
- [ ] **VALID-18**: robots.txt configured correctly

## 7.3 Security Validation

- [ ] **VALID-19**: Security scan completed (no high/critical issues)
- [ ] **VALID-20**: Penetration test performed (external/third-party)
- [ ] **VALID-21**: OWASP Top 10 vulnerabilities checked
- [ ] **VALID-22**: SQL injection tests passed
- [ ] **VALID-23**: XSS vulnerability tests passed
- [ ] **VALID-24**: CSRF protection verified
- [ ] **VALID-25**: Authentication bypass attempts failed

## 7.4 Performance Validation

- [ ] **VALID-26**: Load test passed (100 concurrent users, no errors)
- [ ] **VALID-27**: Stress test completed (identified breaking point)
- [ ] **VALID-28**: Mobile performance acceptable (LCP < 4s on 4G)
- [ ] **VALID-29**: No memory leaks in 24-hour soak test
- [ ] **VALID-30**: Cache warming strategy verified

---

# 🚢 8. GO-LIVE PREPARATION

## 8.1 Pre-Launch Checklist (24 Hours Before)

- [ ] **LAUNCH-01**: Staging environment final smoke test
- [ ] **LAUNCH-02**: Production database backup verified
- [ ] **LAUNCH-03**: DNS changes prepared (but not applied)
- [ ] **LAUNCH-04**: SSL certificates valid (>30 days)
- [ ] **LAUNCH-05**: Monitoring dashboards configured
- [ ] **LAUNCH-06**: Alert channels tested (get test alert)
- [ ] **LAUNCH-07**: Emergency contact list created
- [ ] **LAUNCH-08**: Rollback plan rehearsed
- [ ] **LAUNCH-09**: Communication plan ready (team/stakeholders)
- [ ] **LAUNCH-10**: Support team briefed

## 8.2 Launch Day Checklist

- [ ] **LAUNCH-11**: Maintenance mode enabled on old site (if applicable)
- [ ] **LAUNCH-12**: Final production deployment executed
- [ ] **LAUNCH-13**: Database migrated to production
- [ ] **LAUNCH-14**: DNS records updated
- [ ] **LAUNCH-15**: SSL verification (<https://billoria.ad> loads)
- [ ] **LAUNCH-16**: CDN cache warmed/purged
- [ ] **LAUNCH-17**: Smoke tests completed:
  - [ ] Homepage loads
  - [ ] Login works
  - [ ] Registration works
  - [ ] Search returns results
  - [ ] Map displays billboard
  - [ ] API endpoints respond
- [ ] **LAUNCH-18**: Monitoring shows green (no errors)
- [ ] **LAUNCH-19**: Team on standby for 4 hours
- [ ] **LAUNCH-20**: Status page updated ("Operational")

## 8.3 Post-Launch (First 48 Hours)

- [ ] **LAUNCH-21**: Monitor error rates every 2 hours
- [ ] **LAUNCH-22**: Check performance metrics (response times, Core Web Vitals)
- [ ] **LAUNCH-23**: Review application logs for errors
- [ ] **LAUNCH-24**: Verify backup ran successfully
- [ ] **LAUNCH-25**: User feedback collection started
- [ ] **LAUNCH-26**: Support ticket queue monitored
- [ ] **LAUNCH-27**: Analytics verify traffic patterns
- [ ] **LAUNCH-28**: Hot-fix deployment ready (if needed)

---

# 🎯 CRITICAL PATH ITEMS (Must Complete)

These items MUST be completed before production launch:

### 🔴 Highest Priority (P0 - Blockers)

1. ✅ **SEC-06**: Production `settings.php` configured
2. ✅ **SEC-22**: CSRF tokens moved to httpOnly cookies
3. ✅ **PERF-19**: Production build successful
4. ✅ **INFRA-14**: SSL certificates installed
5. ✅ **VALID-01**: Critical user flows tested
6. ✅ **VALID-19**: Security scan passed

### 🟠 High Priority (P1 - Launch Risks)

7. ✅ **SEC-02**: No critical vulnerabilities
2. ✅ **PERF-37-39**: Core Web Vitals meet targets
3. ✅ **INFRA-30**: Automated backups configured
4. ✅ **MON-01**: Error tracking enabled
5. ✅ **TEST-24**: E2E tests passing
6. ✅ **DOC-11-13**: Legal pages published

---

# 📈 SUCCESS CRITERIA

## Launch Readiness Score

**Target: 95%+ completion across all sections**

- Security: ___/32 items (Target: 100%)
- Performance: ___/40 items (Target: 90%)
- Testing: ___/32 items (Target: 85%)
- Infrastructure: ___/37 items (Target: 95%)
- Monitoring: ___/27 items (Target: 90%)
- Documentation: ___/15 items (Target: 80%)

## Post-Launch KPIs (Week 1)

- [ ] **Uptime**: > 99.5%
- [ ] **Error Rate**: < 0.5%
- [ ] **Average Response Time**: < 250ms (backend)
- [ ] **Lighthouse Score**: > 85 (frontend)
- [ ] **Zero** critical security incidents
- [ ] **Zero** data loss incidents

---

**Checklist Owner:** ___________________  
**Target Launch Date:** ___________________  
**Sign-off Required:** CTO, Security Lead, DevOps Lead  

**Last Review:** April 1, 2026  
**Next Review:** Weekly until launch, then monthly

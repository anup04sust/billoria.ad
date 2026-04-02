# OTP Verification API Integration Guide

## ✅ Completed

### Backend (Drupal 11)

1. **Database Table** ✓
   - `billoria_user_verifications` table created
   - Generic design supports email, phone, SMS, 2FA, etc.
   - Secure SHA-256 hashing with constant-time comparison
   - Indexed for performance

2. **Service Layer** ✓
   - `UserVerificationService` with 10+ methods
   - Rate limiting, attempt tracking, expiry management
   - OTP generation, verification, cleanup
   - Statistics and status tracking

3. **REST API Endpoints** ✓
   - `POST /api/v1/verification/email/send-otp` - Send email OTP
   - `POST /api/v1/verification/email/verify-otp` - Verify email OTP
   - `POST /api/v1/verification/phone/send-otp` - Send phone SMS OTP
   - `POST /api/v1/verification/phone/verify-otp` - Verify phone OTP
   - `GET /api/v1/verification/status` - Get verification status

4. **Security Features** ✓
   - Cookie-based authentication
   - CSRF protection (via Drupal)
   - Rate limiting (60s cooldown)
   - Max attempts (5 for email, 3 for phone)
   - Code expiry (10min email, 5min phone)
   - Hashed code storage

### Frontend (Next.js 16)

1. **TypeScript Types** ✓
   - `/types/verification.ts` - Complete type definitions
   - Response types, error types, status types

2. **API Client** ✓
   - `/lib/api/verification.ts` - Full API client
   - Type-safe methods with error handling
   - Custom error classes: `RateLimitError`, `VerificationError`

3. **React Hook** ✓
   - `/lib/hooks/useOtpVerification.ts` - Custom hook
   - State management, countdown timer, error handling
   - Easy integration: `const { sendCode, verifyCode, sending, verifying, error, retryAfter } = useOtpVerification('email')`

4. **UI Integration** ✓
   - Agency profile page updated with real API calls
   - "Verify Email" button with rate limit countdown
   - Modal with 6-digit input and validation
   - Error messages with attempts remaining
   - Success handling with profile update

## 🧪 Testing

### Backend Tests

```bash
cd cmsapi
ddev drush scr scripts/test-verification-service.php
```

**Test Results:**
- ✓ OTP generation (6-digit code)
- ✓ Verification record creation
- ✓ Database queries
- ✓ Wrong code detection (attempts remaining)
- ✓ Correct code verification
- ✓ Rate limiting (60s cooldown)
- ✓ Statistics tracking
- ✓ Status management (pending, verified, cancelled)

### Frontend Integration

1. Start Next.js dev server:
```bash
cd frontendapp
ddev start
```

2. Navigate to: `http://billoria-ad.ddev.site:3000/agency/profile`

3. Test flow:
   - Click "Verify Email" button
   - Modal opens with 6-digit input
   - Check email for OTP code
   - Enter code and click "Verify Code"
   - Success message displays
   - Email badge updates to "Email Verified"
   - Trust score increases by +10

### API Testing with cURL

```bash
# 1. Login first to get session cookie
curl -X POST http://billoria-ad-api.ddev.site/api/v1/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"admin","password":"admin"}' \
  -c cookies.txt

# 2. Send email OTP
curl -X POST http://billoria-ad-api.ddev.site/api/v1/verification/email/send-otp \
  -H 'Content-Type: application/json' \
  -b cookies.txt

# 3. Check logs for OTP code
ddev logs | grep "Email OTP sent"

# 4. Verify with code
curl -X POST http://billoria-ad-api.ddev.site/api/v1/verification/email/verify-otp \
  -H 'Content-Type: application/json' \
  -b cookies.txt \
  -d '{"code":"123456"}'

# 5. Check status
curl -X GET http://billoria-ad-api.ddev.site/api/v1/verification/status \
  -b cookies.txt
```

## 📋 Usage Examples

### React Component

```tsx
import { useOtpVerification } from '@/lib/hooks/useOtpVerification';

function EmailVerification() {
  const [modalOpen, setModalOpen] = useState(false);
  const [code, setCode] = useState('');

  const {
    sendCode,
    verifyCode,
    sending,
    verifying,
    error,
    retryAfter,
    clearError,
  } = useOtpVerification('email', {
    onSendSuccess: () => setModalOpen(true),
    onVerifySuccess: (data) => {
      console.log('Trust score:', data.data?.trustScore);
      setModalOpen(false);
    },
  });

  return (
    <>
      <button
        onClick={sendCode}
        disabled={sending || retryAfter > 0}
      >
        {sending ? 'Sending...' : retryAfter > 0 ? `Wait ${retryAfter}s` : 'Verify Email'}
      </button>

      {modalOpen && (
        <Modal onClose={() => setModalOpen(false)}>
          <input
            value={code}
            onChange={(e) => setCode(e.target.value.replace(/\D/g, '').slice(0, 6))}
            placeholder="Enter 6-digit code"
          />
          {error && <p className="error">{error}</p>}
          <button
            onClick={() => verifyCode(code)}
            disabled={verifying || code.length !== 6}
          >
            {verifying ? 'Verifying...' : 'Verify'}
          </button>
        </Modal>
      )}
    </>
  );
}
```

### Direct API Usage

```typescript
import { sendEmailOtp, verifyEmailOtp, RateLimitError, VerificationError } from '@/lib/api/verification';

async function verifyMyEmail() {
  try {
    // Send code
    const sendResult = await sendEmailOtp();
    console.log('Code sent to:', sendResult.data?.email);
    console.log('Expires in:', sendResult.data?.expiresIn, 'seconds');

    // Get OTP from user...
    const userCode = prompt('Enter 6-digit code:');

    // Verify code
    const verifyResult = await verifyEmailOtp(userCode!);
    console.log('Email verified!');
    console.log('Trust score:', verifyResult.data?.trustScore);

  } catch (err) {
    if (err instanceof RateLimitError) {
      console.error(`Rate limited. Wait ${err.retryAfter} seconds.`);
    } else if (err instanceof VerificationError) {
      console.error(err.getUserMessage());
      if (err.attemptsRemaining !== undefined) {
        console.log(`${err.attemptsRemaining} attempts remaining`);
      }
    } else {
      console.error('Unknown error:', err);
    }
  }
}
```

## 🔐 Security Notes

1. **Never log OTP codes in production** - Currently logged for development
2. **Enable HTTPS in production** - Cookies must be secure
3. **Configure email service** - Currently using PHP mail() placeholder
4. **Integrate SMS gateway** - For phone verification (BD-SMS, SSL Wireless, etc.)
5. **Monitor rate limiting** - Adjust cooldown as needed
6. **Set up cron job** - Clean up old records (see VERIFICATION_SYSTEM.md)

## 📚 Documentation

- **API Reference**: `/application-wiki/VERIFICATION_OTP_API.md`
- **Backend Service**: `/cmsapi/web/modules/custom/billoria_accounts/VERIFICATION_SYSTEM.md`
- **TypeScript Types**: `/frontendapp/types/verification.ts`
- **API Client**: `/frontendapp/lib/api/verification.ts`
- **React Hook**: `/frontendapp/lib/hooks/useOtpVerification.ts`

## 🚀 Next Steps

1. **Email Service Integration**
   - Replace PHP mail() with SendGrid, Mailgun, or AWS SES
   - Design HTML email template for OTP codes
   - Add email delivery tracking

2. **SMS Gateway Integration**
   - Integrate BD-SMS or SSL Wireless for Bangladesh
   - Implement phone number validation
   - Add SMS delivery status tracking

3. **UI Enhancements**
   - Add auto-focus and auto-submit for 6-digit input
   - Show expiry countdown timer in modal
   - Add "paste" functionality for OTP codes
   - Improve mobile keyboard (numeric input)

4. **Production Readiness**
   - Set up cron job for cleanup (see hook_cron in VERIFICATION_SYSTEM.md)
   - Configure rate limiting thresholds
   - Add monitoring/alerting for failed attempts
   - Implement IP-based rate limiting (optional)

## ✅ Integration Checklist

- [x] Database table created and tested
- [x] Service layer implemented with security features
- [x] REST API endpoints with authentication
- [x] TypeScript types defined
- [x] API client with error handling
- [x] React hook for state management
- [x] UI integrated in profile page
- [x] Rate limiting working (60s cooldown)
- [x] Attempt tracking (5 max for email)
- [x] Code expiry (10 minutes)
- [x] Trust score updates
- [ ] Email service integration (production)
- [ ] SMS gateway integration (production)
- [ ] Cron cleanup job (production)
- [ ] Load testing (production)

## 🐛 Known Issues

None - All tests passing ✓

## 💡 Tips

- Use `retryAfter` countdown to improve UX
- Display `attemptsRemaining` to users
- Clear modal state on close
- Auto-focus code input when modal opens
- Validate code format before API call (6 digits)
- Show masked email/phone for privacy

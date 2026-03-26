# Billoria Frontend - Onboarding Integration Guide

## Overview

This guide shows how to integrate the Drupal REST APIs for user registration and onboarding into the Next.js frontend.

## What's Been Created (Backend)

### Drupal CMS API Endpoints

1. **Organization Content Type** (35 fields)
   - Common fields: name, type, contacts, address, verification
   - Brand-specific: industry, budget, campaign preferences
   - Agency-specific: services, portfolio, client management
   - Owner-specific: inventory, coverage, maintenance

2. **User Entity Extensions** (13 fields)
   - Mobile number, verification flags, organization references
   - Token management for email/phone verification

3. **REST API Endpoints** (billoria_onboarding module)
   - `POST /api/register` - Create user + organization
   - `POST /api/verify-email` - Verify email with token
   - `POST /api/request-phone-otp` - Request phone OTP
   - `POST /api/verify-phone` - Verify phone with OTP
   - `POST /api/resend-verification` - Resend verification email
   - `GET /api/user/profile` - Get user profile + organization data
   - `GET /api/organization/{nid}/status` - Get organization stats

4. **CORS Already Configured**
   - Allows: `http://localhost:3000`, `http://billoria-ad.ddev.site`

---

## Frontend Implementation Tasks

### Phase 1: Setup API Client (30 mins)

Create `lib/api/client.ts`:

```typescript
import axios from 'axios';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://billoria.ad.ddev.site';

export const apiClient = axios.create({
  baseURL: API_BASE,
  withCredentials: true, // Important for cookie-based auth
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add request interceptor for auth token (if using JWT later)
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('authToken');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Add response interceptor for error handling
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Redirect to login
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

Create `lib/api/auth.ts`:

```typescript
import { apiClient } from './client';

export interface RegisterData {
  accountType: 'brand' | 'agency' | 'owner';
  user: {
    name: string;
    email: string;
    password: string;
    mobileNumber: string;
  };
  organization: {
    name: string;
    officialEmail: string;
    officialPhone: string;
    website?: string;
    division: number;
    district: number;
    cityCorporation?: number;
    fullAddress: string;
    postalCode?: string;
    businessRegNumber?: string;
    tin?: string;
    establishmentYear?: number;
    [key: string]: any; // Type-specific fields
  };
}

export const authAPI = {
  async register(data: RegisterData) {
    const response = await apiClient.post('/api/register', data);
    return response.data;
  },

  async verifyEmail(uid: number, token: string) {
    const response = await apiClient.post('/api/verify-email', { uid, token });
    return response.data;
  },

  async requestPhoneOtp() {
    const response = await apiClient.post('/api/request-phone-otp');
    return response.data;
  },

  async verifyPhone(otp: string) {
    const response = await apiClient.post('/api/verify-phone', { otp });
    return response.data;
  },

  async resendVerification() {
    const response = await apiClient.post('/api/resend-verification');
    return response.data;
  },

  async getUserProfile() {
    const response = await apiClient.get('/api/user/profile');
    return response.data;
  },

  async getOrganizationStatus(nid: number) {
    const response = await apiClient.get(`/api/organization/${nid}/status`);
    return response.data;
  },
};
```

---

### Phase 2: Create Registration Pages (2-3 hours)

#### Account Type Selection Page

`app/register/page.tsx`:

```tsx
'use client';

import Link from 'next/link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

export default function RegisterPage() {
  const accountTypes = [
    {
      type: 'brand',
      title: 'Brand / Advertiser',
      description: 'Looking to promote your brand on billboards across Bangladesh',
      features: [
        'Search & browse billboard inventory',
        'Get instant quotes',
        'Manage campaigns',
        'Track performance',
      ],
      color: 'border-red-500',
    },
    {
      type: 'agency',
      title: 'Advertising Agency',
      description: 'Managing OOH campaigns for multiple client brands',
      features: [
        'Multi-client campaign management',
        'Bulk booking capabilities',
        'Client reporting tools',
        'Own or partner inventory',
      ],
      color: 'border-blue-500',
    },
    {
      type: 'owner',
      title: 'Billboard Owner',
      description: 'List and monetize your billboard inventory',
      features: [
        'List your billboards',
        'Receive booking inquiries',
        'Manage availability',
        'Track revenue',
      ],
      color: 'border-green-500',
    },
  ];

  return (
    <div className="container mx-auto py-12">
      <div className="text-center mb-12">
        <h1 className="text-4xl font-bold mb-4">Welcome to Billoria</h1>
        <p className="text-xl text-gray-600">
          Bangladesh's premier billboard advertising marketplace. Choose your account type:
        </p>
      </div>

      <div className="grid md:grid-cols-3 gap-6">
        {accountTypes.map((account) => (
          <Card key={account.type} className={`border-t-4 ${account.color} hover:shadow-lg transition-shadow`}>
            <CardHeader>
              <CardTitle>{account.title}</CardTitle>
              <CardDescription>{account.description}</CardDescription>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 mb-6">
                {account.features.map((feature, idx) => (
                  <li key={idx} className="flex items-start">
                    <span className="text-green-500 mr-2">✓</span>
                    <span>{feature}</span>
                  </li>
                ))}
              </ul>
              <Link
                href={`/register/${account.type}`}
                className="block w-full text-center bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition-colors"
              >
                Register as {account.title.split(' ')[0]}
              </Link>
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="text-center mt-8">
        <p className="text-gray-600">
          Already have an account?{' '}
          <Link href="/login" className="text-blue-600 hover:underline">
            Log in
          </Link>
        </p>
      </div>
    </div>
  );
}
```

#### Brand Registration Page

`app/register/brand/page.tsx`:

```tsx
'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { authAPI } from '@/lib/api/auth';

export default function BrandRegistration() {
  const router = useRouter();
  const [step, setStep] = useState(1);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  
  const [formData, setFormData] = useState({
    accountType: 'brand' as const,
    user: {
      name: '',
      email: '',
      password: '',
      mobileNumber: '',
    },
    organization: {
      name: '',
      officialEmail: '',
      officialPhone: '',
      website: '',
      division: 0,
      district: 0,
      fullAddress: '',
      parentCompany: '',
      annualBudgetRange: '',
      bookingDuration: '',
      preferredRegions: [] as number[],
    },
  });

  const [userId, setUserId] = useState<number>();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      if (step === 1) {
        // Submit registration
        const result = await authAPI.register(formData);
        console.log('Registration result:', result);
        
        setUserId(result.data.userId);
        setStep(2); // Move to email verification
        
      } else if (step === 2) {
        // Handle email verification (user enters code manually)
        router.push('/dashboard');
      }
    } catch (err: any) {
      setError(err.response?.data?.error || 'Registration failed');
      console.error('Registration error:', err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container mx-auto max-w-2xl py-12">
      {/* Progress Indicator */}
      <div className="flex justify-between mb-8">
        {['Account', 'Email Verify', 'Organization', 'Details'].map((label, idx) => (
          <div
            key={idx}
            className={`flex-1 text-center ${
              idx + 1 === step ? 'text-blue-600' : idx + 1 < step ? 'text-green-600' : 'text-gray-400'
            }`}
          >
            <div className={`w-10 h-10 mx-auto rounded-full flex items-center justify-center mb-2 ${
              idx + 1 === step ? 'bg-blue-600 text-white' : idx + 1 < step ? 'bg-green-600 text-white' : 'bg-gray-300'
            }`}>
              {idx + 1}
            </div>
            <div className="text-sm">{label}</div>
          </div>
        ))}
      </div>

      <form onSubmit={handleSubmit} className="space-y-6 bg-white p-8 rounded-lg shadow">
        {step === 1 && (
          <>
            <h2 className="text-2xl font-bold mb-4">Create Your Brand Account</h2>
            
            <div>
              <label className="block text-sm font-medium mb-2">Full Name</label>
              <input
                type="text"
                required
                className="w-full px-4 py-2 border rounded-md"
                value={formData.user.name}
                onChange={(e) => setFormData({
                  ...formData,
                  user: { ...formData.user, name: e.target.value }
                })}
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-2">Email Address</label>
              <input
                type="email"
                required
                className="w-full px-4 py-2 border rounded-md"
                value={formData.user.email}
                onChange={(e) => setFormData({
                  ...formData,
                  user: { ...formData.user, email: e.target.value }
                })}
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-2">Mobile Number</label>
              <input
                type="tel"
                required
                placeholder="+8801712345678"
                className="w-full px-4 py-2 border rounded-md"
                value={formData.user.mobileNumber}
                onChange={(e) => setFormData({
                  ...formData,
                  user: { ...formData.user, mobileNumber: e.target.value }
                })}
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-2">Password</label>
              <input
                type="password"
                required
                className="w-full px-4 py-2 border rounded-md"
                value={formData.user.password}
                onChange={(e) => setFormData({
                  ...formData,
                  user: { ...formData.user, password: e.target.value }
                })}
              />
            </div>

            {/* Organization fields */}
            <div className="border-t pt-6">
              <h3 className="text-xl font-semibold mb-4">Organization Details</h3>
              
              <div>
                <label className="block text-sm font-medium mb-2">Brand/Company Name</label>
                <input
                  type="text"
                  required
                  className="w-full px-4 py-2 border rounded-md"
                  value={formData.organization.name}
                  onChange={(e) => setFormData({
                    ...formData,
                    organization: { ...formData.organization, name: e.target.value }
                  })}
                />
              </div>

              {/* Add more organization fields as needed */}
            </div>

            {error && (
              <div className="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                {error}
              </div>
            )}

            <button
              type="submit"
              disabled={loading}
              className="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 disabled:bg-gray-400"
            >
              {loading ? 'Creating Account...' : 'Create Account & Continue'}
            </button>
          </>
        )}

        {step === 2 && (
          <>
            <h2 className="text-2xl font-bold mb-4">Verify Your Email</h2>
            <p className="text-gray-600 mb-4">
              We've sent a verification email to <strong>{formData.user.email}</strong>
            </p>
            <p className="text-gray-600 mb-4">
              Please check your inbox and click the verification link, or enter the code below:
            </p>
            
            <div>
              <label className="block text-sm font-medium mb-2">Verification Code</label>
              <input
                type="text"
                className="w-full px-4 py-2 border rounded-md"
                placeholder="Enter code from email"
              />
            </div>

            <button
              type="button"
              className="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700"
              onClick={() => router.push('/dashboard')}
            >
              Continue to Dashboard
            </button>
          </>
        )}
      </form>
    </div>
  );
}
```

---

### Phase 2: Create Dashboard (1-2 hours)

`app/dashboard/page.tsx`:

```tsx
'use client';

import { useEffect, useState } from 'react';
import { authAPI } from '@/lib/api/auth';

interface UserProfile {
  user: {
    id: number;
    name: string;
    email: string;
    emailVerified: boolean;
    phoneVerified: boolean;
  };
  organization: {
    id: number;
    name: string;
    type: string;
    verificationStatus: string;
    trustScore: number;
    profileCompletion: number;
  };
  verificationStatus: {
    emailVerified: boolean;
    phoneVerified: boolean;
    businessVerified: boolean;
    trustScore: number;
  };
  profileCompletion: number;
}

export default function Dashboard() {
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadProfile();
  }, []);

  const loadProfile = async () => {
    try {
      const data = await authAPI.getUserProfile();
      setProfile(data);
    } catch (error) {
      console.error('Failed to load profile:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>Loading...</div>;
  if (!profile) return <div>No profile found</div>;

  return (
    <div className="container mx-auto py-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold">Welcome, {profile.user.name}</h1>
        <p className="text-gray-600">
          {profile.organization.name} <span className="text-sm">({profile.organization.type})</span>
        </p>
      </div>

      <div className="grid md:grid-cols-3 gap-6">
        {/* Verification Status */}
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold mb-4">Account Status</h3>
          <div className={`inline-block px-4 py-2 rounded-full text-sm font-medium mb-4 ${
            profile.organization.verificationStatus === 'verified' ? 'bg-green-100 text-green-800' :
            profile.organization.verificationStatus === 'email_verified' ? 'bg-blue-100 text-blue-800' :
            'bg-yellow-100 text-yellow-800'
          }`}>
            {profile.organization.verificationStatus.replace('_', ' ').toUpperCase()}
          </div>
          <div className="text-2xl font-bold text-green-600">
            Trust Score: {profile.verificationStatus.trustScore}/100
          </div>
        </div>

        {/* Profile Completion */}
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold mb-4">Profile Completion</h3>
          <div className="mb-4">
            <div className="w-full bg-gray-200 rounded-full h-6">
              <div
                className="bg-gradient-to-r from-blue-500 to-green-500 h-6 rounded-full transition-all"
                style={{ width: `${profile.profileCompletion}%` }}
              />
            </div>
          </div>
          <p className="text-center text-xl font-bold">{profile.profileCompletion}% Complete</p>
          <button className="w-full mt-4 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
            Complete Profile
          </button>
        </div>

        {/* Verification Steps */}
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold mb-4">Verification Steps</h3>
          <ul className="space-y-3">
            <li className={profile.verificationStatus.emailVerified ? 'text-green-600' : 'text-gray-400'}>
              <span className="mr-2">{profile.verificationStatus.emailVerified ? '✓' : '○'}</span>
              Email Verified
            </li>
            <li className={profile.verificationStatus.phoneVerified ? 'text-green-600' : 'text-gray-400'}>
              <span className="mr-2">{profile.verificationStatus.phoneVerified ? '✓' : '○'}</span>
              Phone Verified
              {!profile.verificationStatus.phoneVerified && (
                <button className="ml-2 text-sm text-blue-600 hover:underline">
                  Verify Now
                </button>
              )}
            </li>
            <li className={profile.verificationStatus.businessVerified ? 'text-green-600' : 'text-gray-400'}>
              <span className="mr-2">{profile.verificationStatus.businessVerified ? '✓' : '○'}</span>
              Business Documents
            </li>
          </ul>
        </div>
      </div>

      {/* Quick Actions based on account type */}
      <div className="mt-8 bg-white p-6 rounded-lg shadow">
        <h3 className="text-lg font-semibold mb-4">Quick Actions</h3>
        <div className="flex gap-4">
          {profile.organization.type === 'owner' ? (
            <>
              <button className="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700">
                Add New Billboard
              </button>
              <button className="bg-white border border-gray-300 px-6 py-3 rounded-md hover:bg-gray-50">
                Manage Inventory
              </button>
              <button className="bg-white border border-gray-300 px-6 py-3 rounded-md hover:bg-gray-50">
                Booking Inquiries
              </button>
            </>
          ) : (
            <>
              <button className="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700">
                Browse Billboards
              </button>
              <button className="bg-white border border-gray-300 px-6 py-3 rounded-md hover:bg-gray-50">
                My Bookings
              </button>
              <button className="bg-white border border-gray-300 px-6 py-3 rounded-md hover:bg-gray-50">
                Campaigns
              </button>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
```

---

### Phase 3: Email Verification Flow (1 hour)

`app/verify-email/page.tsx`:

```tsx
'use client';

import { useEffect, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { authAPI } from '@/lib/api/auth';

export default function VerifyEmailPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [status, setStatus] = useState<'verifying' | 'success' | 'error'>('verifying');
  const [message, setMessage] = useState('');

  useEffect(() => {
    const uid = searchParams.get('uid');
    const token = searchParams.get('token');

    if (uid && token) {
      verifyEmail(parseInt(uid), token);
    } else {
      setStatus('error');
      setMessage('Invalid verification link');
    }
  }, [searchParams]);

  const verifyEmail = async (uid: number, token: string) => {
    try {
      const result = await authAPI.verifyEmail(uid, token);
      setStatus('success');
      setMessage(result.message);
      
      // Redirect to dashboard after 2 seconds
      setTimeout(() => {
        router.push('/dashboard');
      }, 2000);
    } catch (error: any) {
      setStatus('error');
      setMessage(error.response?.data?.error || 'Verification failed');
    }
  };

  return (
    <div className="container mx-auto max-w-md py-12">
      <div className={`p-8 rounded-lg text-center ${
        status === 'success' ? 'bg-green-50 border border-green-200' :
        status === 'error' ? 'bg-red-50 border border-red-200' :
        'bg-blue-50 border border-blue-200'
      }`}>
        {status === 'verifying' && (
          <>
            <div className="animate-spin w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full mx-auto mb-4" />
            <h2 className="text-xl font-bold">Verifying your email...</h2>
          </>
        )}
        
        {status === 'success' && (
          <>
            <div className="text-green-600 text-6xl mb-4">✓</div>
            <h2 className="text-xl font-bold text-green-800 mb-2">Email Verified!</h2>
            <p className="text-gray-600">{message}</p>
            <p className="text-sm text-gray-500 mt-4">Redirecting to dashboard...</p>
          </>
        )}
        
        {status === 'error' && (
          <>
            <div className="text-red-600 text-6xl mb-4">✗</div>
            <h2 className="text-xl font-bold text-red-800 mb-2">Verification Failed</h2>
            <p className="text-gray-600">{message}</p>
            <button
              onClick={() => router.push('/register')}
              className="mt-6 bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700"
            >
              Back to Registration
            </button>
          </>
        )}
      </div>
    </div>
  );
}
```

---

## Environment Variables

Create `.env.local` in Next.js root:

```bash
# Drupal API
NEXT_PUBLIC_API_URL=http://billoria.ad.ddev.site

# For production
# NEXT_PUBLIC_API_URL=https://api.billoria.ad
```

---

## Testing the API

### Using cURL (for testing):

```bash
# Register a brand
curl -X POST http://billoria.ad.ddev.site/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "accountType": "brand",
    "user": {
      "name": "Test User",
      "email": "test@example.com",
      "password": "Password123!",
      "mobileNumber": "+8801712345678"
    },
    "organization": {
      "name": "Test Brand Ltd",
      "officialEmail": "info@testbrand.com",
      "officialPhone": "+8801712345678",
      "division": 1,
      "district": 10,
      "fullAddress": "123 Test Street, Dhaka"
    }
  }'

# Verify email
curl -X POST http://billoria.ad.ddev.site/api/verify-email \
  -H "Content-Type: application/json" \
  -d '{
    "uid": 123,
    "token": "abc123def456..."
  }'

# Get profile (requires authentication cookie)
curl -X GET http://billoria.ad.ddev.site/api/user/profile \
  --cookie "SESSION_COOKIE_HERE"
```

---

## Next Steps for Frontend

1. **Install dependencies**:
   ```bash
   cd frontendapp
   npm install axios react-hook-form @tanstack/react-query
   ```

2. **Create API client** (`lib/api/client.ts` and `lib/api/auth.ts`)

3. **Build registration pages**:
   - `/app/register/page.tsx` - Account type selection
   - `/app/register/brand/page.tsx` - Brand registration form
   - `/app/register/agency/page.tsx` - Agency registration form
   - `/app/register/owner/page.tsx` - Owner registration form

4. **Build verification pages**:
   - `/app/verify-email/page.tsx` - Email verification handler
   - `/app/verify-phone/page.tsx` - Phone verification form

5. **Build dashboard**:
   - `/app/dashboard/page.tsx` - Main dashboard with profile completion

6. **Add state management**:
   - Use React Query for API calls and caching
   - Use Zustand or Context for auth state

---

## Backend Complete ✓

The Drupal backend is now ready for headless operation:

- ✅ Organization content type (35 fields)
- ✅ User entity extensions (13 fields)
- ✅ REST API endpoints (7 endpoints)
- ✅ Email verification system
- ✅ Phone OTP system (placeholder)
- ✅ Role assignment (brand_user, agency, billboard_owner)
- ✅ Trust score calculation
- ✅ Profile completion calculation
- ✅ CORS enabled for localhost:3000

Ready for Next.js integration!

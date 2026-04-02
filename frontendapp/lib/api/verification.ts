/**
 * Verification API Client for OTP-based email/phone verification
 * 
 * @see /application-wiki/VERIFICATION_OTP_API.md for full API documentation
 */

import type {
  SendOtpResponse,
  VerifyOtpResponse,
  VerificationStatusResponse,
  VerificationType,
} from '@/types/verification';

const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_BASE_URL ||
  process.env.NEXT_PUBLIC_API_URL ||
  'https://billoria-ad-api.ddev.site';

const FALLBACK_URLS = [
  'https://billoria-ad-api.ddev.site',
  'https://api.billoria-ad.ddev.site',
  'http://billoria-ad-api.ddev.site:33000',
];

async function apiFetch(path: string, options: RequestInit = {}): Promise<Response> {
  const urls = [...new Set([API_BASE_URL, ...FALLBACK_URLS])];
  let lastError: Error | null = null;

  for (const baseUrl of urls) {
    try {
      const response = await fetch(`${baseUrl}${path}`, {
        ...options,
        mode: 'cors',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          ...options.headers,
        },
      });
      if (response.ok || response.status === 400 || response.status === 401 || response.status === 404 || response.status === 429) {
        return response;
      }
    } catch (err) {
      lastError = err as Error;
    }
  }
  throw lastError || new Error('All API URLs failed');
}

/**
 * Send OTP verification code to email
 * 
 * @throws {Error} Rate limited, authentication error, or API failure
 * @returns Promise with masked email and expiry time
 */
export async function sendEmailOtp(): Promise<SendOtpResponse> {
  const response = await apiFetch('/api/v1/verification/email/send-otp', {
    method: 'POST',
  });

  if (!response.ok) {
    if (response.status === 429) {
      const error: RateLimitError = await response.json();
      throw new RateLimitError(error.message, error.retryAfter);
    }
    if (response.status === 401) {
      throw new Error('Authentication required. Please log in.');
    }
    const error = await response.json();
    throw new Error(error.error || error.message || 'Failed to send verification code');
  }

  return response.json();
}

/**
 * Verify email OTP code
 * 
 * @param code - 6-digit OTP code
 * @throws {Error} Invalid code, expired, max attempts, or API failure
 * @returns Promise with verification result
 */
export async function verifyEmailOtp(code: string): Promise<VerifyOtpResponse> {
  if (!/^\d{6}$/.test(code)) {
    throw new Error('Code must be 6 digits');
  }

  const response = await apiFetch('/api/v1/verification/email/verify-otp', {
    method: 'POST',
    body: JSON.stringify({ code }),
  });

  const result: VerifyOtpResponse = await response.json();

  if (!result.success) {
    throw new VerificationError(result.error, result.message, result.data?.attempts_remaining);
  }

  return result;
}

/**
 * Send OTP verification code to phone
 * 
 * @throws {Error} Rate limited, authentication error, or API failure
 * @returns Promise with masked phone and expiry time
 */
export async function sendPhoneOtp(): Promise<SendOtpResponse> {
  const response = await apiFetch('/api/v1/verification/phone/send-otp', {
    method: 'POST',
  });

  if (!response.ok) {
    if (response.status === 429) {
      const error: RateLimitError = await response.json();
      throw new RateLimitError(error.message, error.retryAfter);
    }
    if (response.status === 401) {
      throw new Error('Authentication required. Please log in.');
    }
    const error = await response.json();
    throw new Error(error.error || error.message || 'Failed to send verification code');
  }

  return response.json();
}

/**
 * Verify phone OTP code
 * 
 * @param code - 6-digit OTP code
 * @throws {Error} Invalid code, expired, max attempts, or API failure
 * @returns Promise with verification result
 */
export async function verifyPhoneOtp(code: string): Promise<VerifyOtpResponse> {
  if (!/^\d{6}$/.test(code)) {
    throw new Error('Code must be 6 digits');
  }

  const response = await apiFetch('/api/v1/verification/phone/verify-otp', {
    method: 'POST',
    body: JSON.stringify({ code }),
  });

  const result: VerifyOtpResponse = await response.json();

  if (!result.success) {
    throw new VerificationError(result.error, result.message, result.data?.attempts_remaining);
  }

  return result;
}

/**
 * Get verification status for email and phone
 * 
 * @throws {Error} Authentication or API failure
 * @returns Promise with verification status
 */
export async function getVerificationStatus(): Promise<VerificationStatusResponse> {
  const response = await apiFetch('/api/v1/verification/status', {
    method: 'GET',
  });

  if (!response.ok) {
    if (response.status === 401) {
      throw new Error('Authentication required. Please log in.');
    }
    throw new Error('Failed to fetch verification status');
  }

  return response.json();
}

/**
 * Generic send OTP helper
 */
export async function sendOtp(type: VerificationType): Promise<SendOtpResponse> {
  return type === 'email' ? sendEmailOtp() : sendPhoneOtp();
}

/**
 * Generic verify OTP helper
 */
export async function verifyOtp(type: VerificationType, code: string): Promise<VerifyOtpResponse> {
  return type === 'email' ? verifyEmailOtp(code) : verifyPhoneOtp(code);
}

/**
 * Custom error class for rate limiting
 */
export class RateLimitError extends Error {
  constructor(message: string, public retryAfter: number) {
    super(message);
    this.name = 'RateLimitError';
  }
}

/**
 * Custom error class for verification failures
 */
export class VerificationError extends Error {
  constructor(
    public code: VerifyOtpResponse['error'],
    message: string,
    public attemptsRemaining?: number
  ) {
    super(message);
    this.name = 'VerificationError';
  }

  getUserMessage(): string {
    switch (this.code) {
      case 'invalid_code':
        return this.attemptsRemaining !== undefined
          ? `Invalid code. ${this.attemptsRemaining} attempt${this.attemptsRemaining !== 1 ? 's' : ''} remaining.`
          : 'Invalid verification code.';
      case 'expired':
        return 'Verification code has expired. Please request a new one.';
      case 'max_attempts':
        return 'Too many failed attempts. Please request a new code.';
      case 'not_found':
        return 'No pending verification found. Please request a new code.';
      default:
        return this.message;
    }
  }
}

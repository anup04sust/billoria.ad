// API Types for OTP Verification System
// Location: frontendapp/types/verification.ts

/**
 * Response from send OTP endpoints (/email/send-otp, /phone/send-otp)
 */
export interface SendOtpResponse {
  success: boolean;
  message: string;
  data?: {
    email?: string;      // Masked email (e.g., "us**@example.com")
    phone?: string;      // Masked phone (e.g., "+88017123XXXX")
    expiresIn: number;   // Seconds until expiry (600 for email, 300 for phone)
  };
}

/**
 * Response from verify OTP endpoints (/email/verify-otp, /phone/verify-otp)
 */
export interface VerifyOtpResponse {
  success: boolean;
  message: string;
  error?: 'invalid_code' | 'expired' | 'max_attempts' | 'not_found';
  data?: {
    verification_id?: number;
    verification_type?: 'email' | 'phone';
    identifier?: string;              // Full email or phone
    emailVerified?: boolean;
    phoneVerified?: boolean;
    trustScore?: number;              // Updated trust score
    attempts_remaining?: number;      // Only present on failed attempts
  };
}

/**
 * Response from /verification/status endpoint
 */
export interface VerificationStatusResponse {
  success: boolean;
  data: {
    email: VerificationChannelStatus;
    phone: VerificationChannelStatus;
  };
}

export interface VerificationChannelStatus {
  verified: boolean;
  hasPending: boolean;
  expiresAt: number | null;  // Unix timestamp
}

/**
 * Rate limit error response (429 status)
 */
export interface RateLimitError {
  error: 'rate_limit';
  message: string;
  retryAfter: number;  // Seconds until next request allowed
}

/**
 * Generic API error response
 */
export interface ApiError {
  error: string;
  message?: string;
}

/**
 * Verification type discriminator
 */
export type VerificationType = 'email' | 'phone';

/**
 * Result of verification attempt
 */
export type VerificationResult = 
  | { success: true; data: VerifyOtpResponse['data'] }
  | { success: false; error: VerifyOtpResponse['error']; message: string; attemptsRemaining?: number };

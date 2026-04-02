/**
 * React Hook for OTP Verification
 * 
 * Handles email/phone verification with OTP codes including:
 * - Send code with rate limiting
 * - Verify code with attempt tracking
 * - Automatic countdown timer
 * - Error handling
 * 
 * @example
 * ```tsx
 * function ProfilePage() {
 *   const { sendCode, verifyCode, sending, verifying, error, retryAfter } = useOtpVerification('email');
 * 
 *   const handleSend = async () => {
 *     try {
 *       await sendCode();
 *       setModalOpen(true);
 *     } catch (err) {
 *       // Error handled by hook
 *     }
 *   };
 * 
 *   const handleVerify = async (code: string) => {
 *     try {
 *       const result = await verifyCode(code);
 *       // Update UI with result.data.trustScore, etc.
 *     } catch (err) {
 *       // Error handled by hook
 *     }
 *   };
 * 
 *   return (
 *     <button onClick={handleSend} disabled={!canSend}>
 *       {sending ? 'Sending...' : retryAfter > 0 ? `Wait ${retryAfter}s` : 'Send Code'}
 *     </button>
 *   );
 * }
 * ```
 */

import { useState, useEffect, useCallback } from 'react';
import { sendOtp, verifyOtp, RateLimitError, VerificationError } from '@/lib/api/verification';
import type { VerificationType, VerifyOtpResponse, SendOtpResponse } from '@/types/verification';

export interface UseOtpVerificationOptions {
  onSendSuccess?: (data: SendOtpResponse) => void;
  onVerifySuccess?: (data: VerifyOtpResponse) => void;
  onError?: (error: Error) => void;
}

export function useOtpVerification(type: VerificationType, options?: UseOtpVerificationOptions) {
  const [sending, setSending] = useState(false);
  const [verifying, setVerifying] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [retryAfter, setRetryAfter] = useState(0);
  const [attemptsRemaining, setAttemptsRemaining] = useState<number | null>(null);
  const [lastSentData, setLastSentData] = useState<SendOtpResponse['data'] | null>(null);

  // Countdown timer for rate limiting
  useEffect(() => {
    if (retryAfter > 0) {
      const timer = setTimeout(() => setRetryAfter((prev) => prev - 1), 1000);
      return () => clearTimeout(timer);
    }
  }, [retryAfter]);

  /**
   * Send OTP code to email or phone
   */
  const sendCode = useCallback(async () => {
    if (retryAfter > 0) {
      setError(`Please wait ${retryAfter} seconds before requesting a new code.`);
      return;
    }

    setSending(true);
    setError(null);
    setAttemptsRemaining(null);

    try {
      const result = await sendOtp(type);
      setLastSentData(result.data ?? null);
      options?.onSendSuccess?.(result);
      return result;
    } catch (err) {
      if (err instanceof RateLimitError) {
        setRetryAfter(err.retryAfter);
        setError(err.message);
        // Don't throw - rate limit is handled by setting state
        return;
      } else {
        const errorMessage = err instanceof Error ? err.message : 'Failed to send verification code';
        setError(errorMessage);
        options?.onError?.(err instanceof Error ? err : new Error(errorMessage));
        throw err;
      }
    } finally {
      setSending(false);
    }
  }, [type, retryAfter, options]);

  /**
   * Verify OTP code
   */
  const verifyCode = useCallback(async (code: string) => {
    if (!code || code.length !== 6) {
      const errorMessage = 'Please enter a valid 6-digit code';
      setError(errorMessage);
      throw new Error(errorMessage);
    }

    setVerifying(true);
    setError(null);

    try {
      const result = await verifyOtp(type, code);
      setAttemptsRemaining(null);
      options?.onVerifySuccess?.(result);
      return result;
    } catch (err) {
      if (err instanceof VerificationError) {
        setError(err.getUserMessage());
        setAttemptsRemaining(err.attemptsRemaining ?? null);
      } else {
        const errorMessage = err instanceof Error ? err.message : 'Verification failed';
        setError(errorMessage);
      }
      options?.onError?.(err instanceof Error ? err : new Error('Verification failed'));
      throw err;
    } finally {
      setVerifying(false);
    }
  }, [type, options]);

  /**
   * Clear error message
   */
  const clearError = useCallback(() => {
    setError(null);
    setAttemptsRemaining(null);
  }, []);

  /**
   * Reset all state
   */
  const reset = useCallback(() => {
    setSending(false);
    setVerifying(false);
    setError(null);
    setRetryAfter(0);
    setAttemptsRemaining(null);
    setLastSentData(null);
  }, []);

  return {
    // Actions
    sendCode,
    verifyCode,
    clearError,
    reset,

    // State
    sending,
    verifying,
    error,
    retryAfter,
    attemptsRemaining,
    lastSentData,

    // Computed
    canSend: !sending && retryAfter === 0,
    canVerify: !verifying,
    isLoading: sending || verifying,
  };
}

/**
 * Hook  for verification status polling
 * 
 * @example
 * ```tsx
 * const { status, loading, refresh } = useVerificationStatus();
 * 
 * if (status?.email.verified) {
 *   // Show verified badge
 * }
 * 
 * if (status?.phone.hasPending) {
 *   // Show pending verification
 * }
 * ```
 */
import { getVerificationStatus } from '@/lib/api/verification';
import type { VerificationStatusResponse } from '@/types/verification';

export function useVerificationStatus() {
  const [status, setStatus] = useState<VerificationStatusResponse['data'] | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchStatus = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const result = await getVerificationStatus();
      setStatus(result.data);
      return result.data;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Failed to fetch status';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchStatus();
  }, [fetchStatus]);

  return {
    status,
    loading,
    error,
    refresh: fetchStatus,
  };
}

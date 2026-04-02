'use client';

import { useState, useEffect, useRef } from 'react';
import { IconX, IconEmailVerified } from '@/lib/icons/ui-icons';
import './edit-profile-modal.css';

interface EmailVerificationModalProps {
  email: string | null;
  onClose: () => void;
  onVerify: (code: string) => Promise<void>;
  verifying: boolean;
  error: string | null;
  message: string;
}

export function EmailVerificationModal({
  email,
  onClose,
  onVerify,
  verifying,
  error,
  message,
}: EmailVerificationModalProps) {
  const overlayRef = useRef<HTMLDivElement>(null);
  const [code, setCode] = useState('');
  const [submitting, setSubmitting] = useState(false);

  // Freeze body scroll while modal is open
  useEffect(() => {
    const prev = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => { document.body.style.overflow = prev; };
  }, []);

  // Close on Escape key
  useEffect(() => {
    const handleEsc = (e: KeyboardEvent) => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', handleEsc);
    return () => window.removeEventListener('keydown', handleEsc);
  }, [onClose]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!code.trim() || submitting || verifying) return;

    setSubmitting(true);
    try {
      await onVerify(code.trim());
    } finally {
      setSubmitting(false);
    }
  };

  const handleOverlayClick = (e: React.MouseEvent) => {
    if (e.target === overlayRef.current) {
      onClose();
    }
  };

  return (
    <div className="epm-overlay" ref={overlayRef} onClick={handleOverlayClick}>
      <div className="epm-modal" style={{ maxWidth: '32rem' }}>
        <div className="epm-header">
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
            <div style={{
              width: '2.5rem',
              height: '2.5rem',
              borderRadius: '50%',
              background: 'linear-gradient(135deg, #059669 0%, #047857 100%)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              color: '#fff',
            }}>
              <IconEmailVerified />
            </div>
            <h3 className="epm-title">Verify Email Address</h3>
          </div>
          <button
            type="button"
            className="epm-close"
            onClick={onClose}
            disabled={submitting || verifying}
          >
            <IconX />
          </button>
        </div>

        <form onSubmit={handleSubmit}>
          <div className="epm-body">
            {/* Info Section */}
            <div style={{ 
              background: '#f0fdf4', 
              border: '1px solid #bbf7d0',
              borderRadius: '0.375rem',
              padding: '1rem',
              marginBottom: '1.5rem'
            }}>
              {message && (
                <p style={{ 
                  fontSize: '0.875rem', 
                  color: '#065f46',
                  marginBottom: email ? '0.5rem' : '0',
                  lineHeight: '1.5'
                }}>
                  {message}
                </p>
              )}
              {email && (
                <p style={{ 
                  fontSize: '0.875rem', 
                  color: '#047857',
                  margin: 0,
                  fontWeight: 600
                }}>
                  ✉️ {email}
                </p>
              )}
            </div>

            {/* Code Input Section */}
            <div className="epm-section">
              <label className="epm-label" htmlFor="verification-code">
                Enter 6-Digit Code
              </label>
              <input
                id="verification-code"
                type="text"
                inputMode="numeric"
                className="epm-input"
                value={code}
                onChange={(e) => {
                  const val = e.target.value.replace(/\D/g, '').slice(0, 6);
                  setCode(val);
                }}
                placeholder="000000"
                maxLength={6}
                autoFocus
                autoComplete="one-time-code"
                disabled={submitting || verifying}
                style={{ 
                  letterSpacing: '0.75rem',
                  fontSize: '1.5rem',
                  textAlign: 'center',
                  fontWeight: 700,
                  padding: '0.875rem',
                  fontVariantNumeric: 'tabular-nums'
                }}
              />
              <p className="epm-hint" style={{ marginTop: '0.5rem' }}>
                Check your email inbox (and spam folder) for the verification code
              </p>
            </div>

            {/* Error Message */}
            {error && (
              <div style={{
                backgroundColor: '#fef2f2',
                border: '1px solid #fecaca',
                borderRadius: '0.375rem',
                padding: '0.875rem',
                marginTop: '1rem',
                display: 'flex',
                gap: '0.75rem',
                alignItems: 'flex-start'
              }}>
                <span style={{ fontSize: '1.25rem' }}>⚠️</span>
                <p style={{ 
                  fontSize: '0.875rem', 
                  color: '#991b1b',
                  margin: 0,
                  flex: 1,
                  lineHeight: '1.5'
                }}>
                  {error}
                </p>
              </div>
            )}
          </div>

          <div className="epm-footer">
            <button
              type="button"
              className="epm-btn epm-btn--cancel"
              onClick={onClose}
              disabled={submitting || verifying}
            >
              Cancel
            </button>
            <button
              type="submit"
              className="epm-btn epm-btn--save"
              disabled={code.length !== 6 || submitting || verifying}
            >
              {submitting || verifying ? 'Verifying...' : 'Verify Code'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

'use client';

import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import './login.css';

export function LoginForm() {
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError('');
    setLoading(true);
    // TODO: Replace with authAPI.login()
    await new Promise((r) => setTimeout(r, 800));
    setLoading(false);
    setError('Invalid email or password. Please try again.');
  }

  return (
    <div className="login-page">
      {/* Left brand panel */}
      <div className="login-brand">
        <div className="login-brand__inner">
          <div className="login-brand__center">
            <Link href="/" className="login-brand__logo">
              <Image
                src="/billoria-logo-white.svg"
                alt="Billoria Adpoint"
                width={220}
                height={38}
                priority
              />
            </Link>
            <div className="login-brand__content">
              <h1 className="login-brand__headline">
                Bangladesh&apos;s Premier<br />Billboard Marketplace
              </h1>
              <p className="login-brand__sub">
                Discover, compare and book outdoor advertising spaces across every division — from Dhaka to Chittagong.
              </p>
            </div>
          </div>

          <p className="login-brand__copy">
            &copy; {new Date().getFullYear()} Billoria Adpoint. All rights reserved.
          </p>
        </div>
      </div>

      {/* Right form panel */}
      <div className="login-panel">
        <div className="login-panel__inner">
          <Link href="/" className="login-back-home">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M13 8H3M7 12l-4-4 4-4"/>
            </svg>
            Back to Home
          </Link>

          <div className="login-panel__header">
            <h2 className="login-panel__title">Welcome back</h2>
            <p className="login-panel__sub">
              Sign in to your account to continue
            </p>
          </div>

          {error && (
            <div className="login-alert" role="alert">
              <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd"/>
              </svg>
              {error}
            </div>
          )}

          <form className="login-form" onSubmit={handleSubmit} noValidate>
            <div className="login-form__group">
              <label className="login-form__label" htmlFor="email">
                Email address
              </label>
              <div className="login-form__input-wrap">
                <span className="login-form__input-icon">
                  <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M2.5 6.5l7.5 5 7.5-5M2.5 5.5h15a1 1 0 011 1v8a1 1 0 01-1 1h-15a1 1 0 01-1-1v-8a1 1 0 011-1z"/>
                  </svg>
                </span>
                <input
                  id="email"
                  name="email"
                  type="email"
                  autoComplete="email"
                  required
                  className="login-form__input"
                  placeholder="you@example.com"
                />
              </div>
            </div>

            <div className="login-form__group">
              <div className="login-form__label-row">
                <label className="login-form__label" htmlFor="password">
                  Password
                </label>
                <Link href="/forgot-password" className="login-form__forgot">
                  Forgot password?
                </Link>
              </div>
              <div className="login-form__input-wrap">
                <span className="login-form__input-icon">
                  <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5">
                    <rect x="3" y="9" width="14" height="10" rx="1" strokeLinecap="round" strokeLinejoin="round"/>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M7 9V6a3 3 0 016 0v3"/>
                  </svg>
                </span>
                <input
                  id="password"
                  name="password"
                  type={showPassword ? 'text' : 'password'}
                  autoComplete="current-password"
                  required
                  className="login-form__input login-form__input--padded-right"
                  placeholder="••••••••"
                />
                <button
                  type="button"
                  className="login-form__toggle-pw"
                  onClick={() => setShowPassword((v) => !v)}
                  aria-label={showPassword ? 'Hide password' : 'Show password'}
                >
                  {showPassword ? (
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 10C3.226 13.307 6.368 15.5 10 15.5c1.394 0 2.717-.356 3.865-.983M6.877 6.877A3 3 0 0113.12 13.12M3 3l14 14"/>
                    </svg>
                  ) : (
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M10 4.5C5.5 4.5 2 10 2 10s3.5 5.5 8 5.5S18 10 18 10s-3.5-5.5-8-5.5z"/>
                      <circle cx="10" cy="10" r="2.5" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  )}
                </button>
              </div>
            </div>

            <div className="login-form__remember">
              <label className="login-form__checkbox-label">
                <input type="checkbox" name="remember" className="login-form__checkbox" />
                Keep me signed in
              </label>
            </div>

            <button
              type="submit"
              className="login-form__submit"
              disabled={loading}
            >
              {loading ? (
                <>
                  <span className="login-form__spinner" aria-hidden="true" />
                  Signing in…
                </>
              ) : (
                'Sign In'
              )}
            </button>
          </form>

          <p className="login-register" style={{ marginTop: '1.5rem' }}>
            Don&apos;t have an account?{' '}
            <Link href="/register" className="login-register__link">
              Create one free
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}

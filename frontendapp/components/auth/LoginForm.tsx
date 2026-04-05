'use client';

import { useState, useEffect } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import { authAPI } from '@/lib/api/auth';
import { getDashboardRoute } from '@/app/dashboard/page';
import { IconArrowLeft, IconAlertFilled, IconMail, IconLock, IconEyeOff, IconEyeOpen } from '@/lib/icons/ui-icons';
import './login.css';

export function LoginForm() {
  const searchParams = useSearchParams();
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  // Redirect immediately if already authenticated
  useEffect(() => {
    const user = authAPI.getCurrentUser();
    if (user) window.location.href = searchParams.get('next') || getDashboardRoute(user.roles);
  }, [searchParams]);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError('');
    setLoading(true);

    const form = e.currentTarget;
    const email = (form.elements.namedItem('email') as HTMLInputElement).value.trim();
    const password = (form.elements.namedItem('password') as HTMLInputElement).value;

    const result = await authAPI.login(email, password);
    setLoading(false);

    if (result.success && result.user) {
      const next = searchParams.get('next');
      window.location.href = next || getDashboardRoute(result.user.roles);
    } else {
      setError(result.error ?? 'Login failed. Please try again.');
    }
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
            <IconArrowLeft />
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
              <IconAlertFilled />
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
                  <IconMail />
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
                  <IconLock />
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
                    <IconEyeOff />
                  ) : (
                    <IconEyeOpen />
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

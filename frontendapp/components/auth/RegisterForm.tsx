'use client';

import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { IconTag, IconNetwork, IconBillboard, IconCheckCircle, IconCheckFilled, IconAlertFilled, IconEyeOff, IconEyeOpen, IconArrowRight, IconArrowLeft } from '@/lib/icons/ui-icons';
import './register.css';

type AccountType = 'brand' | 'agency' | 'owner';
type Step = 1 | 2 | 3;

const ACCOUNT_TYPES: { value: AccountType; label: string; icon: React.ReactNode; description: string }[] = [
  {
    value: 'brand',
    label: 'Brand',
    icon: <IconTag />,
    description: 'Advertise on billboards — search, book and manage campaigns',
  },
  {
    value: 'agency',
    label: 'Agency',
    icon: <IconNetwork />,
    description: 'Manage outdoor campaigns for clients — media planning & buying',
  },
  {
    value: 'owner',
    label: 'Billboard Owner',
    icon: <IconBillboard />,
    description: 'List your billboard inventory and receive direct booking requests',
  },
];

interface FormData {
  // Account type
  accountType: AccountType;
  // Step 1 — Personal
  name: string;
  email: string;
  password: string;
  confirmPassword: string;
  mobileNumber: string;
  // Step 2 — Organisation
  orgName: string;
  officialEmail: string;
  officialPhone: string;
  website: string;
  fullAddress: string;
  businessRegNumber: string;
  // Step 3 — Account-type specifics
  annualBudgetRange: string;
  agencyServices: string[];
  inventoryCount: string;
  installationServices: boolean;
}

const INITIAL: FormData = {
  accountType: 'brand',
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  mobileNumber: '',
  orgName: '',
  officialEmail: '',
  officialPhone: '',
  website: '',
  fullAddress: '',
  businessRegNumber: '',
  annualBudgetRange: '',
  agencyServices: [],
  inventoryCount: '',
  installationServices: false,
};

const AGENCY_SERVICES = [
  { value: 'media_planning', label: 'Media Planning' },
  { value: 'creative', label: 'Creative' },
  { value: 'ooh', label: 'OOH / Outdoor' },
  { value: 'digital', label: 'Digital' },
  { value: 'btl', label: 'BTL Activation' },
  { value: 'events', label: 'Events' },
];

function PasswordStrength({ password }: { password: string }) {
  const checks = [
    password.length >= 8,
    /[A-Z]/.test(password),
    /[0-9]/.test(password),
    /[^A-Za-z0-9]/.test(password),
  ];
  const score = checks.filter(Boolean).length;
  const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
  const colors = ['', '#ef4444', '#f59e0b', '#3b82f6', '#10b981'];

  if (!password) return null;

  return (
    <div className="reg-pw-strength">
      <div className="reg-pw-strength__bars">
        {[1, 2, 3, 4].map((i) => (
          <span
            key={i}
            className="reg-pw-strength__bar"
            style={{ background: i <= score ? colors[score] : undefined }}
          />
        ))}
      </div>
      <span className="reg-pw-strength__label" style={{ color: colors[score] }}>
        {labels[score]}
      </span>
    </div>
  );
}

export function RegisterForm() {
  const [step, setStep] = useState<Step>(1);
  const [form, setForm] = useState<FormData>(INITIAL);
  const [showPw, setShowPw] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  function set(field: keyof FormData, value: unknown) {
    setForm((f) => ({ ...f, [field]: value }));
    setError('');
  }

  function toggleService(val: string) {
    set(
      'agencyServices',
      form.agencyServices.includes(val)
        ? form.agencyServices.filter((s) => s !== val)
        : [...form.agencyServices, val],
    );
  }

  function validateStep1() {
    if (!form.name.trim()) return 'Full name is required.';
    if (!form.email.trim()) return 'Email address is required.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) return 'Enter a valid email address.';
    if (form.password.length < 8) return 'Password must be at least 8 characters.';
    if (form.password !== form.confirmPassword) return 'Passwords do not match.';
    if (!form.mobileNumber.trim()) return 'Mobile number is required.';
    return '';
  }

  function validateStep2() {
    if (!form.orgName.trim()) return 'Organisation name is required.';
    if (!form.officialEmail.trim()) return 'Official email is required.';
    if (!form.officialPhone.trim()) return 'Official phone is required.';
    if (!form.fullAddress.trim()) return 'Address is required.';
    return '';
  }

  function nextStep() {
    const err = step === 1 ? validateStep1() : step === 2 ? validateStep2() : '';
    if (err) { setError(err); return; }
    setError('');
    setStep((s) => (s + 1) as Step);
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setLoading(true);
    // TODO: Replace with authAPI.register(form)
    await new Promise((r) => setTimeout(r, 1000));
    setLoading(false);
    setSuccess(true);
  }

  if (success) {
    return (
      <div className="reg-page">
        <div className="reg-success">
          <div className="reg-success__icon">
            <IconCheckCircle />
          </div>
          <h2>Account created!</h2>
          <p>We&apos;ve sent a verification email to <strong>{form.email}</strong>. Check your inbox and click the link to activate your account.</p>
          <Link href="/login" className="reg-success__btn">Go to Sign In</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="reg-page">
      {/* Left brand panel */}
      <div className="reg-brand">
        <div className="reg-brand__inner">
          <Link href="/" className="reg-brand__logo">
            <Image src="/billoria-logo-white.svg" alt="Billoria Adpoint" width={200} height={34} priority />
          </Link>

          <div className="reg-brand__steps">
            <p className="reg-brand__steps-label">Registration steps</p>
            {[
              { n: 1, label: 'Your details' },
              { n: 2, label: 'Organisation' },
              { n: 3, label: 'Account setup' },
            ].map(({ n, label }) => (
              <div key={n} className={`reg-brand__step${step === n ? ' reg-brand__step--active' : ''}${step > n ? ' reg-brand__step--done' : ''}`}>
                <span className="reg-brand__step-num">
                  {step > n ? (
                    <IconCheckFilled />
                  ) : n}
                </span>
                <span className="reg-brand__step-label">{label}</span>
              </div>
            ))}
          </div>

          <div className="reg-brand__account-type">
            <p className="reg-brand__steps-label">Registering as</p>
            <span className="reg-brand__at-pill">
              {ACCOUNT_TYPES.find((t) => t.value === form.accountType)?.label}
            </span>
          </div>

          <p className="reg-brand__copy">
            &copy; {new Date().getFullYear()} Billoria Adpoint
          </p>
        </div>
      </div>

      {/* Right form panel */}
      <div className="reg-panel">
        <div className="reg-panel__inner">

          {/* Mobile logo */}
          <Link href="/" className="reg-panel__mobile-logo">
            <Image src="/billoria-logo-evening.svg" alt="Billoria" width={160} height={28} />
          </Link>

          <Link href="/" className="reg-back-home">
            <IconArrowLeft />
            Back to Home
          </Link>

          {/* Step progress bar (mobile) */}
          <div className="reg-progress">
            {[1, 2, 3].map((n) => (
              <div key={n} className={`reg-progress__dot${step === n ? ' reg-progress__dot--active' : ''}${step > n ? ' reg-progress__dot--done' : ''}`} />
            ))}
          </div>

          {/* ── Step 1: Account type + personal ── */}
          {step === 1 && (
            <>
              <div className="reg-panel__header">
                <h2 className="reg-panel__title">Create your account</h2>
                <p className="reg-panel__sub">Step 1 of 3 — Choose account type &amp; personal details</p>
              </div>

              {/* Account type selector */}
              <div className="reg-at-grid">
                {ACCOUNT_TYPES.map((at) => (
                  <button
                    key={at.value}
                    type="button"
                    className={`reg-at-card${form.accountType === at.value ? ' reg-at-card--active' : ''}`}
                    onClick={() => set('accountType', at.value)}
                  >
                    <span className="reg-at-card__icon">{at.icon}</span>
                    <span className="reg-at-card__label">{at.label}</span>
                    <span className="reg-at-card__desc">{at.description}</span>
                  </button>
                ))}
              </div>

              {error && <div className="reg-alert" role="alert"><AlertIcon />{error}</div>}

              <form className="reg-form" onSubmit={(e) => { e.preventDefault(); nextStep(); }}>
                <div className="reg-form__row">
                  <Field label="Full name" id="name" required>
                    <input id="name" type="text" className="reg-input" placeholder="Ahmed Rahman" autoComplete="name" value={form.name} onChange={(e) => set('name', e.target.value)} required />
                  </Field>
                  <Field label="Mobile number" id="mobile" required hint="+8801XXXXXXXXX">
                    <input id="mobile" type="tel" className="reg-input" placeholder="+8801XXXXXXXXX" autoComplete="tel" value={form.mobileNumber} onChange={(e) => set('mobileNumber', e.target.value)} required />
                  </Field>
                </div>

                <Field label="Email address" id="email" required>
                  <input id="email" type="email" className="reg-input" placeholder="you@company.com" autoComplete="email" value={form.email} onChange={(e) => set('email', e.target.value)} required />
                </Field>

                <Field label="Password" id="password" required hint="At least 8 characters">
                  <div className="reg-input-wrap">
                    <input id="password" type={showPw ? 'text' : 'password'} className="reg-input reg-input--pr" placeholder="••••••••" autoComplete="new-password" value={form.password} onChange={(e) => set('password', e.target.value)} required />
                    <button type="button" className="reg-input-toggle" onClick={() => setShowPw((v) => !v)} aria-label="Toggle password">
                      <EyeIcon open={showPw} />
                    </button>
                  </div>
                  <PasswordStrength password={form.password} />
                </Field>

                <Field label="Confirm password" id="confirmPw" required>
                  <input id="confirmPw" type={showPw ? 'text' : 'password'} className="reg-input" placeholder="••••••••" autoComplete="new-password" value={form.confirmPassword} onChange={(e) => set('confirmPassword', e.target.value)} required />
                </Field>

                <button type="submit" className="reg-btn">
                  Continue <ArrowRight />
                </button>
              </form>
            </>
          )}

          {/* ── Step 2: Organisation ── */}
          {step === 2 && (
            <>
              <div className="reg-panel__header">
                <h2 className="reg-panel__title">Organisation details</h2>
                <p className="reg-panel__sub">Step 2 of 3 — Tell us about your organisation</p>
              </div>

              {error && <div className="reg-alert" role="alert"><AlertIcon />{error}</div>}

              <form className="reg-form" onSubmit={(e) => { e.preventDefault(); nextStep(); }}>
                <Field label="Organisation / company name" id="orgName" required>
                  <input id="orgName" type="text" className="reg-input" placeholder="Acme Corporation Ltd." value={form.orgName} onChange={(e) => set('orgName', e.target.value)} required />
                </Field>

                <div className="reg-form__row">
                  <Field label="Official email" id="offEmail" required>
                    <input id="offEmail" type="email" className="reg-input" placeholder="info@acme.com" value={form.officialEmail} onChange={(e) => set('officialEmail', e.target.value)} required />
                  </Field>
                  <Field label="Official phone" id="offPhone" required>
                    <input id="offPhone" type="tel" className="reg-input" placeholder="+8802XXXXXXXX" value={form.officialPhone} onChange={(e) => set('officialPhone', e.target.value)} required />
                  </Field>
                </div>

                <Field label="Website" id="website" hint="Optional">
                  <input id="website" type="url" className="reg-input" placeholder="https://acme.com" value={form.website} onChange={(e) => set('website', e.target.value)} />
                </Field>

                <Field label="Full address" id="address" required>
                  <textarea id="address" className="reg-input reg-textarea" rows={2} placeholder="House, Road, Area, City" value={form.fullAddress} onChange={(e) => set('fullAddress', e.target.value)} required />
                </Field>

                <Field label="Business registration number" id="brn" hint="Optional">
                  <input id="brn" type="text" className="reg-input" placeholder="RJSC-XXXXXXXXXX" value={form.businessRegNumber} onChange={(e) => set('businessRegNumber', e.target.value)} />
                </Field>

                <div className="reg-form__nav">
                  <button type="button" className="reg-btn reg-btn--outline" onClick={() => setStep(1)}>
                    <ArrowLeft /> Back
                  </button>
                  <button type="submit" className="reg-btn">
                    Continue <ArrowRight />
                  </button>
                </div>
              </form>
            </>
          )}

          {/* ── Step 3: Account-type specifics ── */}
          {step === 3 && (
            <>
              <div className="reg-panel__header">
                <h2 className="reg-panel__title">Account setup</h2>
                <p className="reg-panel__sub">Step 3 of 3 — A few more details to personalise your experience</p>
              </div>

              {error && <div className="reg-alert" role="alert"><AlertIcon />{error}</div>}

              <form className="reg-form" onSubmit={handleSubmit}>

                {/* Brand-specific */}
                {form.accountType === 'brand' && (
                  <>
                    <Field label="Annual OOH budget range" id="budget" hint="Optional — helps us show relevant inventory">
                      <select id="budget" className="reg-input reg-select" value={form.annualBudgetRange} onChange={(e) => set('annualBudgetRange', e.target.value)}>
                        <option value="">Select range…</option>
                        <option value="under_5l">Under ৳5 Lakh</option>
                        <option value="5l_20l">৳5 – 20 Lakh</option>
                        <option value="20l_50l">৳20 – 50 Lakh</option>
                        <option value="50l_1cr">৳50 Lakh – 1 Crore</option>
                        <option value="over_1cr">Over ৳1 Crore</option>
                      </select>
                    </Field>
                  </>
                )}

                {/* Agency-specific */}
                {form.accountType === 'agency' && (
                  <>
                    <fieldset className="reg-fieldset">
                      <legend className="reg-fieldset__legend">Services offered <span className="reg-hint">Select all that apply</span></legend>
                      <div className="reg-check-grid">
                        {AGENCY_SERVICES.map((s) => (
                          <label key={s.value} className="reg-check-label">
                            <input
                              type="checkbox"
                              className="reg-checkbox"
                              checked={form.agencyServices.includes(s.value)}
                              onChange={() => toggleService(s.value)}
                            />
                            {s.label}
                          </label>
                        ))}
                      </div>
                    </fieldset>
                  </>
                )}

                {/* Owner-specific */}
                {form.accountType === 'owner' && (
                  <>
                    <Field label="Estimated billboard inventory count" id="invCount" hint="Optional">
                      <input id="invCount" type="number" min="0" className="reg-input" placeholder="e.g. 12" value={form.inventoryCount} onChange={(e) => set('inventoryCount', e.target.value)} />
                    </Field>
                    <div className="reg-toggle-row">
                      <label className="reg-check-label" htmlFor="install">
                        <input
                          id="install"
                          type="checkbox"
                          className="reg-checkbox"
                          checked={form.installationServices}
                          onChange={(e) => set('installationServices', e.target.checked)}
                        />
                        We provide installation &amp; maintenance services
                      </label>
                    </div>
                  </>
                )}

                <div className="reg-terms">
                  <label className="reg-check-label">
                    <input type="checkbox" className="reg-checkbox" required />
                    I agree to the{' '}
                    <Link href="/terms" className="reg-link">Terms of Service</Link>
                    {' '}and{' '}
                    <Link href="/privacy" className="reg-link">Privacy Policy</Link>
                  </label>
                </div>

                <div className="reg-form__nav">
                  <button type="button" className="reg-btn reg-btn--outline" onClick={() => setStep(2)}>
                    <ArrowLeft /> Back
                  </button>
                  <button type="submit" className="reg-btn" disabled={loading}>
                    {loading ? <><Spinner /> Creating account…</> : 'Create account'}
                  </button>
                </div>
              </form>
            </>
          )}

          <p className="reg-signin">
            Already have an account?{' '}
            <Link href="/login" className="reg-link">Sign in</Link>
          </p>
        </div>
      </div>
    </div>
  );
}

/* ── Small reusable helpers ── */
function Field({ label, id, required, hint, children }: { label: string; id: string; required?: boolean; hint?: string; children: React.ReactNode }) {
  return (
    <div className="reg-field">
      <div className="reg-field__label-row">
        <label className="reg-label" htmlFor={id}>{label}{required && <span className="reg-required">*</span>}</label>
        {hint && <span className="reg-hint">{hint}</span>}
      </div>
      {children}
    </div>
  );
}

function AlertIcon() {
  return <IconAlertFilled />;
}

function EyeIcon({ open }: { open: boolean }) {
  return open ? <IconEyeOff /> : <IconEyeOpen />;
}

function ArrowRight() {
  return <IconArrowRight />;
}

function ArrowLeft() {
  return <IconArrowLeft />;
}

function Spinner() {
  return <span className="reg-spinner" aria-hidden="true" />;
}

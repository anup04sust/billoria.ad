'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import Image from 'next/image';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { authAPI } from '@/lib/api/auth';
import { profileAPI, ProfileAuthError } from '@/lib/api/profile';
import type { UserProfile, ProfileOrganization } from '@/lib/api/profile';
import { EditPersonalModal } from '@/components/dashboard/EditPersonalModal';
import { EditOrganizationModal } from '@/components/dashboard/EditOrganizationModal';
import { EditOrganizationLogoModal } from '@/components/dashboard/EditOrganizationLogoModal';
import { EditServiceCoverageModal } from '@/components/dashboard/EditServiceCoverageModal';
import { DocumentUploadModal } from '@/components/dashboard/DocumentUploadModal';
import { PhoneVerificationModal } from '@/components/dashboard/PhoneVerificationModal';
import { EmailVerificationModal } from '@/components/dashboard/EmailVerificationModal';
import { useOtpVerification } from '@/lib/hooks/useOtpVerification';
import '@/components/dashboard/dashboard-sidebar.css';
import '@/components/dashboard/dashboard-topbar.css';
import '@/components/dashboard/dashboard.css';
import '@/components/dashboard/profile.css';
import {
  IconUser,
  IconBuilding,
  IconShield,
  IconMap,
  IconEdit,
  IconCheck,
  IconClock,
  IconVerifiedRibbon,
  IconEmailVerified,
  IconPhone,
  IconDocumentVerified,
} from '@/lib/icons/ui-icons';

// ── Helpers ───────────────────────────────────────────────────────────────────
function Field({ label, value }: { label: string; value?: string | number | null }) {
  return (
    <div className="db-profile-field">
      <span className="db-profile-field__label">{label}</span>
      {value ? (
        <span className="db-profile-field__value">{value}</span>
      ) : (
        <span className="db-profile-field__value db-profile-field__value--empty">Not provided</span>
      )}
    </div>
  );
}

function Bar({ label, value }: { label: string; value: number }) {
  return (
    <div className="db-profile-bar-wrap">
      <div className="db-profile-bar-label">
        <span>{label}</span>
        <span>{value}%</span>
      </div>
      <div className="db-profile-bar">
        <div className="db-profile-bar__fill" style={{ width: `${value}%` }} />
      </div>
    </div>
  );
}

function VerifyTag({ ok, label }: { ok: boolean; label: string }) {
  return (
    <span className={`db-profile-verify ${ok ? 'db-profile-verify--ok' : 'db-profile-verify--no'}`}>
      {ok ? <IconCheck /> : <IconClock />}
      {label}
    </span>
  );
}

function getDocumentTypeLabel(filename: string): string {
  const lowerName = filename.toLowerCase();
  if (lowerName.includes('tin') || lowerName.includes('bin')) {
    return 'TIN/BIN Certification';
  }
  if (lowerName.includes('license') || lowerName.includes('licence')) {
    return 'Business License';
  }
  if (lowerName.includes('electric') || lowerName.includes('bill') || lowerName.includes('utility')) {
    return 'Office Electric Bill';
  }
  return 'Verification Document';
}

const SERVICE_LABELS: Record<string, string> = {
  media_planning: 'Media Planning',
  creative: 'Creative',
  ooh: 'OOH',
  digital: 'Digital',
  activation: 'Activation',
};

// ── Skeleton ──────────────────────────────────────────────────────────────────
function Skeleton() {
  return (
    <div className="db-profile-skeleton">
      <div className="db-profile-skeleton__block">
        <div className="db-profile-skeleton__banner" />
        <div className="db-profile-skeleton__row">
          <div className="db-profile-skeleton__circle" />
          <div className="db-profile-skeleton__lines">
            <div className="db-profile-skeleton__line db-profile-skeleton__line--mid" />
            <div className="db-profile-skeleton__line db-profile-skeleton__line--short" />
          </div>
        </div>
      </div>
      {[1, 2].map((i) => (
        <div key={i} className="db-profile-skeleton__block">
          <div className="db-profile-skeleton__fields">
            {[1, 2, 3, 4].map((j) => (
              <div key={j} className="db-profile-skeleton__field">
                <div className="db-profile-skeleton__field-label" />
                <div className="db-profile-skeleton__field-val" />
              </div>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}

// ── Agency details sub-section ─────────────────────────────────────────────────
function AgencyDetails({ org }: { org: ProfileOrganization }) {
  const details = org.agencyDetails;
  if (!details) return null;

  return (
    <>
      <hr style={{ border: 'none', borderTop: '1px solid var(--color-gray-100, #f3f4f6)', margin: '0.25rem 0' }} />
      <div className="db-profile-fields-grid">
        <Field label="Portfolio Size" value={details.portfolioSize} />
        <Field label="Owns Inventory" value={details.ownsInventory ? 'Yes' : 'No'} />
        <Field label="Operations Contact" value={details.operationsContact} />
        <Field label="Finance Contact" value={details.financeContact} />
        <Field label="Office Address" value={org.fullAddress} />
      </div>

      {details.agencyServices.length > 0 && (
        <div className="db-profile-field">
          <span className="db-profile-field__label">Services Offered</span>
          <div className="db-profile-tags" style={{ marginTop: '0.25rem' }}>
            {details.agencyServices.map((s) => (
              <span key={s} className="db-profile-tag">{SERVICE_LABELS[s] ?? s}</span>
            ))}
          </div>
        </div>
      )}
    </>
  );
}

// ── Service Coverage Card ──────────────────────────────────────────────────────
function ServiceCoverageCard({ org, onEdit }: { org: ProfileOrganization; onEdit: () => void }) {
  const districts = org.districts ?? [];

  return (
    <div className="db-profile-card db-profile-card--full">
      <div className="db-profile-card__head">
        <span className="db-profile-card__head-icon"><IconMap /></span>
        <h2 className="db-profile-card__title">Service Coverage</h2>
        <button className="db-profile-card__edit-btn" type="button" onClick={onEdit}>
          <IconEdit /> Edit
        </button>
      </div>
      <div className="db-profile-card__body">
        <div className="db-profile-fields-grid">   
          <Field label="Nationwide" value={org.nationwideService ? 'Yes' : 'No'} />
          <Field label="International" value={org.internationalService ? 'Yes' : 'No'} />
          <Field label="Division" value={org.divisions?.map(d => d.name).join(', ')} />
        </div>

        {districts.length > 0 && (
          <div className="db-profile-field" style={{ marginTop: '1rem' }}>
            <span className="db-profile-field__label">Coverage District{districts.length > 1 ? 's' : ''}</span>
            <div className="db-profile-tags" style={{ marginTop: '0.25rem' }}>
              {districts.map((r) => (
                <span key={r.id} className="db-profile-tag">{r.name}</span>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

// ── Organisation card ──────────────────────────────────────────────────────────
function OrgCard({ org, onEdit }: { org: ProfileOrganization; onEdit?: () => void }) {
  const isVerified = org.verificationStatus === 'verified';

  return (
    <div className="db-profile-card db-profile-card--full">
      <div className="db-profile-card__head">
        <span className="db-profile-card__head-icon"><IconBuilding /></span>
        <h2 className="db-profile-card__title">Agency Information</h2>
        {onEdit && (
          <button className="db-profile-card__edit-btn" type="button" onClick={onEdit}>
            <IconEdit /> Edit
          </button>
        )}
        {isVerified && (
          <span className="db-profile-hero__verified" style={{ marginLeft: onEdit ? '0.5rem' : 'auto' }}><IconCheck /> Verified</span>
        )}
      </div>

      <div className="db-profile-card__body">
        {/* Progress bars */}
        <div className="db-profile-fields-grid">
          <Bar label="Profile Completion" value={org.profileCompletion} />
          <Bar label="Trust Score" value={org.trustScore} />
        </div>

        <hr style={{ border: 'none', borderTop: '1px solid var(--color-gray-100, #f3f4f6)', margin: '0.25rem 0' }} />

        {/* Core fields */}
        <div className="db-profile-fields-grid">
          <Field label="Agency Name" value={org.name} />
          <Field label="Official Email" value={org.officialEmail} />
          <Field label="Official Phone" value={org.officialPhone} />
          <Field label="Website" value={org.website} />
          <Field label="Est. Year" value={org.establishmentYear} />
          <Field label="Business Reg. No." value={org.businessRegNumber} />
          <Field label="TIN" value={org.tin} />
        </div>

        {/* Agency-specific */}
        <AgencyDetails org={org} />
      </div>
    </div>
  );
}

// ── Page ──────────────────────────────────────────────────────────────────────
export default function AgencyProfilePage() {
  const router = useRouter();
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [editPersonal, setEditPersonal] = useState(false);
  const [editOrg, setEditOrg] = useState(false);
  const [editLogo, setEditLogo] = useState(false);
  const [editCoverage, setEditCoverage] = useState(false);
  const [editDocuments, setEditDocuments] = useState(false);

  // Email verification UI state using custom hook
  const [isEmailVerificationModalOpen, setIsEmailVerificationModalOpen] = useState(false);
  const [emailVerificationCode, setEmailVerificationCode] = useState('');
  const [emailVerificationMessage, setEmailVerificationMessage] = useState('');

  // Phone verification UI state
  const [isPhoneVerificationModalOpen, setIsPhoneVerificationModalOpen] = useState(false);
  const [phoneVerificationCode, setPhoneVerificationCode] = useState('');
  const [phoneVerificationMessage, setPhoneVerificationMessage] = useState('');

  const {
    sendCode: sendEmailCode,
    verifyCode: verifyEmailCode,
    sending: sendingEmail,
    verifying: verifyingEmail,
    error: emailVerificationError,
    retryAfter: emailRetryAfter,
    clearError: clearEmailError,
  } = useOtpVerification('email', {
    onSendSuccess: (data) => {
      setEmailVerificationMessage(`A 6-digit code was sent to ${data?.data?.email ?? 'your email'}.`);
      setIsEmailVerificationModalOpen(true);
      setEmailVerificationCode('');
    },
    onVerifySuccess: (data) => {
      setEmailVerificationMessage('Email has been verified successfully.');
      setIsEmailVerificationModalOpen(false);
      // Update local UI state
      setProfile((prev) => {
        if (!prev) return prev;
        const updatedUser = { ...prev.user, emailVerified: true };
        return { ...prev, user: updatedUser };
      });
    },
  });

  const {
    sendCode: sendPhoneCode,
    verifyCode: verifyPhoneCode,
    sending: sendingPhone,
    verifying: verifyingPhone,
    error: phoneVerificationError,
    retryAfter: phoneRetryAfter,
    clearError: clearPhoneError,
  } = useOtpVerification('phone', {
    onSendSuccess: (data) => {
      setPhoneVerificationMessage(`A 6-digit code was sent to ${data?.data?.phone ?? 'your phone'}.`);
      setIsPhoneVerificationModalOpen(true);
      setPhoneVerificationCode('');
    },
    onVerifySuccess: (data) => {
      setPhoneVerificationMessage('Phone has been verified successfully.');
      setIsPhoneVerificationModalOpen(false);
      // Update local UI state
      setProfile((prev) => {
        if (!prev) return prev;
        const updatedUser = { ...prev.user, phoneVerified: true };
        return { ...prev, user: updatedUser };
      });
    },
  });

  useEffect(() => {
    if (!authAPI.isLoggedIn()) {
      router.replace('/login?next=/agency/profile');
      return;
    }
    profileAPI.get()
      .then(setProfile)
      .catch((err) => {
        if (err instanceof ProfileAuthError) {
          authAPI.clearLocalSession();
          router.replace('/login?next=/agency/profile');
          return;
        }
        setError('Failed to load profile. Please try again.');
      })
      .finally(() => setLoading(false));
  }, [router]);

  const user = profile?.user;
  const org = profile?.organizations.find((o) => o.isActive) ?? profile?.organizations[0];
  const initials = org?.name
    ? org.name.split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase()
    : 'LOGO';

  // ----- Email verification handler -----
  const verifyEmailCodeHandler = async (code: string) => {
    try {
      await verifyEmailCode(code);
    } catch (err) {
      console.error('verifyEmailCode failed', err);
    }
  };

  // ----- Phone verification handler -----
  const verifyPhoneCodeHandler = async (code: string) => {
    try {
      await verifyPhoneCode(code);
    } catch (err) {
      console.error('verifyPhoneCode failed', err);
    }
  };



  return (
    <div className="db-shell">
      <DashboardSidebar role="agency" />
      <div className="db-main">
        <DashboardTopbar role="agency" title="My Profile" subtitle="Manage your account and agency information" />
        <div className="db-content">
          {loading && <Skeleton />}

          {error && (
            <div className="db-panel" style={{ padding: '2rem', textAlign: 'center', color: 'var(--color-gray-500)' }}>
              {error}
            </div>
          )}

          {!loading && !error && profile && (
            <div className="db-profile">
              {/* ── Hero ── */}
              <div className="db-profile-hero">
                <div className="db-profile-hero__banner" />
                <div className="db-profile-hero__body">
                  <div className="db-profile-hero__avatar-wrap">
                     <div className="db-profile-hero__avatar db-profile-hero__avatar--logo">
                     <button
                      type="button"
                      className="db-profile-hero__avatar-edit"
                      onClick={() => setEditLogo(true)}
                      aria-label="Edit organization logo"
                    >
                      <svg width="12" height="16" fill="currentColor" viewBox="0 0 16 16">
  <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
</svg>
                    </button>
                    {org?.logo ? (
                      <Image src={org.logo} alt={`${org?.name} logo`} width={72} height={72} style={{ objectFit: 'contain' }} unoptimized />
                    ) : (
                      <div style={{ width: 72, height: 72, borderRadius: 12, background: '#d1d5db', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '1rem', fontWeight: 700, color: '#1f2937' }}>
                        {initials}
                      </div>
                    )}
                    </div>
                  </div>
                  <div className="db-profile-hero__meta">
                    <h1 className="db-profile-hero__name">{user?.name}</h1>
                    <div className="db-profile-hero__badges">
                      <span className="db-profile-hero__role">Agency User</span>
                      <VerifyTag ok={!!user?.emailVerified} label={user?.emailVerified ? 'Email Verified' : 'Email Unverified'} />
                      {user?.phoneVerified !== undefined && (
                        <VerifyTag ok={!!user.phoneVerified} label={user.phoneVerified ? 'Phone Verified' : 'Phone Unverified'} />
                      )}
                    </div>
                  </div>
                </div>
              </div>

              {/* ── Grid ── */}
              <div className="db-profile-grid">
                {/* Personal info */}
                <div className="db-profile-card">
                  <div className="db-profile-card__head">
                    <span className="db-profile-card__head-icon"><IconUser /></span>
                    <h2 className="db-profile-card__title">Personal Information</h2>
                    <button className="db-profile-card__edit-btn" type="button" onClick={() => setEditPersonal(true)}>
                      <IconEdit /> Edit
                    </button>
                  </div>
                  <div className="db-profile-card__body">
                    <Field label="Full Name" value={user?.name} />
                    <Field label="Email Address" value={user?.email} />
                    <Field label="Mobile Number" value={user?.mobileNumber} />
                    <Field label="Designation" value={user?.designation} />
                    <Field label="Department" value={user?.department} />
                  </div>
                </div>

                {/* Trust & verification */}
                <div className="db-profile-card">
                  <div className="db-profile-card__head">
                    <span className="db-profile-card__head-icon"><IconShield /></span>
                    <h2 className="db-profile-card__title">Trust & Verification</h2>
                  </div>
                  <div className="db-profile-card__body">
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', flexWrap: 'wrap' }}>
                        <VerifyTag ok={!!user?.emailVerified} label={user?.emailVerified ? 'Email verified' : 'Email not verified'} />
                        {!user?.emailVerified && (
                          <button
                            type="button"
                            className="db-profile-card__edit-btn"
                            onClick={sendEmailCode}
                            disabled={sendingEmail || emailRetryAfter > 0}
                            style={{ fontSize: '0.75rem', padding: '0.2rem 0.5rem', height: 'auto' }}
                          >
                            {sendingEmail ? 'Sending...' : emailRetryAfter > 0 ? `Wait ${emailRetryAfter}s` : 'Verify Email'}
                          </button>
                        )}
                      </div>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', flexWrap: 'wrap' }}>
                        <VerifyTag ok={!!user?.phoneVerified} label={user?.phoneVerified ? 'Phone verified' : 'Phone not verified'} />
                        {!user?.phoneVerified && user?.mobileNumber && (
                          <button
                            type="button"
                            className="db-profile-card__edit-btn"
                            onClick={sendPhoneCode}
                            disabled={sendingPhone || phoneRetryAfter > 0}
                            style={{ fontSize: '0.75rem', padding: '0.2rem 0.5rem', height: 'auto' }}
                          >
                            {sendingPhone ? 'Sending...' : phoneRetryAfter > 0 ? `Wait ${phoneRetryAfter}s` : 'Verify Phone'}
                          </button>
                        )}
                      </div>
                      <VerifyTag ok={org?.verificationDocsStatus === 'verified'} label={org?.verificationDocsStatus === 'verified' ? 'Documents verified' : org?.verificationDocsStatus === 'rejected' ? 'Documents rejected' : 'Documents pending'} />
                    </div>

                    {org?.verificationDocsStatus === 'verified' && (
                      <div className="db-profile-field" style={{ marginTop: '1rem' }}>
                        <span className="db-profile-field__label">Verification Status</span>
                        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.75rem', marginTop: '0.75rem' }}>
                          {user?.emailVerified && (
                            <span style={{
                              position: 'relative',
                              width: '3.5rem',
                              height: '3.5rem',
                              display: 'inline-flex',
                              alignItems: 'center',
                              justifyContent: 'center',
                              borderRadius: '50%',
                              backgroundColor: 'var(--color-green-50, #f0fdf4)',
                              border: '2px solid var(--color-green-200, #bbf7d0)',
                            }}>
                              <span style={{ position: 'absolute', width: '100%', height: '100%', color: 'var(--color-green-600, #16a34a)', opacity: 0.15 }}>
                                <IconVerifiedRibbon />
                              </span>
                              <span style={{ position: 'relative', width: '1.5rem', height: '1.5rem', color: 'var(--color-green-700, #15803d)' }}>
                                <IconEmailVerified />
                              </span>
                            </span>
                          )}
                          {user?.phoneVerified && (
                            <span style={{
                              position: 'relative',
                              width: '3.5rem',
                              height: '3.5rem',
                              display: 'inline-flex',
                              alignItems: 'center',
                              justifyContent: 'center',
                              borderRadius: '50%',
                              backgroundColor: 'var(--color-green-50, #f0fdf4)',
                              border: '2px solid var(--color-green-200, #bbf7d0)',
                            }}>
                              <span style={{ position: 'absolute', width: '100%', height: '100%', color: 'var(--color-green-600, #16a34a)', opacity: 0.15 }}>
                                <IconVerifiedRibbon />
                              </span>
                              <span style={{ position: 'relative', width: '1.5rem', height: '1.5rem', color: 'var(--color-green-700, #15803d)' }}>
                                <IconPhone />
                              </span>
                            </span>
                          )}
                          <span style={{
                            position: 'relative',
                            width: '3.5rem',
                            height: '3.5rem',
                            display: 'inline-flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            borderRadius: '50%',
                            backgroundColor: 'var(--color-green-50, #f0fdf4)',
                            border: '2px solid var(--color-green-200, #bbf7d0)',
                          }}>
                            <span style={{ position: 'absolute', width: '100%', height: '100%', color: 'var(--color-green-600, #16a34a)', opacity: 0.15 }}>
                              <IconVerifiedRibbon />
                            </span>
                            <span style={{ position: 'relative', width: '1.5rem', height: '1.5rem', color: 'var(--color-green-700, #15803d)' }}>
                              <IconDocumentVerified />
                            </span>
                          </span>
                        </div>
                      </div>
                    )}
                   
                    {org?.verificationDocsStatus !== 'verified' && (
                      <div className="db-profile-field" style={{ marginTop: '1rem' }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '0.5rem' }}>
                          <span className="db-profile-field__label">Verification Documents</span>
                          <button
                            type="button"
                            className="db-profile-card__edit-btn"
                            onClick={() => setEditDocuments(true)}
                            style={{ fontSize: '0.75rem', padding: '0.3rem 0.6rem', height: 'auto' }}
                          >
                            <IconEdit /> {org?.verificationDocuments && org.verificationDocuments.length > 0 ? 'Update Files' : 'Upload Documents'}
                          </button>
                        </div>

                        {org?.verificationDocsStatus && (
                          <div style={{ marginBottom: '0.5rem' }}>
                            <span className={`db-profile-verify ${
                              org.verificationDocsStatus === 'rejected' ? 'db-profile-verify--no' :
                              'db-profile-verify--pending'
                            }`}>
                              <IconClock />
                              {org.verificationDocsStatus === 'pending_review' ? 'Pending Review' :
                               org.verificationDocsStatus === 'rejected' ? 'Rejected' :
                               'Unknown Status'}
                            </span>
                          </div>
                        )}

                        {org?.verificationDocuments && org.verificationDocuments.length > 0 && (
                          <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem', marginTop: '0.25rem' }}>
                            {org.verificationDocuments.map((doc, idx) => (
                              <a
                                key={idx}
                                href={doc.url}
                                target="_blank"
                                rel="noopener noreferrer"
                                style={{
                                  display: 'flex',
                                  alignItems: 'center',
                                  gap: '0.5rem',
                                  padding: '0.5rem',
                                  borderRadius: '0.375rem',
                                  backgroundColor: 'var(--color-gray-50, #f9fafb)',
                                  textDecoration: 'none',
                                  color: 'var(--color-blue-600, #2563eb)',
                                  fontSize: '0.875rem',
                                  border: '1px solid var(--color-gray-200, #e5e7eb)',
                                }}
                              >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                  <polyline points="17 21 17 13 7 13 7 21" />
                                </svg>
                                <div style={{ display: 'flex', flexDirection: 'column' }}>
                                  <span>{getDocumentTypeLabel(doc.filename)}</span>
                                  {doc.description && (
                                    <span style={{ fontSize: '0.75rem', color: 'var(--color-gray-500)', marginTop: '0.125rem' }}>
                                      {doc.description}
                                    </span>
                                  )}
                                </div>
                              </a>
                            ))}
                          </div>
                        )}

                        {(!org?.verificationDocuments || org.verificationDocuments.length === 0) && (
                          <div style={{ padding: '1rem', textAlign: 'center', color: 'var(--color-gray-500)', fontSize: '0.875rem' }}>
                            No documents uploaded yet
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                </div>
              </div>

              {/* ── Agency org card (full width) ── */}
              {org && <OrgCard org={org} onEdit={() => setEditOrg(true)} />}

              {/* ── Service Coverage Card ── */}
              {org && <ServiceCoverageCard org={org} onEdit={() => setEditCoverage(true)} />}

              {/* ── Edit modals ── */}
              {editPersonal && user && (
                <EditPersonalModal
                  user={user}
                  onClose={() => setEditPersonal(false)}
                  onSaved={(updatedUser) => {
                    setProfile((prev) => prev ? { ...prev, user: updatedUser } : prev);
                  }}
                />
              )}

              {editOrg && org && (
                <EditOrganizationModal
                  org={org}
                  onClose={() => setEditOrg(false)}
                  onSaved={(updatedOrg) => {
                    setProfile((prev) => prev ? {
                      ...prev,
                      organizations: prev.organizations.map((o) =>
                        o.id === updatedOrg.id ? updatedOrg : o
                      ),
                    } : prev);
                  }}
                />
              )}

              {editLogo && org && (
                <EditOrganizationLogoModal
                  org={org}
                  onClose={() => setEditLogo(false)}
                  onSaved={(updatedOrg: ProfileOrganization) => {
                    setProfile((prev) => prev ? {
                      ...prev,
                      organizations: prev.organizations.map((o) =>
                        o.id === updatedOrg.id ? updatedOrg : o
                      ),
                    } : prev);
                  }}
                />
              )}

              {editCoverage && org && (
                <EditServiceCoverageModal
                  org={org}
                  onClose={() => setEditCoverage(false)}
                  onSaved={(updatedOrg) => {
                    setProfile((prev) => prev ? {
                      ...prev,
                      organizations: prev.organizations.map((o) =>
                        o.id === updatedOrg.id ? updatedOrg : o
                      ),
                    } : prev);
                  }}
                />
              )}

              {editDocuments && org && (
                <DocumentUploadModal
                  org={org}
                  onClose={() => setEditDocuments(false)}
                  onSaved={(updatedOrg) => {
                    setProfile((prev) => prev ? {
                      ...prev,
                      organizations: prev.organizations.map((o) =>
                        o.id === updatedOrg.id ? updatedOrg : o
                      ),
                    } : prev);
                  }}
                />
              )}

              {/* Email Verification Modal */}
              {isEmailVerificationModalOpen && (
                <EmailVerificationModal
                  email={user?.email ?? null}
                  onClose={() => {
                    setIsEmailVerificationModalOpen(false);
                    setEmailVerificationCode('');
                    clearEmailError();
                  }}
                  onVerify={verifyEmailCodeHandler}
                  verifying={verifyingEmail}
                  error={emailVerificationError}
                  message={emailVerificationMessage}
                />
              )}

              {/* Phone Verification Modal */}
              {isPhoneVerificationModalOpen && (
                <PhoneVerificationModal
                  phoneNumber={user?.mobileNumber ?? null}
                  onClose={() => {
                    setIsPhoneVerificationModalOpen(false);
                    setPhoneVerificationCode('');
                    clearPhoneError();
                  }}
                  onVerify={verifyPhoneCodeHandler}
                  verifying={verifyingPhone}
                  error={phoneVerificationError}
                  message={phoneVerificationMessage}
                />
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

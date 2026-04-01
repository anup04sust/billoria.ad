'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { authAPI } from '@/lib/api/auth';
import { profileAPI, ProfileAuthError } from '@/lib/api/profile';
import type { UserProfile, ProfileOrganization } from '@/lib/api/profile';
import { EditProfileModal } from '@/components/dashboard/EditProfileModal';
import '@/components/dashboard/dashboard-sidebar.css';
import '@/components/dashboard/dashboard-topbar.css';
import '@/components/dashboard/dashboard.css';
import '@/components/dashboard/profile.css';

// ── Icons ─────────────────────────────────────────────────────────────────────
const IconUser = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" /><circle cx="12" cy="7" r="4" />
  </svg>
);
const IconBuilding = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
    <rect x="3" y="3" width="18" height="18" rx="1" /><path d="M9 22V12h6v10M9 7h1m4 0h1M9 11h1m4 0h1" />
  </svg>
);
const IconShield = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
  </svg>
);
const IconMap = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
    <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" /><line x1="8" y1="2" x2="8" y2="18" /><line x1="16" y1="6" x2="16" y2="22" />
  </svg>
);
const IconEdit = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
  </svg>
);
const IconCheck = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
    <polyline points="20 6 9 17 4 12" />
  </svg>
);
const IconClock = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />
  </svg>
);

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
  const regions = details.preferredRegions;

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

      <hr style={{ border: 'none', borderTop: '1px solid var(--color-gray-100, #f3f4f6)', margin: '0.25rem 0' }} />
      <div className="db-profile-card__head" style={{ paddingBottom: '0.25rem' }}>
        <span className="db-profile-card__head-icon"><IconMap /></span>
        <h3 className="db-profile-card__title" style={{ fontSize: '0.875rem' }}>Service Coverage</h3>
      </div>
      <div className="db-profile-fields-grid">
        <Field label="Division" value={org.division?.name} />
        <Field label="District" value={org.district?.name} />
      </div>

      {regions.length > 0 && (
        <div className="db-profile-field">
          <span className="db-profile-field__label">Preferred Regions</span>
          <div className="db-profile-tags">
            {regions.map((r) => (
              <span key={r.id} className="db-profile-tag">{r.name}</span>
            ))}
          </div>
        </div>
      )}
    </>
  );
}

// ── Organisation card ──────────────────────────────────────────────────────────
function OrgCard({ org }: { org: ProfileOrganization }) {
  const isVerified = org.verificationStatus === 'verified';

  return (
    <div className="db-profile-card db-profile-card--full">
      <div className="db-profile-card__head">
        <span className="db-profile-card__head-icon"><IconBuilding /></span>
        <h2 className="db-profile-card__title">Agency Information</h2>
        <span style={{ marginLeft: 'auto' }}>
          {isVerified ? (
            <span className="db-profile-hero__verified"><IconCheck /> Verified</span>
          ) : (
            <span className="db-profile-hero__verified db-profile-hero__verified--pending"><IconClock /> {org.verificationStatus}</span>
          )}
        </span>
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
  const [editOpen, setEditOpen] = useState(false);

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
  const initials = user?.name
    ? user.name.split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase()
    : 'U';

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
                  <div className="db-profile-hero__avatar">{initials}</div>
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
                  <div className="db-profile-hero__actions">
                    <button className="db-profile-hero__edit-btn" type="button" onClick={() => setEditOpen(true)}>
                      <IconEdit /> Edit Profile
                    </button>
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
                    <Bar label="Trust Score" value={user?.trustScore ?? 0} />
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem', marginTop: '0.5rem' }}>
                      <VerifyTag ok={!!user?.emailVerified} label={user?.emailVerified ? 'Email verified' : 'Email not verified'} />
                      <VerifyTag ok={!!user?.phoneVerified} label={user?.phoneVerified ? 'Phone verified' : 'Phone not verified'} />
                    </div>
                    {user?.roles && (
                      <div className="db-profile-field">
                        <span className="db-profile-field__label">Assigned Roles</span>
                        <div className="db-profile-tags" style={{ marginTop: '0.25rem' }}>
                          {user.roles.map((r) => (
                            <span key={r} className="db-profile-tag">{r.replace(/_/g, ' ')}</span>
                          ))}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>

              {/* ── Agency org card (full width) ── */}
              {org && <OrgCard org={org} />}

              {/* ── Edit modal ── */}
              {editOpen && user && (
                <EditProfileModal
                  user={user}
                  org={org}
                  onClose={() => setEditOpen(false)}
                  onSaved={(updatedUser, updatedOrg) => {
                    setProfile((prev) => prev ? {
                      ...prev,
                      user: updatedUser,
                      organizations: prev.organizations.map((o) =>
                        updatedOrg && o.id === updatedOrg.id ? updatedOrg : o
                      ),
                    } : prev);
                  }}
                />
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

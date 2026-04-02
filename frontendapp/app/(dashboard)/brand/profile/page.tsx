'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { authAPI } from '@/lib/api/auth';
import { profileAPI, ProfileAuthError } from '@/lib/api/profile';
import type { UserProfile, ProfileOrganization } from '@/lib/api/profile';
import { EditPersonalModal } from '@/components/dashboard/EditPersonalModal';
import { EditOrganizationModal } from '@/components/dashboard/EditOrganizationModal';
import { EditServiceCoverageModal } from '@/components/dashboard/EditServiceCoverageModal';
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
} from '@/lib/icons/ui-icons';

// ── Helpers ──────────────────────────────────────────────────────────────────
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

const BUDGET_LABELS: Record<string, string> = {
  under_1l: 'Under ৳1 Lakh',
  '1l_5l': '৳1L – ৳5L',
  '5l_20l': '৳5L – ৳20L',
  '20l_1cr': '৳20L – ৳1 Crore',
  above_1cr: 'Above ৳1 Crore',
};

const DURATION_LABELS: Record<string, string> = {
  weekly: 'Weekly',
  monthly: 'Monthly',
  quarterly: 'Quarterly',
  seasonal: 'Seasonal',
  annual: 'Annual',
};

const SERVICE_LABELS: Record<string, string> = {
  media_planning: 'Media Planning',
  creative: 'Creative',
  ooh: 'OOH',
  digital: 'Digital',
  activation: 'Activation',
};

// ── Skeleton ─────────────────────────────────────────────────────────────────
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

// ── Organisation card ─────────────────────────────────────────────────────────
function OrgCard({ org, onEdit }: { org: ProfileOrganization; onEdit?: () => void }) {
  const isVerified = org.verificationStatus === 'verified';


  return (
    <div className="db-profile-card db-profile-card--full">
      {/* Head */}
      <div className="db-profile-card__head">
        <span className="db-profile-card__head-icon"><IconBuilding /></span>
        <h2 className="db-profile-card__title">
          {org.type === 'brand' ? 'Brand' : org.type === 'agency' ? 'Agency' : 'Organization'} Information
        </h2>
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
          <Field label="Organization Name" value={org.name} />
          <Field label="Official Email" value={org.officialEmail} />
          <Field label="Official Phone" value={org.officialPhone} />
          <Field label="Website" value={org.website} />
          <Field label="Est. Year" value={org.establishmentYear} />
          <Field label="Business Reg. No." value={org.businessRegNumber} />
          <Field label="TIN" value={org.tin} />
        </div>

        {/* Brand-specific */}
        {org.brandDetails && (
          <>
            <hr style={{ border: 'none', borderTop: '1px solid var(--color-gray-100, #f3f4f6)', margin: '0.25rem 0' }} />
            <div className="db-profile-fields-grid">
              <Field label="Parent Company" value={org.brandDetails.parentCompany} />
              <Field
                label="Annual Budget Range"
                value={org.brandDetails.annualBudgetRange ? BUDGET_LABELS[org.brandDetails.annualBudgetRange] ?? org.brandDetails.annualBudgetRange : null}
              />
              <Field
                label="Preferred Booking Duration"
                value={org.brandDetails.bookingDuration ? DURATION_LABELS[org.brandDetails.bookingDuration] ?? org.brandDetails.bookingDuration : null}
              />
            </div>
          </>
        )}

        {/* Agency-specific */}
        {org.agencyDetails && (
          <>
            <hr style={{ border: 'none', borderTop: '1px solid var(--color-gray-100, #f3f4f6)', margin: '0.25rem 0' }} />
            <div className="db-profile-fields-grid">
              <Field label="Portfolio Size" value={org.agencyDetails.portfolioSize} />
              <Field label="Owns Inventory" value={org.agencyDetails.ownsInventory ? 'Yes' : 'No'} />
              <Field label="Operations Contact" value={org.agencyDetails.operationsContact} />
              <Field label="Finance Contact" value={org.agencyDetails.financeContact} />
            </div>
            {org.agencyDetails.agencyServices.length > 0 && (
              <div className="db-profile-field">
                <span className="db-profile-field__label">Services</span>
                <div className="db-profile-tags">
                  {org.agencyDetails.agencyServices.map((s) => (
                    <span key={s} className="db-profile-tag">{SERVICE_LABELS[s] ?? s}</span>
                  ))}
                </div>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}

// ── Service Coverage Card ──────────────────────────────────────────────────────
function ServiceCoverageCard({ org, onEdit }: { org: ProfileOrganization; onEdit: () => void }) {
  const regions = org.agencyDetails?.preferredRegions ?? org.brandDetails?.preferredRegions ?? [];

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
          <Field label="Division" value={org.division?.name} />
          <Field label="District" value={org.district?.name} />
          <Field label="Office Address" value={org.fullAddress} />
          <Field label="Nationwide" value={org.nationwideService ? 'Yes' : 'No'} />
          <Field label="International" value={org.internationalService ? 'Yes' : 'No'} />
        </div>

        {regions.length > 0 && (
          <div className="db-profile-field" style={{ marginTop: '1rem' }}>
            <span className="db-profile-field__label">Preferred Regions</span>
            <div className="db-profile-tags" style={{ marginTop: '0.25rem' }}>
              {regions.map((r) => (
                <span key={r.id} className="db-profile-tag">{r.name}</span>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

// ── Page ──────────────────────────────────────────────────────────────────────
export default function BrandProfilePage() {
  const router = useRouter();
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [editPersonal, setEditPersonal] = useState(false);
  const [editOrg, setEditOrg] = useState(false);
  const [editCoverage, setEditCoverage] = useState(false);

  useEffect(() => {
    if (!authAPI.isLoggedIn()) {
      router.replace('/login?next=/brand/profile');
      return;
    }
    profileAPI.get()
      .then(setProfile)
      .catch((err) => {
        if (err instanceof ProfileAuthError) {
          authAPI.clearLocalSession();
          router.replace('/login?next=/brand/profile');
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
      <DashboardSidebar role="brand" />
      <div className="db-main">
        <DashboardTopbar role="brand" title="My Profile" subtitle="Manage your account and brand information" />
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
                      <span className="db-profile-hero__role">Brand User</span>
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
                {/* User info */}
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

                {/* Security / trust */}
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

              {/* ── Organisation card (full width) ── */}
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
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

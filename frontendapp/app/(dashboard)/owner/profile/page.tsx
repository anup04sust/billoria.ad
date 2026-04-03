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

export default function OwnerProfilePage() {
  const router = useRouter();
  const [user, setUser] = useState<ReturnType<typeof authAPI.getCurrentUser>>(null);
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [organization, setOrganization] = useState<ProfileOrganization | null>(null);
  const [editPersonal, setEditPersonal] = useState(false);
  const [editOrg, setEditOrg] = useState(false);
  const [editCoverage, setEditCoverage] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const currentUser = authAPI.getCurrentUser();
    if (!currentUser || !currentUser.roles.includes('billboard_owner')) {
      router.replace('/login?next=/owner/profile');
      return;
    }
    setUser(currentUser);

    async function fetchProfile() {
      try {
        const data = await profileAPI.getProfile();
        setProfile(data.user);
        setOrganization(data.organization);
      } catch (err) {
        if (err instanceof ProfileAuthError) {
          router.replace('/login?next=/owner/profile');
        }
      } finally {
        setLoading(false);
      }
    }
    fetchProfile();
  }, [router]);

  if (!user || loading) {
    return (
      <div className="db-container">
        <DashboardSidebar />
        <div className="db-main">
          <DashboardTopbar />
          <div className="db-content">
            <div className="db-loading">Loading profile...</div>
          </div>
        </div>
      </div>
    );
  }

  const completeness = profile?.profile_completeness ?? 0;

  return (
    <div className="db-container">
      <DashboardSidebar />
      <div className="db-main">
        <DashboardTopbar />
        <div className="db-content">
          <div className="db-profile-header">
            <div>
              <h1 className="db-profile-title">My Profile</h1>
              <p className="db-profile-subtitle">Billboard Owner Account</p>
            </div>
          </div>

          {/* Profile Completeness */}
          {completeness < 100 && (
            <div className="db-profile-alert">
              <IconClock />
              <div>
                <strong>Complete your profile</strong>
                <p>A complete profile helps advertisers trust and contact you more easily.</p>
              </div>
            </div>
          )}

          <div className="db-profile-grid">
            {/* Personal Information */}
            <section className="db-profile-card">
              <div className="db-profile-card__header">
                <div className="db-profile-card__header-left">
                  <IconUser />
                  <h2 className="db-profile-card__title">Personal Information</h2>
                </div>
                <button className="db-profile-card__edit-btn" type="button" onClick={() => setEditPersonal(true)}>
                  <IconEdit /> Edit
                </button>
              </div>
              <div className="db-profile-card__body">
                <Field label="Full Name" value={profile?.full_name} />
                <Field label="Email" value={profile?.email} />
                <Field label="Mobile Number" value={profile?.mobile_number} />
                <Field label="NID/Passport" value={profile?.nid_passport} />
                <Bar label="Personal Completeness" value={profile?.personal_completeness ?? 0} />
              </div>
            </section>

            {/* Organization */}
            {organization && (
              <section className="db-profile-card">
                <div className="db-profile-card__header">
                  <div className="db-profile-card__header-left">
                    <IconBuilding />
                    <h2 className="db-profile-card__title">Organization Details</h2>
                  </div>
                  <button className="db-profile-card__edit-btn" type="button" onClick={() => setEditOrg(true)}>
                    <IconEdit /> Edit
                  </button>
                </div>
                <div className="db-profile-card__body">
                  <Field label="Organization Name" value={organization.name} />
                  <Field label="Type" value={organization.type} />
                  <Field label="Registration Number" value={organization.registration_number} />
                  <Field label="Address" value={organization.address} />
                  <Field label="Phone" value={organization.phone} />
                  <Field label="Email" value={organization.email} />
                  <Bar label="Organization Completeness" value={organization.completeness ?? 0} />
                </div>
              </section>
            )}

            {/* Service Coverage */}
            <section className="db-profile-card">
              <div className="db-profile-card__header">
                <div className="db-profile-card__header-left">
                  <IconMap />
                  <h2 className="db-profile-card__title">Service Coverage</h2>
                </div>
                <button className="db-profile-card__edit-btn" type="button" onClick={() => setEditCoverage(true)}>
                  <IconEdit /> Edit
                </button>
              </div>
              <div className="db-profile-card__body">
                <Field label="Districts" value={profile?.coverage_districts?.join(', ')} />
                <Field label="Upazilas" value={profile?.coverage_upazilas?.join(', ')} />
                <Bar label="Coverage Completeness" value={profile?.coverage_completeness ?? 0} />
              </div>
            </section>

            {/* Verification Status */}
            <section className="db-profile-card">
              <div className="db-profile-card__header">
                <div className="db-profile-card__header-left">
                  <IconShield />
                  <h2 className="db-profile-card__title">Verification Status</h2>
                </div>
              </div>
              <div className="db-profile-card__body">
                <div className="db-profile-verify-item">
                  {profile?.mobile_verified ? <IconCheck className="verified" /> : <IconClock />}
                  <span>Mobile Number {profile?.mobile_verified ? 'Verified' : 'Pending'}</span>
                </div>
                <div className="db-profile-verify-item">
                  {profile?.email_verified ? <IconCheck className="verified" /> : <IconClock />}
                  <span>Email {profile?.email_verified ? 'Verified' : 'Pending'}</span>
                </div>
                <div className="db-profile-verify-item">
                  {profile?.nid_verified ? <IconCheck className="verified" /> : <IconClock />}
                  <span>NID/Passport {profile?.nid_verified ? 'Verified' : 'Pending'}</span>
                </div>
              </div>
            </section>
          </div>

          {/* Modals */}
          {editPersonal && user && (
            <EditPersonalModal
              user={user}
              onClose={() => setEditPersonal(false)}
              onSuccess={() => window.location.reload()}
            />
          )}
          {editOrg && organization && (
            <EditOrganizationModal
              organization={organization}
              onClose={() => setEditOrg(false)}
              onSuccess={() => window.location.reload()}
            />
          )}
          {editCoverage && (
            <EditServiceCoverageModal
              onClose={() => setEditCoverage(false)}
              onSuccess={() => window.location.reload()}
            />
          )}
        </div>
      </div>
    </div>
  );
}

'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { authAPI } from '@/lib/api/auth';
import { profileAPI, ProfileAuthError } from '@/lib/api/profile';
import type { UserProfile } from '@/lib/api/profile';
import { EditPersonalModal } from '@/components/dashboard/EditPersonalModal';
import '@/components/dashboard/dashboard-sidebar.css';
import '@/components/dashboard/dashboard-topbar.css';
import '@/components/dashboard/dashboard.css';
import '@/components/dashboard/profile.css';
import {
  IconUser,
  IconShield,
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

export default function AdminProfilePage() {
  const router = useRouter();
  const [user, setUser] = useState<ReturnType<typeof authAPI.getCurrentUser>>(null);
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [editPersonal, setEditPersonal] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const currentUser = authAPI.getCurrentUser();
    if (!currentUser || !currentUser.roles.includes('platform_admin')) {
      router.replace('/login?next=/admin/profile');
      return;
    }
    setUser(currentUser);

    async function fetchProfile() {
      try {
        const data = await profileAPI.getProfile();
        setProfile(data.user);
      } catch (err) {
        if (err instanceof ProfileAuthError) {
          router.replace('/login?next=/admin/profile');
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

  return (
    <div className="db-container">
      <DashboardSidebar />
      <div className="db-main">
        <DashboardTopbar />
        <div className="db-content">
          <div className="db-profile-header">
            <div>
              <h1 className="db-profile-title">My Profile</h1>
              <p className="db-profile-subtitle">Platform Administrator Account</p>
            </div>
          </div>

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

            {/* Admin Privileges */}
            <section className="db-profile-card">
              <div className="db-profile-card__header">
                <div className="db-profile-card__header-left">
                  <IconShield />
                  <h2 className="db-profile-card__title">Administrator Privileges</h2>
                </div>
              </div>
              <div className="db-profile-card__body">
                <div className="db-profile-verify-item">
                  <IconCheck className="verified" />
                  <span>Full Platform Access</span>
                </div>
                <div className="db-profile-verify-item">
                  <IconCheck className="verified" />
                  <span>User Management</span>
                </div>
                <div className="db-profile-verify-item">
                  <IconCheck className="verified" />
                  <span>Content Moderation</span>
                </div>
                <div className="db-profile-verify-item">
                  <IconCheck className="verified" />
                  <span>System Configuration</span>
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
        </div>
      </div>
    </div>
  );
}

'use client';

import { useState, useEffect, useRef } from 'react';
import { profileAPI } from '@/lib/api/profile';
import type { ProfileUser, ProfileOrganization, UpdateUserPayload, UpdateOrgPayload } from '@/lib/api/profile';
import profileOptions from '@/data/profile-options.json';
import './edit-profile-modal.css';

// ── Icons ─────────────────────────────────────────────────────────────────────
const IconX = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
  </svg>
);
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

// ── Constants ─────────────────────────────────────────────────────────────────
const BUDGET_OPTIONS = [
  { value: 'under_1l',   label: 'Under ৳1 Lakh' },
  { value: '1l_5l',      label: '৳1L – ৳5L' },
  { value: '5l_20l',     label: '৳5L – ৳20L' },
  { value: '20l_1cr',    label: '৳20L – ৳1 Crore' },
  { value: 'above_1cr',  label: 'Above ৳1 Crore' },
];

const DURATION_OPTIONS = [
  { value: 'weekly',    label: 'Weekly' },
  { value: 'monthly',   label: 'Monthly' },
  { value: 'quarterly', label: 'Quarterly' },
  { value: 'seasonal',  label: 'Seasonal' },
  { value: 'annual',    label: 'Annual' },
];

const SERVICE_OPTIONS = [
  { value: 'media_planning', label: 'Media Planning' },
  { value: 'creative',       label: 'Creative' },
  { value: 'ooh',            label: 'OOH' },
  { value: 'digital',        label: 'Digital' },
  { value: 'activation',     label: 'Activation' },
];

// ── Props ─────────────────────────────────────────────────────────────────────
export interface EditProfileModalProps {
  user: ProfileUser;
  org?: ProfileOrganization;
  section?: 'personal' | 'organization';
  onClose: () => void;
  onSaved: (updatedUser: ProfileUser, updatedOrg?: ProfileOrganization) => void;
}

type Tab = 'personal' | 'organization';

// ── Component ─────────────────────────────────────────────────────────────────
export function EditProfileModal({ user, org, section, onClose, onSaved }: EditProfileModalProps) {
  const initialTab = section || (org ? 'personal' : 'personal');
  const [tab, setTab] = useState<Tab>(initialTab);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const overlayRef = useRef<HTMLDivElement>(null);

  // ── Personal fields ──────────────────────────────────────────────────────
  const [fullName, setFullName]           = useState(user.name ?? '');
  const [mobileNumber, setMobileNumber]   = useState(user.mobileNumber ?? '');
  const [designation, setDesignation]     = useState(user.designation ?? '');
  const [department, setDepartment]       = useState(user.department ?? '');

  // ── Org core fields ──────────────────────────────────────────────────────
  const [officialEmail, setOfficialEmail] = useState(org?.officialEmail ?? '');
  const [officialPhone, setOfficialPhone] = useState(org?.officialPhone ?? '');
  const [website, setWebsite]             = useState(org?.website ?? '');
  const [fullAddress, setFullAddress]     = useState(org?.fullAddress ?? '');
  const [bizReg, setBizReg]               = useState(org?.businessRegNumber ?? '');
  const [tin, setTin]                     = useState(org?.tin ?? '');
  const [estYear, setEstYear]             = useState(org?.establishmentYear?.toString() ?? '');

  // ── Brand fields ─────────────────────────────────────────────────────────
  const [parentCompany, setParentCompany]   = useState(org?.brandDetails?.parentCompany ?? '');
  const [budgetRange, setBudgetRange]       = useState(org?.brandDetails?.annualBudgetRange ?? '');
  const [bookingDuration, setBookingDuration] = useState(org?.brandDetails?.bookingDuration ?? '');

  // ── Agency fields ────────────────────────────────────────────────────────
  const [portfolioSize, setPortfolioSize]   = useState(org?.agencyDetails?.portfolioSize ?? '');
  const [ownsInventory, setOwnsInventory]   = useState(org?.agencyDetails?.ownsInventory ?? false);
  const [opsContact, setOpsContact]         = useState(org?.agencyDetails?.operationsContact ?? '');
  const [financeContact, setFinanceContact] = useState(org?.agencyDetails?.financeContact ?? '');
  const [services, setServices]             = useState<string[]>(org?.agencyDetails?.agencyServices ?? []);

  // Freeze body scroll while modal is open
  useEffect(() => {
    const prev = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => { document.body.style.overflow = prev; };
  }, []);

  // Close on Escape key
  useEffect(() => {
    function onKey(e: KeyboardEvent) { if (e.key === 'Escape') onClose(); }
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  function toggleService(val: string) {
    setServices((prev) => prev.includes(val) ? prev.filter((s) => s !== val) : [...prev, val]);
  }

  async function handleSave() {
    setSaving(true);
    setError('');
    try {
      let savedUser = user;
      let savedOrg  = org;

      if (tab === 'personal') {
        const payload: UpdateUserPayload = {
          name:         fullName     || null,
          mobileNumber: mobileNumber || null,
          designation:  designation  || null,
          department:   department   || null,
        };
        savedUser = await profileAPI.updateUser(payload);
      }

      if (tab === 'organization' && org) {
        const payload: UpdateOrgPayload = {
          officialEmail:     officialEmail     || null,
          officialPhone:     officialPhone     || null,
          website:           website           || null,
          fullAddress:       fullAddress       || null,
          businessRegNumber: bizReg            || null,
          tin:               tin               || null,
          establishmentYear: estYear ? parseInt(estYear, 10) : null,
        };

        if (org.type === 'brand') {
          payload.parentCompany   = parentCompany   || null;
          payload.annualBudgetRange = budgetRange   || null;
          payload.bookingDuration = bookingDuration || null;
        }
        if (org.type === 'agency') {
          payload.portfolioSize     = portfolioSize   || null;
          payload.ownsInventory     = ownsInventory;
          payload.operationsContact = opsContact      || null;
          payload.financeContact    = financeContact  || null;
          payload.agencyServices    = services;
        }

        savedOrg = await profileAPI.updateOrganization(org.id, payload);
      }

      onSaved(savedUser, savedOrg);
      onClose();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Save failed. Please try again.');
    } finally {
      setSaving(false);
    }
  }

  return (
    <div
      className="epm-overlay"
      ref={overlayRef}
      onClick={(e) => { if (e.target === overlayRef.current) onClose(); }}
      role="dialog"
      aria-modal="true"
      aria-label="Edit Profile"
    >
      <div className="epm-modal">
        {/* Header */}
        <div className="epm-header">
          <h2 className="epm-title">Edit Profile</h2>
          <button className="epm-close" onClick={onClose} type="button" aria-label="Close"><IconX /></button>
        </div>

        {/* Tabs */}
        <div className="epm-tabs">
          <button
            className={`epm-tab ${tab === 'personal' ? 'epm-tab--active' : ''}`}
            onClick={() => { setTab('personal'); setError(''); }}
            type="button"
          >
            <IconUser /> Personal
          </button>
          {org && (
            <button
              className={`epm-tab ${tab === 'organization' ? 'epm-tab--active' : ''}`}
              onClick={() => { setTab('organization'); setError(''); }}
              type="button"
            >
              <IconBuilding /> Organization
            </button>
          )}
        </div>

        {/* Body */}
        <div className="epm-body">
          {/* ── Personal tab ── */}
          {tab === 'personal' && (
            <div className="epm-section">
              <div className="epm-row">
                <label className="epm-label">Username</label>
                <input className="epm-input epm-input--readonly" value={user.username} readOnly />
              </div>
              <div className="epm-row">
                <label className="epm-label">Full Name</label>
                <input
                  className="epm-input"
                  value={fullName}
                  onChange={(e) => setFullName(e.target.value)}
                  placeholder="Your full name"
                  type="text"
                />
              </div>
              <div className="epm-row">
                <label className="epm-label">Email Address</label>
                <input className="epm-input epm-input--readonly" value={user.email} readOnly />
              </div>
              <div className="epm-row">
                <label className="epm-label">Mobile Number</label>
                <input
                  className="epm-input"
                  value={mobileNumber}
                  onChange={(e) => setMobileNumber(e.target.value)}
                  placeholder="+880XXXXXXXXXX"
                  type="tel"
                />
              </div>
              <div className="epm-grid">
                <div className="epm-row">
                  <label className="epm-label">Designation</label>
                  <input
                    className="epm-input"
                    value={designation}
                    onChange={(e) => setDesignation(e.target.value)}
                    placeholder="e.g. Marketing Manager"
                    list="designation-options"
                  />
                  <datalist id="designation-options">
                    {profileOptions.designations.map((option) => (
                      <option key={option} value={option} />
                    ))}
                  </datalist>
                </div>
                <div className="epm-row">
                  <label className="epm-label">Department</label>
                  <input
                    className="epm-input"
                    value={department}
                    onChange={(e) => setDepartment(e.target.value)}
                    placeholder="e.g. Marketing"
                    list="department-options"
                  />
                  <datalist id="department-options">
                    {profileOptions.departments.map((option) => (
                      <option key={option} value={option} />
                    ))}
                  </datalist>
                </div>
              </div>
            </div>
          )}

          {/* ── Organization tab ── */}
          {tab === 'organization' && org && (
            <div className="epm-section">
              <div className="epm-grid">
                <div className="epm-row">
                  <label className="epm-label">Official Email</label>
                  <input className="epm-input" value={officialEmail} onChange={(e) => setOfficialEmail(e.target.value)} type="email" placeholder="office@company.com" />
                </div>
                <div className="epm-row">
                  <label className="epm-label">Official Phone</label>
                  <input className="epm-input" value={officialPhone} onChange={(e) => setOfficialPhone(e.target.value)} type="tel" placeholder="+880XXXXXXXXXX" />
                </div>
                <div className="epm-row">
                  <label className="epm-label">Website</label>
                  <input className="epm-input" value={website} onChange={(e) => setWebsite(e.target.value)} type="url" placeholder="https://example.com" />
                </div>
                <div className="epm-row">
                  <label className="epm-label">Est. Year</label>
                  <input className="epm-input" value={estYear} onChange={(e) => setEstYear(e.target.value)} type="number" placeholder="2010" min="1900" max="2100" />
                </div>
                <div className="epm-row">
                  <label className="epm-label">Business Reg. No.</label>
                  <input className="epm-input" value={bizReg} onChange={(e) => setBizReg(e.target.value)} placeholder="RJSC-XXXXXX" />
                </div>
                <div className="epm-row">
                  <label className="epm-label">TIN</label>
                  <input className="epm-input" value={tin} onChange={(e) => setTin(e.target.value)} placeholder="Tax Identification Number" />
                </div>
              </div>
              <div className="epm-row">
                <label className="epm-label">Office Address</label>
                <textarea className="epm-input epm-textarea" value={fullAddress} onChange={(e) => setFullAddress(e.target.value)} placeholder="Full office address" rows={2} />
              </div>

              {/* Brand-specific */}
              {org.type === 'brand' && (
                <>
                  <div className="epm-divider" />
                  <div className="epm-grid">
                    <div className="epm-row">
                      <label className="epm-label">Parent Company</label>
                      <input className="epm-input" value={parentCompany} onChange={(e) => setParentCompany(e.target.value)} placeholder="Parent company name" />
                    </div>
                    <div className="epm-row">
                      <label className="epm-label">Annual Budget Range</label>
                      <select className="epm-input epm-select" value={budgetRange} onChange={(e) => setBudgetRange(e.target.value)}>
                        <option value="">Select range</option>
                        {BUDGET_OPTIONS.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                      </select>
                    </div>
                    <div className="epm-row">
                      <label className="epm-label">Preferred Booking Duration</label>
                      <select className="epm-input epm-select" value={bookingDuration} onChange={(e) => setBookingDuration(e.target.value)}>
                        <option value="">Select duration</option>
                        {DURATION_OPTIONS.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                      </select>
                    </div>
                  </div>
                </>
              )}

              {/* Agency-specific */}
              {org.type === 'agency' && (
                <>
                  <div className="epm-divider" />
                  <div className="epm-grid">
                    <div className="epm-row">
                      <label className="epm-label">Portfolio Size</label>
                      <input className="epm-input" value={portfolioSize} onChange={(e) => setPortfolioSize(e.target.value)} placeholder="e.g. 50+ clients" />
                    </div>
                    <div className="epm-row">
                      <label className="epm-label">Operations Contact</label>
                      <input className="epm-input" value={opsContact} onChange={(e) => setOpsContact(e.target.value)} placeholder="Name or phone" />
                    </div>
                    <div className="epm-row">
                      <label className="epm-label">Finance Contact</label>
                      <input className="epm-input" value={financeContact} onChange={(e) => setFinanceContact(e.target.value)} placeholder="Name or phone" />
                    </div>
                    <div className="epm-row">
                      <label className="epm-label epm-label--checkbox">
                        <input type="checkbox" checked={ownsInventory} onChange={(e) => setOwnsInventory(e.target.checked)} className="epm-checkbox" />
                        Owns Billboard Inventory
                      </label>
                    </div>
                  </div>
                  <div className="epm-row">
                    <label className="epm-label">Services Offered</label>
                    <div className="epm-checkgroup">
                      {SERVICE_OPTIONS.map((s) => (
                        <label key={s.value} className="epm-checkitem">
                          <input type="checkbox" checked={services.includes(s.value)} onChange={() => toggleService(s.value)} className="epm-checkbox" />
                          {s.label}
                        </label>
                      ))}
                    </div>
                  </div>
                </>
              )}
            </div>
          )}
        </div>

        {/* Footer */}
        {error && <div className="epm-error">{error}</div>}
        <div className="epm-footer">
          <button className="epm-btn epm-btn--cancel" onClick={onClose} type="button" disabled={saving}>Cancel</button>
          <button className="epm-btn epm-btn--save" onClick={handleSave} type="button" disabled={saving}>
            {saving ? 'Saving…' : 'Save Changes'}
          </button>
        </div>
      </div>
    </div>
  );
}

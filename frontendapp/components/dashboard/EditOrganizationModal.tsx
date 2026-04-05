'use client';

import { useState, useEffect, useRef, type ChangeEvent } from 'react';
import { profileAPI } from '@/lib/api/profile';
import type { ProfileOrganization, UpdateOrgPayload } from '@/lib/api/profile';
import { IconX } from '@/lib/icons/ui-icons';
import './edit-profile-modal.css';

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
export interface EditOrganizationModalProps {
  org: ProfileOrganization;
  onClose: () => void;
  onSaved: (updatedOrg: ProfileOrganization) => void;
}

// ── Component ─────────────────────────────────────────────────────────────────
export function EditOrganizationModal({ org, onClose, onSaved }: EditOrganizationModalProps) {
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const overlayRef = useRef<HTMLDivElement>(null);

  // ── Org core fields ──────────────────────────────────────────────────────
  const [name, setName]                   = useState(org.name ?? '');
  const [officialEmail, setOfficialEmail] = useState(org.officialEmail ?? '');
  const [officialPhone, setOfficialPhone] = useState(org.officialPhone ?? '');
  const [website, setWebsite]             = useState(org.website ?? '');
  const [fullAddress, setFullAddress]     = useState(org.fullAddress ?? '');
  const [bizReg, setBizReg]               = useState(org.businessRegNumber ?? '');
  const [tin, setTin]                     = useState(org.tin ?? '');
  const [estYear, setEstYear]             = useState(org.establishmentYear?.toString() ?? '');

  // ── Brand fields ─────────────────────────────────────────────────────────
  const [parentCompany, setParentCompany]   = useState(org.brandDetails?.parentCompany ?? '');
  const [budgetRange, setBudgetRange]       = useState(org.brandDetails?.annualBudgetRange ?? '');
  const [bookingDuration, setBookingDuration] = useState(org.brandDetails?.bookingDuration ?? '');

  // ── Agency fields ────────────────────────────────────────────────────────
  const [portfolioSize, setPortfolioSize]   = useState(org.agencyDetails?.portfolioSize ?? '');
  const [ownsInventory, setOwnsInventory]   = useState(org.agencyDetails?.ownsInventory ?? false);
  const [opsContact, setOpsContact]         = useState(org.agencyDetails?.operationsContact ?? '');
  const [financeContact, setFinanceContact] = useState(org.agencyDetails?.financeContact ?? '');
  const [services, setServices]             = useState<string[]>(org.agencyDetails?.agencyServices ?? []);

  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [logoError, setLogoError] = useState('');
  const [logoUploading, setLogoUploading] = useState(false);

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

  // Preview selected logo file locally before upload
  useEffect(() => {
    if (!logoFile) {
      setLogoPreview(null);
      return;
    }
    const objectUrl = URL.createObjectURL(logoFile);
    setLogoPreview(objectUrl);
    return () => URL.revokeObjectURL(objectUrl);
  }, [logoFile]);

  function toggleService(val: string) {
    setServices((prev) => prev.includes(val) ? prev.filter((s) => s !== val) : [...prev, val]);
  }

  function handleLogoSelection(event: ChangeEvent<HTMLInputElement>) {
    setLogoError('');
    const file = event.target.files?.[0] ?? null;
    if (!file) {
      setLogoFile(null);
      return;
    }

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      setLogoError('Invalid file format. Please upload JPG, PNG, WEBP, SVG, GIF, or BMP.');
      setLogoFile(null);
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      setLogoError('File is too large. Maximum 2MB allowed.');
      setLogoFile(null);
      return;
    }
    setLogoFile(file);
  }

  async function handleLogoUpload() {
    if (!logoFile) {
      setLogoError('Select an image first.');
      return;
    }

    setLogoUploading(true);
    setLogoError('');
    try {
      const updatedOrg = await profileAPI.uploadOrganizationLogo(org.id, logoFile);
      onSaved(updatedOrg);
      setLogoFile(null);
      setLogoPreview(null);
    } catch (err) {
      setLogoError(err instanceof Error ? err.message : 'Logo upload failed.');
    } finally {
      setLogoUploading(false);
    }
  }

  async function handleRemoveLogo() {
    if (!org.logo) return;
    setLogoUploading(true);
    setLogoError('');
    try {
      const updatedOrg = await profileAPI.deleteOrganizationLogo(org.id);
      onSaved(updatedOrg);
      setLogoFile(null);
      setLogoPreview(null);
    } catch (err) {
      setLogoError(err instanceof Error ? err.message : 'Logo removal failed.');
    } finally {
      setLogoUploading(false);
    }
  }

  async function handleSave() {
    setSaving(true);
    setError('');
    try {
        const payload: UpdateOrgPayload = {
        name:              name              || null,
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

      const savedOrg = await profileAPI.updateOrganization(org.id, payload);
      onSaved(savedOrg);
      onClose();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Save failed. Please try again.');
    } finally {
      setSaving(false);
    }
  }

  const orgTypeLabel = org.type === 'brand' ? 'Brand' : org.type === 'agency' ? 'Agency' : 'Organization';

  return (
    <div
      className="epm-overlay"
      ref={overlayRef}
      onClick={(e) => { if (e.target === overlayRef.current) onClose(); }}
      role="dialog"
      aria-modal="true"
      aria-label={`Edit ${orgTypeLabel} Information`}
    >
      <div className="epm-modal">
        {/* Header */}
        <div className="epm-header">
          <h2 className="epm-title">Edit {orgTypeLabel} Information</h2>
          <button className="epm-close" onClick={onClose} type="button" aria-label="Close">
            <IconX />
          </button>
        </div>

        {/* Error */}
        {error && <div className="epm-error">{error}</div>}

        {/* Body */}
        <div className="epm-body">
          <div className="epm-section">
            <div className="epm-grid">
              <div className="epm-row">
                <label className="epm-label">Agency Name</label>
                <input className="epm-input" value={name} onChange={(e) => setName(e.target.value)} type="text" placeholder="Agency Name" />
              </div>
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

            <div className="epm-divider" />
            <div className="epm-row">
              <label className="epm-label">Logo</label>
              <div className="epm-logo-upload">
                <img
                  src={logoPreview || org.logo || '/placeholder-logo.png'}
                  alt="Organization logo preview"
                  className="epm-logo-preview"
                  onError={(e) => { (e.target as HTMLImageElement).src = '/placeholder-logo.png'; }}
                />
                <div className="epm-logo-controls">
                  <input type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif,image/bmp" onChange={handleLogoSelection} />
                  <button type="button" className="epm-btn epm-btn--secondary" onClick={handleLogoUpload} disabled={logoUploading || !logoFile}>
                    {logoUploading ? 'Uploading…' : 'Upload logo'}
                  </button>
                  {org.logo && (
                    <button type="button" className="epm-btn epm-btn--danger" onClick={handleRemoveLogo} disabled={logoUploading}>
                      Remove current logo
                    </button>
                  )}
                </div>
              </div>
              <p className="epm-hint">Recommended: 400x400 px, max 2MB, JPG/PNG/WEBP/SVG/GIF/BMP.</p>
              {logoError && <div className="epm-error">{logoError}</div>}
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
        </div>

        {/* Footer */}
        <div className="epm-footer">
          <button className="epm-btn epm-btn--cancel" onClick={onClose} type="button">
            Cancel
          </button>
          <button className="epm-btn epm-btn--save" onClick={handleSave} type="button" disabled={saving}>
            {saving ? 'Saving…' : 'Save Changes'}
          </button>
        </div>
      </div>
    </div>
  );
}

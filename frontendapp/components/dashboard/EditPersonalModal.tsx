'use client';

import { useState, useEffect, useRef } from 'react';
import { profileAPI } from '@/lib/api/profile';
import type { ProfileUser, UpdateUserPayload } from '@/lib/api/profile';
import profileOptions from '@/data/profile-options.json';
import './edit-profile-modal.css';

// ── Icons ─────────────────────────────────────────────────────────────────────
const IconX = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
  </svg>
);

// ── Props ─────────────────────────────────────────────────────────────────────
export interface EditPersonalModalProps {
  user: ProfileUser;
  onClose: () => void;
  onSaved: (updatedUser: ProfileUser) => void;
}

// ── Component ─────────────────────────────────────────────────────────────────
export function EditPersonalModal({ user, onClose, onSaved }: EditPersonalModalProps) {
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const overlayRef = useRef<HTMLDivElement>(null);

  // ── Personal fields ──────────────────────────────────────────────────────
  const [fullName, setFullName]           = useState(user.name ?? '');
  const [mobileNumber, setMobileNumber]   = useState(user.mobileNumber ?? '');
  const [designation, setDesignation]     = useState(user.designation ?? '');
  const [department, setDepartment]       = useState(user.department ?? '');

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

  async function handleSave() {
    setSaving(true);
    setError('');
    try {
      const payload: UpdateUserPayload = {
        name:         fullName     || null,
        mobileNumber: mobileNumber || null,
        designation:  designation  || null,
        department:   department   || null,
      };
      const savedUser = await profileAPI.updateUser(payload);
      onSaved(savedUser);
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
      aria-label="Edit Personal Information"
    >
      <div className="epm-modal">
        {/* Header */}
        <div className="epm-header">
          <h2 className="epm-title">Edit Personal Information</h2>
          <button className="epm-close" onClick={onClose} type="button" aria-label="Close">
            <IconX />
          </button>
        </div>

        {/* Error */}
        {error && <div className="epm-error">{error}</div>}

        {/* Body */}
        <div className="epm-body">
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

'use client';

import { useEffect, useRef, useState } from 'react';
import { profileAPI } from '@/lib/api/profile';
import type { ProfileOrganization, TaxonomyTermOption, UpdateOrgPayload } from '@/lib/api/profile';
import './edit-profile-modal.css';

const IconX = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
  </svg>
);

export interface EditServiceCoverageModalProps {
  org: ProfileOrganization;
  onClose: () => void;
  onSaved: (updatedOrg: ProfileOrganization) => void;
}

export function EditServiceCoverageModal({ org, onClose, onSaved }: EditServiceCoverageModalProps) {
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [termsLoading, setTermsLoading] = useState(true);
  const [divisionOptions, setDivisionOptions] = useState<TaxonomyTermOption[]>([]);
  const [districtOptions, setDistrictOptions] = useState<TaxonomyTermOption[]>([]);
  const [selectedDivisionIds, setSelectedDivisionIds] = useState<string[]>(org.divisions?.map(d => String(d.id)) ?? []);
  const [selectedDistrictIds, setSelectedDistrictIds] = useState<string[]>(
    org.districts?.map(r => String(r.id)) ?? []
  );
  const [nationwideService, setNationwideService] = useState(!!org.nationwideService);
  const [internationalService, setInternationalService] = useState(!!org.internationalService);
  const overlayRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const prev = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => { document.body.style.overflow = prev; };
  }, []);

  useEffect(() => {
    const handleEsc = (e: KeyboardEvent) => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', handleEsc);
    return () => window.removeEventListener('keydown', handleEsc);
  }, [onClose]);

  useEffect(() => {
    let cancelled = false;

    async function loadTerms() {
      try {
        setTermsLoading(true);
        const [divisions, districts] = await Promise.all([
          profileAPI.getTaxonomyTerms('division'),
          profileAPI.getTaxonomyTerms('district'),
        ]);

        if (cancelled) {
          return;
        }

        setDivisionOptions(divisions);
        setDistrictOptions(districts);
      } catch (err) {
        if (!cancelled) {
          setError(err instanceof Error ? err.message : 'Failed to load location terms.');
        }
      } finally {
        if (!cancelled) {
          setTermsLoading(false);
        }
      }
    }

    loadTerms();

    return () => {
      cancelled = true;
    };
  }, []);

  const filteredDistrictOptions = selectedDivisionIds.length > 0
    ? districtOptions.filter((option) => option.parentId && selectedDivisionIds.includes(String(option.parentId)))
    : districtOptions;

  function handleNationwideChange(checked: boolean) {
    setNationwideService(checked);
    if (checked) {
      setSelectedDivisionIds([]);
      setSelectedDistrictIds([]);
    }
  }

  function toggleDivision(divisionId: string) {
    const isSelecting = !selectedDivisionIds.includes(divisionId);
    setSelectedDivisionIds((prev) => isSelecting
      ? [...prev, divisionId]
      : prev.filter((id) => id !== divisionId));

    const divisionDistricts = districtOptions
      .filter((option) => option.parentId && String(option.parentId) === divisionId)
      .map((option) => String(option.id));

    if (isSelecting) {
      setSelectedDistrictIds((prev) => [...new Set([...prev, ...divisionDistricts])]);
    } else {
      setSelectedDistrictIds((prev) => prev.filter((id) => !divisionDistricts.includes(id)));
    }
  }

  function toggleDistrict(districtId: string) {
    setSelectedDistrictIds((prev) => prev.includes(districtId)
      ? prev.filter((id) => id !== districtId)
      : [...prev, districtId]);
  }

  async function handleSave() {
    setSaving(true);
    setError('');
    try {
      const payload: UpdateOrgPayload = {
        divisions: !nationwideService ? selectedDivisionIds.map(id => Number(id)) : [],
        districts: !nationwideService ? selectedDistrictIds.map(id => Number(id)) : [],
        nationwideService,
        internationalService,
      };
      const updatedOrg = await profileAPI.updateOrganization(org.id, payload);
      onSaved(updatedOrg);
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
      aria-label="Edit Service Coverage"
    >
      <div className="epm-modal">
        <div className="epm-header">
          <h2 className="epm-title">Edit Service Coverage</h2>
          <button className="epm-close" onClick={onClose} type="button" aria-label="Close">
            <IconX />
          </button>
        </div>

        {error && <div className="epm-error">{error}</div>}

        <div className="epm-body">
          <div className="epm-section">
            <div className="epm-row">
              <label className="epm-label epm-label--checkbox">
                <input
                  type="checkbox"
                  checked={nationwideService}
                  onChange={(e) => handleNationwideChange(e.target.checked)}
                  className="epm-checkbox"
                />
                Nationwide Service
              </label>
            </div>

            <div className="epm-row">
              <label className="epm-label epm-label--checkbox">
                <input
                  type="checkbox"
                  checked={internationalService}
                  onChange={(e) => setInternationalService(e.target.checked)}
                  className="epm-checkbox"
                />
                International Service
              </label>
            </div>

            <div className="epm-row">
              <label className="epm-label" style={nationwideService ? { opacity: 0.4 } : undefined}>Divisions</label>
              {nationwideService ? (
                <div className="epm-input epm-input--readonly">Not applicable — Nationwide Service is enabled.</div>
              ) : (
                <div className="epm-checkgroup" style={{ maxHeight: '12rem', overflowY: 'auto' }}>
                  {divisionOptions.length > 0 ? divisionOptions.map((option) => (
                    <label key={option.id} className="epm-checkitem">
                      <input
                        type="checkbox"
                        checked={selectedDivisionIds.includes(String(option.id))}
                        onChange={() => toggleDivision(String(option.id))}
                        className="epm-checkbox"
                        disabled={termsLoading || saving}
                      />
                      {option.name}
                    </label>
                  )) : (
                    <div className="epm-input epm-input--readonly">
                      {termsLoading ? 'Loading divisions...' : 'No divisions available'}
                    </div>
                  )}
                </div>
              )}
            </div>

            <div className="epm-row">
              <label className="epm-label" style={nationwideService ? { opacity: 0.4 } : undefined}>Districts</label>
              {nationwideService ? (
                <div className="epm-input epm-input--readonly">Not applicable — Nationwide Service is enabled.</div>
              ) : (
                <div className="epm-checkgroup" style={{ maxHeight: '12rem', overflowY: 'auto' }}>
                  {filteredDistrictOptions.length > 0 ? filteredDistrictOptions.map((option) => (
                    <label key={option.id} className="epm-checkitem">
                      <input
                        type="checkbox"
                        checked={selectedDistrictIds.includes(String(option.id))}
                        onChange={() => toggleDistrict(String(option.id))}
                        className="epm-checkbox"
                        disabled={termsLoading || saving}
                      />
                      {option.name}
                    </label>
                  )) : (
                    <div className="epm-input epm-input--readonly">
                      {selectedDivisionIds.length > 0 ? 'No districts available for the selected divisions.' : 'Select divisions to see districts.'}
                    </div>
                  )}
                </div>
              )}
            </div>
          </div>
        </div>

        <div className="epm-footer">
          <button className="epm-btn epm-btn--cancel" onClick={onClose} type="button">Cancel</button>
          <button className="epm-btn epm-btn--save" onClick={handleSave} type="button" disabled={saving}>
            {saving ? 'Saving…' : 'Save Changes'}
          </button>
        </div>
      </div>
    </div>
  );
}
'use client';

import { useEffect, useState, useRef } from 'react';
import { profileAPI } from '@/lib/api/profile';
import type { ProfileOrganization } from '@/lib/api/profile';
import { IconX, IconDocument } from '@/lib/icons/ui-icons';
import './edit-profile-modal.css';

export interface EditOrganizationLogoModalProps {
  org: ProfileOrganization;
  onClose: () => void;
  onSaved: (updatedOrg: ProfileOrganization) => void;
}

export function EditOrganizationLogoModal({ org, onClose, onSaved }: EditOrganizationLogoModalProps) {
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [preview, setPreview] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [dragOver, setDragOver] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(0);
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    const prevOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => {
      document.body.style.overflow = prevOverflow;
      if (preview) URL.revokeObjectURL(preview);
    };
  }, [preview]);

  useEffect(() => {
    if (!logoFile) {
      setPreview(null);
      return;
    }

    const objectUrl = URL.createObjectURL(logoFile);
    setPreview(objectUrl);

    return () => URL.revokeObjectURL(objectUrl);
  }, [logoFile]);

  useEffect(() => {
    const handleEsc = (e: KeyboardEvent) => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', handleEsc);
    return () => window.removeEventListener('keydown', handleEsc);
  }, [onClose]);

  function validateFile(file: File): boolean {
    if (!['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'image/gif', 'image/bmp'].includes(file.type)) {
      setError('Invalid file format. Please upload JPG, PNG, WEBP, SVG, GIF, or BMP.');
      return false;
    }
    if (file.size > 2 * 1024 * 1024) {
      setError('File is too large. Maximum 2MB allowed.');
      return false;
    }
    return true;
  }

  function setFile(file: File | null) {
    setError('');
    if (!file) {
      setLogoFile(null);
      return;
    }
    if (validateFile(file)) {
      setLogoFile(file);
    }
  }

  function handleFileSelect(event: React.ChangeEvent<HTMLInputElement>) {
    const file = event.target.files?.[0] ?? null;
    setFile(file);
  }

  function handleDragOver(event: React.DragEvent) {
    event.preventDefault();
    setDragOver(true);
  }

  function handleDragLeave(event: React.DragEvent) {
    event.preventDefault();
    setDragOver(false);
  }

  function handleDrop(event: React.DragEvent) {
    event.preventDefault();
    setDragOver(false);
    const file = event.dataTransfer.files?.[0] ?? null;
    setFile(file);
  }

  function triggerFileSelect() {
    fileInputRef.current?.click();
  }

  async function handleUpload() {
    if (!logoFile) {
      setError('Please select a file to upload.');
      return;
    }

    setSaving(true);
    setError('');
    setUploadProgress(0);

    try {
      // Simulate progress for better UX
      const progressInterval = setInterval(() => {
        setUploadProgress((prev) => Math.min(prev + 10, 90));
      }, 100);

      const updatedOrg = await profileAPI.uploadOrganizationLogo(org.id, logoFile);
      clearInterval(progressInterval);
      setUploadProgress(100);
      setTimeout(() => {
        setPreview(updatedOrg.logo);
        onSaved(updatedOrg);
        setFile(null);
        onClose();
      }, 500);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Logo upload failed.');
      setUploadProgress(0);
    } finally {
      setSaving(false);
    }
  }

  async function handleRemove() {
    setSaving(true);
    setError('');

    try {
      const updatedOrg = await profileAPI.deleteOrganizationLogo(org.id);
      onSaved(updatedOrg);
      setFile(null);
      onClose();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Logo removal failed.');
    } finally {
      setSaving(false);
    }
  }

  return (
    <div
      className="epm-overlay"
      onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
      role="dialog"
      aria-modal="true"
      aria-label="Edit organization logo"
    >
      <div className="epm-modal">
        <div className="epm-header">
          <h2 className="epm-title">Update Organization Logo</h2>
          <button className="epm-close" type="button" onClick={onClose} aria-label="Close">
            <IconX />
          </button>
        </div>

        <div className="epm-body">
          <div className="epm-row">
            
            <div className="epm-logo-upload">
              <img
                src={preview || org.logo || '/no-logo.svg'}
                alt="Logo preview"
                className="epm-logo-preview"
                onError={(e) => { (e.target as HTMLImageElement).src = '/no-logo.svg'; }}
              />
            </div>
          </div>

          <div className="epm-row">
            <label className="epm-label">Choose new logo</label>
            <div
              className={`epm-drop-zone ${dragOver ? 'epm-drop-zone--active' : ''}`}
              onDragOver={handleDragOver}
              onDragLeave={handleDragLeave}
              onDrop={handleDrop}
              onClick={triggerFileSelect}
            >
              <div className="epm-drop-zone-content">
                <span className="epm-drop-icon"><IconDocument /></span>
                <p className="epm-drop-text">
                  {logoFile ? `Selected: ${logoFile.name}` : 'Drag & drop your logo here, or click to browse'}
                </p>
                <p className="epm-drop-hint">JPG, PNG, WEBP, SVG, GIF, BMP up to 2MB</p>
              </div>
            </div>
            <input
              ref={fileInputRef}
              type="file"
              accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif,image/bmp"
              onChange={handleFileSelect}
              style={{ display: 'none' }}
            />
          </div>

          {saving && (
            <div className="epm-row">
              <div className="epm-progress">
                <div className="epm-progress-bar" style={{ width: `${uploadProgress}%` }} />
                <span className="epm-progress-text">{uploadProgress}%</span>
              </div>
            </div>
          )}

          {error ? <div className="epm-error">{error}</div> : null}
        </div>

        <div className="epm-footer">
          <button type="button" className="epm-btn epm-btn--cancel" onClick={onClose} disabled={saving}>Cancel</button>
          <button type="button" className="epm-btn epm-btn--danger" onClick={handleRemove} disabled={saving || !org.logo}>Remove Logo</button>
          <button type="button" className="epm-btn epm-btn--save" onClick={handleUpload} disabled={saving || !logoFile}>
            {saving ? 'Processing …' : 'Upload Logo'}
          </button>
        </div>
      </div>
    </div>
  );
}

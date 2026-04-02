'use client';

import { useState, useEffect, useRef } from 'react';
import type { ProfileOrganization } from '@/lib/api/profile';
import { profileAPI } from '@/lib/api/profile';
import { IconX } from '@/lib/icons/ui-icons';
import './edit-profile-modal.css';

interface DocumentUploadModalProps {
  org: ProfileOrganization;
  onClose: () => void;
  onSaved: (org: ProfileOrganization) => void;
}

interface DocumentField {
  id: string;
  label: string;
  description: string;
  file: File | null;
  required: boolean;
}

export function DocumentUploadModal({ org, onClose, onSaved }: DocumentUploadModalProps) {
  const overlayRef = useRef<HTMLDivElement>(null);
  const [documents, setDocuments] = useState<DocumentField[]>([
    {
      id: 'tin_bin',
      label: 'TIN/BIN Certificate',
      description: 'Tax Identification Number or Business Identification Number certificate',
      file: null,
      required: true,
    },
    {
      id: 'business_license',
      label: 'Business License',
      description: 'Trade license or business registration certificate',
      file: null,
      required: true,
    },
    {
      id: 'electric_bill',
      label: 'Office Electric Bill',
      description: 'Recent office electricity bill (last 1 month) to confirm business address verification',
      file: null,
      required: false,
    },
  ]);

  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState('');

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

  const handleFileChange = (id: string, file: File | null) => {
    setDocuments((prev) =>
      prev.map((doc) => (doc.id === id ? { ...doc, file } : doc))
    );
    setError('');
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    // Validate required files
    const missingRequired = documents.filter((doc) => doc.required && !doc.file);
    if (missingRequired.length > 0) {
      setError(`Please upload: ${missingRequired.map((d) => d.label).join(', ')}`);
      return;
    }

    // Validate file sizes (max 5MB each)
    const oversizedFiles = documents.filter(
      (doc) => doc.file && doc.file.size > 5 * 1024 * 1024
    );
    if (oversizedFiles.length > 0) {
      setError('File size must not exceed 5MB');
      return;
    }

    // Validate file types
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    const invalidFiles = documents.filter(
      (doc) => doc.file && !allowedTypes.includes(doc.file.type)
    );
    if (invalidFiles.length > 0) {
      setError('Only PDF, JPG, and PNG files are allowed');
      return;
    }

    setUploading(true);

    try {
      // Create FormData
      const formData = new FormData();
      formData.append('organization_id', org.id.toString());

      // Collect metadata
      const types: string[] = [];
      const labels: string[] = [];
      const descriptions: string[] = [];

      documents.forEach((doc) => {
        if (doc.file) {
          formData.append('documents[]', doc.file, `${doc.id}_${doc.file.name}`);
          types.push(doc.id);
          labels.push(doc.label);
          descriptions.push(doc.description);
        }
      });

      // Send metadata as JSON strings to avoid Drupal InputBag validation issues
      formData.append('document_types', JSON.stringify(types));
      formData.append('document_labels', JSON.stringify(labels));
      formData.append('document_descriptions', JSON.stringify(descriptions));

      const response = await profileAPI.uploadVerificationDocuments(org.id, formData);

      if (response.success && response.data) {
        onSaved(response.data);
        onClose();
      } else {
        setError(response.message || 'Upload failed');
      }
    } catch (err) {
      console.error('Upload error:', err);
      setError(err instanceof Error ? err.message : 'Failed to upload documents. Please try again.');
    } finally {
      setUploading(false);
    }
  };

  const hasExistingDocuments = org.verificationDocuments && org.verificationDocuments.length > 0;

  return (
    <div 
      className="epm-overlay" 
      ref={overlayRef}
      onClick={(e) => { if (e.target === overlayRef.current) onClose(); }}
      role="dialog"
      aria-modal="true"
      aria-label="Upload Verification Documents"
    >
      <div className="epm-modal" onClick={(e) => e.stopPropagation()}>
        <div className="epm-header">
          <h2 className="epm-title">
            {hasExistingDocuments ? 'Update Verification Documents' : 'Upload Verification Documents'}
          </h2>
          <button type="button" onClick={onClose} className="epm-close" aria-label="Close">
            <IconX />
          </button>
        </div>

        <form onSubmit={handleSubmit}>
          <div className="epm-body">
            {hasExistingDocuments && (
              <div style={{ 
                padding: '0.75rem', 
                backgroundColor: 'var(--color-blue-50, #eff6ff)', 
                borderRadius: '0.375rem',
                marginBottom: '1rem',
                fontSize: '0.875rem',
                color: 'var(--color-blue-700, #1d4ed8)'
              }}>
                <strong>Note:</strong> Uploading new documents will replace existing ones and reset verification status to "Pending Review".
              </div>
            )}

            <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
              {documents.map((doc) => (
                <div key={doc.id} className="epm-row">
                  <label className="epm-label">
                    {doc.label}
                    {doc.required && <span style={{ color: 'var(--color-red-500, #ef4444)' }}> *</span>}
                  </label>
                  <p style={{ fontSize: '0.75rem', color: 'var(--color-gray-500)', marginTop: '0.25rem', marginBottom: '0.5rem' }}>
                    {doc.description}
                  </p>
                  
                  <div style={{ position: 'relative' }}>
                    <input
                      type="file"
                      id={`file-${doc.id}`}
                      accept=".pdf,.jpg,.jpeg,.png"
                      onChange={(e) => handleFileChange(doc.id, e.target.files?.[0] || null)}
                      style={{ display: 'none' }}
                    />
                    <label
                      htmlFor={`file-${doc.id}`}
                      style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        gap: '0.5rem',
                        padding: '2rem 1rem',
                        border: '2px dashed var(--color-gray-300, #d1d5db)',
                        borderRadius: '0.5rem',
                        cursor: 'pointer',
                        backgroundColor: doc.file ? 'var(--color-green-50, #f0fdf4)' : 'var(--color-gray-50, #f9fafb)',
                        transition: 'all 0.2s',
                      }}
                      onDragOver={(e) => {
                        e.preventDefault();
                        e.currentTarget.style.borderColor = 'var(--color-blue-500, #3b82f6)';
                      }}
                      onDragLeave={(e) => {
                        e.currentTarget.style.borderColor = 'var(--color-gray-300, #d1d5db)';
                      }}
                      onDrop={(e) => {
                        e.preventDefault();
                        e.currentTarget.style.borderColor = 'var(--color-gray-300, #d1d5db)';
                        const file = e.dataTransfer.files[0];
                        if (file) handleFileChange(doc.id, file);
                      }}
                    >
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                      </svg>
                      <div style={{ textAlign: 'center' }}>
                        {doc.file ? (
                          <>
                            <div style={{ color: 'var(--color-green-700, #15803d)', fontWeight: 500 }}>
                              ✓ {doc.file.name}
                            </div>
                            <div style={{ fontSize: '0.75rem', color: 'var(--color-gray-500)', marginTop: '0.25rem' }}>
                              {(doc.file.size / 1024).toFixed(0)} KB • Click to change
                            </div>
                          </>
                        ) : (
                          <>
                            <div style={{ color: 'var(--color-gray-700)', fontWeight: 500 }}>
                              Click to upload or drag and drop
                            </div>
                            <div style={{ fontSize: '0.75rem', color: 'var(--color-gray-500)', marginTop: '0.25rem' }}>
                              PDF, JPG, or PNG (max 5MB)
                            </div>
                          </>
                        )}
                      </div>
                    </label>
                  </div>
                </div>
              ))}
            </div>

            {error && (
              <div style={{
                marginTop: '1rem',
                padding: '0.75rem',
                backgroundColor: 'var(--color-red-50, #fef2f2)',
                color: 'var(--color-red-700, #b91c1c)',
                borderRadius: '0.375rem',
                fontSize: '0.875rem',
              }}>
                {error}
              </div>
            )}
          </div>

          <div className="epm-footer">
            <span style={{ fontSize: '0.875rem', color: 'var(--color-gray-600, #4b5563)', fontWeight: 500 }}>
              {documents.filter(d => d.file).length}/{documents.length} files added
            </span>
            <div style={{ display: 'flex', gap: '0.75rem' }}>
              <button
                type="button"
                onClick={onClose}
                className="epm-btn epm-btn--cancel"
                disabled={uploading}
              >
                Cancel
              </button>
              <button
                type="submit"
                className="epm-btn epm-btn--save"
                disabled={uploading}
              >
                {uploading ? 'Uploading...' : hasExistingDocuments ? 'Update Documents' : 'Upload Documents'}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}

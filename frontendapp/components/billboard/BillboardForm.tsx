'use client';

import { useState, useEffect, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import { billboardAPI, type CreateBillboardData, type FieldConfigData, type TaxonomyOption } from '@/lib/api/billboard';
import { profileAPI } from '@/lib/api/profile';
import { TypeaheadInput } from './TypeaheadInput';
import { IconAlertCircle } from '@/lib/icons/ui-icons';
import './billboard-form.css';

interface BillboardFormProps {
  onSuccess?: (billboardId: string) => void;
  onCancel?: () => void;
  redirectPath?: string;
  role?: 'agency' | 'owner' | 'admin';
  billboardUuid?: string;
}

const TABS = [
  { id: 'basic', label: 'Basic Info' },
  { id: 'dimensions', label: 'Dimensions' },
  { id: 'location', label: 'Location' },
  { id: 'pricing', label: 'Pricing & Scores' },
  { id: 'status', label: 'Status & Options' },
] as const;

type TabId = (typeof TABS)[number]['id'];

export function BillboardForm({ onSuccess, onCancel, redirectPath, role, billboardUuid }: BillboardFormProps) {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState<TabId>('basic');
  const [organizations, setOrganizations] = useState<Array<{ id: number; name: string }>>([]);
  const [username, setUsername] = useState<string>('');
  const [fieldConfig, setFieldConfig] = useState<FieldConfigData | null>(null);
  const [configLoading, setConfigLoading] = useState(true);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [savedBillboardId, setSavedBillboardId] = useState<number | null>(null);
  const [formData, setFormData] = useState<Partial<CreateBillboardData>>({
    title: '',
    owner_organization: undefined,
    media_format: undefined,
    placement_type: undefined,
    display_size: '',
    width_ft: undefined,
    height_ft: undefined,
    division: undefined,
    district: undefined,
    upazila_thana: undefined,
    city_corporation: undefined,
    area_zone: undefined,
    road_name: undefined,
    road_type: undefined,
    latitude: undefined,
    longitude: undefined,
    facing_direction: '',
    traffic_direction: undefined,
    visibility_class: undefined,
    illumination_type: undefined,
    rate_card_price: undefined,
    currency: 'BDT',
    commercial_score: undefined,
    traffic_score: undefined,
    booking_mode: undefined,
    availability_status: undefined,
    owner_contact_number: '',
    is_premium: false,
    is_active: true,
  });

  useEffect(() => {
    async function fetchInitialData() {
      try {
        const promises: Promise<unknown>[] = [
          profileAPI.get(),
          billboardAPI.fieldConfig(),
        ];
        if (billboardUuid) {
          promises.push(billboardAPI.getByUuid(billboardUuid).then(
            (res) => {
              console.log('[BillboardForm] getByUuid success:', res);
              return res;
            },
            (err) => {
              console.error('[BillboardForm] getByUuid error:', err);
              throw err;
            }
          ));
        }

        const [profileResult, configResult, billboardResult] = await Promise.allSettled(promises);

        if (profileResult.status === 'fulfilled') {
          // Debug log all results
          console.log('[BillboardForm] profileResult:', profileResult);
          console.log('[BillboardForm] configResult:', configResult);
          console.log('[BillboardForm] billboardResult:', billboardResult);
          const profile = profileResult.value as Awaited<ReturnType<typeof profileAPI.get>>;
          if (profile.user?.username) {
            setUsername(profile.user.username);
          }
          if (profile.organizations && profile.organizations.length > 0) {
            const orgs = profile.organizations.map(org => ({
              id: org.id,
              name: org.name,
            }));
            setOrganizations(orgs);
            if (!billboardUuid && orgs.length === 1) {
              setFormData(prev => ({ ...prev, owner_organization: orgs[0].id }));
            }
          }
        } else {
          setError('Failed to load your organizations');
        }

        if (configResult.status === 'fulfilled') {
          const configValue = configResult.value as Awaited<ReturnType<typeof billboardAPI.fieldConfig>>;
          if (configValue.success) {
            setFieldConfig(configValue.data);
          }
        }

        // Pre-populate form for edit mode
        if (billboardUuid && billboardResult?.status === 'fulfilled') {
          const billboard = (billboardResult.value as { data: import('@/types/billboard').Billboard }).data;
          setSavedBillboardId(Number(billboard.id));
          setFormData({
            title: billboard.title || '',
            owner_organization: billboard.owner_organization ? Number(billboard.owner_organization.id) : undefined,
            media_format: billboard.media_format ? Number(billboard.media_format.id) : undefined,
            placement_type: billboard.placement_type ? Number(billboard.placement_type.id) : undefined,
            display_size: billboard.display_size || '',
            width_ft: billboard.width_ft ? Number(billboard.width_ft) : undefined,
            height_ft: billboard.height_ft ? Number(billboard.height_ft) : undefined,
            division: billboard.division ? Number(billboard.division.id) : undefined,
            district: billboard.district ? Number(billboard.district.id) : undefined,
            area_zone: billboard.area_zone ? Number(billboard.area_zone.id) : undefined,
            road_name: billboard.road_name ? Number(billboard.road_name.id) : undefined,
            road_type: billboard.road_type ? Number(billboard.road_type.id) : undefined,
            latitude: billboard.latitude ? Number(billboard.latitude) : undefined,
            longitude: billboard.longitude ? Number(billboard.longitude) : undefined,
            facing_direction: billboard.facing_direction || '',
            rate_card_price: billboard.rate_card_price ? Number(billboard.rate_card_price) : undefined,
            currency: billboard.currency || 'BDT',
            commercial_score: billboard.commercial_score ? Number(billboard.commercial_score) : undefined,
            traffic_score: billboard.traffic_score ? Number(billboard.traffic_score) : undefined,
            booking_mode: billboard.booking_mode ? Number(billboard.booking_mode.id) : undefined,
            availability_status: billboard.availability_status ? Number(billboard.availability_status.id) : undefined,
            is_premium: billboard.is_premium === '1',
            is_active: billboard.is_active !== '0',
          });
        } else if (billboardUuid && billboardResult?.status === 'rejected') {
          setError('Failed to load billboard data for editing');
        }
      } catch (err) {
        setError('Failed to load form configuration');
      } finally {
        setConfigLoading(false);
      }
    }
    fetchInitialData();
  }, [billboardUuid]);

  const handleInputChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>
  ) => {
    const { name, value, type } = e.target;
    // Entity reference selects (taxonomy terms, org) must be sent as numbers.
    const numericSelects = ['owner_organization', 'division', 'district', 'upazila_thana',
      'city_corporation', 'area_zone', 'media_format', 'placement_type', 'road_name',
      'road_type', 'traffic_direction', 'visibility_class', 'illumination_type',
      'booking_mode', 'availability_status'];
    const parsed = type === 'number' ? (value ? parseFloat(value) : undefined) :
                   type === 'checkbox' ? (e.target as HTMLInputElement).checked :
                   (numericSelects.includes(name) && value) ? Number(value) :
                   value || undefined;

    setFormData(prev => {
      const next = { ...prev, [name]: parsed };

      // Cascading resets: when a parent changes, clear its children.
      if (name === 'division') {
        next.district = undefined;
        next.upazila_thana = undefined;
        next.city_corporation = undefined;
        next.area_zone = undefined;
      } else if (name === 'district') {
        next.upazila_thana = undefined;
        next.city_corporation = undefined;
        next.area_zone = undefined;
      } else if (name === 'upazila_thana') {
        next.area_zone = undefined;
      }

      return next;
    });
  };

  /** Save current form data — create on first save, update afterwards. */
  const saveFormData = async (): Promise<boolean> => {
    setError(null);
    setLoading(true);

    try {
      if (savedBillboardId) {
        // Update existing billboard.
        const response = await billboardAPI.update(savedBillboardId, formData as CreateBillboardData);
        if (!response.success) {
          throw new Error('Failed to update billboard');
        }
      } else {
        // First save — only title is required to start.
        if (!formData.title?.trim()) {
          throw new Error('Please enter a billboard title');
        }
        const response = await billboardAPI.create(formData as CreateBillboardData);
        if (response.success) {
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          const nid = Number((response.data as any).billboard_id || response.data.id);
          setSavedBillboardId(nid);
        } else {
          throw new Error('Failed to create billboard');
        }
      }
      return true;
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save billboard');
      return false;
    } finally {
      setLoading(false);
    }
  };

  /** Save & Publish — saves current data, then requests publish (server validates). */
  const handlePublish = async () => {
    const saved = await saveFormData();
    if (!saved || !savedBillboardId) return;

    setLoading(true);
    setError(null);
    try {
      const response = await billboardAPI.publish(savedBillboardId);
      if (response.success) {
        if (onSuccess) {
          onSuccess(String(savedBillboardId));
        } else if (redirectPath) {
          router.push(redirectPath);
        } else {
          router.back();
        }
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to publish billboard');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    // Form submit is just a save (no publish).
    const saved = await saveFormData();
    if (saved) {
      if (onSuccess) {
        onSuccess(String(savedBillboardId));
      } else if (redirectPath) {
        router.push(redirectPath);
      } else {
        router.back();
      }
    }
  };

  const handleSubmitForReview = async () => {
    await handlePublish();
  };

  const getOptions = (fieldKey: string): TaxonomyOption[] => {
    return fieldConfig?.options[fieldKey] || [];
  };

  const fetchTitleSuggestions = useCallback(
    (query: string) => billboardAPI.titleSuggest(query),
    []
  );

  const handleTitleChange = useCallback((value: string) => {
    setFormData(prev => ({ ...prev, title: value }));
  }, []);

  const getFieldLabel = (fieldKey: string, fallback: string): string => {
    return fieldConfig?.fields[fieldKey]?.label || fallback;
  };

  const isRequired = (fieldKey: string): boolean => {
    return fieldConfig?.fields[fieldKey]?.required ?? false;
  };

  /** Render a <select> populated from taxonomy options */
  const renderTaxSelect = (fieldKey: string, fallbackLabel: string, placeholder: string, filteredOptions?: TaxonomyOption[]) => {
    const options = filteredOptions ?? getOptions(fieldKey);
    const label = getFieldLabel(fieldKey, fallbackLabel);
    const parentKey = fieldKey === 'district' ? 'division'
                    : fieldKey === 'upazila_thana' ? 'district'
                    : fieldKey === 'city_corporation' ? 'district'
                    : fieldKey === 'area_zone' ? 'district'
                    : null;
    const isDisabled = parentKey ? !(formData as Record<string, unknown>)[parentKey] : false;
    return (
      <div className="bf-field">
        <label htmlFor={fieldKey} className="bf-label">
          {label}
        </label>
        <select
          id={fieldKey}
          name={fieldKey}
          value={String((formData as Record<string, unknown>)[fieldKey] ?? '')}
          onChange={handleInputChange}
          className="bf-select"
          disabled={isDisabled}
        >
          <option value="">{isDisabled ? `Select ${parentKey} first` : placeholder}</option>
          {options.map(opt => (
            <option key={opt.id} value={opt.id}>{opt.label}</option>
          ))}
        </select>
      </div>
    );
  };

  /** Get filtered options based on parent selection */
  const getFilteredDistricts = (): TaxonomyOption[] => {
    const divisionId = formData.division ? Number(formData.division) : null;
    if (!divisionId) return [];
    return getOptions('district').filter(d => d.divisionId === divisionId);
  };

  const getFilteredUpazilas = (): TaxonomyOption[] => {
    const districtId = formData.district ? Number(formData.district) : null;
    if (!districtId) return [];
    return getOptions('upazila_thana').filter(u => u.districtId === districtId);
  };

  const getFilteredCityCorporations = (): TaxonomyOption[] => {
    const districtId = formData.district ? Number(formData.district) : null;
    if (!districtId) return [];
    return getOptions('city_corporation').filter(c => c.districtId === districtId);
  };

  const getFilteredAreaZones = (): TaxonomyOption[] => {
    const districtId = formData.district ? Number(formData.district) : null;
    if (!districtId) return [];
    let zones = getOptions('area_zone').filter(a => a.districtId === districtId);
    // Further filter by upazila if selected.
    const upazilaId = formData.upazila_thana ? Number(formData.upazila_thana) : null;
    if (upazilaId) {
      zones = zones.filter(a => a.upazilaId === upazilaId);
    }
    return zones;
  };

  const currentIndex = TABS.findIndex(t => t.id === activeTab);
  const isFirstTab = currentIndex === 0;
  const isLastTab = currentIndex === TABS.length - 1;

  const goNext = async () => {
    if (isLastTab) return;
    const saved = await saveFormData();
    if (saved) {
      setActiveTab(TABS[currentIndex + 1].id);
    }
  };
  const goPrev = () => {
    if (!isFirstTab) setActiveTab(TABS[currentIndex - 1].id);
  };

  if (configLoading) {
    return (
      <div className="bf-form bf-form--loading">
        <div className="bf-spinner" />
        <p>Loading form configuration...</p>
      </div>
    );
  }

  return (
    <form className="bf-form" onSubmit={handleSubmit}>
      {error && (
        <div className="bf-error">
          <IconAlertCircle />
          <span>{error}</span>
        </div>
      )}

      {/* Tab Navigation */}
      <div className="bf-tabs">
        {TABS.map((tab, i) => (
          <button
            key={tab.id}
            type="button"
            className={`bf-tabs__btn ${activeTab === tab.id ? 'bf-tabs__btn--active' : ''}`}
            onClick={() => setActiveTab(tab.id)}
          >
            <span className="bf-tabs__num">{i + 1}</span>
            <span className="bf-tabs__label">{tab.label}</span>
          </button>
        ))}
      </div>

      {/* Tab Panels */}
      <div className="bf-panel">

        {/* Basic Info */}
        {activeTab === 'basic' && (
          <div className="bf-panel__content">
            <div className="bf-row">
              <div className="bf-field">
                <label htmlFor="title" className="bf-label bf-label--required">
                  Billboard Title
                </label>
                <TypeaheadInput
                  id="title"
                  name="title"
                  value={formData.title || ''}
                  onChange={handleTitleChange}
                  fetchSuggestions={fetchTitleSuggestions}
                  className="bf-input"
                  placeholder="e.g., Gulshan-2 Circle Premium Billboard"
                  required
                />
              </div>

            </div>

            <div className="bf-row">
              <div className="bf-field">
                <label htmlFor="owner_organization" className="bf-label">
                  Owner Organization
                </label>
                {role === 'agency' && organizations.length === 1 ? (
                  <>
                    <input
                      type="text"
                      className="bf-input bf-input--readonly"
                      value={organizations[0].name}
                      readOnly
                      tabIndex={-1}
                    />
                    <input type="hidden" name="owner_organization" value={organizations[0].id} />
                  </>
                ) : (
                  <select
                    id="owner_organization"
                    name="owner_organization"
                    value={formData.owner_organization || ''}
                    onChange={handleInputChange}
                    className="bf-select"
                  >
                    <option value="">Select Organization</option>
                    {organizations.map(org => (
                      <option key={org.id} value={org.id}>{org.name}</option>
                    ))}
                  </select>
                )}
              </div>
              {renderTaxSelect('media_format', 'Media Format', 'Select Media Format')}
            </div>

            <div className="bf-row">
              {renderTaxSelect('placement_type', 'Placement Type', 'Select Placement Type')}
            </div>
          </div>
        )}

        {/* Dimensions */}
        {activeTab === 'dimensions' && (
          <div className="bf-panel__content">
            <div className="bf-row">
              <div className="bf-field">
                <label htmlFor="display_size" className="bf-label">
                  Display Size
                </label>
                <input
                  type="text"
                  id="display_size"
                  name="display_size"
                  value={formData.display_size}
                  onChange={handleInputChange}
                  className="bf-input"
                  placeholder="e.g., 20x30 ft"
                />
              </div>
              <div className="bf-field">
                <label htmlFor="width_ft" className="bf-label">
                  Width (feet)
                </label>
                <input
                  type="number"
                  id="width_ft"
                  name="width_ft"
                  value={formData.width_ft || ''}
                  onChange={handleInputChange}
                  className="bf-input"
                  step="0.01"
                  placeholder="20"
                />
              </div>
              <div className="bf-field">
                <label htmlFor="height_ft" className="bf-label">
                  Height (feet)
                </label>
                <input
                  type="number"
                  id="height_ft"
                  name="height_ft"
                  value={formData.height_ft || ''}
                  onChange={handleInputChange}
                  className="bf-input"
                  step="0.01"
                  placeholder="30"
                />
              </div>
            </div>
          </div>
        )}

        {/* Location */}
        {activeTab === 'location' && (
          <div className="bf-panel__content">
            <div className="bf-row">
              {renderTaxSelect('division', 'Division', 'Select Division')}
              {renderTaxSelect('district', 'District', 'Select District', getFilteredDistricts())}
              {renderTaxSelect('upazila_thana', 'Upazila / Thana', 'Select Upazila / Thana', getFilteredUpazilas())}
            </div>

            <div className="bf-row">
              {renderTaxSelect('city_corporation', 'City Corporation', 'Select City Corporation', getFilteredCityCorporations())}
              {renderTaxSelect('area_zone', 'Area / Zone', 'Select Area / Zone', getFilteredAreaZones())}
              {renderTaxSelect('road_name', 'Road Name', 'Select Road Name')}
            </div>

            <div className="bf-row">
              {renderTaxSelect('road_type', 'Road Type', 'Select Road Type')}
              {renderTaxSelect('traffic_direction', 'Traffic Direction', 'Select Traffic Direction')}
              {renderTaxSelect('visibility_class', 'Visibility Class', 'Select Visibility Class')}
            </div>

            <div className="bf-row">
              {renderTaxSelect('illumination_type', 'Illumination Type', 'Select Illumination Type')}
            </div>

            <div className="bf-row">
              <div className="bf-field">
                <label htmlFor="latitude" className="bf-label">
                  Latitude
                </label>
                <input
                  type="number"
                  id="latitude"
                  name="latitude"
                  value={formData.latitude || ''}
                  onChange={handleInputChange}
                  className="bf-input"
                  step="0.0000001"
                  placeholder="23.7925"
                />
              </div>
              <div className="bf-field">
                <label htmlFor="longitude" className="bf-label">
                  Longitude
                </label>
                <input
                  type="number"
                  id="longitude"
                  name="longitude"
                  value={formData.longitude || ''}
                  onChange={handleInputChange}
                  className="bf-input"
                  step="0.0000001"
                  placeholder="90.4078"
                />
              </div>
              <div className="bf-field">
                <label htmlFor="facing_direction" className="bf-label">
                  Facing Direction
                </label>
                <select
                  id="facing_direction"
                  name="facing_direction"
                  value={formData.facing_direction}
                  onChange={handleInputChange}
                  className="bf-select"
                >
                  <option value="">Select Direction</option>
                  {(fieldConfig?.fields.facing_direction?.options || 
                    ['north', 'south', 'east', 'west', 'northeast', 'northwest', 'southeast', 'southwest']
                  ).map(dir => (
                    <option key={dir} value={dir}>{dir.charAt(0).toUpperCase() + dir.slice(1)}</option>
                  ))}
                </select>
              </div>
            </div>
          </div>
        )}

        {/* Pricing & Scores */}
        {activeTab === 'pricing' && (
          <div className="bf-panel__content">
            <div className="bf-row">
              <div className="bf-field">
                <label htmlFor="rate_card_price" className="bf-label">
                  Rate Card Price
                </label>
                <input
                  type="number"
                  id="rate_card_price"
                  name="rate_card_price"
                  value={formData.rate_card_price || ''}
                  onChange={handleInputChange}
                  className="bf-input"
                  step="0.01"
                  placeholder="150000"
                />
              </div>
              <div className="bf-field">
                <label htmlFor="currency" className="bf-label">
                  Currency
                </label>
                <select
                  id="currency"
                  name="currency"
                  value={formData.currency}
                  onChange={handleInputChange}
                  className="bf-select"
                >
                  {(fieldConfig?.fields.currency?.options || ['BDT', 'USD']).map(c => (
                    <option key={c} value={c}>{c}</option>
                  ))}
                </select>
              </div>
            </div>

            <div className="bf-row">
              <div className="bf-field">
                <label htmlFor="commercial_score" className="bf-label">
                  Commercial Score (0-100)
                </label>
                <input
                  type="number"
                  id="commercial_score"
                  name="commercial_score"
                  value={formData.commercial_score || ''}
                  onChange={handleInputChange}
                  className="bf-input"
                  min="0"
                  max="100"
                  placeholder="85"
                />
              </div>
              <div className="bf-field">
                <label htmlFor="traffic_score" className="bf-label">
                  Traffic Score (0-100)
                </label>
                <input
                  type="number"
                  id="traffic_score"
                  name="traffic_score"
                  value={formData.traffic_score || ''}
                  onChange={handleInputChange}
                  className="bf-input"
                  min="0"
                  max="100"
                  placeholder="90"
                />
              </div>
            </div>
          </div>
        )}

        {/* Status & Options */}
        {activeTab === 'status' && (
          <div className="bf-panel__content">
            <div className="bf-row">
              {renderTaxSelect('availability_status', 'Availability Status', 'Select Status')}
              {renderTaxSelect('booking_mode', 'Booking Mode', 'Select Booking Mode')}
            </div>

            <div className="bf-row">
              <div className="bf-field">
                <label htmlFor="owner_contact_number" className="bf-label">
                  Owner Contact Number
                </label>
                <input
                  type="text"
                  id="owner_contact_number"
                  name="owner_contact_number"
                  value={formData.owner_contact_number}
                  onChange={handleInputChange}
                  className="bf-input"
                  placeholder="e.g., +880 1XXX-XXXXXX"
                />
              </div>
            </div>

            <div className="bf-row">
              <div className="bf-checkbox">
                <input
                  type="checkbox"
                  id="is_premium"
                  name="is_premium"
                  checked={formData.is_premium || false}
                  onChange={handleInputChange}
                  className="bf-checkbox__input"
                />
                <label htmlFor="is_premium" className="bf-checkbox__label">
                  Premium Listing
                </label>
              </div>
              <div className="bf-checkbox">
                <input
                  type="checkbox"
                  id="is_active"
                  name="is_active"
                  checked={formData.is_active !== false}
                  onChange={handleInputChange}
                  className="bf-checkbox__input"
                />
                <label htmlFor="is_active" className="bf-checkbox__label">
                  Active
                </label>
              </div>
            </div>
          </div>
        )}

      </div>

      {/* Actions */}
      <div className="bf-actions">
        {onCancel && !isFirstTab ? null : onCancel && (
          <button type="button" onClick={onCancel} className="bf-btn bf-btn--secondary">
            Cancel
          </button>
        )}
        {!isFirstTab && (
          <button type="button" onClick={goPrev} className="bf-btn bf-btn--secondary">
            Previous
          </button>
        )}
        {!isLastTab && (
          <button type="button" onClick={goNext} disabled={loading} className="bf-btn bf-btn--primary">
            {loading ? 'Saving...' : 'Save & Next'}
          </button>
        )}
        {isLastTab && (
          <>
            <button type="submit" disabled={loading} className="bf-btn bf-btn--primary">
              {loading ? 'Saving...' : 'Save'}
            </button>
            <button type="button" onClick={handleSubmitForReview} disabled={loading} className="bf-btn bf-btn--success">
              {loading ? 'Submitting...' : 'Save & Submit for Review'}
            </button>
          </>
        )}
      </div>
    </form>
  );
}

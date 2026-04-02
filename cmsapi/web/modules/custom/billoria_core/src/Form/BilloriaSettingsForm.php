<?php

namespace Drupal\billoria_core\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Billoria platform settings.
 */
class BilloriaSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['billoria_core.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billoria_core_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('billoria_core.settings');

    // ── Platform Information ──────────────────────────────────────────────
    $form['platform'] = [
      '#type' => 'details',
      '#title' => $this->t('Platform Information'),
      '#open' => TRUE,
    ];

    $form['platform']['site_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Platform Name'),
      '#default_value' => $config->get('site_name') ?: 'Billoria',
      '#description' => $this->t('The name of your billboard marketplace platform.'),
      '#required' => TRUE,
    ];

    $form['platform']['site_tagline'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tagline'),
      '#default_value' => $config->get('site_tagline'),
      '#description' => $this->t('A short description of your platform.'),
    ];

    $form['platform']['contact_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Contact Email'),
      '#default_value' => $config->get('contact_email'),
      '#description' => $this->t('Primary contact email for the platform.'),
      '#required' => TRUE,
    ];

    $form['platform']['support_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Support Phone'),
      '#default_value' => $config->get('support_phone'),
      '#description' => $this->t('Support phone number (e.g., +880 1812345678).'),
    ];

    // ── Business Settings ──────────────────────────────────────────────────
    $form['business'] = [
      '#type' => 'details',
      '#title' => $this->t('Business Settings'),
      '#open' => FALSE,
    ];

    $form['business']['commission_rate'] = [
      '#type' => 'number',
      '#title' => $this->t('Platform Commission Rate (%)'),
      '#default_value' => $config->get('commission_rate') ?: 10,
      '#min' => 0,
      '#max' => 100,
      '#step' => 0.5,
      '#description' => $this->t('Percentage commission charged on bookings.'),
    ];

    $form['business']['vat_rate'] = [
      '#type' => 'number',
      '#title' => $this->t('VAT Rate (%)'),
      '#default_value' => $config->get('vat_rate') ?: 15,
      '#min' => 0,
      '#max' => 100,
      '#step' => 0.1,
      '#description' => $this->t('Value Added Tax rate for Bangladesh.'),
    ];

    $form['business']['currency'] = [
      '#type' => 'select',
      '#title' => $this->t('Currency'),
      '#default_value' => $config->get('currency') ?: 'BDT',
      '#options' => [
        'BDT' => $this->t('BDT (৳) - Bangladeshi Taka'),
        'USD' => $this->t('USD ($) - US Dollar'),
        'EUR' => $this->t('EUR (€) - Euro'),
      ],
      '#description' => $this->t('Platform currency for pricing.'),
    ];

    $form['business']['min_booking_duration'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum Booking Duration (days)'),
      '#default_value' => $config->get('min_booking_duration') ?: 7,
      '#min' => 1,
      '#max' => 365,
      '#description' => $this->t('Minimum number of days for a billboard booking.'),
    ];

    // ── Verification Settings ──────────────────────────────────────────────
    $form['verification'] = [
      '#type' => 'details',
      '#title' => $this->t('Verification & Trust'),
      '#open' => FALSE,
    ];

    $form['verification']['auto_approve_agencies'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Auto-approve agency registrations'),
      '#default_value' => $config->get('auto_approve_agencies') ?: FALSE,
      '#description' => $this->t('If checked, agencies will be auto-approved without manual review.'),
    ];

    $form['verification']['require_document_verification'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require document verification'),
      '#default_value' => $config->get('require_document_verification') ?: TRUE,
      '#description' => $this->t('Require organizations to upload verification documents.'),
    ];

    $form['verification']['email_verification_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require email verification'),
      '#default_value' => $config->get('email_verification_required') ?: TRUE,
      '#description' => $this->t('Users must verify their email to access platform features.'),
    ];

    $form['verification']['phone_verification_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require phone verification'),
      '#default_value' => $config->get('phone_verification_required') ?: TRUE,
      '#description' => $this->t('Users must verify their phone number.'),
    ];

    $form['verification']['trust_score_threshold'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum Trust Score for Bookings'),
      '#default_value' => $config->get('trust_score_threshold') ?: 40,
      '#min' => 0,
      '#max' => 100,
      '#description' => $this->t('Users need this minimum trust score to make bookings.'),
    ];

    // ── API Settings ───────────────────────────────────────────────────────
    $form['api'] = [
      '#type' => 'details',
      '#title' => $this->t('API Configuration'),
      '#open' => FALSE,
    ];

    $form['api']['api_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable API access'),
      '#default_value' => $config->get('api_enabled') ?? TRUE,
      '#description' => $this->t('Enable/disable all API endpoints.'),
    ];

    $form['api']['api_rate_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('API Rate Limit (requests/minute)'),
      '#default_value' => $config->get('api_rate_limit') ?: 60,
      '#min' => 1,
      '#max' => 1000,
      '#description' => $this->t('Maximum API requests per minute per user.'),
    ];

    $form['api']['cors_allowed_origins'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CORS Allowed Origins'),
      '#default_value' => $config->get('cors_allowed_origins') ?: "http://localhost:3000\nhttps://billoria-ad.ddev.site:3000",
      '#rows' => 4,
      '#description' => $this->t('One origin per line. Use * to allow all (not recommended for production).'),
    ];

    // ── Email Settings ─────────────────────────────────────────────────────
    $form['email'] = [
      '#type' => 'details',
      '#title' => $this->t('Email Configuration'),
      '#open' => FALSE,
    ];

    $form['email']['from_email'] = [
      '#type' => 'email',
      '#title' => $this->t('From Email Address'),
      '#default_value' => $config->get('from_email') ?: 'noreply@billoria.ad',
      '#description' => $this->t('Email address used for system emails.'),
    ];

    $form['email']['from_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From Name'),
      '#default_value' => $config->get('from_name') ?: 'Billoria Platform',
      '#description' => $this->t('Name shown in system emails.'),
    ];

    $form['email']['admin_notification_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Admin Notification Email'),
      '#default_value' => $config->get('admin_notification_email'),
      '#description' => $this->t('Email for admin notifications (new registrations, reports, etc.).'),
    ];

    // ── Feature Flags ──────────────────────────────────────────────────────
    $form['features'] = [
      '#type' => 'details',
      '#title' => $this->t('Feature Flags'),
      '#open' => FALSE,
      '#description' => $this->t('Enable or disable specific platform features.'),
    ];

    $form['features']['enable_booking'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Booking System'),
      '#default_value' => $config->get('enable_booking') ?? TRUE,
      '#description' => $this->t('Allow users to book billboards.'),
    ];

    $form['features']['enable_payments'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Payment Processing'),
      '#default_value' => $config->get('enable_payments') ?: FALSE,
      '#description' => $this->t('Enable online payment gateway integration.'),
    ];

    $form['features']['enable_reviews'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Reviews & Ratings'),
      '#default_value' => $config->get('enable_reviews') ?? TRUE,
      '#description' => $this->t('Allow users to review billboard owners.'),
    ];

    $form['features']['enable_analytics'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Analytics Dashboard'),
      '#default_value' => $config->get('enable_analytics') ?? TRUE,
      '#description' => $this->t('Show analytics to users.'),
    ];

    $form['features']['maintenance_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Maintenance Mode'),
      '#default_value' => $config->get('maintenance_mode') ?: FALSE,
      '#description' => $this->t('Put the platform in maintenance mode.'),
    ];

    // ── Geographic Settings ────────────────────────────────────────────────
    $form['geography'] = [
      '#type' => 'details',
      '#title' => $this->t('Geographic Settings'),
      '#open' => FALSE,
    ];

    $form['geography']['default_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Country'),
      '#default_value' => $config->get('default_country') ?: 'BD',
      '#options' => [
        'BD' => $this->t('Bangladesh'),
        'IN' => $this->t('India'),
        'PK' => $this->t('Pakistan'),
        'NP' => $this->t('Nepal'),
      ],
    ];

    $form['geography']['timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Timezone'),
      '#default_value' => $config->get('timezone') ?: 'Asia/Dhaka',
      '#options' => [
        'Asia/Dhaka' => $this->t('Asia/Dhaka (GMT+6)'),
        'Asia/Kolkata' => $this->t('Asia/Kolkata (GMT+5:30)'),
        'Asia/Karachi' => $this->t('Asia/Karachi (GMT+5)'),
        'UTC' => $this->t('UTC'),
      ],
    ];

    // ── Advanced Settings ──────────────────────────────────────────────────
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced Settings'),
      '#open' => FALSE,
    ];

    $form['advanced']['debug_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Debug Mode'),
      '#default_value' => $config->get('debug_mode') ?: FALSE,
      '#description' => $this->t('Enable verbose logging for debugging. <strong>Disable in production!</strong>'),
    ];

    $form['advanced']['cache_lifetime'] = [
      '#type' => 'number',
      '#title' => $this->t('API Cache Lifetime (seconds)'),
      '#default_value' => $config->get('cache_lifetime') ?: 3600,
      '#min' => 0,
      '#max' => 86400,
      '#description' => $this->t('How long to cache API responses. 0 = no cache.'),
    ];

    $form['advanced']['session_timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Session Timeout (seconds)'),
      '#default_value' => $config->get('session_timeout') ?: 86400,
      '#min' => 600,
      '#max' => 604800,
      '#description' => $this->t('User session expiration time.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('billoria_core.settings')
      ->set('site_name', $form_state->getValue('site_name'))
      ->set('site_tagline', $form_state->getValue('site_tagline'))
      ->set('contact_email', $form_state->getValue('contact_email'))
      ->set('support_phone', $form_state->getValue('support_phone'))
      ->set('commission_rate', $form_state->getValue('commission_rate'))
      ->set('vat_rate', $form_state->getValue('vat_rate'))
      ->set('currency', $form_state->getValue('currency'))
      ->set('min_booking_duration', $form_state->getValue('min_booking_duration'))
      ->set('auto_approve_agencies', $form_state->getValue('auto_approve_agencies'))
      ->set('require_document_verification', $form_state->getValue('require_document_verification'))
      ->set('email_verification_required', $form_state->getValue('email_verification_required'))
      ->set('phone_verification_required', $form_state->getValue('phone_verification_required'))
      ->set('trust_score_threshold', $form_state->getValue('trust_score_threshold'))
      ->set('api_enabled', $form_state->getValue('api_enabled'))
      ->set('api_rate_limit', $form_state->getValue('api_rate_limit'))
      ->set('cors_allowed_origins', $form_state->getValue('cors_allowed_origins'))
      ->set('from_email', $form_state->getValue('from_email'))
      ->set('from_name', $form_state->getValue('from_name'))
      ->set('admin_notification_email', $form_state->getValue('admin_notification_email'))
      ->set('enable_booking', $form_state->getValue('enable_booking'))
      ->set('enable_payments', $form_state->getValue('enable_payments'))
      ->set('enable_reviews', $form_state->getValue('enable_reviews'))
      ->set('enable_analytics', $form_state->getValue('enable_analytics'))
      ->set('maintenance_mode', $form_state->getValue('maintenance_mode'))
      ->set('default_country', $form_state->getValue('default_country'))
      ->set('timezone', $form_state->getValue('timezone'))
      ->set('debug_mode', $form_state->getValue('debug_mode'))
      ->set('cache_lifetime', $form_state->getValue('cache_lifetime'))
      ->set('session_timeout', $form_state->getValue('session_timeout'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

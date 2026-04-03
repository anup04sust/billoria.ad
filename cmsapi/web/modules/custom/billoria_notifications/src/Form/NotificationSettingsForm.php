<?php

namespace Drupal\billoria_notifications\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Billoria Notifications settings.
 */
class NotificationSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'billoria_notifications.firebase';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billoria_notifications_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['firebase'] = [
      '#type' => 'details',
      '#title' => $this->t('Firebase Cloud Messaging Settings'),
      '#open' => TRUE,
      '#description' => $this->t('<strong>Firebase Cloud Messaging V1 API (Recommended)</strong><br>
        The Legacy API is no longer available. Use the V1 API with Service Account credentials.<br>
        Get credentials from the <a href="@url" target="_blank">Firebase Console</a> → Project Settings → Service Accounts → Generate new private key', [
        '@url' => 'https://console.firebase.google.com/',
      ]),
    ];

    $form['firebase']['use_v1_api'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Firebase V1 API (Recommended)'),
      '#description' => $this->t('The modern Firebase Cloud Messaging API. Uncheck only if using the deprecated Legacy API.'),
      '#default_value' => $config->get('use_v1_api') ?? TRUE,
    ];

    $form['firebase']['service_account_json'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Service Account JSON'),
      '#description' => $this->t('<strong>How to get Service Account JSON:</strong><ol>
        <li>Go to <a href="https://console.firebase.google.com/" target="_blank">Firebase Console</a></li>
        <li>Select your project</li>
        <li>Go to <strong>Project Settings</strong> (gear icon) → <strong>Service Accounts</strong> tab</li>
        <li>Click "<strong>Generate new private key</strong>"</li>
        <li>Copy the entire JSON content and paste it here</li>
        </ol>
        <strong>Security:</strong> This JSON contains sensitive credentials. Drupal will store it securely in the database.'),
      '#default_value' => $config->get('service_account_json'),
      '#rows' => 10,
      '#states' => [
        'visible' => [
          ':input[name="use_v1_api"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="use_v1_api"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['firebase']['legacy_divider'] = [
      '#markup' => '<hr><h3>' . $this->t('Legacy API (Deprecated - Not Recommended)') . '</h3>',
      '#states' => [
        'visible' => [
          ':input[name="use_v1_api"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['firebase']['server_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Firebase Server Key (Legacy)'),
      '#description' => $this->t('<strong>WARNING:</strong> The Legacy API was deprecated and may no longer work. Use V1 API instead.'),
      '#default_value' => $config->get('server_key'),
      '#required' => FALSE,
      '#maxlength' => 255,
      '#placeholder' => $this->t('AAAA...'),
      '#states' => [
        'visible' => [
          ':input[name="use_v1_api"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['firebase']['test'] = [
      '#type' => 'details',
      '#title' => $this->t('Test Configuration'),
      '#open' => FALSE,
    ];

    $form['firebase']['test']['info'] = [
      '#markup' => $this->t('<p>After saving your Firebase Server Key, you can test push notifications:</p>
        <ol>
          <li>Register a device token via the API</li>
          <li>Create a notification programmatically</li>
          <li>Check Firebase Console for delivery stats</li>
        </ol>
        <p><strong>API Endpoints:</strong></p>
        <ul>
          <li><code>POST /api/v1/notifications/fcm/register</code></li>
          <li><code>GET /api/v1/notifications/fcm/tokens</code></li>
        </ul>
        <p>See the <a href="/admin/help/billoria_notifications">module help page</a> for complete documentation.</p>'),
    ];

    $form['notifications'] = [
      '#type' => 'details',
      '#title' => $this->t('Notification Settings'),
      '#open' => FALSE,
    ];

    $form['notifications']['auto_push'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable automatic push notifications'),
      '#description' => $this->t('When enabled, push notifications will be sent automatically when creating new notifications. You can override this per-notification.'),
      '#default_value' => $config->get('auto_push') ?? TRUE,
    ];

    $form['notifications']['cleanup_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable automatic cleanup via cron'),
      '#description' => $this->t('Automatically delete expired notifications and inactive FCM tokens during cron runs.'),
      '#default_value' => $config->get('cleanup_enabled') ?? TRUE,
    ];

    $form['notifications']['token_retention_days'] = [
      '#type' => 'number',
      '#title' => $this->t('Inactive token retention (days)'),
      '#description' => $this->t('Number of days to keep inactive FCM tokens before deletion during cron.'),
      '#default_value' => $config->get('token_retention_days') ?? 30,
      '#min' => 1,
      '#max' => 365,
      '#states' => [
        'visible' => [
          ':input[name="cleanup_enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['statistics'] = [
      '#type' => 'details',
      '#title' => $this->t('Statistics'),
      '#open' => FALSE,
    ];

    // Get current statistics.
    $database = \Drupal::database();
    
    $total_notifications = $database->select('billoria_notifications', 'n')
      ->countQuery()
      ->execute()
      ->fetchField();

    $unread_notifications = $database->select('billoria_notifications', 'n')
      ->condition('is_read', 0)
      ->countQuery()
      ->execute()
      ->fetchField();

    $total_tokens = $database->select('billoria_fcm_tokens', 'f')
      ->countQuery()
      ->execute()
      ->fetchField();

    $active_tokens = $database->select('billoria_fcm_tokens', 'f')
      ->condition('is_active', 1)
      ->countQuery()
      ->execute()
      ->fetchField();

    $form['statistics']['stats'] = [
      '#markup' => $this->t('<div class="billoria-stats">
        <h3>Current Statistics</h3>
        <table>
          <tr><th>Total Notifications:</th><td>@total_notifications</td></tr>
          <tr><th>Unread Notifications:</th><td>@unread_notifications</td></tr>
          <tr><th>Total Device Tokens:</th><td>@total_tokens</td></tr>
          <tr><th>Active Tokens:</th><td>@active_tokens</td></tr>
        </table>
      </div>', [
        '@total_notifications' => $total_notifications,
        '@unread_notifications' => $unread_notifications,
        '@total_tokens' => $total_tokens,
        '@active_tokens' => $active_tokens,
      ]),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $use_v1 = $form_state->getValue('use_v1_api');
    
    if ($use_v1) {
      $service_account_json = $form_state->getValue('service_account_json');
      
      if (!empty($service_account_json)) {
        // Validate JSON format.
        $decoded = json_decode($service_account_json, TRUE);
        if (json_last_error() !== JSON_ERROR_NONE) {
          $form_state->setErrorByName('service_account_json', $this->t('Invalid JSON format. Please copy the entire JSON file content.'));
        }
        elseif (!isset($decoded['project_id']) || !isset($decoded['private_key'])) {
          $form_state->setErrorByName('service_account_json', $this->t('The JSON does not appear to be a valid Firebase Service Account file. It should contain "project_id" and "private_key" fields.'));
        }
      }
    }
    else {
      // Legacy API validation.
      $server_key = $form_state->getValue('server_key');
      if (!empty($server_key) && strlen($server_key) < 100) {
        $form_state->setErrorByName('server_key', $this->t('The Firebase Server Key appears to be invalid. It should be a long string starting with "AAAA".'));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(static::SETTINGS)
      ->set('use_v1_api', $form_state->getValue('use_v1_api'))
      ->set('service_account_json', $form_state->getValue('service_account_json'))
      ->set('server_key', $form_state->getValue('server_key'))
      ->set('auto_push', $form_state->getValue('auto_push'))
      ->set('cleanup_enabled', $form_state->getValue('cleanup_enabled'))
      ->set('token_retention_days', $form_state->getValue('token_retention_days'))
      ->save();

    // Clear cache to reinitialize Firebase service.
    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);

    $use_v1 = $form_state->getValue('use_v1_api');
    if ($use_v1 && !empty($form_state->getValue('service_account_json'))) {
      $this->messenger()->addStatus($this->t('Firebase V1 API configured successfully. You can now send push notifications.'));
    }
    elseif (!$use_v1 && !empty($form_state->getValue('server_key'))) {
      $this->messenger()->addWarning($this->t('Legacy API configured. Note: This API is deprecated and may not work. Consider migrating to V1 API.'));
    }
  }

}

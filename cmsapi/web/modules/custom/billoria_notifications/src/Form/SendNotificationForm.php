<?php

namespace Drupal\billoria_notifications\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\billoria_notifications\NotificationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;

/**
 * Form for sending notifications to users.
 */
class SendNotificationForm extends FormBase {

  /**
   * The notification manager service.
   *
   * @var \Drupal\billoria_notifications\NotificationManager
   */
  protected $notificationManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a SendNotificationForm object.
   */
  public function __construct(
    NotificationManager $notification_manager,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->notificationManager = $notification_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('billoria_notifications.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billoria_notifications_send_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;

    // Recipient selection
    $form['recipient_section'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Recipients'),
      '#collapsible' => FALSE,
    ];

    $form['recipient_section']['recipient_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Send to'),
      '#options' => [
        'user' => $this->t('Specific user'),
        'role' => $this->t('All users with role'),
        'all' => $this->t('All users'),
      ],
      '#default_value' => 'user',
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::recipientTypeCallback',
        'wrapper' => 'recipient-options',
        'event' => 'change',
      ],
    ];

    $form['recipient_section']['recipient_options'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'recipient-options'],
    ];

    $recipient_type = $form_state->getValue(['recipient_section', 'recipient_type']) ?? 'user';

    if ($recipient_type === 'user') {
      $form['recipient_section']['recipient_options']['user_id'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('User'),
        '#target_type' => 'user',
        '#description' => $this->t('Start typing username or email to search.'),
        '#required' => FALSE,
      ];
    }
    elseif ($recipient_type === 'role') {
      $roles = user_role_names(TRUE);
      unset($roles['authenticated']); // Remove authenticated as it's essentially "all"
      
      $form['recipient_section']['recipient_options']['role'] = [
        '#type' => 'select',
        '#title' => $this->t('Role'),
        '#options' => $roles,
        '#required' => FALSE,
        '#description' => $this->t('Select a role to notify all users with that role.'),
      ];
    }
    elseif ($recipient_type === 'all') {
      $user_count = $this->entityTypeManager->getStorage('user')
        ->getQuery()
        ->condition('status', 1)
        ->accessCheck(FALSE)
        ->count()
        ->execute();
      
      $form['recipient_section']['recipient_options']['all_confirm'] = [
        '#markup' => '<div class="messages messages--warning">' . 
          $this->t('This will send a notification to <strong>@count active users</strong>.', [
            '@count' => $user_count,
          ]) . '</div>',
      ];
    }

    // Notification content
    $form['content_section'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Notification Content'),
      '#collapsible' => FALSE,
    ];

    $form['content_section']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Notification Type'),
      '#options' => [
        'system' => $this->t('System'),
        'announcement' => $this->t('Announcement'),
        'welcome' => $this->t('Welcome'),
        'booking' => $this->t('Booking'),
        'payment' => $this->t('Payment'),
        'verification' => $this->t('Verification'),
        'promotion' => $this->t('Promotion'),
      ],
      '#default_value' => 'announcement',
      '#required' => TRUE,
    ];

    $form['content_section']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 100,
      '#required' => TRUE,
      '#description' => $this->t('Short notification title (max 100 characters).'),
    ];

    $form['content_section']['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#rows' => 4,
      '#required' => TRUE,
      '#description' => $this->t('The main notification message.'),
      '#maxlength' => 500,
    ];

    $form['content_section']['priority'] = [
      '#type' => 'select',
      '#title' => $this->t('Priority'),
      '#options' => [
        'low' => $this->t('Low'),
        'normal' => $this->t('Normal'),
        'high' => $this->t('High'),
        'urgent' => $this->t('Urgent'),
      ],
      '#default_value' => 'normal',
      '#required' => TRUE,
    ];

    // Advanced options
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced Options'),
      '#open' => FALSE,
    ];

    $form['advanced']['action_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Action URL'),
      '#description' => $this->t('Optional URL to navigate to when notification is clicked (e.g., /dashboard/bookings).'),
      '#placeholder' => '/dashboard',
    ];

    $form['advanced']['expires'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Set expiration'),
      '#default_value' => FALSE,
    ];

    $form['advanced']['expires_days'] = [
      '#type' => 'number',
      '#title' => $this->t('Expires in (days)'),
      '#min' => 1,
      '#max' => 365,
      '#default_value' => 30,
      '#states' => [
        'visible' => [
          ':input[name="advanced[expires]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Push notification options
    $form['push_section'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Push Notification'),
      '#collapsible' => FALSE,
    ];

    $form['push_section']['send_push'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Send as push notification'),
      '#default_value' => TRUE,
      '#description' => $this->t('If enabled, this will trigger push notifications to all registered devices.'),
    ];

    $form['push_section']['push_info'] = [
      '#markup' => '<div class="description">' . 
        $this->t('Push notifications will be sent to users who have enabled notifications in the app.') . 
        '</div>',
    ];

    // Preview section
    $form['preview'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Preview'),
      '#attributes' => ['class' => ['notification-preview']],
    ];

    $form['preview']['preview_markup'] = [
      '#markup' => '<div id="notification-preview" class="notification-preview-box">' . 
        '<div class="preview-label">' . $this->t('Preview') . '</div>' .
        '<div class="preview-title"><strong>' . $this->t('Your notification will appear here') . '</strong></div>' .
        '<div class="preview-message">' . $this->t('Fill in the form to see a preview') . '</div>' .
        '</div>',
      '#allowed_tags' => ['div', 'strong', 'em', 'br'],
    ];

    // Add custom CSS
    $form['#attached']['library'][] = 'billoria_notifications/send_form';

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send Notification'),
      '#button_type' => 'primary',
    ];

    $form['actions']['preview_button'] = [
      '#type' => 'button',
      '#value' => $this->t('Update Preview'),
      '#ajax' => [
        'callback' => '::previewCallback',
        'wrapper' => 'notification-preview',
      ],
    ];

    return $form;
  }

  /**
   * AJAX callback for recipient type change.
   */
  public function recipientTypeCallback(array &$form, FormStateInterface $form_state) {
    return $form['recipient_section']['recipient_options'];
  }

  /**
   * AJAX callback for preview.
   */
  public function previewCallback(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue(['content_section', 'title']) ?? $this->t('Notification Title');
    $message = $form_state->getValue(['content_section', 'message']) ?? $this->t('Notification message');
    $type = $form_state->getValue(['content_section', 'type']) ?? 'announcement';
    $priority = $form_state->getValue(['content_section', 'priority']) ?? 'normal';

    $priority_icons = [
      'low' => '📋',
      'normal' => '🔔',
      'high' => '⚠️',
      'urgent' => '🚨',
    ];

    $icon = $priority_icons[$priority] ?? '🔔';

    $markup = '<div id="notification-preview" class="notification-preview-box notification-preview-' . $priority . '">' .
      '<div class="preview-label">' . $this->t('Preview (@type - @priority)', [
        '@type' => ucfirst($type),
        '@priority' => ucfirst($priority),
      ]) . '</div>' .
      '<div class="preview-icon">' . $icon . '</div>' .
      '<div class="preview-title"><strong>' . htmlspecialchars($title) . '</strong></div>' .
      '<div class="preview-message">' . nl2br(htmlspecialchars($message)) . '</div>' .
      '</div>';

    return [
      '#markup' => $markup,
      '#allowed_tags' => ['div', 'strong', 'em', 'br'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $recipient_type = $form_state->getValue(['recipient_section', 'recipient_type']);

    if ($recipient_type === 'user') {
      $user_id = $form_state->getValue(['recipient_section', 'recipient_options', 'user_id']);
      if (empty($user_id)) {
        $form_state->setErrorByName('recipient_section][recipient_options][user_id', 
          $this->t('Please select a user.'));
      }
    }
    elseif ($recipient_type === 'role') {
      $role = $form_state->getValue(['recipient_section', 'recipient_options', 'role']);
      if (empty($role)) {
        $form_state->setErrorByName('recipient_section][recipient_options][role', 
          $this->t('Please select a role.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $recipient_type = $form_state->getValue(['recipient_section', 'recipient_type']);
    $type = $form_state->getValue(['content_section', 'type']);
    $title = $form_state->getValue(['content_section', 'title']);
    $message = $form_state->getValue(['content_section', 'message']);
    $priority = $form_state->getValue(['content_section', 'priority']);
    $send_push = $form_state->getValue(['push_section', 'send_push']);
    
    // Build metadata
    $metadata = [];
    $action_url = $form_state->getValue(['advanced', 'action_url']);
    if (!empty($action_url)) {
      $metadata['action'] = 'navigate';
      $metadata['url'] = $action_url;
    }

    // Calculate expiration
    $expires_at = NULL;
    if ($form_state->getValue(['advanced', 'expires'])) {
      $days = $form_state->getValue(['advanced', 'expires_days']);
      $expires_at = time() + ($days * 86400);
    }

    // Get recipients
    $recipients = $this->getRecipients($recipient_type, $form_state);

    if (empty($recipients)) {
      $this->messenger()->addError($this->t('No recipients found.'));
      return;
    }

    // Send notifications
    $sent = 0;
    $failed = 0;

    foreach ($recipients as $uid) {
      $nid = $this->notificationManager->createNotification(
        uid: $uid,
        type: $type,
        title: $title,
        message: $message,
        metadata: $metadata,
        priority: $priority,
        expires_at: $expires_at,
        send_push: $send_push
      );

      if ($nid) {
        $sent++;
      }
      else {
        $failed++;
      }
    }

    if ($sent > 0) {
      $this->messenger()->addStatus($this->t('Successfully sent @count notification(s).', [
        '@count' => $sent,
      ]));

      if ($send_push) {
        $this->messenger()->addStatus($this->t('Push notifications were sent to registered devices.'));
      }
    }

    if ($failed > 0) {
      $this->messenger()->addWarning($this->t('Failed to send @count notification(s).', [
        '@count' => $failed,
      ]));
    }
  }

  /**
   * Get list of recipient user IDs based on selection.
   *
   * @param string $recipient_type
   *   The recipient type (user, role, all).
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   Array of user IDs.
   */
  protected function getRecipients(string $recipient_type, FormStateInterface $form_state): array {
    $query = $this->entityTypeManager->getStorage('user')->getQuery()
      ->condition('status', 1)
      ->accessCheck(FALSE);

    if ($recipient_type === 'user') {
      $user_id = $form_state->getValue(['recipient_section', 'recipient_options', 'user_id']);
      return [$user_id];
    }
    elseif ($recipient_type === 'role') {
      $role = $form_state->getValue(['recipient_section', 'recipient_options', 'role']);
      $query->condition('roles', $role);
    }
    // For 'all', no additional conditions needed

    return $query->execute();
  }

}

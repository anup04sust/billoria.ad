<?php

namespace Drupal\billoria_sms\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\billoria_sms\Service\SmsSender;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure SMS API settings.
 */
class SmsSettingsForm extends ConfigFormBase {

  /**
   * The SMS sender service.
   *
   * @var \Drupal\billoria_sms\Service\SmsSender
   */
  protected $smsSender;

  /**
   * Constructs a SmsSettingsForm object.
   *
   * @param \Drupal\billoria_sms\Service\SmsSender $sms_sender
   *   The SMS sender service.
   */
  public function __construct(SmsSender $sms_sender) {
    $this->smsSender = $sms_sender;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('billoria_sms.sender')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['billoria_sms.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billoria_sms_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('billoria_sms.settings');

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alpha SMS API Key'),
      '#description' => $this->t('Enter your API key from <a href="@url" target="_blank">Alpha SMS dashboard</a>.', [
        '@url' => 'https://sms.net.bd/',
      ]),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['sender_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Sender ID'),
      '#description' => $this->t('Optional. Your approved sender ID (e.g., "Billoria"). <strong>Leave empty if not approved</strong> - messages will be sent from a numeric sender. Contact Alpha SMS to get sender ID approved.'),
      '#default_value' => $config->get('sender_id'),
      '#maxlength' => 20,
    ];

    $form['otp_template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('OTP Message Template'),
      '#description' => $this->t('Template for OTP verification messages. Available placeholders: <strong>{{code}}</strong> (verification code), <strong>{{minutes}}</strong> (validity period), <strong>{{brand}}</strong> (Billoria). Keep under 160 characters for single SMS.'),
      '#default_value' => $config->get('otp_template') ?: 'Your Billoria verification code is {{code}}. Valid for {{minutes}} minutes. Do not share this code.',
      '#rows' => 3,
      '#attributes' => [
        'placeholder' => 'Your Billoria verification code is {{code}}. Valid for {{minutes}} minutes. Do not share this code.',
      ],
    ];

    $form['template_help'] = [
      '#type' => 'details',
      '#title' => $this->t('Template Examples'),
      '#open' => FALSE,
    ];

    $form['template_help']['examples'] = [
      '#markup' => '<ul>' .
        '<li><strong>Short:</strong> {{code}} is your Billoria verification code. Valid {{minutes}}min.</li>' .
        '<li><strong>Formal:</strong> Dear user, your {{brand}} verification code is {{code}}. Please use within {{minutes}} minutes.</li>' .
        '<li><strong>Security-focused:</strong> {{brand}}: Your OTP is {{code}}. DO NOT share. Expires in {{minutes}} min.</li>' .
        '<li><strong>Bengali:</strong> আপনার Billoria যাচাইকরণ কোড: {{code}}। {{minutes}} মিনিটের জন্য বৈধ।</li>' .
        '</ul>',
    ];

    $form['test_phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test Phone Number'),
      '#description' => $this->t('Enter a phone number to test SMS sending (e.g., 01812345678).'),
      '#maxlength' => 15,
    ];

    $form['actions']['test'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send Test SMS'),
      '#submit' => ['::testSms'],
      '#limit_validation_errors' => [['api_key'], ['test_phone']],
    ];

    // Show account balance if configured
    if ($this->smsSender->isConfigured()) {
      $balance_result = $this->smsSender->getBalance();
      if ($balance_result['success']) {
        $form['balance'] = [
          '#type' => 'item',
          '#title' => $this->t('Account Balance'),
          '#markup' => $this->t('@balance BDT', [
            '@balance' => $balance_result['balance'],
          ]),
          '#weight' => -10,
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Test SMS sending.
   */
  public function testSms(array &$form, FormStateInterface $form_state) {
    $api_key = $form_state->getValue('api_key');
    $test_phone = $form_state->getValue('test_phone');

    if (empty($test_phone)) {
      $this->messenger()->addError($this->t('Please enter a test phone number.'));
      return;
    }

    // Temporarily save the API key to test
    $this->config('billoria_sms.settings')
      ->set('api_key', $api_key)
      ->save();

    $result = $this->smsSender->sendSms(
      $test_phone,
      'Test message from Billoria SMS module. Integration successful!'
    );

    if ($result['success']) {
      $this->messenger()->addStatus($this->t('Test SMS sent successfully! Request ID: @id', [
        '@id' => $result['request_id'],
      ]));
    }
    else {
      $this->messenger()->addError($this->t('Failed to send test SMS: @message', [
        '@message' => $result['message'],
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('billoria_sms.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('sender_id', $form_state->getValue('sender_id'))
      ->set('otp_template', $form_state->getValue('otp_template'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

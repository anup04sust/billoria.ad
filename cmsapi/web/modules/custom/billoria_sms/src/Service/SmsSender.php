<?php

namespace Drupal\billoria_sms\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Service for sending SMS via Alpha SMS API.
 *
 * @see https://api.sms.net.bd/
 */
class SmsSender {

  /**
   * Alpha SMS API base URL.
   */
  const API_BASE_URL = 'https://api.sms.net.bd';

  /**
   * Error code mappings from Alpha SMS API.
   */
  const ERROR_CODES = [
    0   => 'Success',
    400 => 'Missing or invalid parameter',
    403 => 'No permissions to perform request',
    404 => 'Resource not found',
    405 => 'Authorization required',
    409 => 'Unknown server error',
    410 => 'Account expired',
    411 => 'Reseller account expired or suspended',
    412 => 'Invalid schedule',
    413 => 'Invalid sender ID',
    414 => 'Message is empty',
    415 => 'Message is too long',
    416 => 'No valid number found',
    417 => 'Insufficient balance',
    420 => 'Content blocked',
    421 => 'Test mode - SMS only to registered number until first recharge',
  ];

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a SmsSender object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(
    ClientInterface $http_client,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('billoria_sms');
  }

  /**
   * Get the configured API key.
   *
   * @return string|null
   *   The API key or NULL if not configured.
   */
  protected function getApiKey(): ?string {
    $config = $this->configFactory->get('billoria_sms.settings');
    return $config->get('api_key');
  }

  /**
   * Get the configured sender ID.
   *
   * @return string|null
   *   The sender ID or NULL if not configured.
   */
  protected function getSenderId(): ?string {
    $config = $this->configFactory->get('billoria_sms.settings');
    return $config->get('sender_id');
  }

  /**
   * Get the configured OTP template.
   *
   * @return string
   *   The OTP message template.
   */
  protected function getOtpTemplate(): string {
    $config = $this->configFactory->get('billoria_sms.settings');
    $template = $config->get('otp_template');
    
    // Default template if not configured
    if (empty($template)) {
      return 'Your Billoria verification code is {{code}}. Valid for {{minutes}} minutes. Do not share this code.';
    }
    
    return $template;
  }

  /**
   * Normalize phone number to Bangladesh format (880XXXXXXXXXX).
   *
   * @param string $number
   *   The phone number.
   *
   * @return string
   *   The normalized phone number.
   */
  protected function normalizePhoneNumber(string $number): string {
    // Remove all non-digit characters
    $number = preg_replace('/\D/', '', $number);

    // If starts with 0, replace with 880
    if (substr($number, 0, 1) === '0') {
      $number = '880' . substr($number, 1);
    }
    // If doesn't start with 880, prepend it
    elseif (substr($number, 0, 3) !== '880') {
      $number = '880' . $number;
    }

    return $number;
  }

  /**
   * Send SMS to single or multiple recipients.
   *
   * @param string|array $to
   *   Phone number(s). Can be string for single or array for multiple.
   * @param string $message
   *   The message content.
   * @param array $options
   *   Optional parameters:
   *   - schedule: DateTime string (Y-m-d H:i:s) for scheduled sending
   *   - sender_id: Override default sender ID
   *   - content_id: Content ID for bulk SMS.
   *
   * @return array
   *   Response array with 'success', 'request_id', 'message', and 'error_code'.
   *
   * @throws \Exception
   *   When SMS sending fails critically.
   */
  public function sendSms($to, string $message, array $options = []): array {
    $api_key = $this->getApiKey();
    if (empty($api_key)) {
      $this->logger->error('SMS API key not configured');
      return [
        'success' => FALSE,
        'message' => 'SMS service not configured',
        'error_code' => 405,
      ];
    }

    // Normalize phone numbers
    $recipients = is_array($to) ? $to : [$to];
    $recipients = array_map([$this, 'normalizePhoneNumber'], $recipients);
    $to_param = implode(',', $recipients);

    // Build request parameters
    $params = [
      'api_key' => $api_key,
      'msg' => $message,
      'to' => $to_param,
    ];

    // Add optional parameters
    if (!empty($options['schedule'])) {
      $params['schedule'] = $options['schedule'];
    }
    
    // Only add sender_id if explicitly provided or configured (not empty)
    $sender_id = NULL;
    if (!empty($options['sender_id'])) {
      $sender_id = $options['sender_id'];
    }
    elseif ($configured_sender = $this->getSenderId()) {
      if (!empty(trim($configured_sender))) {
        $sender_id = $configured_sender;
      }
    }
    
    if ($sender_id !== NULL) {
      $params['sender_id'] = $sender_id;
    }
    
    if (!empty($options['content_id'])) {
      $params['content_id'] = $options['content_id'];
    }

    try {
      $response = $this->httpClient->post(self::API_BASE_URL . '/sendsms', [
        'form_params' => $params,
        'timeout' => 30,
      ]);

      $body = json_decode($response->getBody()->getContents(), TRUE);

      if (!empty($body['error']) && $body['error'] !== 0) {
        $error_code = $body['error'];
        $error_message = self::ERROR_CODES[$error_code] ?? 'Unknown error';
        $this->logger->error('SMS sending failed: @error (@code)', [
          '@error' => $error_message,
          '@code' => $error_code,
        ]);

        return [
          'success' => FALSE,
          'message' => $error_message,
          'error_code' => $error_code,
        ];
      }

      $this->logger->info('SMS sent successfully. Request ID: @id', [
        '@id' => $body['data']['request_id'] ?? 'N/A',
      ]);

      return [
        'success' => TRUE,
        'request_id' => $body['data']['request_id'] ?? NULL,
        'message' => $body['msg'] ?? 'SMS sent successfully',
        'error_code' => 0,
      ];

    }
    catch (RequestException $e) {
      $this->logger->error('SMS API request failed: @message', [
        '@message' => $e->getMessage(),
      ]);

      return [
        'success' => FALSE,
        'message' => 'Failed to connect to SMS service',
        'error_code' => 409,
      ];
    }
  }

  /**
   * Send OTP verification code.
   *
   * @param string $phone
   *   The phone number.
   * @param string $code
   *   The verification code (typically 6 digits).
   * @param int $validity_minutes
   *   How many minutes the code is valid (default: 10).
   *
   * @return array
   *   Response array from sendSms().
   */
  public function sendOtp(string $phone, string $code, int $validity_minutes = 10): array {
    $template = $this->getOtpTemplate();
    
    // Replace placeholders
    $message = str_replace(
      ['{{code}}', '{{minutes}}', '{{brand}}'],
      [$code, $validity_minutes, 'Billoria'],
      $template
    );

    return $this->sendSms($phone, $message);
  }

  /**
   * Get delivery report for a sent message.
   *
   * @param int $request_id
   *   The request ID from sendSms() response.
   *
   * @return array
   *   Report data with status and charges.
   */
  public function getReport(int $request_id): array {
    $api_key = $this->getApiKey();
    if (empty($api_key)) {
      return [
        'success' => FALSE,
        'message' => 'SMS service not configured',
      ];
    }

    try {
      $url = sprintf('%s/report/request/%d/?api_key=%s',
        self::API_BASE_URL,
        $request_id,
        urlencode($api_key)
      );

      $response = $this->httpClient->get($url, ['timeout' => 30]);
      $body = json_decode($response->getBody()->getContents(), TRUE);

      if (!empty($body['error']) && $body['error'] !== 0) {
        return [
          'success' => FALSE,
          'message' => self::ERROR_CODES[$body['error']] ?? 'Unknown error',
          'error_code' => $body['error'],
        ];
      }

      return [
        'success' => TRUE,
        'data' => $body['data'] ?? [],
      ];

    }
    catch (RequestException $e) {
      $this->logger->error('SMS report request failed: @message', [
        '@message' => $e->getMessage(),
      ]);

      return [
        'success' => FALSE,
        'message' => 'Failed to get SMS report',
      ];
    }
  }

  /**
   * Get account balance.
   *
   * @return array
   *   Balance information.
   */
  public function getBalance(): array {
    $api_key = $this->getApiKey();
    if (empty($api_key)) {
      return [
        'success' => FALSE,
        'message' => 'SMS service not configured',
      ];
    }

    try {
      $url = sprintf('%s/user/balance/?api_key=%s',
        self::API_BASE_URL,
        urlencode($api_key)
      );

      $response = $this->httpClient->get($url, ['timeout' => 30]);
      $body = json_decode($response->getBody()->getContents(), TRUE);

      if (!empty($body['error']) && $body['error'] !== 0) {
        return [
          'success' => FALSE,
          'message' => self::ERROR_CODES[$body['error']] ?? 'Unknown error',
          'error_code' => $body['error'],
        ];
      }

      return [
        'success' => TRUE,
        'balance' => $body['data']['balance'] ?? '0.0000',
      ];

    }
    catch (RequestException $e) {
      $this->logger->error('SMS balance request failed: @message', [
        '@message' => $e->getMessage(),
      ]);

      return [
        'success' => FALSE,
        'message' => 'Failed to get account balance',
      ];
    }
  }

  /**
   * Check if SMS service is configured and available.
   *
   * @return bool
   *   TRUE if configured, FALSE otherwise.
   */
  public function isConfigured(): bool {
    return !empty($this->getApiKey());
  }

}

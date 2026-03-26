<?php

namespace Drupal\billoria_accounts\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Password\PasswordInterface;

/**
 * Multi-step registration form for Brand/Advertiser accounts.
 */
class BrandRegistrationForm extends FormBase {

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Password hasher.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordHasher;

  /**
   * Constructs a new BrandRegistrationForm.
   */
  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager, PasswordInterface $password_hasher) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->passwordHasher = $password_hasher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('password')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billoria_brand_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Initialize step if not set
    if (!$form_state->has('step')) {
      $form_state->set('step', 1);
    }

    $step = $form_state->get('step');

    $form['#attached']['library'][] = 'billoria_accounts/registration';

    // Progress indicator
    $form['progress'] = [
      '#markup' => $this->buildProgressIndicator($step),
      '#weight' => -100,
    ];

    switch ($step) {
      case 1:
        return $this->buildStepOne($form, $form_state);
      case 2:
        return $this->buildStepTwo($form, $form_state);
      case 3:
        return $this->buildStepThree($form, $form_state);
      case 4:
        return $this->buildStepFour($form, $form_state);
    }

    return $form;
  }

  /**
   * Build progress indicator.
   */
  protected function buildProgressIndicator($current_step) {
    $steps = [
      1 => 'Account',
      2 => 'Email Verification',
      3 => 'Organization',
      4 => 'Profile Details',
    ];

    $output = '<div class="registration-progress">';
    foreach ($steps as $num => $label) {
      $class = $num == $current_step ? 'active' : ($num < $current_step ? 'completed' : '');
      $output .= "<div class='progress-step $class'><span class='step-number'>$num</span><span class='step-label'>$label</span></div>";
    }
    $output .= '</div>';

    return $output;
  }

  /**
   * Step 1: Account Creation (name, email, password, mobile).
   */
  protected function buildStepOne(array &$form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#markup' => '<h3>Create Your Brand Account</h3><p>Let\'s start with your basic information.</p>',
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your Full Name'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('name', ''),
    ];

    $form['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('mail', ''),
      '#description' => $this->t('This will be your login username'),
    ];

    $form['field_mobile_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Mobile Number'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('field_mobile_number', ''),
      '#description' => $this->t('Format: +8801XXXXXXXXX'),
      '#attributes' => [
        'placeholder' => '+8801712345678',
      ],
    ];

    $form['pass'] = [
      '#type' => 'password_confirm',
      '#required' => TRUE,
      '#size' => 25,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Account & Continue'),
      '#button_type' => 'primary',
    ];

    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromRoute('billoria_accounts.register_select'),
      '#attributes' => ['class' => ['button']],
    ];

    return $form;
  }

  /**
   * Step 2: Email Verification.
   */
  protected function buildStepTwo(array &$form, FormStateInterface $form_state) {
    $user_email = $form_state->get('user_email');

    $form['intro'] = [
      '#markup' => '<h3>Verify Your Email</h3>
        <p>We\'ve sent a verification email to <strong>' . $user_email . '</strong></p>
        <p>Please check your inbox and click the verification link, or enter the code below:</p>',
    ];

    $form['verification_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Verification Code'),
      '#required' => TRUE,
      '#maxlength' => 32,
      '#description' => $this->t('Enter the code from the email'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['verify'] = [
      '#type' => 'submit',
      '#value' => $this->t('Verify Email'),
      '#button_type' => 'primary',
    ];

    $form['actions']['resend'] = [
      '#type' => 'submit',
      '#value' => $this->t('Resend Email'),
      '#submit' => ['::resendVerificationEmail'],
      '#limit_validation_errors' => [],
    ];

    return $form;
  }

  /**
   * Step 3: Organization Setup.
   */
  protected function buildStepThree(array &$form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#markup' => '<h3>Your Organization Details</h3><p>Tell us about your brand/company.</p>',
    ];

    $form['org_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Brand/Company Name'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('org_name', ''),
    ];

    $form['field_official_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Official Business Email'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('field_official_email', ''),
    ];

    $form['field_official_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Official Business Phone'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('field_official_phone', ''),
    ];

    $form['field_website'] = [
      '#type' => 'url',
      '#title' => $this->t('Website'),
      '#required' => FALSE,
      '#default_value' => $form_state->getValue('field_website', ''),
    ];

    // Location fields
    $divisions = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('division');
    $division_options = ['' => '- Select Division -'];
    foreach ($divisions as $term) {
      $division_options[$term->tid] = $term->name;
    }

    $form['field_division'] = [
      '#type' => 'select',
      '#title' => $this->t('Division'),
      '#required' => TRUE,
      '#options' => $division_options,
      '#default_value' => $form_state->getValue('field_division', ''),
    ];

    $form['field_full_address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Full Address'),
      '#required' => TRUE,
      '#rows' => 3,
      '#default_value' => $form_state->getValue('field_full_address', ''),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#submit' => ['::previousStep'],
      '#limit_validation_errors' => [],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Step 4: Brand-Specific Profile.
   */
  protected function buildStepFour(array &$form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#markup' => '<h3>Brand Profile Details</h3><p>Help us understand your advertising needs.</p>',
    ];

    $form['field_parent_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Parent Company'),
      '#required' => FALSE,
      '#description' => $this->t('If you are a subsidiary or brand under a larger company'),
      '#default_value' => $form_state->getValue('field_parent_company', ''),
    ];

    $form['field_annual_budget_range'] = [
      '#type' => 'select',
      '#title' => $this->t('Annual Marketing Budget (OOH)'),
      '#required' => FALSE,
      '#options' => [
        '' => '- Select Range -',
        'under_5l' => 'Under 5 Lakhs',
        '5l_20l' => '5-20 Lakhs',
        '20l_50l' => '20-50 Lakhs',
        '50l_1cr' => '50 Lakhs - 1 Crore',
        'over_1cr' => 'Over 1 Crore',
      ],
      '#default_value' => $form_state->getValue('field_annual_budget_range', ''),
    ];

    $form['field_booking_duration'] = [
      '#type' => 'select',
      '#title' => $this->t('Preferred Campaign Duration'),
      '#required' => FALSE,
      '#options' => [
        '' => '- Select Duration -',
        'short_term' => 'Short-term (1-3 months)',
        'seasonal' => 'Seasonal (3-6 months)',
        'annual' => 'Annual (6-12 months)',
        'long_term' => 'Long-term (12+ months)',
      ],
      '#default_value' => $form_state->getValue('field_booking_duration', ''),
    ];

    $divisions = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('division');
    $division_options = [];
    foreach ($divisions as $term) {
      $division_options[$term->tid] = $term->name;
    }

    $form['field_preferred_regions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Preferred Advertising Regions'),
      '#options' => $division_options,
      '#description' => $this->t('Select the regions where you typically advertise'),
      '#default_value' => $form_state->getValue('field_preferred_regions', []),
    ];

    $form['field_business_reg_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Business Registration Number (Optional)'),
      '#required' => FALSE,
      '#description' => $this->t('Trade License or Company Registration Number'),
      '#default_value' => $form_state->getValue('field_business_reg_number', ''),
    ];

    $form['field_tin'] = [
      '#type' => 'textfield',
      '#title' => $this->t('TIN (Optional)'),
      '#required' => FALSE,
      '#description' => $this->t('Tax Identification Number'),
      '#default_value' => $form_state->getValue('field_tin', ''),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#submit' => ['::previousStep'],
      '#limit_validation_errors' => [],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Complete Registration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $step = $form_state->get('step');

    if ($step == 1) {
      // Validate email doesn't already exist
      $email = $form_state->getValue('mail');
      if ($email) {
        $users = $this->entityTypeManager->getStorage('user')->loadByProperties(['mail' => $email]);
        if (!empty($users)) {
          $form_state->setErrorByName('mail', $this->t('This email address is already registered.'));
        }
      }

      // Validate mobile number format
      $mobile = $form_state->getValue('field_mobile_number');
      if ($mobile && !preg_match('/^\+880\d{10}$/', $mobile)) {
        $form_state->setErrorByName('field_mobile_number', $this->t('Please enter a valid Bangladesh mobile number (+8801XXXXXXXXX)'));
      }
    }

    if ($step == 2) {
      // Validate verification code
      $code = $form_state->getValue('verification_code');
      $stored_token = $form_state->get('verification_token');

      if ($code !== $stored_token) {
        $form_state->setErrorByName('verification_code', $this->t('Invalid verification code. Please try again.'));
      }

      // Check if token expired
      $token_expiry = $form_state->get('token_expiry');
      if (time() > $token_expiry) {
        $form_state->setErrorByName('verification_code', $this->t('Verification code has expired. Please request a new one.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $step = $form_state->get('step');

    if ($step == 1) {
      // Create user account
      $user = User::create([
        'name' => $form_state->getValue('mail'),
        'mail' => $form_state->getValue('mail'),
        'pass' => $form_state->getValue('pass'),
        'status' => 0, // Inactive until email verified
        'field_mobile_number' => $form_state->getValue('field_mobile_number'),
        'field_email_verified' => FALSE,
        'field_phone_verified' => FALSE,
      ]);

      // Generate verification token
      $token = bin2hex(random_bytes(16));
      $expiry = time() + 3600; // 1 hour

      $user->set('field_verification_token', $token);
      $user->set('field_token_expiry', $expiry);
      $user->save();

      // Store user ID and token in form state
      $form_state->set('user_id', $user->id());
      $form_state->set('user_email', $user->getEmail());
      $form_state->set('verification_token', $token);
      $form_state->set('token_expiry', $expiry);

      // Send verification email
      $this->sendVerificationEmail($user, $token);

      $this->messenger()->addStatus($this->t('Account created! Please verify your email to continue.'));

      // Move to step 2
      $form_state->set('step', 2);
      $form_state->setRebuild();
    }
    elseif ($step == 2) {
      // Email verified, move to organization setup
      $user = User::load($form_state->get('user_id'));
      $user->set('field_email_verified', TRUE);
      $user->set('status', 1); // Activate account
      $user->save();

      $this->messenger()->addStatus($this->t('Email verified successfully!'));

      // Move to step 3
      $form_state->set('step', 3);
      $form_state->setRebuild();
    }
    elseif ($step == 3) {
      // Store organization data, move to step 4
      $form_state->set('step', 4);
      $form_state->setRebuild();
    }
    elseif ($step == 4) {
      // Create organization node
      $user = User::load($form_state->get('user_id'));

      $preferred_regions = array_filter($form_state->getValue('field_preferred_regions', []));

      $organization = Node::create([
        'type' => 'organization',
        'title' => $form_state->getValue('org_name'),
        'field_org_type' => 'brand',
        'field_official_email' => $form_state->getValue('field_official_email'),
        'field_official_phone' => $form_state->getValue('field_official_phone'),
        'field_website' => $form_state->getValue('field_website') ? ['uri' => $form_state->getValue('field_website')] : NULL,
        'field_division' => $form_state->getValue('field_division'),
        'field_full_address' => $form_state->getValue('field_full_address'),
        'field_primary_admin' => $user->id(),
        'field_verification_status' => 'email_verified',
        'field_trust_score' => 50,
        'field_profile_completion' => 40, // Basic info completed
        'field_parent_company' => $form_state->getValue('field_parent_company'),
        'field_annual_budget_range' => $form_state->getValue('field_annual_budget_range'),
        'field_booking_duration' => $form_state->getValue('field_booking_duration'),
        'field_preferred_regions' => !empty($preferred_regions) ? array_values($preferred_regions) : [],
        'field_business_reg_number' => $form_state->getValue('field_business_reg_number'),
        'field_tin' => $form_state->getValue('field_tin'),
        'status' => 1,
      ]);
      $organization->save();

      // Link organization to user
      $user->set('field_organization', [$organization->id()]);
      $user->set('field_active_organization', $organization->id());
      $user->set('field_is_primary_admin', TRUE);
      $user->save();

      // Assign brand manager role
      $user->addRole('brand_manager');
      $user->save();

      $this->messenger()->addStatus($this->t('Registration complete! Welcome to Billoria.'));

      // Log the user in
      user_login_finalize($user);

      // Redirect to dashboard
      $form_state->setRedirect('billoria_accounts.organization_dashboard');
    }
  }

  /**
   * Previous step handler.
   */
  public function previousStep(array &$form, FormStateInterface $form_state) {
    $step = $form_state->get('step');
    $form_state->set('step', $step - 1);
    $form_state->setRebuild();
  }

  /**
   * Resend verification email.
   */
  public function resendVerificationEmail(array &$form, FormStateInterface $form_state) {
    $user = User::load($form_state->get('user_id'));

    // Generate new token
    $token = bin2hex(random_bytes(16));
    $expiry = time() + 3600;

    $user->set('field_verification_token', $token);
    $user->set('field_token_expiry', $expiry);
    $user->save();

    $form_state->set('verification_token', $token);
    $form_state->set('token_expiry', $expiry);

    $this->sendVerificationEmail($user, $token);

    $this->messenger()->addStatus($this->t('Verification email sent! Please check your inbox.'));
    $form_state->setRebuild();
  }

  /**
   * Send verification email to user.
   */
  protected function sendVerificationEmail(User $user, $token) {
    $verification_url = Url::fromRoute('billoria_accounts.verify_email', [
      'uid' => $user->id(),
      'token' => $token,
    ], [
      'absolute' => TRUE,
    ])->toString();

    $params = [
      'subject' => 'Verify your email for Billoria',
      'body' => "Hello " . $user->getAccountName() . ",\n\n"
        . "Please verify your email address by clicking the link below:\n\n"
        . $verification_url . "\n\n"
        . "Or enter this code in the registration form: " . $token . "\n\n"
        . "This link will expire in 1 hour.\n\n"
        . "If you didn't create this account, please ignore this email.\n\n"
        . "Best regards,\nBilloria Team",
    ];

    $mailManager = \Drupal::service('plugin.manager.mail');
    $mailManager->mail('billoria_accounts', 'verify_email', $user->getEmail(), 'en', $params, NULL, TRUE);

    \Drupal::logger('billoria_accounts')->notice('Verification email sent to @email', [
      '@email' => $user->getEmail(),
    ]);
  }

}

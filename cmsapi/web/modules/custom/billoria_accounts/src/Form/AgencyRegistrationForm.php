<?php

namespace Drupal\billoria_accounts\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Multi-step registration form for Advertising Agency accounts.
 *
 * Extends BrandRegistrationForm with agency-specific fields.
 */
class AgencyRegistrationForm extends BrandRegistrationForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billoria_agency_registration_form';
  }

  /**
   * Step 4: Agency-Specific Profile (override from Brand).
   */
  protected function buildStepFour(array &$form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#markup' => '<h3>Agency Profile Details</h3><p>Tell us about your agency services and expertise.</p>',
    ];

    $form['field_agency_services'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Services Offered'),
      '#required' => FALSE,
      '#options' => [
        'media_planning' => 'Media Planning',
        'creative' => 'Creative Design',
        'ooh' => 'Out-of-Home (OOH)',
        'digital' => 'Digital Marketing',
        'btl' => 'Below The Line (BTL)',
        'events' => 'Events & Activations',
      ],
      '#description' => $this->t('Select all services your agency provides'),
      '#default_value' => $form_state->getValue('field_agency_services', []),
    ];

    $form['field_portfolio_size'] = [
      '#type' => 'select',
      '#title' => $this->t('Client Portfolio Size'),
      '#required' => FALSE,
      '#options' => [
        '' => '- Select Size -',
        'small' => 'Small (1-10 clients)',
        'medium' => 'Medium (10-50 clients)',
        'large' => 'Large (50+ clients)',
      ],
      '#default_value' => $form_state->getValue('field_portfolio_size', ''),
    ];

    $form['field_owns_inventory'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('We own billboard inventory'),
      '#description' => $this->t('Check if your agency owns billboards that you want to list'),
      '#default_value' => $form_state->getValue('field_owns_inventory', 0),
    ];

    $divisions = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('division');
    $division_options = [];
    foreach ($divisions as $term) {
      $division_options[$term->tid] = $term->name;
    }

    $form['field_preferred_regions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Geographic Focus Areas'),
      '#options' => $division_options,
      '#description' => $this->t('Regions where you operate'),
      '#default_value' => $form_state->getValue('field_preferred_regions', []),
    ];

    $form['field_operations_contact'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Operations Contact'),
      '#required' => FALSE,
      '#description' => $this->t('Name and phone of operations manager'),
      '#default_value' => $form_state->getValue('field_operations_contact', ''),
    ];

    $form['field_finance_contact'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Finance Contact'),
      '#required' => FALSE,
      '#description' => $this->t('Name and phone of finance manager'),
      '#default_value' => $form_state->getValue('field_finance_contact', ''),
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $step = $form_state->get('step');

    // Handle steps 1-3 with parent logic
    if ($step < 4) {
      parent::submitForm($form, $form_state);
      return;
    }

    // Step 4: Create agency organization
    if ($step == 4) {
      $user = \Drupal\user\Entity\User::load($form_state->get('user_id'));

      $preferred_regions = array_filter($form_state->getValue('field_preferred_regions', []));
      $agency_services = array_filter($form_state->getValue('field_agency_services', []));

      $organization = \Drupal\node\Entity\Node::create([
        'type' => 'organization',
        'title' => $form_state->getValue('org_name'),
        'field_org_type' => 'agency',
        'field_official_email' => $form_state->getValue('field_official_email'),
        'field_official_phone' => $form_state->getValue('field_official_phone'),
        'field_website' => $form_state->getValue('field_website') ? ['uri' => $form_state->getValue('field_website')] : NULL,
        'field_division' => $form_state->getValue('field_division'),
        'field_full_address' => $form_state->getValue('field_full_address'),
        'field_primary_admin' => $user->id(),
        'field_verification_status' => 'email_verified',
        'field_trust_score' => 50,
        'field_profile_completion' => 40,
        'field_agency_services' => !empty($agency_services) ? array_values($agency_services) : [],
        'field_portfolio_size' => $form_state->getValue('field_portfolio_size'),
        'field_owns_inventory' => $form_state->getValue('field_owns_inventory'),
        'field_preferred_regions' => !empty($preferred_regions) ? array_values($preferred_regions) : [],
        'field_operations_contact' => $form_state->getValue('field_operations_contact'),
        'field_finance_contact' => $form_state->getValue('field_finance_contact'),
        'field_business_reg_number' => $form_state->getValue('field_business_reg_number'),
        'field_tin' => $form_state->getValue('field_tin'),
        'status' => 1,
      ]);
      $organization->save();

      // Link to user
      $user->set('field_organization', [$organization->id()]);
      $user->set('field_active_organization', $organization->id());
      $user->set('field_is_primary_admin', TRUE);
      $user->save();

      // Assign role
      $user->addRole('agency_manager');
      $user->save();

      $this->messenger()->addStatus($this->t('Agency registration complete! Welcome to Billoria.'));

      // Log in
      user_login_finalize($user);

      $form_state->setRedirect('billoria_accounts.organization_dashboard');
    }
  }

}

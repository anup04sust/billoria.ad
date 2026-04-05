<?php

declare(strict_types=1);

namespace Drupal\billoria_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Exposes selective site settings for the frontend.
 */
class SiteSettingsController extends ControllerBase {

  /**
   * Returns public site settings.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with site settings.
   */
  public function get(): JsonResponse {
    $config = $this->config('system.site');
    $theme_settings = $this->config('system.theme.global');

    $data = [
      'site_name' => $config->get('name') ?: 'Billoria',
      'site_slogan' => $config->get('slogan') ?: '',
      'site_mail' => $config->get('mail') ?: '',
      'front_page' => $config->get('page.front') ?: '/',
    ];

    return new JsonResponse([
      'success' => TRUE,
      'data' => $data,
      'timestamp' => time(),
    ]);
  }

}

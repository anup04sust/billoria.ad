<?php

declare(strict_types=1);

namespace Drupal\billoria_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * API Helper Service.
 *
 * Provides helper methods for API integrations and data formatting.
 */
class ApiHelper {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs an ApiHelper object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('billoria_core');
  }

  /**
   * Format billboard for API response.
   *
   * @param \Drupal\node\NodeInterface $billboard
   *   The billboard node.
   *
   * @return array
   *   Formatted billboard data.
   */
  public function formatBillboard(NodeInterface $billboard): array {
    if ($billboard->bundle() !== 'billboard') {
      return [];
    }

    $data = [
      'id' => $billboard->id(),
      'uuid' => $billboard->uuid(),
      'title' => $billboard->getTitle(),
      'billboard_id' => $billboard->hasField('field_billboard_id') ? $billboard->get('field_billboard_id')->value : null,
      'status' => $billboard->isPublished() ? 'published' : 'unpublished',
      'created' => $billboard->getCreatedTime(),
      'updated' => $billboard->getChangedTime(),
    ];

    // Add field data if fields exist.
    $field_mappings = [
      'field_media_format' => 'media_format',
      'field_placement_type' => 'placement_type',
      'field_display_size' => 'display_size',
      'field_width_ft' => 'width_ft',
      'field_height_ft' => 'height_ft',
      'field_division' => 'division',
      'field_district' => 'district',
      'field_upazila_thana' => 'upazila_thana',
      'field_city_corporation' => 'city_corporation',
      'field_area_zone' => 'area_zone',
      'field_road_name' => 'road_name',
      'field_road_type' => 'road_type',
      'field_latitude' => 'latitude',
      'field_longitude' => 'longitude',
      'field_facing_direction' => 'facing_direction',
      'field_traffic_direction' => 'traffic_direction',
      'field_visibility_class' => 'visibility_class',
      'field_illumination_type' => 'illumination_type',
      'field_rate_card_price' => 'rate_card_price',
      'field_currency' => 'currency',
      'field_commercial_score' => 'commercial_score',
      'field_traffic_score' => 'traffic_score',
      'field_booking_mode' => 'booking_mode',
      'field_availability_status' => 'availability_status',
      'field_owner_organization' => 'owner_organization',
      'field_owner_contact_number' => 'owner_contact_number',
      'field_is_premium' => 'is_premium',
      'field_is_active' => 'is_active',
    ];

    foreach ($field_mappings as $field_name => $key) {
      if ($billboard->hasField($field_name) && !$billboard->get($field_name)->isEmpty()) {
        $field = $billboard->get($field_name);
        // Handle entity references (taxonomy terms, nodes)
        if ($field->getFieldDefinition()->getType() === 'entity_reference') {
          $entity = $field->entity;
          if ($entity) {
            $data[$key] = [
              'id' => $entity->id(),
              'label' => $entity->label(),
            ];
          }
        } else {
          $data[$key] = $field->value;
        }
      }
    }

    // Add hero image with all sizes.
    if ($billboard->hasField('field_hero_image') && !$billboard->get('field_hero_image')->isEmpty()) {
      $data['hero_image'] = $this->formatImageField($billboard->get('field_hero_image'), 'hero');
    }

    // Add gallery images.
    if ($billboard->hasField('field_gallery') && !$billboard->get('field_gallery')->isEmpty()) {
      $data['gallery'] = [];
      foreach ($billboard->get('field_gallery') as $gallery_item) {
        if (!$gallery_item->isEmpty()) {
          $data['gallery'][] = $this->formatImageField($gallery_item, 'gallery');
        }
      }
    }

    return $data;
  }

  /**
   * Format image field with multiple sizes.
   *
   * @param mixed $image_field
   *   The image field item.
   * @param string $type
   *   Image type ('hero' or 'gallery').
   *
   * @return array
   *   Formatted image data with URLs for all sizes.
   */
  protected function formatImageField($image_field, string $type = 'hero'): array {
    $image_data = [];

    if (empty($image_field->entity)) {
      return $image_data;
    }

    $file = $image_field->entity;
    $uri = $file->getFileUri();

    // Get the base URL from current request or use default.
    $base_url = \Drupal::request()->getSchemeAndHttpHost();

    // Original image URL.
    $image_data['original'] = $file->createFileUrl();

    // Generate URLs for different sizes based on type.
    if ($type === 'hero') {
      $styles = ['large', 'medium', 'thumbnail'];
      foreach ($styles as $size) {
        $style_name = 'billboard_hero_' . $size;
        if ($style = \Drupal\image\Entity\ImageStyle::load($style_name)) {
          $image_data[$size] = $style->buildUrl($uri);
        }
      }
    } elseif ($type === 'gallery') {
      $styles = ['large', 'thumbnail'];
      foreach ($styles as $size) {
        $style_name = 'billboard_gallery_' . $size;
        if ($style = \Drupal\image\Entity\ImageStyle::load($style_name)) {
          $image_data[$size] = $style->buildUrl($uri);
        }
      }
    }

    // Add metadata.
    $image_data['alt'] = $image_field->alt ?? '';
    $image_data['title'] = $image_field->title ?? '';
    $image_data['width'] = $image_field->width;
    $image_data['height'] = $image_field->height;
    $image_data['mime_type'] = $file->getMimeType();
    $image_data['size'] = $file->getSize();

    return $image_data;
  }

  /**
   * Format multiple billboards for API.
   *
   * @param \Drupal\node\NodeInterface[] $billboards
   *   Array of billboard nodes.
   *
   * @return array
   *   Array of formatted billboard data.
   */
  public function formatBillboards(array $billboards): array {
    $formatted = [];
    foreach ($billboards as $billboard) {
      $formatted[] = $this->formatBillboard($billboard);
    }
    return $formatted;
  }

  /**
   * Normalize taxonomy term for API.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The taxonomy term.
   *
   * @return array
   *   Normalized term data.
   */
  public function normalizeTaxonomyTerm($term): array {
    if (!$term) {
      return [];
    }

    return [
      'id' => $term->id(),
      'uuid' => $term->uuid(),
      'name' => $term->getName(),
      'description' => $term->getDescription(),
      'weight' => $term->getWeight(),
      'langcode' => $term->language()->getId(),
    ];
  }

  /**
   * Build API error response.
   *
   * @param string $message
   *   Error message.
   * @param int $code
   *   HTTP status code.
   *
   * @return array
   *   Error response array.
   */
  public function buildErrorResponse(string $message, int $code = 400): array {
    return [
      'error' => TRUE,
      'message' => $message,
      'code' => $code,
      'timestamp' => time(),
    ];
  }

  /**
   * Build API success response.
   *
   * @param mixed $data
   *   Response data.
   * @param string $message
   *   Optional success message.
   *
   * @return array
   *   Success response array.
   */
  public function buildSuccessResponse($data, string $message = 'Success'): array {
    return [
      'success' => TRUE,
      'message' => $message,
      'data' => $data,
      'timestamp' => time(),
    ];
  }

}

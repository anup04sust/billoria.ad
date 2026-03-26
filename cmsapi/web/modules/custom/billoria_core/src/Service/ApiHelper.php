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
      'code' => $billboard->get('field_billboard_code')->value ?? '',
      'status' => $billboard->isPublished() ? 'published' : 'unpublished',
      'created' => $billboard->getCreatedTime(),
      'updated' => $billboard->getChangedTime(),
    ];

    // Add field data if fields exist.
    $field_mappings = [
      'field_district' => 'district',
      'field_area' => 'area',
      'field_address' => 'address',
      'field_coordinates' => 'coordinates',
      'field_billboard_type' => 'billboard_type',
      'field_width' => 'width',
      'field_height' => 'height',
      'field_base_price' => 'base_price',
      'field_pricing_model' => 'pricing_model',
      'field_availability_status' => 'availability_status',
      'field_verification_status' => 'verification_status',
    ];

    foreach ($field_mappings as $field_name => $key) {
      if ($billboard->hasField($field_name) && !$billboard->get($field_name)->isEmpty()) {
        $data[$key] = $billboard->get($field_name)->value;
      }
    }

    return $data;
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

<?php

declare(strict_types=1);

namespace Drupal\billoria_core\Controller;

use Drupal\billoria_core\Service\ApiHelper;
use Drupal\billoria_core\Service\BillboardManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Billoria API Controller.
 *
 * Provides custom API endpoints for billboard operations.
 */
class BillboardApiController extends ControllerBase {

  /**
   * Constructs a BillboardApiController object.
   */
  public function __construct(
    protected BillboardManager $billboardManager,
    protected ApiHelper $apiHelper,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected AccountInterface $currentUser,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('billoria_core.billboard_manager'),
      $container->get('billoria_core.api_helper'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * Search billboards endpoint.
   */
  public function search(Request $request): JsonResponse {
    if (!$this->currentUser->hasPermission('access billoria api')) {
      return $this->error('Access denied', 403);
    }

    $criteria = $this->filterEmpty([
      'district' => $request->query->get('district'),
      'billboard_type' => $request->query->get('billboard_type'),
      'availability_status' => $request->query->get('availability_status'),
      'verified' => $request->query->get('verified', TRUE),
      'min_price' => $request->query->get('min_price'),
      'max_price' => $request->query->get('max_price'),
    ]);

    $limit = $this->getIntQuery($request, 'limit', 20, 1, 100);
    $offset = $this->getIntQuery($request, 'offset', 0, 0);

    $billboards = $this->billboardManager->getAvailableBillboards($criteria, $limit, $offset);

    return $this->success([
      'billboards' => $this->apiHelper->formatBillboards($billboards),
      'count' => count($billboards),
      'limit' => $limit,
      'offset' => $offset,
    ]);
  }

  /**
   * Check billboard availability endpoint.
   */
  public function checkAvailability(int $billboard_id, Request $request): JsonResponse {
    if (!$this->currentUser->hasPermission('access billoria api')) {
      return $this->error('Access denied', 403);
    }

    $billboard = $this->loadBillboard($billboard_id);
    if (!$billboard) {
      return $this->error('Billboard not found', 404);
    }

    $start_date = (string) $request->query->get('start_date', '');
    $end_date = (string) $request->query->get('end_date', '');

    if ($start_date === '' || $end_date === '') {
      return $this->error('start_date and end_date required', 400);
    }

    $is_available = $this->billboardManager->isAvailableForBooking(
      $billboard,
      $start_date,
      $end_date,
    );

    $pricing = [];
    if ($is_available) {
      $pricing = $this->billboardManager->calculateBillboardPrice(
        $billboard,
        $start_date,
        $end_date,
      );
    }

    return $this->success([
      'available' => $is_available,
      'billboard_id' => $billboard_id,
      'start_date' => $start_date,
      'end_date' => $end_date,
      'pricing' => $pricing,
    ]);
  }

  /**
   * Create billboard endpoint.
   */
  public function createBillboard(Request $request): JsonResponse {
    if (!$this->currentUser->hasPermission('create billboard content')) {
      return $this->error('Access denied', 403);
    }

    $data = json_decode($request->getContent(), TRUE);
    if ($data === NULL) {
      return $this->error('Invalid JSON', 400);
    }

    try {
      $billboard = $this->billboardManager->createBillboard($data);

      return $this->success([
        'billboard_id' => (int) $billboard->id(),
        'billboard' => $this->apiHelper->formatBillboard($billboard),
        'message' => 'Billboard created successfully',
      ], 201);
    }
    catch (\Throwable $e) {
      return $this->error($e->getMessage(), 400);
    }
  }

  /**
   * Get billboard details endpoint.
   */
  public function read(int $billboard_id): JsonResponse {
    $billboard = $this->loadBillboard($billboard_id);
    if (!$billboard) {
      return $this->error('Billboard not found', 404);
    }

    if (!$billboard->access('view', $this->currentUser)) {
      return $this->error('Access denied', 403);
    }

    return $this->success($this->apiHelper->formatBillboard($billboard));
  }

  /**
   * Get billboard details by UUID.
   *
   * GET /api/v1/billboard/uuid/{uuid}
   */
  public function readByUuid(string $uuid): JsonResponse {
    $billboard = $this->loadBillboardByUuid($uuid);
    if (!$billboard) {
      return $this->error('Billboard not found', 404);
    }

    if (!$this->canAccessOwnedBillboard($billboard, 'view')) {
      return $this->error('Access denied', 403);
    }

    return $this->success($this->apiHelper->formatBillboard($billboard));
  }

  /**
   * List billboards endpoint.
   */
  public function list(Request $request): JsonResponse {
    $filters = $this->filterEmpty([
      'division' => $request->query->get('division'),
      'district' => $request->query->get('district'),
      'area_zone' => $request->query->get('area_zone'),
      'media_format' => $request->query->get('media_format'),
      'availability_status' => $request->query->get('availability_status'),
      'is_premium' => $request->query->get('is_premium'),
      'min_price' => $request->query->get('min_price'),
      'max_price' => $request->query->get('max_price'),
      'owner_organization' => $request->query->get('owner_organization'),
    ]);

    $limit = $this->getIntQuery($request, 'limit', 20, 1, 100);
    $offset = $this->getIntQuery($request, 'offset', 0, 0);
    $sort = (string) $request->query->get('sort', 'created');
    $order = strtoupper((string) $request->query->get('order', 'DESC'));
    $order = in_array($order, ['ASC', 'DESC'], TRUE) ? $order : 'DESC';

    $billboards = $this->billboardManager->getAvailableBillboards($filters, $limit, $offset, $sort, $order);

    return $this->success([
      'billboards' => $this->apiHelper->formatBillboards($billboards),
      'count' => count($billboards),
      'limit' => $limit,
      'offset' => $offset,
    ]);
  }

  /**
   * Get billboards owned by current user's organization(s).
   *
   * GET /api/v1/billboard/my-billboards
   */
  public function myBillboards(Request $request): JsonResponse {
    if (!$this->currentUser->isAuthenticated()) {
      return $this->error('User must be logged in', 401);
    }

    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    if (!$user) {
      return $this->error('User not found', 404);
    }

    $organization_ids = $this->getUserOrganizationIds($user);
    if ($organization_ids === []) {
      return $this->success([
        'billboards' => [],
        'count' => 0,
        'review_stats' => [
          'draft' => 0,
          'pending_review' => 0,
          'approved' => 0,
          'revision_requested' => 0,
          'rejected' => 0,
        ],
        'publish_stats' => [
          'published' => 0,
          'unpublished' => 0,
        ],
        'message' => 'No organization found for current user',
      ]);
    }

    $filters = $this->filterEmpty([
      'division' => $request->query->get('division'),
      'district' => $request->query->get('district'),
      'area_zone' => $request->query->get('area_zone'),
      'media_format' => $request->query->get('media_format'),
      'availability_status' => $request->query->get('availability_status'),
      'review_status' => $request->query->get('review_status'),
      'is_premium' => $request->query->get('is_premium'),
      'min_price' => $request->query->get('min_price'),
      'max_price' => $request->query->get('max_price'),
    ]);

    $limit = $this->getIntQuery($request, 'limit', 500, 1, 500);
    $offset = $this->getIntQuery($request, 'offset', 0, 0);
    $sort = (string) $request->query->get('sort', 'created');
    $order = strtoupper((string) $request->query->get('order', 'DESC'));
    $order = in_array($order, ['ASC', 'DESC'], TRUE) ? $order : 'DESC';

    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'billboard')
      ->condition('field_owner_organization', $organization_ids, 'IN');

    $this->applyMyBillboardsFilters($query, $filters);
    $this->applySafeSort($query, $sort, $order);
    $query->range($offset, $limit);

    $nids = $query->execute();
    $nodes = $nids ? $this->entityTypeManager->getStorage('node')->loadMultiple($nids) : [];

    [$review_stats, $publish_stats] = $this->buildOrganizationBillboardStats($organization_ids);

    $count_query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'billboard')
      ->condition('field_owner_organization', $organization_ids, 'IN');
    $this->applyMyBillboardsFilters($count_query, $filters);
    $total = (int) $count_query->count()->execute();

    return $this->success([
      'billboards' => $this->apiHelper->formatBillboards($nodes),
      'count' => count($nodes),
      'total' => $total,
      'limit' => $limit,
      'offset' => $offset,
      'review_stats' => $review_stats,
      'publish_stats' => $publish_stats,
    ]);
  }

  /**
   * Update billboard endpoint.
   */
  public function update(int $billboard_id, Request $request): JsonResponse {
    $billboard = $this->loadBillboard($billboard_id);
    if (!$billboard) {
      return $this->error('Billboard not found', 404);
    }

    if (!$this->canAccessOwnedBillboard($billboard, 'update')) {
      return $this->error('Access denied', 403);
    }

    $data = json_decode($request->getContent(), TRUE);
    if ($data === NULL) {
      return $this->error('Invalid JSON', 400);
    }

    try {
      $this->billboardManager->updateBillboard($billboard, $data);
      $billboard = $this->loadBillboard((int) $billboard->id());

      return $this->success([
        'billboard_id' => (int) $billboard->id(),
        'billboard' => $this->apiHelper->formatBillboard($billboard),
        'message' => 'Billboard updated successfully',
      ]);
    }
    catch (\Throwable $e) {
      return $this->error($e->getMessage(), 400);
    }
  }

  /**
   * Publish billboard endpoint — validates all required fields before publish.
   */
  public function publish(int $billboard_id): JsonResponse {
    $billboard = $this->loadBillboard($billboard_id);
    if (!$billboard) {
      return $this->error('Billboard not found', 404);
    }

    if (!$this->canAccessOwnedBillboard($billboard, 'update')) {
      return $this->error('Access denied', 403);
    }

    try {
      $result = $this->billboardManager->publishBillboard($billboard);

      if (!empty($result['missing'])) {
        return $this->error(
          'Cannot submit — missing required fields: ' . implode(', ', $result['missing']),
          422,
        );
      }

      return $this->success([
        'billboard_id' => (int) $billboard->id(),
        'billboard_generated_id' => $billboard->get('field_billboard_id')->value ?? NULL,
        'review_status' => 'pending_review',
        'message' => 'Billboard submitted for review successfully',
      ]);
    }
    catch (\Throwable $e) {
      return $this->error($e->getMessage(), 400);
    }
  }

  /**
   * Delete billboard endpoint.
   */
  public function delete(int $billboard_id): JsonResponse {
    $billboard = $this->loadBillboard($billboard_id);
    if (!$billboard) {
      return $this->error('Billboard not found', 404);
    }

    if (!$this->canAccessOwnedBillboard($billboard, 'delete')) {
      return $this->error('Access denied', 403);
    }

    try {
      $billboard->delete();

      return $this->success([
        'billboard_id' => $billboard_id,
        'message' => 'Billboard deleted successfully',
      ]);
    }
    catch (\Throwable $e) {
      return $this->error($e->getMessage(), 400);
    }
  }

  /**
   * Autocomplete billboard titles.
   *
   * GET /api/v1/billboard/title-suggest?q=keyword
   */
  public function titleSuggest(Request $request): JsonResponse {
    $query = trim((string) $request->query->get('q', ''));

    if (mb_strlen($query) < 2) {
      return $this->success(['suggestions' => []]);
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $entity_query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'billboard')
      ->condition('status', 1)
      ->condition('title', '%' . $query . '%', 'LIKE')
      ->sort('title', 'ASC')
      ->range(0, 10);

    $nids = $entity_query->execute();
    $suggestions = [];

    if (!empty($nids)) {
      $nodes = $storage->loadMultiple($nids);
      foreach ($nodes as $node) {
        $suggestions[] = [
          'id' => (int) $node->id(),
          'title' => $node->label(),
        ];
      }
    }

    return $this->success(['suggestions' => $suggestions]);
  }

  /**
   * Get billboard field configuration.
   *
   * Returns required/optional field definitions and taxonomy term options
   * for the billboard create/edit form.
   *
   * GET /api/v1/billboard/config
   */
  public function fieldConfig(): JsonResponse {
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');

    $vocabularies = [
      'media_format' => 'media_format',
      'placement_type' => 'placement_type',
      'division' => 'division',
      'district' => 'district',
      'upazila_thana' => 'upazila_thana',
      'city_corporation' => 'city_corporation',
      'area_zone' => 'area_zone',
      'road_name' => 'road_name',
      'road_type' => 'road_type',
      'traffic_direction' => 'traffic_direction',
      'visibility_class' => 'visibility_class',
      'illumination_type' => 'illumination_type',
      'booking_mode' => 'booking_mode',
      'availability_status' => 'availability_status',
    ];

    $options = [];
    $parent_ref_fields = [
      'district' => ['field_division' => 'divisionId'],
      'upazila_thana' => ['field_district' => 'districtId'],
      'city_corporation' => ['field_district' => 'districtId'],
      'area_zone' => [
        'field_district' => 'districtId',
        'field_upazila_thana' => 'upazilaId',
        'field_city_corporation' => 'cityCorporationId',
      ],
    ];

    foreach ($vocabularies as $field_key => $vid) {
      $terms = $term_storage->loadByProperties(['vid' => $vid, 'status' => 1]);
      $items = [];

      foreach ($terms as $term) {
        $item = [
          'id' => (int) $term->id(),
          'label' => $term->getName(),
          'weight' => (int) $term->getWeight(),
        ];

        if (isset($parent_ref_fields[$field_key])) {
          foreach ($parent_ref_fields[$field_key] as $ref_field => $json_key) {
            if ($term->hasField($ref_field) && !$term->get($ref_field)->isEmpty()) {
              $item[$json_key] = (int) $term->get($ref_field)->target_id;
            }
          }
        }

        $items[] = $item;
      }

      usort($items, static function (array $a, array $b): int {
        if ($a['weight'] !== $b['weight']) {
          return $a['weight'] <=> $b['weight'];
        }
        return strcmp($a['label'], $b['label']);
      });

      $options[$field_key] = $items;
    }

    $fields = [
      'title' => [
        'type' => 'text',
        'label' => 'Billboard Title',
        'required' => TRUE,
        'maxlength' => 255,
      ],
      'billboard_id' => [
        'type' => 'text',
        'label' => 'Billboard ID',
        'required' => FALSE,
        'placeholder' => 'BB-DH-001',
      ],
      'owner_organization' => [
        'type' => 'entity_reference',
        'label' => 'Owner Organization',
        'required' => TRUE,
        'description' => 'Loaded from user profile',
      ],
      'media_format' => [
        'type' => 'taxonomy',
        'label' => 'Media Format',
        'required' => TRUE,
      ],
      'placement_type' => [
        'type' => 'taxonomy',
        'label' => 'Placement Type',
        'required' => FALSE,
      ],
      'display_size' => [
        'type' => 'text',
        'label' => 'Display Size',
        'required' => FALSE,
        'placeholder' => '20x30 ft',
      ],
      'width_ft' => [
        'type' => 'decimal',
        'label' => 'Width (feet)',
        'required' => FALSE,
      ],
      'height_ft' => [
        'type' => 'decimal',
        'label' => 'Height (feet)',
        'required' => FALSE,
      ],
      'division' => [
        'type' => 'taxonomy',
        'label' => 'Division',
        'required' => FALSE,
      ],
      'district' => [
        'type' => 'taxonomy',
        'label' => 'District',
        'required' => FALSE,
      ],
      'upazila_thana' => [
        'type' => 'taxonomy',
        'label' => 'Upazila / Thana',
        'required' => FALSE,
      ],
      'city_corporation' => [
        'type' => 'taxonomy',
        'label' => 'City Corporation',
        'required' => FALSE,
      ],
      'area_zone' => [
        'type' => 'taxonomy',
        'label' => 'Area / Zone',
        'required' => FALSE,
      ],
      'road_name' => [
        'type' => 'taxonomy',
        'label' => 'Road Name',
        'required' => FALSE,
      ],
      'road_type' => [
        'type' => 'taxonomy',
        'label' => 'Road Type',
        'required' => FALSE,
      ],
      'latitude' => [
        'type' => 'decimal',
        'label' => 'Latitude',
        'required' => TRUE,
      ],
      'longitude' => [
        'type' => 'decimal',
        'label' => 'Longitude',
        'required' => TRUE,
      ],
      'facing_direction' => [
        'type' => 'select',
        'label' => 'Facing Direction',
        'required' => FALSE,
        'options' => ['north', 'south', 'east', 'west', 'northeast', 'northwest', 'southeast', 'southwest'],
      ],
      'traffic_direction' => [
        'type' => 'taxonomy',
        'label' => 'Traffic Direction',
        'required' => FALSE,
      ],
      'visibility_class' => [
        'type' => 'taxonomy',
        'label' => 'Visibility Class',
        'required' => FALSE,
      ],
      'illumination_type' => [
        'type' => 'taxonomy',
        'label' => 'Illumination Type',
        'required' => FALSE,
      ],
      'rate_card_price' => [
        'type' => 'decimal',
        'label' => 'Rate Card Price',
        'required' => FALSE,
      ],
      'currency' => [
        'type' => 'select',
        'label' => 'Currency',
        'required' => FALSE,
        'default' => 'BDT',
        'options' => ['BDT', 'USD'],
      ],
      'commercial_score' => [
        'type' => 'integer',
        'label' => 'Commercial Score',
        'required' => FALSE,
        'min' => 0,
        'max' => 100,
      ],
      'traffic_score' => [
        'type' => 'integer',
        'label' => 'Traffic Score',
        'required' => FALSE,
        'min' => 0,
        'max' => 100,
      ],
      'booking_mode' => [
        'type' => 'taxonomy',
        'label' => 'Booking Mode',
        'required' => FALSE,
      ],
      'availability_status' => [
        'type' => 'taxonomy',
        'label' => 'Availability Status',
        'required' => FALSE,
      ],
      'owner_contact_number' => [
        'type' => 'text',
        'label' => 'Owner Contact Number',
        'required' => FALSE,
      ],
      'is_premium' => [
        'type' => 'boolean',
        'label' => 'Premium Listing',
        'required' => FALSE,
        'default' => FALSE,
      ],
      'is_active' => [
        'type' => 'boolean',
        'label' => 'Active',
        'required' => FALSE,
        'default' => TRUE,
      ],
      'notes' => [
        'type' => 'textarea',
        'label' => 'Notes',
        'required' => FALSE,
      ],
    ];

    $tabs = [
      [
        'id' => 'basic',
        'label' => 'Basic Info',
        'fields' => ['title', 'billboard_id', 'owner_organization', 'media_format', 'placement_type'],
      ],
      [
        'id' => 'dimensions',
        'label' => 'Dimensions',
        'fields' => ['display_size', 'width_ft', 'height_ft'],
      ],
      [
        'id' => 'location',
        'label' => 'Location',
        'fields' => ['division', 'district', 'upazila_thana', 'city_corporation', 'area_zone', 'road_name', 'road_type', 'latitude', 'longitude', 'facing_direction', 'traffic_direction', 'visibility_class', 'illumination_type'],
      ],
      [
        'id' => 'pricing',
        'label' => 'Pricing & Scores',
        'fields' => ['rate_card_price', 'currency', 'commercial_score', 'traffic_score'],
      ],
      [
        'id' => 'status',
        'label' => 'Status & Options',
        'fields' => ['availability_status', 'booking_mode', 'owner_contact_number', 'is_premium', 'is_active', 'notes'],
      ],
    ];

    return $this->success([
      'fields' => $fields,
      'options' => $options,
      'tabs' => $tabs,
    ]);
  }

  /**
   * Loads a billboard by node ID.
   */
  protected function loadBillboard(int $billboard_id): ?NodeInterface {
    $billboard = $this->entityTypeManager->getStorage('node')->load($billboard_id);
    return ($billboard instanceof NodeInterface && $billboard->bundle() === 'billboard') ? $billboard : NULL;
  }

  /**
   * Loads a billboard by UUID.
   */
  protected function loadBillboardByUuid(string $uuid): ?NodeInterface {
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['uuid' => $uuid, 'type' => 'billboard']);

    $billboard = reset($nodes);
    return $billboard instanceof NodeInterface ? $billboard : NULL;
  }

  /**
   * Checks entity access with organization-ownership fallback.
   */
  protected function canAccessOwnedBillboard(NodeInterface $billboard, string $operation): bool {
    if ($billboard->access($operation, $this->currentUser)) {
      return TRUE;
    }

    $roles = $this->currentUser->getRoles();
    $allowed_roles = ['agency', 'owner', 'platform_admin'];
    $is_allowed_role = count(array_intersect($roles, $allowed_roles)) > 0;

    if (!$is_allowed_role) {
      return FALSE;
    }

    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    if (!$user || !$billboard->hasField('field_owner_organization') || $billboard->get('field_owner_organization')->isEmpty()) {
      return FALSE;
    }

    $owner_org = $billboard->get('field_owner_organization')->entity;
    if (!$owner_org) {
      return FALSE;
    }

    foreach ($this->getUserOrganizationIds($user) as $organization_id) {
      if ((int) $organization_id === (int) $owner_org->id()) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Returns organization IDs attached to a user entity.
   */
  protected function getUserOrganizationIds($user): array {
    $organization_ids = [];

    if ($user->hasField('field_organization') && !$user->get('field_organization')->isEmpty()) {
      foreach ($user->get('field_organization') as $org_ref) {
        if (!empty($org_ref->target_id)) {
          $organization_ids[] = (int) $org_ref->target_id;
        }
      }
    }

    return $organization_ids;
  }

  /**
   * Applies filters to the my-billboards entity query.
   */
  protected function applyMyBillboardsFilters($query, array $filters): void {
    if (isset($filters['division'])) {
      $query->condition('field_division', $filters['division']);
    }
    if (isset($filters['district'])) {
      $query->condition('field_district', $filters['district']);
    }
    if (isset($filters['area_zone'])) {
      $query->condition('field_area_zone', $filters['area_zone']);
    }
    if (isset($filters['media_format'])) {
      $query->condition('field_media_format', $filters['media_format']);
    }
    if (isset($filters['availability_status'])) {
      $query->condition('field_availability_status', $filters['availability_status']);
    }
    if (isset($filters['review_status'])) {
      $query->condition('field_review_status', $filters['review_status']);
    }
    if (isset($filters['is_premium'])) {
      $query->condition('field_is_premium', (int) ((bool) $filters['is_premium']));
    }
    if (isset($filters['min_price'])) {
      $query->condition('field_rate_card_price', $filters['min_price'], '>=');
    }
    if (isset($filters['max_price'])) {
      $query->condition('field_rate_card_price', $filters['max_price'], '<=');
    }
  }

  /**
   * Applies a safe sort field.
   */
  protected function applySafeSort($query, string $sort, string $order): void {
    $allowed_sorts = [
      'created' => 'created',
      'changed' => 'changed',
      'title' => 'title',
      'rate_card_price' => 'field_rate_card_price',
      'traffic_score' => 'field_traffic_score',
      'commercial_score' => 'field_commercial_score',
    ];

    $sort_field = $allowed_sorts[$sort] ?? 'created';
    $query->sort($sort_field, $order);
  }

  /**
   * Builds organization billboard stats.
   */
  protected function buildOrganizationBillboardStats(array $organization_ids): array {
    $stats_query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'billboard')
      ->condition('field_owner_organization', $organization_ids, 'IN');

    $stats_nids = $stats_query->execute();
    $review_stats = [
      'draft' => 0,
      'pending_review' => 0,
      'approved' => 0,
      'revision_requested' => 0,
      'rejected' => 0,
    ];
    $publish_stats = [
      'published' => 0,
      'unpublished' => 0,
    ];

    if (!empty($stats_nids)) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($stats_nids);
      foreach ($nodes as $node) {
        $review_status = ($node->hasField('field_review_status') && !$node->get('field_review_status')->isEmpty())
          ? $node->get('field_review_status')->value
          : 'draft';

        if (isset($review_stats[$review_status])) {
          $review_stats[$review_status]++;
        }

        if ($node->isPublished()) {
          $publish_stats['published']++;
        }
        else {
          $publish_stats['unpublished']++;
        }
      }
    }

    return [$review_stats, $publish_stats];
  }

  /**
   * Removes only NULL and empty-string values.
   */
  protected function filterEmpty(array $values): array {
    return array_filter($values, static fn ($value): bool => $value !== NULL && $value !== '');
  }

  /**
   * Safely reads and clamps an integer query parameter.
   */
  protected function getIntQuery(Request $request, string $key, int $default, int $min = PHP_INT_MIN, ?int $max = NULL): int {
    $value = (int) $request->query->get($key, $default);
    $value = max($min, $value);

    if ($max !== NULL) {
      $value = min($max, $value);
    }

    return $value;
  }

  /**
   * Builds a success JSON response.
   */
  protected function success(array $data, int $status = 200): JsonResponse {
    return new JsonResponse($this->apiHelper->buildSuccessResponse($data), $status);
  }

  /**
   * Builds an error JSON response.
   */
  protected function error(string $message, int $status): JsonResponse {
    return new JsonResponse($this->apiHelper->buildErrorResponse($message, $status), $status);
  }

}
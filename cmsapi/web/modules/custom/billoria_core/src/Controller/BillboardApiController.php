<?php

declare(strict_types=1);

namespace Drupal\billoria_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\billoria_core\Service\BillboardManager;
use Drupal\billoria_core\Service\ApiHelper;
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
   * The billboard manager.
   *
   * @var \Drupal\billoria_core\Service\BillboardManager
   */
  protected BillboardManager $billboardManager;

  /**
   * The API helper.
   *
   * @var \Drupal\billoria_core\Service\ApiHelper
   */
  protected ApiHelper $apiHelper;

  /**
   * Constructs a BillboardApiController object.
   *
   * @param \Drupal\billoria_core\Service\BillboardManager $billboard_manager
   *   The billboard manager.
   * @param \Drupal\billoria_core\Service\ApiHelper $api_helper
   *   The API helper.
   */
  public function __construct(
    BillboardManager $billboard_manager,
    ApiHelper $api_helper
  ) {
    $this->billboardManager = $billboard_manager;
    $this->apiHelper = $api_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('billoria_core.billboard_manager'),
      $container->get('billoria_core.api_helper')
    );
  }

  /**
   * Search billboards endpoint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response.
   */
  public function search(Request $request): JsonResponse {
    // Check API access permission.
    if (!$this->currentUser()->hasPermission('access billoria api')) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Access denied', 403),
        403
      );
    }

    // Get search parameters.
    $criteria = [
      'district' => $request->query->get('district'),
      'billboard_type' => $request->query->get('billboard_type'),
      'availability_status' => $request->query->get('availability_status'),
      'verified' => $request->query->get('verified', TRUE),
      'min_price' => $request->query->get('min_price'),
      'max_price' => $request->query->get('max_price'),
    ];

    // Remove empty criteria.
    $criteria = array_filter($criteria, fn($value) => $value !== NULL && $value !== '');

    $limit = (int) $request->query->get('limit', 20);
    $offset = (int) $request->query->get('offset', 0);

    // Get billboards.
    $billboards = $this->billboardManager->getAvailableBillboards($criteria, $limit, $offset);

    // Format response.
    $formatted = $this->apiHelper->formatBillboards($billboards);

    return new JsonResponse(
      $this->apiHelper->buildSuccessResponse([
        'billboards' => $formatted,
        'count' => count($formatted),
        'limit' => $limit,
        'offset' => $offset,
      ])
    );
  }

  /**
   * Check billboard availability endpoint.
   *
   * @param int $billboard_id
   *   Billboard node ID.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response.
   */
  public function checkAvailability(int $billboard_id, Request $request): JsonResponse {
    if (!$this->currentUser()->hasPermission('access billoria api')) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Access denied', 403),
        403
      );
    }

    $storage = $this->entityTypeManager()->getStorage('node');
    $billboard = $storage->load($billboard_id);

    if (!$billboard || $billboard->bundle() !== 'billboard') {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Billboard not found', 404),
        404
      );
    }

    $start_date = $request->query->get('start_date');
    $end_date = $request->query->get('end_date');

    if (!$start_date || !$end_date) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('start_date and end_date required', 400),
        400
      );
    }

    $is_available = $this->billboardManager->isAvailableForBooking(
      $billboard,
      $start_date,
      $end_date
    );

    $pricing = [];
    if ($is_available) {
      $pricing = $this->billboardManager->calculateBillboardPrice(
        $billboard,
        $start_date,
        $end_date
      );
    }

    return new JsonResponse(
      $this->apiHelper->buildSuccessResponse([
        'available' => $is_available,
        'billboard_id' => $billboard_id,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'pricing' => $pricing,
      ])
    );
  }

  /**
   * Create billboard endpoint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response.
   */
  public function createBillboard(Request $request): JsonResponse {
    // Check permission.
    if (!$this->currentUser()->hasPermission('create billboard content')) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Access denied', 403),
        403
      );
    }

    $data = json_decode($request->getContent(), TRUE);
    if (!$data) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Invalid JSON', 400),
        400
      );
    }

    try {
      $billboard = $this->billboardManager->createBillboard($data);

      return new JsonResponse(
        $this->apiHelper->buildSuccessResponse([
          'billboard_id' => $billboard->id(),
          'message' => 'Billboard created successfully',
        ]),
        201
      );
    }
    catch (\Exception $e) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse($e->getMessage(), 400),
        400
      );
    }
  }

  /**
   * Get billboard details endpoint.
   *
   * @param int $billboard_id
   *   Billboard node ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response.
   */
  public function read(int $billboard_id): JsonResponse {
    $storage = $this->entityTypeManager()->getStorage('node');
    $billboard = $storage->load($billboard_id);

    if (!$billboard || $billboard->bundle() !== 'billboard') {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Billboard not found', 404),
        404
      );
    }

    // Check access.
    if (!$billboard->access('view', $this->currentUser())) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Access denied', 403),
        403
      );
    }

    $formatted = $this->apiHelper->formatBillboard($billboard);

    return new JsonResponse(
      $this->apiHelper->buildSuccessResponse($formatted)
    );
  }

  /**
   * List billboards endpoint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response.
   */
  public function list(Request $request): JsonResponse {
    $filters = [
      'division' => $request->query->get('division'),
      'district' => $request->query->get('district'),
      'area_zone' => $request->query->get('area_zone'),
      'media_format' => $request->query->get('media_format'),
      'availability_status' => $request->query->get('availability_status'),
      'is_premium' => $request->query->get('is_premium'),
      'min_price' => $request->query->get('min_price'),
      'max_price' => $request->query->get('max_price'),
      'owner_organization' => $request->query->get('owner_organization'),
    ];

    // Remove null values.
    $filters = array_filter($filters, fn($value) => $value !== NULL && $value !== '');

    $limit = (int) $request->query->get('limit', 20);
    $offset = (int) $request->query->get('offset', 0);
    $sort = $request->query->get('sort', 'created');
    $order = $request->query->get('order', 'DESC');

    $billboards = $this->billboardManager->getAvailableBillboards($filters, $limit, $offset, $sort, $order);
    $formatted = $this->apiHelper->formatBillboards($billboards);

    return new JsonResponse(
      $this->apiHelper->buildSuccessResponse([
        'billboards' => $formatted,
        'count' => count($formatted),
        'limit' => $limit,
        'offset' => $offset,
      ])
    );
  }

  /**
   * Update billboard endpoint.
   *
   * @param int $billboard_id
   *   Billboard node ID.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response.
   */
  public function update(int $billboard_id, Request $request): JsonResponse {
    $storage = $this->entityTypeManager()->getStorage('node');
    $billboard = $storage->load($billboard_id);

    if (!$billboard || $billboard->bundle() !== 'billboard') {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Billboard not found', 404),
        404
      );
    }

    // Check permission.
    if (!$billboard->access('update', $this->currentUser())) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Access denied', 403),
        403
      );
    }

    $data = json_decode($request->getContent(), TRUE);
    if (!$data) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Invalid JSON', 400),
        400
      );
    }

    try {
      $this->billboardManager->updateBillboard($billboard, $data);

      return new JsonResponse(
        $this->apiHelper->buildSuccessResponse([
          'billboard_id' => $billboard->id(),
          'message' => 'Billboard updated successfully',
        ])
      );
    }
    catch (\Exception $e) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse($e->getMessage(), 400),
        400
      );
    }
  }

  /**
   * Delete billboard endpoint.
   *
   * @param int $billboard_id
   *   Billboard node ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response.
   */
  public function delete(int $billboard_id): JsonResponse {
    $storage = $this->entityTypeManager()->getStorage('node');
    $billboard = $storage->load($billboard_id);

    if (!$billboard || $billboard->bundle() !== 'billboard') {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Billboard not found', 404),
        404
      );
    }

    // Check permission.
    if (!$billboard->access('delete', $this->currentUser())) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse('Access denied', 403),
        403
      );
    }

    try {
      $billboard->delete();

      return new JsonResponse(
        $this->apiHelper->buildSuccessResponse([
          'billboard_id' => $billboard_id,
          'message' => 'Billboard deleted successfully',
        ])
      );
    }
    catch (\Exception $e) {
      return new JsonResponse(
        $this->apiHelper->buildErrorResponse($e->getMessage(), 400),
        400
      );
    }
  }

}

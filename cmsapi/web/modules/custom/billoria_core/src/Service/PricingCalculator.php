<?php

declare(strict_types=1);

namespace Drupal\billoria_core\Service;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * Pricing Calculator Service.
 *
 * Handles price calculations for billboard bookings.
 */
class PricingCalculator {

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a PricingCalculator object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->logger = $logger_factory->get('billoria_core');
  }

  /**
   * Calculate booking price.
   *
   * @param \Drupal\node\NodeInterface $billboard
   *   The billboard node.
   * @param string $start_date
   *   Start date in YYYY-MM-DD format.
   * @param string $end_date
   *   End date in YYYY-MM-DD format.
   *
   * @return array
   *   Pricing breakdown.
   */
  public function calculate(NodeInterface $billboard, string $start_date, string $end_date): array {
    $base_price = 0;
    $pricing_model = 'monthly';

    // Get base price.
    if ($billboard->hasField('field_base_price') && !$billboard->get('field_base_price')->isEmpty()) {
      $base_price = (float) $billboard->get('field_base_price')->value;
    }

    // Get pricing model.
    if ($billboard->hasField('field_pricing_model') && !$billboard->get('field_pricing_model')->isEmpty()) {
      $pricing_model = $billboard->get('field_pricing_model')->value;
    }

    // Calculate duration.
    $start = new \DateTime($start_date);
    $end = new \DateTime($end_date);
    $duration_days = $start->diff($end)->days;

    // Calculate total based on pricing model.
    $total = 0;
    $unit_count = 0;

    switch ($pricing_model) {
      case 'daily':
        $unit_count = $duration_days;
        $total = $base_price * $duration_days;
        break;

      case 'weekly':
        $unit_count = ceil($duration_days / 7);
        $total = $base_price * $unit_count;
        break;

      case 'monthly':
        $unit_count = ceil($duration_days / 30);
        $total = $base_price * $unit_count;
        break;

      case 'campaign':
        $unit_count = 1;
        $total = $base_price;
        break;

      default:
        $unit_count = $duration_days;
        $total = $base_price * $duration_days;
    }

    // Apply discounts for longer bookings.
    $discount_rate = $this->calculateDiscountRate($duration_days);
    $discount_amount = $total * $discount_rate;
    $final_total = $total - $discount_amount;

    return [
      'base_price' => $base_price,
      'pricing_model' => $pricing_model,
      'duration_days' => $duration_days,
      'unit_count' => $unit_count,
      'subtotal' => $total,
      'discount_rate' => $discount_rate,
      'discount_amount' => $discount_amount,
      'total' => $final_total,
      'currency' => 'BDT',
    ];
  }

  /**
   * Calculate discount rate based on duration.
   *
   * @param int $duration_days
   *   Number of days.
   *
   * @return float
   *   Discount rate (0.0 to 1.0).
   */
  protected function calculateDiscountRate(int $duration_days): float {
    // Discount tiers (configurable later).
    if ($duration_days >= 365) {
      return 0.20; // 20% discount for yearly bookings.
    }
    elseif ($duration_days >= 180) {
      return 0.15; // 15% discount for 6+ months.
    }
    elseif ($duration_days >= 90) {
      return 0.10; // 10% discount for 3+ months.
    }
    elseif ($duration_days >= 30) {
      return 0.05; // 5% discount for 1+ month.
    }

    return 0.0; // No discount for short durations.
  }

}

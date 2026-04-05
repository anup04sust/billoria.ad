<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require __DIR__ . '/../vendor/autoload.php';
$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
$container = $kernel->getContainer();
$container->get('request_stack')->push($request);

$storage = \Drupal::entityTypeManager()->getStorage('node');

$total = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'billboard')->count()->execute();
$published = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'billboard')->condition('status', 1)->count()->execute();
$unpublished = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'billboard')->condition('status', 0)->count()->execute();

echo "Total billboards: $total\n";
echo "Published: $published\n";
echo "Unpublished: $unpublished\n";

// Check review statuses
$nids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'billboard')->execute();
$nodes = $storage->loadMultiple($nids);
$review_counts = [];
foreach ($nodes as $node) {
  $status = $node->hasField('field_review_status') && !$node->get('field_review_status')->isEmpty()
    ? $node->get('field_review_status')->value : 'none';
  $review_counts[$status] = ($review_counts[$status] ?? 0) + 1;
}
echo "\nReview status breakdown:\n";
foreach ($review_counts as $status => $count) {
  echo "  $status: $count\n";
}

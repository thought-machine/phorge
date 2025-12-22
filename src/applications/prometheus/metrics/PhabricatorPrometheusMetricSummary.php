<?php

use Prometheus\CollectorRegistry;

/**
 * @phutil-external-symbol class CollectorRegistry
 */
abstract class PhabricatorPrometheusMetricSummary extends PhabricatorPrometheusMetric {

  final public function register(CollectorRegistry $registry): void {
    $this->metric = $registry->getOrRegisterSummary(
      self::METRIC_NAMESPACE,
      $this->getName(),
      $this->getHelp(),
      $this->getLabels(),
      maxAgeSeconds: $this->getMaxAgeSeconds());
  }

  public function getMaxAgeSeconds(): int {
    return 600;
  }

  final public function observe(float $value, array $labels): void
  {
    $this->metric->observe($value, $labels);
  }
}

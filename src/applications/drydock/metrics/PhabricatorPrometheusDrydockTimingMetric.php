<?php

final class PhabricatorPrometheusDrydockTimingMetric extends PhabricatorPrometheusMetricSummary {
  const CMD_KEY = "command_key";

  public function getName(): string {
    return 'drydock_command_time_taken_seconds';
  }

  public function getHelp(): string {
    return 'A summary of the time taken by various commands ran by Drydock';
  }

  public function getLabels(): array {
    return [self::CMD_KEY];
  }

  public function getValues(): array
  {
    // This function is not used, instead we call observe() for each observation.
    return [];
  }

  public function timeCommand(string $command_key, callable $function) {
    $start_time = microtime(true);
    $function();
    $end_time = microtime(true);
    $time_elapsed = $end_time - $start_time;
    $this->observe($time_elapsed, array(self::CMD_KEY => $command_key));
  }
}

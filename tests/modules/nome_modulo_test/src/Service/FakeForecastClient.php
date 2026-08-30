<?php

declare(strict_types=1);

namespace Drupal\nome_modulo_test\Service;

use Drupal\nome_modulo\Service\ForecastClientInterface;

/**
 * Provides predictable forecast data for tests.
 */
final class FakeForecastClient implements ForecastClientInterface {

  /**
   * {@inheritdoc}
   */
  public function getForecast(): array {
    return [
      'temperature' => 20,
      'weather_code' => 0,
    ];
  }

}

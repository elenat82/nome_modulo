<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Service;

/**
 * Retrieves weather forecast data.
 */
final class ForecastClient implements ForecastClientInterface {

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

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
  public function getForecast(): ?array {
    return [
      'location' => 'Test location',
      'timezone' => 'Europe/Rome',
      'temperature_unit' => '°C',
      'days' => [
      [
        'date' => '2026-09-01',
        'weather_code' => 0,
        'high' => 25.0,
        'low' => 15.0,
      ],
      [
        'date' => '2026-09-02',
        'weather_code' => 1,
        'high' => 24.0,
        'low' => 14.0,
      ],
      [
        'date' => '2026-09-03',
        'weather_code' => 2,
        'high' => 23.0,
        'low' => 13.0,
      ],
      ],
    ];
  }

}

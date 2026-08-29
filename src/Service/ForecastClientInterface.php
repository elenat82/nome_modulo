<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Service;

/**
 * Defines an interface for retrieving weather forecast data.
 */
interface ForecastClientInterface {

  /**
   * Gets weather forecast data.
   *
   * @return array
   *   The weather forecast data.
   */
  public function getForecast(): array;

}

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
   * @return array{
   *   location: string,
   *   timezone: string,
   *   temperature_unit: string,
   *   days: list<array{
   *     date: string,
   *     weather_code: int,
   *     high: float,
   *     low: float
   *   }>
   *   }|null
   *   The normalized forecast data, or NULL when it cannot be retrieved.
   */
  public function getForecast(): ?array;

}

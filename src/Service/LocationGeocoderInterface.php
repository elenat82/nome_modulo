<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Service;

/**
 * Provides location geocoding.
 */
interface LocationGeocoderInterface {

  /**
   * Searches for locations matching the given query.
   *
   * @param string $query
   *   The location name or search query.
   *
   * @return list<array{
   *   name: string,
   *   country: string|null,
   *   admin1: string|null,
   *   latitude: float,
   *   longitude: float,
   *   timezone: string
   *   }>
   *   The matching normalized locations.
   */
  public function search(string $query): array;

}

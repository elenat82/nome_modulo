<?php

declare(strict_types=1);

namespace Drupal\nome_modulo_test\Service;

use Drupal\nome_modulo\Service\LocationGeocoderInterface;

/**
 * Provides deterministic location geocoding for tests.
 */
final class FakeLocationGeocoder implements LocationGeocoderInterface {

  /**
   * Searches for test locations matching the given query.
   *
   * @param string $query
   *   The location search query.
   *
   * @return array
   *   The matching test locations.
   */
  public function search(string $query): array {
    return match (mb_strtolower(trim($query))) {
      'turin' => [
        [
          'name' => 'Turin',
          'country' => 'Italy',
          'admin1' => 'Piedmont',
          'latitude' => 45.07049,
          'longitude' => 7.68682,
          'timezone' => 'Europe/Rome',
        ],
      ],

      'springfield' => [
        [
          'name' => 'Springfield',
          'country' => 'United States',
          'admin1' => 'Illinois',
          'latitude' => 39.80172,
          'longitude' => -89.64371,
          'timezone' => 'America/Chicago',
        ],
        [
          'name' => 'Springfield',
          'country' => 'United States',
          'admin1' => 'Massachusetts',
          'latitude' => 42.10148,
          'longitude' => -72.58981,
          'timezone' => 'America/New_York',
        ],
      ],

      'milan' => [
      [
        'name' => 'Milan',
        'country' => 'Italy',
        'admin1' => 'Lombardy',
        'latitude' => 45.46427,
        'longitude' => 9.18951,
        'timezone' => 'Europe/Rome',
      ],
      ],

      default => [],
    };
  }

}

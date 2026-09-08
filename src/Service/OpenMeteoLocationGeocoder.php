<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Provides location geocoding through Open-Meteo.
 */
final class OpenMeteoLocationGeocoder implements LocationGeocoderInterface {

  /**
   * The Open-Meteo geocoding endpoint.
   */
  private const ENDPOINT = 'https://geocoding-api.open-meteo.com/v1/search';

  /**
   * The maximum number of location candidates to request.
   */
  private const RESULT_LIMIT = 5;

  /**
   * Constructs an OpenMeteoLocationGeocoder object.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
  ) {}

  /**
   * Searches for locations matching the given query.
   */
  public function search(string $query): array {
    $query = trim($query);

    if (mb_strlen($query) < 2) {
      return [];
    }

    try {
      $response = $this->httpClient->request(
        'GET',
        self::ENDPOINT,
        [
          'query' => [
            'name' => $query,
            'count' => self::RESULT_LIMIT,
            'format' => 'json',
            'language' => 'en',
          ],
        ],
      );

      $data = json_decode(
        (string) $response->getBody(),
        TRUE,
        512,
        JSON_THROW_ON_ERROR,
      );
    }
    catch (GuzzleException | \JsonException) {
      return [];
    }

    if (
      !is_array($data) ||
      !isset($data['results']) ||
      !is_array($data['results'])
    ) {
      return [];
    }

    $locations = [];

    foreach ($data['results'] as $result) {
      if (
        !is_array($result) ||
        !isset(
          $result['name'],
          $result['latitude'],
          $result['longitude'],
          $result['timezone'],
        ) ||
        !is_string($result['name']) ||
        !is_numeric($result['latitude']) ||
        !is_numeric($result['longitude']) ||
        !is_string($result['timezone'])
      ) {
        continue;
      }

      $locations[] = [
        'name' => $result['name'],
        'country' => isset($result['country']) &&
        is_string($result['country'])
          ? $result['country']
          : NULL,
        'admin1' => isset($result['admin1']) &&
        is_string($result['admin1'])
          ? $result['admin1']
          : NULL,
        'latitude' => (float) $result['latitude'],
        'longitude' => (float) $result['longitude'],
        'timezone' => $result['timezone'],
      ];
    }

    return $locations;
  }

}

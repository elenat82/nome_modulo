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
   * The connection timeout in seconds.
   */
  private const CONNECT_TIMEOUT = 5;

  /**
   * The request timeout in seconds.
   */
  private const REQUEST_TIMEOUT = 10;

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
        'connect_timeout' => self::CONNECT_TIMEOUT,
        'timeout' => self::REQUEST_TIMEOUT,
        'allow_redirects' => FALSE,
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
    $validTimezones = \DateTimeZone::listIdentifiers();

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
      trim($result['name']) === '' ||
      !is_numeric($result['latitude']) ||
      !is_numeric($result['longitude']) ||
      !is_string($result['timezone'])
      ) {
        continue;
      }

      $latitude = (float) $result['latitude'];
      $longitude = (float) $result['longitude'];
      $timezone = $result['timezone'];

      if (
      $latitude < -90 ||
      $latitude > 90 ||
      $longitude < -180 ||
      $longitude > 180 ||
      !in_array(
        $timezone,
        $validTimezones,
        TRUE,
      )
      ) {
        continue;
      }

      $locations[] = [
        'name' => trim($result['name']),
        'country' => isset($result['country']) &&
        is_string($result['country'])
          ? trim($result['country'])
          : NULL,
        'admin1' => isset($result['admin1']) &&
        is_string($result['admin1'])
          ? trim($result['admin1'])
          : NULL,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'timezone' => $timezone,
      ];
    }

    return $locations;
  }

}

<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Service;

use GuzzleHttp\ClientInterface;

/**
 * Retrieves weather forecast data.
 */
final class ForecastClient implements ForecastClientInterface {

  /**
   * The Open-Meteo forecast API endpoint.
   */
  private const API_URL = 'https://api.open-meteo.com/v1/forecast';

  /**
   * Temporary latitude used during development.
   */
  private const LATITUDE = 41.9028;

  /**
   * Temporary longitude used during development.
   */
  private const LONGITUDE = 12.4964;

  /**
   * Constructs a ForecastClient object.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getForecast(): array {
    $response = $this->httpClient->request('GET', self::API_URL, [
      'query' => [
        'latitude' => self::LATITUDE,
        'longitude' => self::LONGITUDE,
        'current' => 'temperature_2m,weather_code',
        'timezone' => 'auto',
      ],
    ]);

    $data = json_decode(
      (string) $response->getBody(),
      TRUE,
      512,
      JSON_THROW_ON_ERROR,
    );

    return [
      'temperature' => $data['current']['temperature_2m'],
      'weather_code' => $data['current']['weather_code'],
    ];
  }

}

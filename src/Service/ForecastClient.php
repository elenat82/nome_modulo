<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
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
   * Constructs a ForecastClient object.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getForecast(): array {
    $config = $this->configFactory->get('nome_modulo.settings');

    $latitude = (float) $config->get('latitude');
    $longitude = (float) $config->get('longitude');

    $response = $this->httpClient->request('GET', self::API_URL, [
      'query' => [
        'latitude' => $latitude,
        'longitude' => $longitude,
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

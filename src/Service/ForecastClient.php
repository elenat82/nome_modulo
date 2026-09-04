<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\nome_modulo\Cache\ForecastCache;

/**
 * Retrieves and normalizes weather forecast data.
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
   *   The HTTP client used to communicate with the weather provider.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory used to retrieve module settings.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend used to store forecast data.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service used to calculate cache expiration.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly CacheBackendInterface $cache,
    private readonly TimeInterface $time,
  ) {}

  /**
   * Retrieves and normalizes the weather forecast from Open-Meteo.
   *
   * Uses the module configuration to request daily weather forecast data
   * from Open-Meteo and converts the provider response into the module's
   * internal forecast structure.
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
  public function getForecast(): ?array {
    $config = $this->configFactory->get('nome_modulo.settings');

    $latitude = $config->get('latitude');
    $longitude = $config->get('longitude');

    if (!is_numeric($latitude) || !is_numeric($longitude)) {
      return NULL;
    }

    $location = (string) ($config->get('location') ?? '');
    $timezone = (string) ($config->get('timezone') ?? 'UTC');

    $forecastDays = (int) ($config->get('forecast_days') ?? 5);
    $forecastDays = max(1, min(16, $forecastDays));

    $temperatureUnit = (string) (
      $config->get('temperature_unit') ?? 'celsius'
    );

    if (!in_array(
      $temperatureUnit,
      ['celsius', 'fahrenheit'],
      TRUE,
    )) {
      $temperatureUnit = 'celsius';
    }

    $cacheId = $this->buildCacheId(
    (float) $latitude,
    (float) $longitude,
    $timezone,
    $forecastDays,
    $temperatureUnit,
    );

    $cached = $this->cache->get($cacheId);

    if ($cached !== FALSE && is_array($cached->data)) {
      return $cached->data;
    }

    try {
      $response = $this->httpClient->request(
        'GET',
        self::API_URL,
        [
          'query' => [
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
            'daily' => implode(',', [
              'weather_code',
              'temperature_2m_max',
              'temperature_2m_min',
            ]),
            'timezone' => $timezone,
            'forecast_days' => $forecastDays,
            'temperature_unit' => $temperatureUnit,
          ],
          'headers' => [
            'Accept' => 'application/json',
          ],
          'timeout' => 10,
        ],
      );

      $data = json_decode(
        (string) $response->getBody(),
        TRUE,
        512,
        JSON_THROW_ON_ERROR,
      );

      if (!is_array($data)) {
        throw new \UnexpectedValueException(
          'The weather provider returned an invalid response.',
        );
      }

      $forecast = $this->normalizeForecast($data);
      $forecast['location'] = $location;

      $this->cache->set(
      $cacheId,
      $forecast,
      $this->time->getCurrentTime() + ForecastCache::MAX_AGE,
      [ForecastCache::TAG],
      );

      return $forecast;
    }
    catch (
      GuzzleException |
      \JsonException |
      \UnexpectedValueException
    ) {
      return NULL;
    }
  }

  /**
   * Builds a cache ID for the requested forecast.
   */
  private function buildCacheId(
    float $latitude,
    float $longitude,
    string $timezone,
    int $forecastDays,
    string $temperatureUnit,
  ): string {
    $parameters = implode(':', [
      $latitude,
      $longitude,
      $timezone,
      $forecastDays,
      $temperatureUnit,
    ]);

    return 'nome_modulo:forecast:' . hash(
    'sha256',
    $parameters,
    );
  }

  /**
   * Converts the provider response into the module's internal format.
   *
   * @param array<string, mixed> $data
   *   The decoded provider response.
   *
   * @return array{
   *   timezone: string,
   *   temperature_unit: string,
   *   days: list<array{
   *     date: string,
   *     weather_code: int,
   *     high: float,
   *     low: float
   *   }>
   *   }
   *   The normalized forecast.
   */
  private function normalizeForecast(array $data): array {
    $daily = $data['daily'] ?? NULL;
    $dailyUnits = $data['daily_units'] ?? NULL;

    if (!is_array($daily) || !is_array($dailyUnits)) {
      throw new \UnexpectedValueException(
        'The response does not contain daily forecast data.',
      );
    }

    $dates = $daily['time'] ?? NULL;
    $weatherCodes = $daily['weather_code'] ?? NULL;
    $highs = $daily['temperature_2m_max'] ?? NULL;
    $lows = $daily['temperature_2m_min'] ?? NULL;

    if (
      !is_array($dates) ||
      !is_array($weatherCodes) ||
      !is_array($highs) ||
      !is_array($lows)
    ) {
      throw new \UnexpectedValueException(
        'The daily forecast has an invalid structure.',
      );
    }

    $numberOfDays = count($dates);

    if (
      count($weatherCodes) !== $numberOfDays ||
      count($highs) !== $numberOfDays ||
      count($lows) !== $numberOfDays
    ) {
      throw new \UnexpectedValueException(
        'The daily forecast contains inconsistent data.',
      );
    }

    $days = [];

    foreach ($dates as $index => $date) {
      if (
        !is_string($date) ||
        !is_numeric($weatherCodes[$index]) ||
        !is_numeric($highs[$index]) ||
        !is_numeric($lows[$index])
      ) {
        throw new \UnexpectedValueException(
          'The daily forecast contains an invalid value.',
        );
      }

      $days[] = [
        'date' => $date,
        'weather_code' => (int) $weatherCodes[$index],
        'high' => (float) $highs[$index],
        'low' => (float) $lows[$index],
      ];
    }

    return [
      'timezone' => (string) ($data['timezone'] ?? 'UTC'),
      'temperature_unit' => (string) (
        $dailyUnits['temperature_2m_max'] ?? ''
      ),
      'days' => $days,
    ];
  }

}

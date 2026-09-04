<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Unit;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\nome_modulo\Cache\ForecastCache;
use Drupal\nome_modulo\Service\ForecastClient;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests the weather forecast client.
 */
#[Group('nome_modulo')]
final class ForecastClientTest extends UnitTestCase {

  /**
   * The mocked HTTP client.
   */
  private ClientInterface&MockObject $httpClient;

  /**
   * The mocked forecast cache backend.
   */
  private CacheBackendInterface&MockObject $cache;

  /**
   * The mocked time service.
   */
  private TimeInterface&MockObject $time;

  /**
   * Sets up the test dependencies.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->httpClient = $this->createMock(ClientInterface::class);
    $this->cache = $this->createMock(CacheBackendInterface::class);
    $this->time = $this->createMock(TimeInterface::class);
  }

  /**
   * Tests that cached forecast data is returned without an HTTP request.
   */
  public function testReturnsCachedForecastWithoutHttpRequest(): void {
    $forecast = [
      'location' => 'Turin',
      'timezone' => 'Europe/Rome',
      'temperature_unit' => '°C',
      'days' => [
        [
          'date' => '2026-09-04',
          'weather_code' => 0,
          'high' => 25.0,
          'low' => 15.0,
        ],
      ],
    ];

    $cacheItem = new \stdClass();
    $cacheItem->data = $forecast;

    $this->cache
      ->expects($this->once())
      ->method('get')
      ->with($this->getExpectedCacheId())
      ->willReturn($cacheItem);

    $this->cache
      ->expects($this->never())
      ->method('set');

    $this->httpClient
      ->expects($this->never())
      ->method('request');

    $this->time
      ->expects($this->never())
      ->method('getCurrentTime');

    $client = $this->createForecastClient();

    $this->assertSame(
      $forecast,
      $client->getForecast(),
    );
  }

  /**
   * Tests retrieving, normalizing, and caching forecast data.
   */
  public function testRetrievesNormalizesAndCachesForecast(): void {
    $this->cache
      ->expects($this->once())
      ->method('get')
      ->with($this->getExpectedCacheId())
      ->willReturn(FALSE);

    $providerData = $this->getProviderData();

    $response = new Response(
      200,
      [],
      json_encode(
        $providerData,
        JSON_THROW_ON_ERROR,
      ),
    );

    $this->httpClient
      ->expects($this->once())
      ->method('request')
      ->with(
        'GET',
        'https://api.open-meteo.com/v1/forecast',
        [
          'query' => [
            'latitude' => 45.0693,
            'longitude' => 7.6934,
            'daily' => implode(',', [
              'weather_code',
              'temperature_2m_max',
              'temperature_2m_min',
            ]),
            'timezone' => 'Europe/Rome',
            'forecast_days' => 3,
            'temperature_unit' => 'celsius',
          ],
          'headers' => [
            'Accept' => 'application/json',
          ],
          'timeout' => 10,
        ],
      )
      ->willReturn($response);

    $currentTime = 1_700_000_000;

    $this->time
      ->expects($this->once())
      ->method('getCurrentTime')
      ->willReturn($currentTime);

    $expectedForecast = [
      'timezone' => 'Europe/Rome',
      'temperature_unit' => '°C',
      'days' => [
        [
          'date' => '2026-09-04',
          'weather_code' => 0,
          'high' => 25.0,
          'low' => 15.0,
        ],
        [
          'date' => '2026-09-05',
          'weather_code' => 1,
          'high' => 24.0,
          'low' => 14.0,
        ],
        [
          'date' => '2026-09-06',
          'weather_code' => 2,
          'high' => 23.0,
          'low' => 13.0,
        ],
      ],
      'location' => 'Turin',
    ];

    $this->cache
      ->expects($this->once())
      ->method('set')
      ->with(
        $this->getExpectedCacheId(),
        $expectedForecast,
        $currentTime + ForecastCache::MAX_AGE,
        [ForecastCache::TAG],
      );

    $client = $this->createForecastClient();

    $this->assertSame(
      $expectedForecast,
      $client->getForecast(),
    );
  }

  /**
   * Tests that invalid coordinates prevent a forecast request.
   */
  public function testReturnsNullForInvalidCoordinates(): void {
    $this->cache
      ->expects($this->never())
      ->method('get');

    $this->cache
      ->expects($this->never())
      ->method('set');

    $this->httpClient
      ->expects($this->never())
      ->method('request');

    $this->time
      ->expects($this->never())
      ->method('getCurrentTime');

    $client = $this->createForecastClient([
      'latitude' => 'invalid',
    ]);

    $this->assertNull(
      $client->getForecast(),
    );
  }

  /**
   * Tests that an HTTP error results in no forecast data.
   */
  public function testReturnsNullWhenHttpRequestFails(): void {
    $this->cache
      ->expects($this->once())
      ->method('get')
      ->with($this->getExpectedCacheId())
      ->willReturn(FALSE);

    $request = new Request(
      'GET',
      'https://api.open-meteo.com/v1/forecast',
    );

    $this->httpClient
      ->expects($this->once())
      ->method('request')
      ->willThrowException(
        new ConnectException(
          'Unable to connect to the weather provider.',
          $request,
        ),
      );

    $this->cache
      ->expects($this->never())
      ->method('set');

    $this->time
      ->expects($this->never())
      ->method('getCurrentTime');

    $client = $this->createForecastClient();

    $this->assertNull(
      $client->getForecast(),
    );
  }

  /**
   * Tests that invalid provider data results in no forecast data.
   */
  public function testReturnsNullForInvalidProviderData(): void {
    $this->cache
      ->expects($this->once())
      ->method('get')
      ->with($this->getExpectedCacheId())
      ->willReturn(FALSE);

    $providerData = $this->getProviderData();

    $providerData['daily']['weather_code'] = [
      0,
    ];

    $response = new Response(
      200,
      [],
      json_encode(
        $providerData,
        JSON_THROW_ON_ERROR,
      ),
    );

    $this->httpClient
      ->expects($this->once())
      ->method('request')
      ->willReturn($response);

    $this->cache
      ->expects($this->never())
      ->method('set');

    $this->time
      ->expects($this->never())
      ->method('getCurrentTime');

    $client = $this->createForecastClient();

    $this->assertNull(
      $client->getForecast(),
    );
  }

  /**
   * Creates a forecast client with test configuration.
   *
   * @param array<string, mixed> $overrides
   *   Configuration values that override the defaults.
   *
   * @return \Drupal\nome_modulo\Service\ForecastClient
   *   The forecast client.
   */
  private function createForecastClient(
    array $overrides = [],
  ): ForecastClient {
    $settings = array_replace(
      [
        'location' => 'Turin',
        'latitude' => 45.0693,
        'longitude' => 7.6934,
        'timezone' => 'Europe/Rome',
        'forecast_days' => 3,
        'temperature_unit' => 'celsius',
      ],
      $overrides,
    );

    $configFactory = $this->getConfigFactoryStub([
      'nome_modulo.settings' => $settings,
    ]);

    return new ForecastClient(
      $this->httpClient,
      $configFactory,
      $this->cache,
      $this->time,
    );
  }

  /**
   * Returns provider data used by the tests.
   *
   * @return array<string, mixed>
   *   A simulated Open-Meteo response.
   */
  private function getProviderData(): array {
    return [
      'timezone' => 'Europe/Rome',
      'daily_units' => [
        'temperature_2m_max' => '°C',
        'temperature_2m_min' => '°C',
      ],
      'daily' => [
        'time' => [
          '2026-09-04',
          '2026-09-05',
          '2026-09-06',
        ],
        'weather_code' => [
          0,
          1,
          2,
        ],
        'temperature_2m_max' => [
          25.0,
          24.0,
          23.0,
        ],
        'temperature_2m_min' => [
          15.0,
          14.0,
          13.0,
        ],
      ],
    ];
  }

  /**
   * Returns the expected cache ID for the default test configuration.
   */
  private function getExpectedCacheId(): string {
    return 'nome_modulo:forecast:' . hash(
      'sha256',
      '45.0693:7.6934:Europe/Rome:3:celsius',
    );
  }

}

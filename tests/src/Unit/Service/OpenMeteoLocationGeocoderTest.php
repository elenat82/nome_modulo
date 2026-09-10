<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Unit\Service;

use Drupal\nome_modulo\Service\OpenMeteoLocationGeocoder;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests the Open-Meteo location geocoder.
 */
final class OpenMeteoLocationGeocoderTest extends TestCase {

  /**
   * The connection timeout in seconds.
   */
  private const CONNECT_TIMEOUT = 5;

  /**
   * The request timeout in seconds.
   */
  private const REQUEST_TIMEOUT = 10;

  /**
   * Tests that search results are normalized.
   */
  public function testSearchReturnsNormalizedLocations(): void {
    $httpClient = $this->createMock(ClientInterface::class);

    $response = new Response(
      200,
      [],
      json_encode(
        [
          'results' => [
            [
              'id' => 3165524,
              'name' => 'Turin',
              'latitude' => 45.07049,
              'longitude' => 7.68682,
              'timezone' => 'Europe/Rome',
              'country' => 'Italy',
              'admin1' => 'Piedmont',
            ],
          ],
        ],
        JSON_THROW_ON_ERROR,
      ),
    );

    $httpClient->expects($this->once())
      ->method('request')
      ->with(
        'GET',
        'https://geocoding-api.open-meteo.com/v1/search',
        [
          'query' => [
            'name' => 'Turin',
            'count' => 5,
            'format' => 'json',
            'language' => 'en',
          ],
          'connect_timeout' => self::CONNECT_TIMEOUT,
          'timeout' => self::REQUEST_TIMEOUT,
          'allow_redirects' => FALSE,
        ],
      )
      ->willReturn($response);

    $geocoder = new OpenMeteoLocationGeocoder($httpClient);

    $this->assertSame(
      [
        [
          'name' => 'Turin',
          'country' => 'Italy',
          'admin1' => 'Piedmont',
          'latitude' => 45.07049,
          'longitude' => 7.68682,
          'timezone' => 'Europe/Rome',
        ],
      ],
      $geocoder->search('Turin'),
    );
  }

  /**
   * Tests that an invalid query does not cause an HTTP request.
   */
  public function testShortQueryReturnsNoResults(): void {
    $httpClient = $this->createMock(ClientInterface::class);

    $httpClient->expects($this->never())
      ->method('request');

    $geocoder = new OpenMeteoLocationGeocoder($httpClient);

    $this->assertSame([], $geocoder->search('T'));
  }

  /**
   * Tests that an empty provider result returns no locations.
   */
  public function testEmptyProviderResultReturnsNoLocations(): void {
    $httpClient = $this->createMock(ClientInterface::class);

    $httpClient->method('request')
      ->willReturn(
        new Response(
          200,
          [],
          json_encode(
            ['results' => []],
            JSON_THROW_ON_ERROR,
          ),
        ),
      );

    $geocoder = new OpenMeteoLocationGeocoder($httpClient);

    $this->assertSame([], $geocoder->search('Unknown place'));
  }

  /**
   * Tests that HTTP failures return no locations.
   */
  public function testHttpFailureReturnsNoLocations(): void {
    $httpClient = $this->createMock(ClientInterface::class);

    $request = new Request(
      'GET',
      'https://geocoding-api.open-meteo.com/v1/search',
    );

    $httpClient->method('request')
      ->willThrowException(
        new ConnectException(
          'Connection failed.',
          $request,
        ),
      );

    $geocoder = new OpenMeteoLocationGeocoder($httpClient);

    $this->assertSame([], $geocoder->search('Turin'));
  }

  /**
 * Tests that invalid geocoding results are discarded.
 *
 * @param array $result
 *   The invalid provider result.
 */
  #[DataProvider('invalidGeocodingResultsProvider')]
  public function testInvalidGeocodingResultsAreDiscarded(
    array $result,
  ): void {
    $httpClient = $this->createMock(ClientInterface::class);

    $response = new Response(
    200,
    [],
    json_encode(
      [
        'results' => [
          $result,
        ],
      ],
      JSON_THROW_ON_ERROR,
    ),
    );

    $httpClient->expects($this->once())
      ->method('request')
      ->willReturn($response);

    $geocoder = new OpenMeteoLocationGeocoder($httpClient);

    $this->assertSame(
    [],
    $geocoder->search('Test location'),
    );
  }

  /**
   * Provides invalid geocoding results.
   *
   * @return array<string, array{array<string, mixed>}>
   *   Invalid geocoding result test cases.
   */
  public static function invalidGeocodingResultsProvider(): array {
    return [
      'empty name' => [
      [
        'name' => '   ',
        'latitude' => 45.07049,
        'longitude' => 7.68682,
        'timezone' => 'Europe/Rome',
      ],
      ],
      'latitude outside range' => [
      [
        'name' => 'Test location',
        'latitude' => 91,
        'longitude' => 7.68682,
        'timezone' => 'Europe/Rome',
      ],
      ],
      'longitude outside range' => [
      [
        'name' => 'Test location',
        'latitude' => 45.07049,
        'longitude' => 181,
        'timezone' => 'Europe/Rome',
      ],
      ],
      'latitude below range' => [
      [
        'name' => 'Test location',
        'latitude' => -91,
        'longitude' => 7.68682,
        'timezone' => 'Europe/Rome',
      ],
      ],
      'longitude below range' => [
      [
        'name' => 'Test location',
        'latitude' => 45.07049,
        'longitude' => -181,
        'timezone' => 'Europe/Rome',
      ],
      ],
      'invalid timezone' => [
      [
        'name' => 'Test location',
        'latitude' => 45.07049,
        'longitude' => 7.68682,
        'timezone' => 'Invalid/Zone',
      ],
      ],
    ];
  }

}

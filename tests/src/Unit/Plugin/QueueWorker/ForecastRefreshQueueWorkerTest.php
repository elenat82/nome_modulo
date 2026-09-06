<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Unit\Plugin\QueueWorker;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\State\StateInterface;
use Drupal\nome_modulo\Cache\ForecastCache;
use Drupal\nome_modulo\ForecastStatusStorage;
use Drupal\nome_modulo\Plugin\QueueWorker\ForecastRefreshQueueWorker;
use Drupal\nome_modulo\Service\ForecastClientInterface;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests the forecast refresh queue worker.
 */
#[Group('nome_modulo')]
final class ForecastRefreshQueueWorkerTest extends UnitTestCase {

  /**
   * The mocked forecast client.
   */
  private ForecastClientInterface&MockObject $forecastClient;

  /**
   * The mocked state service.
   */
  private StateInterface&MockObject $state;

  /**
   * The mocked cache tag invalidator.
   */
  private CacheTagsInvalidatorInterface&MockObject $cacheTagsInvalidator;

  /**
   * The mocked time service.
   */
  private TimeInterface&MockObject $time;

  /**
   * The forecast refresh queue worker.
   */
  private ForecastRefreshQueueWorker $queueWorker;

  /**
   * Sets up the test dependencies.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->forecastClient = $this->createMock(
      ForecastClientInterface::class,
    );

    $this->state = $this->createMock(
      StateInterface::class,
    );

    $this->cacheTagsInvalidator = $this->createMock(
      CacheTagsInvalidatorInterface::class,
    );

    $this->time = $this->createMock(
      TimeInterface::class,
    );

    $forecastStatusStorage = new ForecastStatusStorage(
      $this->state,
    );

    $this->queueWorker = new ForecastRefreshQueueWorker(
      [],
      ForecastRefreshQueueWorker::QUEUE_ID,
      [],
      $this->forecastClient,
      $forecastStatusStorage,
      $this->cacheTagsInvalidator,
      $this->time,
    );
  }

  /**
   * Tests a successful forecast refresh.
   */
  public function testSuccessfulForecastRefresh(): void {
    $forecast = [
      'location' => 'Turin',
      'timezone' => 'Europe/Rome',
      'temperature_unit' => '°C',
      'days' => [
        [
          'date' => '2026-09-06',
          'weather_code' => 0,
          'high' => 25.0,
          'low' => 15.0,
        ],
      ],
    ];

    $currentTime = 1_700_000_100;

    $this->cacheTagsInvalidator
      ->expects($this->once())
      ->method('invalidateTags')
      ->with([
        ForecastCache::TAG,
      ]);

    $this->forecastClient
      ->expects($this->once())
      ->method('getForecast')
      ->willReturn($forecast);

    $this->time
      ->expects($this->once())
      ->method('getCurrentTime')
      ->willReturn($currentTime);

    $this->state
      ->expects($this->once())
      ->method('set')
      ->with(
        'nome_modulo.last_successful_forecast_refresh',
        $currentTime,
      );

    $this->queueWorker->processItem([
      'requested_at' => 1_700_000_000,
    ]);
  }

  /**
   * Tests a failed forecast refresh.
   */
  public function testFailedForecastRefreshSuspendsQueue(): void {
    $this->cacheTagsInvalidator
      ->expects($this->once())
      ->method('invalidateTags')
      ->with([
        ForecastCache::TAG,
      ]);

    $this->forecastClient
      ->expects($this->once())
      ->method('getForecast')
      ->willReturn(NULL);

    $this->state
      ->expects($this->never())
      ->method('set');

    $this->time
      ->expects($this->never())
      ->method('getCurrentTime');

    $this->expectException(
      SuspendQueueException::class,
    );

    $this->expectExceptionMessage(
      'The weather forecast could not be refreshed.',
    );

    $this->queueWorker->processItem([
      'requested_at' => 1_700_000_000,
    ]);
  }

}

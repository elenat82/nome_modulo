<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Unit;

use Drupal\Core\State\StateInterface;
use Drupal\nome_modulo\ForecastStatusStorage;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests forecast status storage.
 */
#[Group('nome_modulo')]
final class ForecastStatusStorageTest extends UnitTestCase {

  /**
   * The mocked state service.
   */
  private StateInterface&MockObject $state;

  /**
   * The forecast status storage.
   */
  private ForecastStatusStorage $storage;

  /**
   * Sets up the test dependencies.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->state = $this->createMock(
      StateInterface::class,
    );

    $this->storage = new ForecastStatusStorage(
      $this->state,
    );
  }

  /**
   * Tests storing the last cron execution timestamp.
   */
  public function testSetLastCronRun(): void {
    $timestamp = 1_700_000_000;

    $this->state
      ->expects($this->once())
      ->method('set')
      ->with(
        'nome_modulo.last_cron_run',
        $timestamp,
      );

    $this->storage->setLastCronRun($timestamp);
  }

  /**
   * Tests retrieving the last cron execution timestamp.
   */
  public function testGetLastCronRun(): void {
    $timestamp = 1_700_000_000;

    $this->state
      ->expects($this->once())
      ->method('get')
      ->with('nome_modulo.last_cron_run')
      ->willReturn($timestamp);

    $this->assertSame(
      $timestamp,
      $this->storage->getLastCronRun(),
    );
  }

  /**
   * Tests retrieving an unavailable cron timestamp.
   */
  public function testGetLastCronRunReturnsNull(): void {
    $this->state
      ->expects($this->once())
      ->method('get')
      ->with('nome_modulo.last_cron_run')
      ->willReturn(NULL);

    $this->assertNull(
      $this->storage->getLastCronRun(),
    );
  }

  /**
   * Tests storing the last successful forecast refresh timestamp.
   */
  public function testSetLastSuccessfulRefresh(): void {
    $timestamp = 1_700_000_100;

    $this->state
      ->expects($this->once())
      ->method('set')
      ->with(
        'nome_modulo.last_successful_forecast_refresh',
        $timestamp,
      );

    $this->storage->setLastSuccessfulRefresh($timestamp);
  }

  /**
   * Tests retrieving the last successful forecast refresh timestamp.
   */
  public function testGetLastSuccessfulRefresh(): void {
    $timestamp = 1_700_000_100;

    $this->state
      ->expects($this->once())
      ->method('get')
      ->with('nome_modulo.last_successful_forecast_refresh')
      ->willReturn($timestamp);

    $this->assertSame(
      $timestamp,
      $this->storage->getLastSuccessfulRefresh(),
    );
  }

  /**
   * Tests removing all forecast status values.
   */
  public function testDeleteAll(): void {
    $this->state
      ->expects($this->once())
      ->method('deleteMultiple')
      ->with([
        'nome_modulo.last_cron_run',
        'nome_modulo.last_successful_forecast_refresh',
      ]);

    $this->storage->deleteAll();
  }

}

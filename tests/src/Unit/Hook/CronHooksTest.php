<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Unit\Hook;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\State\StateInterface;
use Drupal\nome_modulo\ForecastStatusStorage;
use Drupal\nome_modulo\Hook\CronHooks;
use Drupal\nome_modulo\Plugin\QueueWorker\ForecastRefreshQueueWorker;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests the cron hook implementation.
 */
#[Group('nome_modulo')]
final class CronHooksTest extends UnitTestCase {

  /**
   * The mocked queue factory.
   */
  private QueueFactory&MockObject $queueFactory;

  /**
   * The mocked queue.
   */
  private QueueInterface&MockObject $queue;

  /**
   * The mocked state service.
   */
  private StateInterface&MockObject $state;

  /**
   * The mocked time service.
   */
  private TimeInterface&MockObject $time;

  /**
   * The cron hook implementation.
   */
  private CronHooks $cronHooks;

  /**
   * Sets up the test dependencies.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->queueFactory = $this->createMock(
      QueueFactory::class,
    );

    $this->queue = $this->createMock(
      QueueInterface::class,
    );

    $this->state = $this->createMock(
      StateInterface::class,
    );

    $this->time = $this->createMock(
      TimeInterface::class,
    );

    $forecastStatusStorage = new ForecastStatusStorage(
      $this->state,
    );

    $this->cronHooks = new CronHooks(
      $this->queueFactory,
      $forecastStatusStorage,
      $this->time,
    );
  }

  /**
   * Tests that cron records its execution and creates a queue item.
   */
  public function testCronCreatesQueueItemWhenQueueIsEmpty(): void {
    $requestTime = 1_700_000_000;

    $this->time
      ->expects($this->once())
      ->method('getRequestTime')
      ->willReturn($requestTime);

    $this->state
      ->expects($this->once())
      ->method('set')
      ->with(
        'nome_modulo.last_cron_run',
        $requestTime,
      );

    $this->queueFactory
      ->expects($this->once())
      ->method('get')
      ->with(ForecastRefreshQueueWorker::QUEUE_ID)
      ->willReturn($this->queue);

    $this->queue
      ->expects($this->once())
      ->method('numberOfItems')
      ->willReturn(0);

    $this->queue
      ->expects($this->once())
      ->method('createItem')
      ->with([
        'requested_at' => $requestTime,
      ]);

    $this->cronHooks->cron();
  }

  /**
   * Tests that cron does not create duplicate queue items.
   */
  public function testCronDoesNotCreateItemWhenQueueIsNotEmpty(): void {
    $requestTime = 1_700_000_000;

    $this->time
      ->expects($this->once())
      ->method('getRequestTime')
      ->willReturn($requestTime);

    $this->state
      ->expects($this->once())
      ->method('set')
      ->with(
        'nome_modulo.last_cron_run',
        $requestTime,
      );

    $this->queueFactory
      ->expects($this->once())
      ->method('get')
      ->with(ForecastRefreshQueueWorker::QUEUE_ID)
      ->willReturn($this->queue);

    $this->queue
      ->expects($this->once())
      ->method('numberOfItems')
      ->willReturn(1);

    $this->queue
      ->expects($this->never())
      ->method('createItem');

    $this->cronHooks->cron();
  }

}

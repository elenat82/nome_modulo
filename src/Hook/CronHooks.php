<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Hook;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Queue\QueueFactory;
use Drupal\nome_modulo\ForecastStatusStorage;
use Drupal\nome_modulo\Plugin\QueueWorker\ForecastRefreshQueueWorker;

/**
 * Implements hooks related to cron.
 */
final class CronHooks {

  /**
   * Constructs a CronHooks object.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The queue factory used to create forecast refresh queue items.
   * @param \Drupal\nome_modulo\ForecastStatusStorage $forecastStatusStorage
   *   The storage used to persist forecast status information.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    private readonly QueueFactory $queueFactory,
    private readonly ForecastStatusStorage $forecastStatusStorage,
    private readonly TimeInterface $time,
  ) {}

  /**
   * Schedules a weather forecast refresh during cron.
   */
  #[Hook('cron')]
  public function cron(): void {
    $requestTime = $this->time->getRequestTime();

    $this->forecastStatusStorage->setLastCronRun(
      $requestTime,
    );

    $queue = $this->queueFactory->get(
      ForecastRefreshQueueWorker::QUEUE_ID,
    );

    if ($queue->numberOfItems() === 0) {
      $queue->createItem([
        'requested_at' => $requestTime,
      ]);
    }
  }

}

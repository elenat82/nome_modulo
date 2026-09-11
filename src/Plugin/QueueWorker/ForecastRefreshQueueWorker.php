<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Plugin\QueueWorker;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\Attribute\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\nome_modulo\Cache\ForecastCache;
use Drupal\nome_modulo\ForecastStatusStorage;
use Drupal\nome_modulo\Service\ForecastClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Refreshes the cached weather forecast.
 */
#[QueueWorker(
  id: self::QUEUE_ID,
  title: new TranslatableMarkup('Refresh weather forecast'),
  cron: [
    'time' => 30,
  ],
)]
final class ForecastRefreshQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Forecast refresh queue plugin ID.
   */
  public const QUEUE_ID = 'nome_modulo_forecast_refresh';

  /**
   * Constructs a ForecastRefreshQueueWorker object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\nome_modulo\Service\ForecastClientInterface $forecastClient
   *   The service used to retrieve weather forecast data.
   * @param \Drupal\nome_modulo\ForecastStatusStorage $forecastStatusStorage
   *   The storage used to persist forecast status information.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   The cache tag invalidator.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected ForecastClientInterface $forecastClient,
    protected ForecastStatusStorage $forecastStatusStorage,
    protected CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    protected TimeInterface $time,
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * Creates an instance of the forecast refresh queue worker.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   A new instance of the forecast refresh queue worker.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get(ForecastClientInterface::class),
      $container->get(ForecastStatusStorage::class),
      $container->get('cache_tags.invalidator'),
      $container->get('datetime.time'),
    );
  }

  /**
   * Processes a forecast refresh queue item.
   *
   * @param mixed $data
   *   The data stored in the queue item.
   *
   * @throws \Drupal\Core\Queue\SuspendQueueException
   *   Thrown when the weather forecast cannot be refreshed.
   */
  public function processItem(
    mixed $data,
  ): void {
    $this->cacheTagsInvalidator->invalidateTags([
      ForecastCache::TAG,
    ]);

    $forecast = $this->forecastClient->getForecast();

    if ($forecast === NULL) {
      throw new SuspendQueueException(
        'The weather forecast could not be refreshed.',
      );
    }

    $this->forecastStatusStorage->setLastSuccessfulRefresh(
      $this->time->getCurrentTime(),
    );
  }

}

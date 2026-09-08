<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\nome_modulo\Cache\ForecastCache;
use Drupal\nome_modulo\Event\NomeModuloEvents;
use Drupal\nome_modulo\Event\WeatherAlertCreatedEvent;
use Psr\Log\LoggerInterface;

/**
 * Reacts to weather and configuration events.
 */
final class WeatherEventSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a WeatherEventSubscriber object.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   The cache tag invalidator.
   * @param \Psr\Log\LoggerInterface $logger
   *   The module logger.
   */
  public function __construct(
    private readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    private readonly LoggerInterface $logger,
  ) {}

  /**
   * Returns the events subscribed by this subscriber.
   *
   * @return array<string, string>
   *   The subscribed event names and their handlers.
   */
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::SAVE => 'onConfigSave',
      NomeModuloEvents::WEATHER_ALERT_CREATED => 'onWeatherAlertCreated',
    ];
  }

  /**
   * Invalidates forecast caches when module settings are saved.
   */
  public function onConfigSave(ConfigCrudEvent $event): void {
    $config = $event->getConfig();

    if ($config->getName() !== 'nome_modulo.settings') {
      return;
    }

    $this->cacheTagsInvalidator->invalidateTags([
      ForecastCache::TAG,
    ]);
  }

  /**
   * Logs the creation of a Weather alert.
   *
   * @param \Drupal\nome_modulo\Event\WeatherAlertCreatedEvent $event
   *   The Weather alert created event.
   */
  public function onWeatherAlertCreated(
    WeatherAlertCreatedEvent $event,
  ): void {
    $alert = $event->getAlert();

    $this->logger->notice(
    'Weather alert @title was created with ID @id.',
    [
      '@title' => $alert->label(),
      '@id' => $alert->id(),
    ],
    );
  }

}

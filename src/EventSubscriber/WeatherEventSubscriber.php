<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Reacts to weather and configuration events.
 */
final class WeatherEventSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a WeatherEventSubscriber object.
   */
  public function __construct(
    private readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
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
      'nome_modulo:forecast',
    ]);
  }

}

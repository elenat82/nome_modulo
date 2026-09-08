<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Unit\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigEvents;
use Drupal\nome_modulo\Event\NomeModuloEvents;
use Drupal\nome_modulo\Event\WeatherAlertCreatedEvent;
use Drupal\nome_modulo\EventSubscriber\WeatherEventSubscriber;
use Drupal\node\NodeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests the Weather event subscriber.
 */
final class WeatherEventSubscriberTest extends TestCase {

  /**
   * Tests the subscribed events.
   */
  public function testSubscribedEvents(): void {
    $events = WeatherEventSubscriber::getSubscribedEvents();

    $this->assertSame(
      'onConfigSave',
      $events[ConfigEvents::SAVE],
    );

    $this->assertSame(
      'onWeatherAlertCreated',
      $events[NomeModuloEvents::WEATHER_ALERT_CREATED],
    );
  }

  /**
   * Tests logging when a Weather alert is created.
   */
  public function testWeatherAlertCreatedIsLogged(): void {
    $cacheTagsInvalidator = $this->createMock(
      CacheTagsInvalidatorInterface::class,
    );

    $logger = $this->createMock(LoggerInterface::class);

    $alert = $this->createMock(NodeInterface::class);
    $alert->method('label')
      ->willReturn('Storm warning');
    $alert->method('id')
      ->willReturn(42);

    $logger->expects($this->once())
      ->method('notice')
      ->with(
        'Weather alert @title was created with ID @id.',
        [
          '@title' => 'Storm warning',
          '@id' => 42,
        ],
      );

    $subscriber = new WeatherEventSubscriber(
      $cacheTagsInvalidator,
      $logger,
    );

    $subscriber->onWeatherAlertCreated(
      new WeatherAlertCreatedEvent($alert),
    );
  }

}

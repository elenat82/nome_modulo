<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Unit\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\nome_modulo\Event\NomeModuloEvents;
use Drupal\nome_modulo\Event\WeatherAlertCreatedEvent;
use Drupal\nome_modulo\Hook\EntityHooks;
use Drupal\node\NodeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Tests the entity hooks.
 */
final class EntityHooksTest extends TestCase {

  /**
   * Tests dispatching the Weather alert created event.
   */
  public function testWeatherAlertCreatedEventIsDispatched(): void {
    $alert = $this->createMock(NodeInterface::class);
    $alert->method('bundle')
      ->willReturn('weather_alert');

    $eventDispatcher = $this->createMock(
      EventDispatcherInterface::class,
    );

    $eventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(
        $this->callback(
          static fn (object $event): bool =>
            $event instanceof WeatherAlertCreatedEvent &&
            $event->getAlert() === $alert,
        ),
        NomeModuloEvents::WEATHER_ALERT_CREATED,
      )
      ->willReturnArgument(0);

    $hooks = new EntityHooks($eventDispatcher);

    $hooks->entityInsert($alert);
  }

  /**
   * Tests that other node bundles do not dispatch the event.
   */
  public function testOtherNodeBundleDoesNotDispatchEvent(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->method('bundle')
      ->willReturn('article');

    $eventDispatcher = $this->createMock(
      EventDispatcherInterface::class,
    );

    $eventDispatcher->expects($this->never())
      ->method('dispatch');

    $hooks = new EntityHooks($eventDispatcher);

    $hooks->entityInsert($node);
  }

  /**
   * Tests that non-node entities do not dispatch the event.
   */
  public function testNonNodeEntityDoesNotDispatchEvent(): void {
    $entity = $this->createMock(EntityInterface::class);

    $eventDispatcher = $this->createMock(
      EventDispatcherInterface::class,
    );

    $eventDispatcher->expects($this->never())
      ->method('dispatch');

    $hooks = new EntityHooks($eventDispatcher);

    $hooks->entityInsert($entity);
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Unit\Extension;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\nome_modulo\Extension\WeatherAlertUninstallValidator;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the Weather alert uninstall validator.
 */
#[Group('nome_modulo')]
final class WeatherAlertUninstallValidatorTest extends UnitTestCase {

  /**
   * Tests that uninstall is blocked when Weather alerts exist.
   */
  public function testUninstallIsBlockedWhenWeatherAlertsExist(): void {
    $query = $this->createMock(QueryInterface::class);

    $query->expects($this->once())
      ->method('accessCheck')
      ->with(FALSE)
      ->willReturnSelf();

    $query->expects($this->once())
      ->method('condition')
      ->with(
        'type',
        'weather_alert',
      )
      ->willReturnSelf();

    $query->expects($this->once())
      ->method('range')
      ->with(
        0,
        1,
      )
      ->willReturnSelf();

    $query->expects($this->once())
      ->method('execute')
      ->willReturn([
        1 => 1,
      ]);

    $storage = $this->createMock(
      EntityStorageInterface::class,
    );

    $storage->expects($this->once())
      ->method('getQuery')
      ->willReturn($query);

    $entityTypeManager = $this->createMock(
      EntityTypeManagerInterface::class,
    );

    $entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($storage);

    $validator = new WeatherAlertUninstallValidator(
      $entityTypeManager,
      $this->getStringTranslationStub(),
    );

    $reasons = $validator->validate('nome_modulo');

    $this->assertCount(
      1,
      $reasons,
    );

    $this->assertSame(
      'Weather alert content exists. Delete all Weather alert content before uninstalling Nome Modulo',
      (string) $reasons[0],
    );
  }

  /**
   * Tests that uninstall is allowed when no Weather alerts exist.
   */
  public function testUninstallIsAllowedWhenNoWeatherAlertsExist(): void {
    $query = $this->createMock(QueryInterface::class);

    $query->method('accessCheck')
      ->with(FALSE)
      ->willReturnSelf();

    $query->method('condition')
      ->with(
        'type',
        'weather_alert',
      )
      ->willReturnSelf();

    $query->method('range')
      ->with(
        0,
        1,
      )
      ->willReturnSelf();

    $query->expects($this->once())
      ->method('execute')
      ->willReturn([]);

    $storage = $this->createMock(
      EntityStorageInterface::class,
    );

    $storage->method('getQuery')
      ->willReturn($query);

    $entityTypeManager = $this->createMock(
      EntityTypeManagerInterface::class,
    );

    $entityTypeManager->method('getStorage')
      ->with('node')
      ->willReturn($storage);

    $validator = new WeatherAlertUninstallValidator(
      $entityTypeManager,
      $this->getStringTranslationStub(),
    );

    $this->assertSame(
      [],
      $validator->validate('nome_modulo'),
    );
  }

  /**
   * Tests that unrelated modules are not affected.
   */
  public function testOtherModulesAreIgnored(): void {
    $entityTypeManager = $this->createMock(
      EntityTypeManagerInterface::class,
    );

    $entityTypeManager->expects($this->never())
      ->method('getStorage');

    $validator = new WeatherAlertUninstallValidator(
      $entityTypeManager,
      $this->getStringTranslationStub(),
    );

    $this->assertSame(
      [],
      $validator->validate('node'),
    );
  }

}

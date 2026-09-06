<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Tests the Weather alert field configuration.
 */
#[Group('nome_modulo')]
#[RunTestsInSeparateProcesses]
final class WeatherAlertFieldsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'filter',
    'text',
    'node',
    'options',
    'datetime',
    'nome_modulo',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    $this->installConfig([
      'nome_modulo',
    ]);
  }

  /**
   * Tests the Weather alert fields.
   */
  public function testWeatherAlertFields(): void {
    $alertLevelStorage = FieldStorageConfig::load(
      'node.field_alert_level',
    );

    $this->assertNotNull($alertLevelStorage);
    $this->assertSame(
      'list_string',
      $alertLevelStorage->getType(),
    );

    $this->assertSame(
    [
      'advisory' => 'Advisory',
      'warning' => 'Warning',
      'severe' => 'Severe',
    ],
    $alertLevelStorage->getSetting('allowed_values'),
    );

    $this->assertSame(
    ['nome_modulo'],
    $this->config('field.storage.node.field_alert_level')
      ->get('dependencies.enforced.module'),
    );

    $alertLevel = FieldConfig::load(
      'node.weather_alert.field_alert_level',
    );

    $this->assertNotNull($alertLevel);
    $this->assertSame(
      'Alert level',
      $alertLevel->label(),
    );
    $this->assertTrue(
      $alertLevel->isRequired(),
    );

    $alertStartStorage = FieldStorageConfig::load(
      'node.field_alert_start',
    );

    $this->assertNotNull($alertStartStorage);
    $this->assertSame(
      'datetime',
      $alertStartStorage->getType(),
    );
    $this->assertSame(
      'datetime',
      $alertStartStorage->getSetting('datetime_type'),
    );

    $this->assertSame(
    ['nome_modulo'],
    $this->config('field.storage.node.field_alert_start')
      ->get('dependencies.enforced.module'),
    );

    $alertStart = FieldConfig::load(
      'node.weather_alert.field_alert_start',
    );

    $this->assertNotNull($alertStart);
    $this->assertTrue(
      $alertStart->isRequired(),
    );

    $alertEndStorage = FieldStorageConfig::load(
      'node.field_alert_end',
    );

    $this->assertNotNull($alertEndStorage);
    $this->assertSame(
      'datetime',
      $alertEndStorage->getType(),
    );
    $this->assertSame(
      'datetime',
      $alertEndStorage->getSetting('datetime_type'),
    );

    $this->assertSame(
    ['nome_modulo'],
    $this->config('field.storage.node.field_alert_end')
      ->get('dependencies.enforced.module'),
    );

    $alertEnd = FieldConfig::load(
      'node.weather_alert.field_alert_end',
    );

    $this->assertNotNull($alertEnd);
    $this->assertTrue(
      $alertEnd->isRequired(),
    );

    $formDisplay = EntityFormDisplay::load(
    'node.weather_alert.default',
    );

    $this->assertNotNull($formDisplay);

    $this->assertSame(
    'options_select',
    $formDisplay->getComponent('field_alert_level')['type'],
    );

    $this->assertSame(
    'datetime_default',
    $formDisplay->getComponent('field_alert_start')['type'],
    );

    $this->assertSame(
    'datetime_default',
    $formDisplay->getComponent('field_alert_end')['type'],
    );

    $viewDisplay = EntityViewDisplay::load(
    'node.weather_alert.default',
    );

    $this->assertNotNull($viewDisplay);

    $this->assertSame(
    'list_default',
    $viewDisplay->getComponent('field_alert_level')['type'],
    );

    $this->assertSame(
    'datetime_default',
    $viewDisplay->getComponent('field_alert_start')['type'],
    );

    $this->assertSame(
    'datetime_default',
    $viewDisplay->getComponent('field_alert_end')['type'],
    );

  }

}

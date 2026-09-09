<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests Weather alert location snapshots.
 */
#[Group('nome_modulo')]
#[RunTestsInSeparateProcesses]
final class WeatherAlertLocationTest extends KernelTestBase {

  /**
   * The modules required for the test.
   *
   * @var string[]
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
   * Sets up the test environment.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema(
    'node',
    [
      'node_access',
    ],
    );

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    $this->installConfig([
      'nome_modulo',
    ]);
  }

  /**
   * Tests that a Weather alert stores the configured location.
   */
  public function testLocationIsStoredWhenAlertIsCreated(): void {
    $this->config('nome_modulo.settings')
      ->set('location', 'Turin, Piedmont, Italy')
      ->save();

    $alert = $this->createWeatherAlert();

    $alert->save();

    $storedAlert = $this->container
      ->get('entity_type.manager')
      ->getStorage('node')
      ->load($alert->id());

    $this->assertInstanceOf(
    NodeInterface::class,
    $storedAlert,
    );

    $this->assertSame(
    'Turin, Piedmont, Italy',
    $storedAlert->get('field_alert_location')->value,
    );
  }

  /**
   * Tests that an existing alert keeps its original location.
   */
  public function testExistingAlertKeepsOriginalLocation(): void {
    $this->config('nome_modulo.settings')
      ->set('location', 'Turin, Piedmont, Italy')
      ->save();

    $alert = $this->createWeatherAlert();
    $alert->save();

    $this->config('nome_modulo.settings')
      ->set('location', 'New York, New York, United States')
      ->save();

    $alert->set(
      'field_alert_level',
      'severe',
    );

    $alert->save();

    $storedAlert = $this->container
      ->get('entity_type.manager')
      ->getStorage('node')
      ->load($alert->id());

    $this->assertInstanceOf(
    NodeInterface::class,
    $storedAlert,
    );

    $this->assertSame(
    'severe',
    $storedAlert->get('field_alert_level')->value,
    );

    $this->assertSame(
    'Turin, Piedmont, Italy',
    $storedAlert->get('field_alert_location')->value,
    );
  }

  /**
   * Creates a Weather alert for testing.
   *
   * @return \Drupal\node\NodeInterface
   *   The unsaved Weather alert.
   */
  private function createWeatherAlert(): NodeInterface {
    $alert = $this->container
      ->get('entity_type.manager')
      ->getStorage('node')
      ->create([
        'type' => 'weather_alert',
        'title' => 'Test weather alert',
        'field_alert_level' => 'warning',
        'field_alert_start' => '2026-09-09T10:00:00',
        'field_alert_end' => '2026-09-09T12:00:00',
      ]);

    $this->assertInstanceOf(
      NodeInterface::class,
      $alert,
    );

    return $alert;
  }

}

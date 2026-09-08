<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests Weather Alert date range validation.
 */
#[Group('nome_modulo')]
#[RunTestsInSeparateProcesses]
final class WeatherAlertValidationTest extends KernelTestBase {

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
   * Sets up the test environment.
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
   * Tests that a valid Weather Alert date range has no violations.
   */
  public function testValidDateRange(): void {
    $alert = $this->createWeatherAlert(
      '2026-09-08T10:00:00',
      '2026-09-08T12:00:00',
    );

    $violations = $alert->validate();

    $this->assertCount(0, $violations);
  }

  /**
   * Tests that an invalid Weather Alert date range causes a violation.
   */
  public function testInvalidDateRange(): void {
    $alert = $this->createWeatherAlert(
      '2026-09-08T12:00:00',
      '2026-09-08T10:00:00',
    );

    $violations = $alert->validate();

    $this->assertCount(1, $violations);
    $this->assertSame(
      'The end date must be later than the start date.',
      (string) $violations[0]->getMessage(),
    );
  }

  /**
   * Creates a Weather Alert for validation testing.
   *
   * @param string $start
   *   The alert start date and time.
   * @param string $end
   *   The alert end date and time.
   *
   * @return \Drupal\node\NodeInterface
   *   The unsaved Weather Alert node.
   */
  private function createWeatherAlert(
    string $start,
    string $end,
  ): NodeInterface {
    $alert = $this->container
      ->get('entity_type.manager')
      ->getStorage('node')
      ->create([
        'type' => 'weather_alert',
        'title' => 'Test weather alert',
        'field_alert_level' => 'warning',
        'field_alert_start' => $start,
        'field_alert_end' => $end,
      ]);

    $this->assertInstanceOf(
      NodeInterface::class,
      $alert,
    );

    return $alert;
  }

  /**
   * Tests that equal start and end dates cause a violation.
   */
  public function testEqualDateRange(): void {
    $alert = $this->createWeatherAlert(
    '2026-09-08T10:00:00',
    '2026-09-08T10:00:00',
    );

    $violations = $alert->validate();

    $this->assertCount(1, $violations);
    $this->assertSame(
    'The end date must be later than the start date.',
    (string) $violations[0]->getMessage(),
    );
  }

}

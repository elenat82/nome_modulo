<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodePreviewMode;
use Drupal\node\NodeTypeInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the Weather alert content type configuration.
 */
#[Group('nome_modulo')]
#[RunTestsInSeparateProcesses]
final class WeatherAlertContentTypeTest extends KernelTestBase {

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
   * Tests that the Weather alert content type is installed.
   */
  public function testWeatherAlertContentTypeIsInstalled(): void {
    $nodeType = $this->container
      ->get('entity_type.manager')
      ->getStorage('node_type')
      ->load('weather_alert');

    $this->assertInstanceOf(
      NodeTypeInterface::class,
      $nodeType,
    );

    $this->assertSame(
      'Weather alert',
      $nodeType->label(),
    );

    $this->assertSame(
      'Provides weather alerts for a geographical area.',
      $nodeType->getDescription(),
    );

    $this->assertSame(
      '',
      $nodeType->getHelp(),
    );

    $this->assertTrue(
      $nodeType->shouldCreateNewRevision(),
    );

    $this->assertSame(
      NodePreviewMode::Optional,
      $nodeType->getPreviewMode(FALSE),
    );

    $this->assertTrue(
      $nodeType->displaySubmitted(),
    );

    $this->assertSame(
      ['nome_modulo'],
      $this->config('node.type.weather_alert')
        ->get('dependencies.enforced.module'),
    );
  }

}

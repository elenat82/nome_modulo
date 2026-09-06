<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Kernel;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\nome_modulo\Cache\ForecastCache;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests forecast cache integration.
 */
#[Group('nome_modulo')]
#[RunTestsInSeparateProcesses]
final class ForecastCacheIntegrationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'nome_modulo',
  ];

  /**
   * The forecast cache backend.
   */
  private CacheBackendInterface $cache;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig([
      'system',
      'nome_modulo',
    ]);

    $this->cache = $this->container->get(
      'cache.nome_modulo',
    );
  }

  /**
   * Tests that saving module settings invalidates forecast cache entries.
   */
  public function testSettingsSaveInvalidatesForecastCache(): void {
    $cacheId = 'nome_modulo:test:forecast';

    $forecast = [
      'location' => 'Turin',
    ];

    $this->cache->set(
      $cacheId,
      $forecast,
      Cache::PERMANENT,
      [
        ForecastCache::TAG,
      ],
    );

    $cached = $this->cache->get($cacheId);

    $this->assertNotFalse($cached);
    $this->assertSame(
      $forecast,
      $cached->data,
    );

    $this->config('nome_modulo.settings')
      ->set('location', 'Milan')
      ->save();

    $this->assertFalse(
      $this->cache->get($cacheId),
    );
  }

  /**
   * Tests that unrelated configuration does not invalidate forecast cache.
   */
  public function testUnrelatedConfigSaveDoesNotInvalidateForecastCache(): void {
    $cacheId = 'nome_modulo:test:forecast';

    $forecast = [
      'location' => 'Turin',
    ];

    $this->cache->set(
    $cacheId,
    $forecast,
    Cache::PERMANENT,
    [
      ForecastCache::TAG,
    ],
    );

    $this->config('system.site')
      ->set('name', 'Test site')
      ->save();

    $cached = $this->cache->get($cacheId);

    $this->assertNotFalse($cached);
    $this->assertSame(
    $forecast,
    $cached->data,
    );
  }

}

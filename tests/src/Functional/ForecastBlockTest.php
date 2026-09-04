<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Functional;

use Drupal\Tests\block\Traits\BlockCreationTrait;
use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the weather forecast block.
 */
#[Group('nome_modulo')]
#[RunTestsInSeparateProcesses]
final class ForecastBlockTest extends BrowserTestBase {

  use BlockCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'nome_modulo',
    'nome_modulo_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that the block is hidden without the required permission.
   */
  public function testBlockAccessWithoutPermission(): void {
    $this->placeBlock('nome_modulo_forecast', [
      'region' => 'content',
    ]);

    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);

    $this->drupalGet('<front>');

    $this->assertSession()->elementNotExists(
      'css',
      '.nome-modulo-forecast-block',
    );
  }

  /**
   * Tests forecast block rendering with the default configuration.
   */
  public function testDefaultBlockConfiguration(): void {
    $this->placeBlock('nome_modulo_forecast', [
      'region' => 'content',
    ]);

    $this->loginUserWithForecastAccess();

    $this->drupalGet('<front>');

    $this->assertSession()->elementExists(
      'css',
      '.nome-modulo-forecast-block',
    );
    $this->assertSession()->elementExists(
    'css',
    '.nome-modulo-forecast-block--summary',
    );

    $this->assertSession()->pageTextContains(
      'Forecast for Test location',
    );

    $this->assertSession()->pageTextContains(
      '2026-09-01',
    );

    $this->assertSession()->pageTextContains(
      '25 °C',
    );

    $this->assertSession()->pageTextContains(
      '15 °C',
    );

    $this->assertSession()->pageTextNotContains(
      '2026-09-02',
    );

    $this->assertSession()->pageTextNotContains(
      '2026-09-03',
    );

    $this->assertSession()->linkExists(
    'View full forecast',
    );

    $link = $this->getSession()
      ->getPage()
      ->findLink('View full forecast');

    $this->assertNotNull($link);

    $this->assertStringContainsString(
    '/weather/extended',
    $link->getAttribute('href'),
    );

  }

  /**
   * Tests the extended forecast block configuration.
   */
  public function testExtendedBlockConfiguration(): void {
    $this->placeBlock('nome_modulo_forecast', [
      'region' => 'content',
      'display' => 'extended',
      'number_of_days' => 2,
      'show_link' => FALSE,
    ]);

    $this->loginUserWithForecastAccess();

    $this->drupalGet('<front>');

    $this->assertSession()->elementExists(
    'css',
    '.nome-modulo-forecast-block--extended',
    );

    $this->assertSession()->pageTextContains(
    '2026-09-01',
    );

    $this->assertSession()->pageTextContains(
    '2026-09-02',
    );

    $this->assertSession()->pageTextNotContains(
    '2026-09-03',
    );

    $this->assertSession()->linkNotExists(
    'View full forecast',
    );
  }

  /**
   * Logs in a user with permission to view the weather forecast.
   */
  private function loginUserWithForecastAccess(): void {
    $account = $this->drupalCreateUser([
      'view weather forecast',
    ]);

    $this->drupalLogin($account);
  }

}

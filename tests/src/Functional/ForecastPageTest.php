<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Functional;

use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the weather forecast page.
 */
#[Group('nome_modulo')]
#[RunTestsInSeparateProcesses]
final class ForecastPageTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'nome_modulo',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests access without the required permission.
   */
  public function testAccessDeniedWithoutPermission(): void {
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);

    $this->drupalGet('/weather');

    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests the forecast page with the required permission.
   */
  public function testForecastPageWithPermission(): void {
    $account = $this->drupalCreateUser([
      'access content',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/weather');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Weather forecast');
    $this->assertSession()->pageTextContains('Current temperature: 20 °C');
  }

}

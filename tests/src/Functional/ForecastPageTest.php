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
    'nome_modulo_test',
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
    $this->loginUserWithForecastAccess();

    $this->drupalGet('/weather');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Weather forecast');
    $this->assertSession()->pageTextContains(
    'Forecast for Test location',
    );
    $this->assertSession()->pageTextContains('2026-09-01');
    $this->assertSession()->elementExists('css', '.nome-modulo-forecast');
    $this->assertSession()->elementTextEquals(
    'css',
    '.nome-modulo-forecast__day--summary .nome-modulo-forecast__value:first-child dt',
    'Weather code',
    );
    $this->assertSession()->elementTextEquals(
    'css',
    '.nome-modulo-forecast__day--summary .nome-modulo-forecast__value:first-child dd',
    '0',
    );
    $this->assertSession()->elementTextEquals(
    'css',
    '.nome-modulo-forecast__day--summary .nome-modulo-forecast__value:nth-child(2) dt',
    'High',
    );

    $this->assertSession()->elementTextEquals(
    'css',
    '.nome-modulo-forecast__day--summary .nome-modulo-forecast__value:nth-child(2) dd',
    '25 °C',
    );

    $this->assertSession()->elementTextEquals(
    'css',
    '.nome-modulo-forecast__day--summary .nome-modulo-forecast__value:nth-child(3) dt',
    'Low',
    );

    $this->assertSession()->elementTextEquals(
    'css',
    '.nome-modulo-forecast__day--summary .nome-modulo-forecast__value:nth-child(3) dd',
    '15 °C',
    );

  }

  /**
   * Tests the default forecast display mode.
   */
  public function testDefaultDisplay(): void {
    $this->loginUserWithForecastAccess();

    $this->drupalGet('/weather');

    $forecast = $this->assertSession()->elementExists(
    'css',
    '[data-nome-modulo-forecast]',
    );

    $this->assertSame(
    'summary',
    $forecast->getAttribute('data-display'),
    );

    $details = $this->assertSession()->elementExists(
    'css',
    '[data-forecast-details]',
    );

    $this->assertTrue($details->hasAttribute('hidden'));

    $button = $this->assertSession()->buttonExists(
    'Show extended forecast',
    );

    $this->assertSame(
    'false',
    $button->getAttribute('aria-expanded'),
    );
  }

  /**
   * Tests the extended forecast display mode.
   */
  public function testExtendedDisplay(): void {
    $this->loginUserWithForecastAccess();

    $this->drupalGet('/weather/extended');

    $this->assertSession()->statusCodeEquals(200);

    $forecast = $this->assertSession()->elementExists(
    'css',
    '[data-nome-modulo-forecast]',
    );

    $this->assertSame(
    'extended',
    $forecast->getAttribute('data-display'),
    );

    $details = $this->assertSession()->elementExists(
    'css',
    '[data-forecast-details]',
    );

    $this->assertFalse($details->hasAttribute('hidden'));

    $this->assertSession()->buttonNotExists(
    'Show extended forecast',
    );

    $this->assertSession()->pageTextContains('2026-09-02');
    $this->assertSession()->pageTextContains('2026-09-03');
  }

  /**
   * Tests an invalid display route parameter.
   */
  public function testInvalidDisplayParameter(): void {
    $this->loginUserWithForecastAccess();

    $this->drupalGet('/weather/invalid');

    $this->assertSession()->statusCodeEquals(404);
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

<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the JavaScript behavior of the weather forecast toggle.
 */
#[Group('nome_modulo')]
#[RunTestsInSeparateProcesses]
final class ForecastToggleTest extends WebDriverTestBase {

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
   * Tests expanding and collapsing the extended forecast.
   */
  public function testForecastToggle(): void {
    $this->loginUserWithForecastAccess();

    $this->drupalGet('/weather');

    $forecast = $this->assertSession()->elementExists(
      'css',
      '[data-nome-modulo-forecast]',
    );

    $details = $this->assertSession()->elementExists(
      'css',
      '[data-forecast-details]',
    );

    $this->assertTrue(
      $details->hasAttribute('hidden'),
    );

    $this->assertFalse(
      $details->isVisible(),
    );

    $button = $this->assertSession()->buttonExists(
      'Show extended forecast',
    );

    $this->assertSame(
      'false',
      $button->getAttribute('aria-expanded'),
    );

    $this->assertFalse(
      $forecast->hasClass('is-expanded'),
    );

    $button->click();

    $details = $this->assertSession()->waitForElementVisible(
      'css',
      '[data-forecast-details]',
    );

    $this->assertNotNull($details);

    $button = $this->assertSession()->buttonExists(
      'Hide extended forecast',
    );

    $this->assertSame(
      'true',
      $button->getAttribute('aria-expanded'),
    );

    $this->assertFalse(
      $details->hasAttribute('hidden'),
    );

    $this->assertSession()->elementExists(
      'css',
      '.nome-modulo-forecast.is-expanded',
    );

    $button->click();

    $this->assertJsCondition(
      'document.querySelector("[data-forecast-details]").hidden === true',
    );

    $details = $this->assertSession()->elementExists(
      'css',
      '[data-forecast-details]',
    );

    $this->assertTrue(
      $details->hasAttribute('hidden'),
    );

    $this->assertFalse(
      $details->isVisible(),
    );

    $button = $this->assertSession()->buttonExists(
      'Show extended forecast',
    );

    $this->assertSame(
      'false',
      $button->getAttribute('aria-expanded'),
    );

    $this->assertSession()->elementNotExists(
      'css',
      '.nome-modulo-forecast.is-expanded',
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

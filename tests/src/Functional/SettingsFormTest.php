<?php

declare(strict_types=1);

namespace Drupal\Tests\nome_modulo\Functional;

use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the Nome Modulo settings form.
 */
#[Group('nome_modulo')]
#[RunTestsInSeparateProcesses]
final class SettingsFormTest extends BrowserTestBase {

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

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests displaying and saving the settings form.
   */
  public function testSettingsForm(): void {
    $account = $this->drupalCreateUser([
      'administer weather forecast settings',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Weather forecast settings');

    $this->assertSession()->fieldValueEquals('latitude', '41.9028');
    $this->assertSession()->fieldValueEquals('longitude', '12.4964');

    $edit = [
      'latitude' => '45.0703',
      'longitude' => '7.6869',
    ];

    $this->submitForm($edit, 'Save configuration');

    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    $config = $this->config('nome_modulo.settings');

    $this->assertSame(45.0703, $config->get('latitude'));
    $this->assertSame(7.6869, $config->get('longitude'));
  }

}

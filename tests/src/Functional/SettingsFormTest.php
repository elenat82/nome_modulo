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
      'administer weather settings',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Weather forecast settings');

    $this->assertSession()->fieldValueEquals(
    'location',
    'Turin',
    );
    $this->assertSession()->fieldValueEquals(
    'latitude',
    '45.0693',
    );
    $this->assertSession()->fieldValueEquals(
    'longitude',
    '7.6934',
    );
    $this->assertSession()->fieldValueEquals(
    'timezone',
    'Europe/Rome',
    );
    $this->assertSession()->fieldValueEquals(
    'forecast_days',
    '5',
    );
    $this->assertSession()->fieldValueEquals(
    'temperature_unit',
    'celsius',
    );

    $edit = [
      'location' => 'Rome',
      'latitude' => '41.9028',
      'longitude' => '12.4964',
      'timezone' => 'Europe/Rome',
      'forecast_days' => '7',
      'temperature_unit' => 'fahrenheit',
    ];

    $this->submitForm($edit, 'Save configuration');

    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    $config = $this->config('nome_modulo.settings');

    $this->assertSame(
    'Rome',
    $config->get('location'),
    );
    $this->assertSame(
    41.9028,
    $config->get('latitude'),
    );
    $this->assertSame(
    12.4964,
    $config->get('longitude'),
    );
    $this->assertSame(
    'Europe/Rome',
    $config->get('timezone'),
    );
    $this->assertSame(
    7,
    $config->get('forecast_days'),
    );
    $this->assertSame(
    'fahrenheit',
    $config->get('temperature_unit'),
    );
  }

  /**
   * Tests settings form validation.
   */
  public function testSettingsFormValidation(): void {
    $account = $this->drupalCreateUser([
      'administer weather settings',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->submitForm([
      'location' => ' A ',
      'latitude' => '45.0693',
      'longitude' => '7.6934',
      'timezone' => 'Europe/Rome',
      'forecast_days' => '5',
      'temperature_unit' => 'celsius',
    ], 'Save configuration');

    $this->assertSession()->pageTextContains(
    'The location must contain at least two characters.',
    );
  }

}

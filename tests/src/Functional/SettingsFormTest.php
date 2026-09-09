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

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests searching for a location.
   */
  public function testLocationSearch(): void {
    $account = $this->drupalCreateUser([
      'administer weather settings',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->submitForm(
    [
      'location_search' => 'Turin',
    ],
    'Search',
    );

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains(
    'Turin, Piedmont, Italy',
    );

    $this->assertSession()->fieldExists(
    'location_candidate',
    );
  }

  /**
   * Tests that searching for a location does not save configuration.
   */
  public function testLocationSearchDoesNotSaveConfiguration(): void {
    $account = $this->drupalCreateUser([
      'administer weather settings',
    ]);
    $this->drupalLogin($account);

    $config = $this->config('nome_modulo.settings');

    $originalLocation = $config->get('location');
    $originalLatitude = $config->get('latitude');
    $originalLongitude = $config->get('longitude');
    $originalTimezone = $config->get('timezone');

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->submitForm(
    [
      'location_search' => 'Milan',
    ],
    'Search',
    );

    $config = $this->config('nome_modulo.settings');

    $this->assertSame(
    $originalLocation,
    $config->get('location'),
    );
    $this->assertSame(
    $originalLatitude,
    $config->get('latitude'),
    );
    $this->assertSame(
    $originalLongitude,
    $config->get('longitude'),
    );
    $this->assertSame(
    $originalTimezone,
    $config->get('timezone'),
    );
  }

  /**
   * Tests saving a geocoded location.
   */
  public function testGeocodedLocationIsSaved(): void {
    $account = $this->drupalCreateUser([
      'administer weather settings',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->submitForm(
    [
      'location_search' => 'Turin',
    ],
    'Search',
    );

    $this->assertSession()->pageTextContains(
    'Turin, Piedmont, Italy',
    );

    $this->submitForm(
    [
      'location_candidate' => '0',
      'forecast_days' => 7,
      'temperature_unit' => 'fahrenheit',
    ],
    'Save configuration',
    );

    $config = $this->config('nome_modulo.settings');

    $this->assertSame(
    'Turin, Piedmont, Italy',
    $config->get('location'),
    );
    $this->assertSame(
    45.07049,
    $config->get('latitude'),
    );
    $this->assertSame(
    7.68682,
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
   * Tests that ambiguous searches show multiple candidates.
   */
  public function testAmbiguousLocationSearch(): void {
    $account = $this->drupalCreateUser([
      'administer weather settings',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->submitForm(
    [
      'location_search' => 'Springfield',
    ],
    'Search',
    );

    $this->assertSession()->pageTextContains(
    'Springfield, Illinois, United States',
    );
    $this->assertSession()->pageTextContains(
    'Springfield, Massachusetts, United States',
    );
  }

  /**
   * Tests validation of a location search that is too short.
   */
  public function testShortLocationSearchIsRejected(): void {
    $account = $this->drupalCreateUser([
      'administer weather settings',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->submitForm(
    [
      'location_search' => 'T',
    ],
    'Search',
    );

    $this->assertSession()->pageTextContains(
    'The location search must contain at least two characters.',
    );
  }

  /**
   * Tests that numeric postal-code searches are rejected.
   */
  public function testNumericLocationSearchIsRejected(): void {
    $account = $this->drupalCreateUser([
      'administer weather settings',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->submitForm(
    [
      'location_search' => '1234',
    ],
    'Search',
    );

    $this->assertSession()->pageTextContains(
    'Enter a location name rather than a numeric postal code.',
    );
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
    'forecast_days',
    '5',
    );
    $this->assertSession()->fieldValueEquals(
    'temperature_unit',
    'celsius',
    );

    $config = $this->config('nome_modulo.settings');

    $originalLocation = $config->get('location');
    $originalLatitude = $config->get('latitude');
    $originalLongitude = $config->get('longitude');
    $originalTimezone = $config->get('timezone');

    $edit = [
      'forecast_days' => '7',
      'temperature_unit' => 'fahrenheit',
    ];

    $this->submitForm($edit, 'Save configuration');

    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    $config = $this->config('nome_modulo.settings');

    $this->assertSame(
    $originalLocation,
    $config->get('location'),
    );

    $this->assertSame(
    $originalLatitude,
    $config->get('latitude'),
    );

    $this->assertSame(
    $originalLongitude,
    $config->get('longitude'),
    );

    $this->assertSame(
    $originalTimezone,
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
   * Tests that forecast days below the minimum are rejected.
   */
  public function testForecastDaysBelowMinimumIsRejected(): void {
    $account = $this->drupalCreateUser([
      'administer weather settings',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->submitForm(
    [
      'forecast_days' => '0',
      'temperature_unit' => 'celsius',
    ],
    'Save configuration',
    );

    $this->assertSession()->pageTextContains(
    'Forecast days must be higher than or equal to 1.',
    );
  }

  /**
   * Tests that forecast days above the maximum are rejected.
   */
  public function testForecastDaysAboveMaximumIsRejected(): void {
    $account = $this->drupalCreateUser([
      'administer weather settings',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/services/nome-modulo');

    $this->submitForm(
    [
      'forecast_days' => '17',
      'temperature_unit' => 'celsius',
    ],
    'Save configuration',
    );

    $this->assertSession()->pageTextContains(
    'Forecast days must be lower than or equal to 16.',
    );
  }

}

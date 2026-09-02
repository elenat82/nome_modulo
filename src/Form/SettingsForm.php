<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Nome Modulo settings form.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * The configuration object name.
   */
  private const CONFIG_NAME = 'nome_modulo.settings';

  /**
   * Maximum number of forecast days supported by the provider.
   */
  private const MAX_FORECAST_DAYS = 16;

  /**
   * Returns the unique ID of the settings form.
   *
   * @return string
   *   The form ID.
   */
  public function getFormId(): string {
    return 'nome_modulo_settings';
  }

  /**
   * Returns the configuration objects editable by this form.
   *
   * @return string[]
   *   The editable configuration object names.
   */
  protected function getEditableConfigNames(): array {
    return [
      self::CONFIG_NAME,
    ];
  }

  /**
   * Builds the module settings form.
   *
   * Provides fields for configuring the location, coordinates, timezone,
   * number of forecast days and temperature unit.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The complete form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Location'),
      '#description' => $this->t(
        'Enter the human-readable name displayed with the forecast.',
      ),
      '#required' => TRUE,
      '#maxlength' => 128,
      '#config_target' => self::CONFIG_NAME . ':location',
    ];

    $form['coordinates'] = [
      '#type' => 'details',
      '#title' => $this->t('Coordinates'),
      '#description' => $this->t(
        'Coordinates are used to request the weather forecast.',
      ),
      '#open' => TRUE,
    ];

    $form['coordinates']['latitude'] = [
      '#type' => 'number',
      '#title' => $this->t('Latitude'),
      '#description' => $this->t(
        'Enter a value between -90 and 90.',
      ),
      '#required' => TRUE,
      '#min' => -90,
      '#max' => 90,
      '#step' => 0.000001,
      '#config_target' => self::CONFIG_NAME . ':latitude',
    ];

    $form['coordinates']['longitude'] = [
      '#type' => 'number',
      '#title' => $this->t('Longitude'),
      '#description' => $this->t(
        'Enter a value between -180 and 180.',
      ),
      '#required' => TRUE,
      '#min' => -180,
      '#max' => 180,
      '#step' => 0.000001,
      '#config_target' => self::CONFIG_NAME . ':longitude',
    ];

    $form['forecast'] = [
      '#type' => 'details',
      '#title' => $this->t('Forecast options'),
      '#open' => TRUE,
    ];

    $timezones = \DateTimeZone::listIdentifiers();

    $form['forecast']['timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Timezone'),
      '#description' => $this->t(
        'Select the timezone used to group the daily forecast.',
      ),
      '#options' => array_combine($timezones, $timezones),
      '#required' => TRUE,
      '#config_target' => self::CONFIG_NAME . ':timezone',
    ];

    $form['forecast']['forecast_days'] = [
      '#type' => 'number',
      '#title' => $this->t('Forecast days'),
      '#description' => $this->t(
        'Enter the number of days requested from the weather provider.',
      ),
      '#required' => TRUE,
      '#min' => 1,
      '#max' => self::MAX_FORECAST_DAYS,
      '#step' => 1,
      '#config_target' => self::CONFIG_NAME . ':forecast_days',
    ];

    $form['forecast']['temperature_unit'] = [
      '#type' => 'radios',
      '#title' => $this->t('Temperature unit'),
      '#options' => [
        'celsius' => $this->t('Celsius'),
        'fahrenheit' => $this->t('Fahrenheit'),
      ],
      '#required' => TRUE,
      '#config_target' => self::CONFIG_NAME . ':temperature_unit',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Validates the settings form.
   */
  public function validateForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    $location = trim((string) $form_state->getValue('location'));

    $form_state->setValue('location', $location);

    if (mb_strlen($location) < 2) {
      $form_state->setErrorByName(
      'location',
      $this->t(
        'The location must contain at least two characters.',
      ),
      );
    }

    $latitude = filter_var(
    $form_state->getValue('latitude'),
    FILTER_VALIDATE_FLOAT,
    );

    if (
    $latitude === FALSE ||
    $latitude < -90 ||
    $latitude > 90
    ) {
      $form_state->setErrorByName(
      'latitude',
      $this->t(
        'The latitude must be a number between -90 and 90.',
      ),
      );
    }

    $longitude = filter_var(
    $form_state->getValue('longitude'),
    FILTER_VALIDATE_FLOAT,
    );

    if (
    $longitude === FALSE ||
    $longitude < -180 ||
    $longitude > 180
    ) {
      $form_state->setErrorByName(
      'longitude',
      $this->t(
        'The longitude must be a number between -180 and 180.',
      ),
      );
    }

    $timezone = (string) $form_state->getValue('timezone');

    if (!in_array(
    $timezone,
    \DateTimeZone::listIdentifiers(),
    TRUE,
    )) {
      $form_state->setErrorByName(
      'timezone',
      $this->t('Select a valid timezone.'),
      );
    }

    $forecastDays = filter_var(
    $form_state->getValue('forecast_days'),
    FILTER_VALIDATE_INT,
    [
      'options' => [
        'min_range' => 1,
        'max_range' => self::MAX_FORECAST_DAYS,
      ],
    ],
    );

    if ($forecastDays === FALSE) {
      $form_state->setErrorByName(
      'forecast_days',
      $this->t(
        'The number of forecast days must be between 1 and @maximum.',
        [
          '@maximum' => self::MAX_FORECAST_DAYS,
        ],
      ),
      );
    }

    $temperatureUnit = (string) $form_state->getValue(
    'temperature_unit',
    );

    if (!in_array(
    $temperatureUnit,
    ['celsius', 'fahrenheit'],
    TRUE,
    )) {
      $form_state->setErrorByName(
      'temperature_unit',
      $this->t('Select a valid temperature unit.'),
      );
    }

    parent::validateForm($form, $form_state);
  }

}

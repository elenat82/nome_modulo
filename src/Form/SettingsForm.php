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

}

<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\nome_modulo\Service\LocationGeocoderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The location geocoder.
   *
   * @var \Drupal\nome_modulo\Service\LocationGeocoderInterface
   */
  protected LocationGeocoderInterface $locationGeocoder;

  /**
   * Constructs a SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed configuration manager.
   * @param \Drupal\nome_modulo\Service\LocationGeocoderInterface $locationGeocoder
   *   The location geocoder.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    TypedConfigManagerInterface $typedConfigManager,
    LocationGeocoderInterface $locationGeocoder,
  ) {
    parent::__construct(
    $configFactory,
    $typedConfigManager,
    );

    $this->locationGeocoder = $locationGeocoder;
  }

  /**
   * Creates a SettingsForm instance from the service container.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @return static
   *   The instantiated settings form.
   */
  public static function create(ContainerInterface $container): static {
    return new static(
    $container->get('config.factory'),
    $container->get('config.typed'),
    $container->get(LocationGeocoderInterface::class),
    );
  }

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
   * Provides location search and forecast configuration fields. Coordinates
   * and timezone are derived from the selected geocoding result.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The complete form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $config = $this->config(self::CONFIG_NAME);

    $form['location_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Location'),
      '#description' => $this->t(
    'Search for a location and select a result to update the coordinates and timezone used for the forecast.',
      ),
      '#open' => TRUE,
    ];

    $form['location_settings']['current_location'] = [
      '#type' => 'item',
      '#title' => $this->t('Current location'),
      '#markup' => $this->t(
    '@location — @latitude, @longitude — @timezone',
    [
      '@location' => (string) $config->get('location'),
      '@latitude' => (string) $config->get('latitude'),
      '@longitude' => (string) $config->get('longitude'),
      '@timezone' => (string) $config->get('timezone'),
    ],
      ),
    ];

    $form['location_settings']['location_search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search location'),
      '#description' => $this->t(
    'Enter a city or a more specific query such as "Turin, Italy".',
      ),
      '#maxlength' => 128,
    ];

    $form['location_settings']['search_location'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#name' => 'search_location',
      '#submit' => [
        '::searchLocation',
      ],
      '#limit_validation_errors' => [
      ['location_search'],
      ],
    ];

    $locationCandidates = $form_state->get('location_candidates');

    if (is_array($locationCandidates)) {
      if ($locationCandidates === []) {
        $form['location_settings']['search_results'] = [
          '#type' => 'item',
          '#title' => $this->t('Search results'),
          '#markup' => $this->t(
        'No matching locations were found, or the geocoding service is temporarily unavailable.',
          ),
        ];
      }
      else {
        $options = [];

        foreach ($locationCandidates as $index => $locationCandidate) {
          $options[$index] = $this->buildLocationLabel(
            $locationCandidate,
          );
        }

        $form['location_settings']['location_candidate'] = [
          '#type' => 'radios',
          '#title' => $this->t('Search results'),
          '#description' => $this->t(
        'Select the location to use and then save the configuration.',
          ),
          '#options' => $options,
        ];
      }
    }

    $form['location'] = [
      '#type' => 'hidden',
      '#config_target' => self::CONFIG_NAME . ':location',
    ];

    $form['latitude'] = [
      '#type' => 'hidden',
      '#config_target' => self::CONFIG_NAME . ':latitude',
    ];

    $form['longitude'] = [
      '#type' => 'hidden',
      '#config_target' => self::CONFIG_NAME . ':longitude',
    ];

    $form['timezone'] = [
      '#type' => 'hidden',
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
   * Searches for matching locations.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function searchLocation(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    $query = trim(
    (string) $form_state->getValue('location_search'),
    );

    $form_state->setValue(
    'location_search',
    $query,
    );

    $form_state->unsetValue('location_candidate');

    $userInput = $form_state->getUserInput();
    unset($userInput['location_candidate']);
    $form_state->setUserInput($userInput);

    $form_state->set(
    'location_candidates',
    $this->locationGeocoder->search($query),
    );

    $form_state->setRebuild();
  }

  /**
   * Builds a human-readable location label.
   *
   * @param array $location
   *   The normalized location. Expected keys are name, country, admin1,
   *   latitude, longitude, and timezone.
   *
   * @return string
   *   The location label.
   */
  private function buildLocationLabel(array $location): string {
    $parts = [
      $location['name'],
    ];

    foreach ([
      $location['admin1'],
      $location['country'],
    ] as $part) {
      if (
      $part !== NULL &&
      !in_array($part, $parts, TRUE)
      ) {
        $parts[] = $part;
      }
    }

    return implode(', ', $parts);
  }

  /**
   * Validates the settings form.
   */
  public function validateForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    $triggeringElement = $form_state->getTriggeringElement();

    if (
    isset($triggeringElement['#name']) &&
    $triggeringElement['#name'] === 'search_location'
    ) {
      $query = trim(
        (string) $form_state->getValue('location_search'),
      );

      $form_state->setValue(
        'location_search',
        $query,
      );

      if (mb_strlen($query) < 2) {
        $form_state->setErrorByName(
          'location_search',
          $this->t(
            'The location search must contain at least two characters.',
          ),
        );
      }

      if (ctype_digit($query)) {
        $form_state->setErrorByName(
        'location_search',
        $this->t(
        'Enter a location name rather than a numeric postal code.',
        ),
        );
      }

      return;
    }

    $locationCandidates = $form_state->get(
    'location_candidates',
    );

    $selectedCandidate = $form_state->getValue(
    'location_candidate',
    );

    if (
    $selectedCandidate !== NULL &&
    is_array($locationCandidates) &&
    isset($locationCandidates[$selectedCandidate])
    ) {
      $location = $locationCandidates[$selectedCandidate];

      $form_state->setValue(
        'location',
        $this->buildLocationLabel($location),
      );

      $form_state->setValue(
        'latitude',
        $location['latitude'],
      );

      $form_state->setValue(
        'longitude',
        $location['longitude'],
      );

      $form_state->setValue(
        'timezone',
        $location['timezone'],
      );
    }
    else {
      $config = $this->config(self::CONFIG_NAME);

      $form_state->setValue(
        'location',
        $config->get('location'),
      );

      $form_state->setValue(
        'latitude',
        $config->get('latitude'),
      );

      $form_state->setValue(
        'longitude',
        $config->get('longitude'),
      );

      $form_state->setValue(
        'timezone',
        $config->get('timezone'),
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

  /**
   * Submits the settings form.
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    parent::submitForm($form, $form_state);

    $form_state->setRedirect('nome_modulo.settings');
  }

}

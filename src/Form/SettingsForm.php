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
   * Provides fields for configuring the latitude and longitude used to retrieve
   * weather forecast data.
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
    $form['latitude'] = [
      '#type' => 'number',
      '#title' => $this->t('Latitude'),
      '#description' => $this->t('Enter a latitude between -90 and 90.'),
      '#min' => -90,
      '#max' => 90,
      '#step' => 0.0001,
      '#config_target' => self::CONFIG_NAME . ':latitude',
      '#required' => TRUE,
    ];

    $form['longitude'] = [
      '#type' => 'number',
      '#title' => $this->t('Longitude'),
      '#description' => $this->t('Enter a longitude between -180 and 180.'),
      '#min' => -180,
      '#max' => 180,
      '#step' => 0.0001,
      '#config_target' => self::CONFIG_NAME . ':longitude',
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

}

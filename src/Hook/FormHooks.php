<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\NodeInterface;

/**
 * Implements hooks related to forms.
 */
final class FormHooks {

  /**
   * Constructs a FormHooks object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Adds location context to Weather alert forms.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current form state.
   * @param string $formId
   *   The form ID.
   */
  #[Hook('form_node_form_alter')]
  public function nodeFormAlter(
    array &$form,
    FormStateInterface $formState,
    string $formId,
  ): void {
    $formObject = $formState->getFormObject();

    if (!method_exists($formObject, 'getEntity')) {
      return;
    }

    $node = $formObject->getEntity();

    if (
      !$node instanceof NodeInterface ||
      $node->bundle() !== 'weather_alert'
    ) {
      return;
    }

    if (
      !$node->isNew() &&
      $node->hasField('field_alert_location') &&
      !$node->get('field_alert_location')->isEmpty()
    ) {
      $location = (string) $node
        ->get('field_alert_location')
        ->value;
    }
    else {
      $location = (string) $this->configFactory
        ->get('nome_modulo.settings')
        ->get('location');
    }

    $form['weather_alert_context'] = [
      '#type' => 'item',
      '#title' => new TranslatableMarkup('Alert location'),
      '#markup' => new TranslatableMarkup(
        'This is an editorial Weather alert for @location. It is created manually and is not generated from Open-Meteo forecast data.',
        [
          '@location' => $location,
        ],
      ),
      '#weight' => -5,
    ];
  }

}

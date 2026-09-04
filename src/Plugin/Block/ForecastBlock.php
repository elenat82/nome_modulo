<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\nome_modulo\Service\ForecastClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\nome_modulo\Cache\ForecastCache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a weather forecast block.
 */
#[Block(
  id: 'nome_modulo_forecast',
  admin_label: new TranslatableMarkup('Weather forecast'),
  category: new TranslatableMarkup('Nome Modulo'),
)]
final class ForecastBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Default number of days displayed by an extended block.
   */
  private const DEFAULT_NUMBER_OF_DAYS = 3;

  /**
   * Maximum number of days displayed by a block.
   */
  private const MAX_NUMBER_OF_DAYS = 16;

  /**
   * Constructs a ForecastBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\nome_modulo\Service\ForecastClientInterface $forecastClient
   *   The service used to retrieve weather forecast data.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly ForecastClientInterface $forecastClient,
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * Creates an instance of the forecast block plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   A new instance of the forecast block plugin.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get(ForecastClientInterface::class),
    );
  }

  /**
   * Builds the weather forecast block.
   *
   * @return array
   *   A render array containing the weather forecast block.
   */
  public function build(): array {
    $forecast = $this->forecastClient->getForecast();

    $display = (string) $this->configuration['display'];
    $showLink = (bool) $this->configuration['show_link'];

    if (
      $forecast === NULL ||
      empty($forecast['days'])
    ) {
      return [
        '#theme' => 'nome_modulo_forecast_block',
        '#location' => NULL,
        '#days' => [],
        '#display' => $display,
        '#temperature_unit' => NULL,
        '#forecast_link' => [],
        '#error_message' => $this->t(
          'The weather forecast is temporarily unavailable.',
        ),
        '#cache' => [
          'max-age' => 60,
        ],
      ];
    }

    $numberOfDays = $display === 'summary'
    ? 1
    : (int) $this->configuration['number_of_days'];

    $days = array_slice(
            $forecast['days'],
            0,
            $numberOfDays,
        );

    $forecastLink = [];
    if ($showLink) {
      $forecastLink = [
        '#type' => 'link',
        '#title' => $this->t('View full forecast'),
        '#url' => Url::fromRoute(
                'nome_modulo.forecast',
                [
                  'display' => 'extended',
                ],
        ),
      ];
    }

    return [
      '#theme' => 'nome_modulo_forecast_block',
      '#location' => (string) $forecast['location'],
      '#days' => $days,
      '#display' => $display,
      '#temperature_unit' => (string) $forecast['temperature_unit'],
      '#forecast_link' => $forecastLink,
      '#error_message' => NULL,
    ];
  }

  /**
   * Returns the default configuration for the forecast block.
   *
   * @return array
   *   The default block configuration.
   */
  public function defaultConfiguration(): array {
    return [
      'display' => 'summary',
      'number_of_days' => self::DEFAULT_NUMBER_OF_DAYS,
      'show_link' => TRUE,
    ] + parent::defaultConfiguration();
  }

  /**
   * Builds the forecast block configuration form.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The block configuration form.
   */
  public function blockForm(
    $form,
    FormStateInterface $form_state,
  ): array {
    $form = parent::blockForm($form, $form_state);

    $form['display'] = [
      '#type' => 'radios',
      '#title' => $this->t('Display mode'),
      '#options' => [
        'summary' => $this->t('Summary'),
        'extended' => $this->t('Extended'),
      ],
      '#default_value' => $this->configuration['display'],
      '#required' => TRUE,
    ];

    $form['number_of_days'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of days'),
      '#description' => $this->t(
      'Used only when the extended display mode is selected.',
      ),
      '#default_value' => $this->configuration['number_of_days'],
      '#min' => 1,
      '#max' => self::MAX_NUMBER_OF_DAYS,
      '#required' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="settings[display]"]' => [
            'value' => 'extended',
          ],
        ],
      ],
    ];

    $form['show_link'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show a link to the full forecast'),
      '#default_value' => $this->configuration['show_link'],
    ];

    return $form;
  }

  /**
   * Validates the forecast block configuration.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function blockValidate(
    $form,
    FormStateInterface $form_state,
  ): void {
    parent::blockValidate($form, $form_state);

    $display = $form_state->getValue('display');

    if (!in_array($display, ['summary', 'extended'], TRUE)) {
      $form_state->setErrorByName(
      'display',
      $this->t('Select a valid display mode.'),
      );
    }

    $numberOfDays = (int) $form_state->getValue(
    'number_of_days',
    );

    if (
    $numberOfDays < 1 ||
    $numberOfDays > self::MAX_NUMBER_OF_DAYS
    ) {
      $form_state->setErrorByName(
      'number_of_days',
      $this->t(
        'The number of days must be between 1 and @maximum.',
        [
          '@maximum' => self::MAX_NUMBER_OF_DAYS,
        ],
      ),
      );
    }
  }

  /**
   * Saves the forecast block configuration.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function blockSubmit(
    $form,
    FormStateInterface $form_state,
  ): void {
    parent::blockSubmit($form, $form_state);

    $this->setConfigurationValue(
    'display',
    $form_state->getValue('display'),
    );

    $this->setConfigurationValue(
    'number_of_days',
    (int) $form_state->getValue('number_of_days'),
    );

    $this->setConfigurationValue(
    'show_link',
    (bool) $form_state->getValue('show_link'),
    );
  }

  /**
   * Determines whether the user can view the forecast block.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account for which access is checked.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  protected function blockAccess(
    AccountInterface $account,
  ): AccessResultInterface {
    return AccessResult::allowedIfHasPermission(
      $account,
      'view weather forecast',
    );
  }

  /**
   * Returns the cache tags associated with the block.
   *
   * @return string[]
   *   The cache tags.
   */
  public function getCacheTags(): array {
    return Cache::mergeTags(
      parent::getCacheTags(),
      [
        ForecastCache::TAG,
      ],
    );
  }

  /**
   * Returns the maximum cache age for the block.
   *
   * @return int
   *   The maximum cache age in seconds.
   */
  public function getCacheMaxAge(): int {
    return Cache::mergeMaxAges(
      parent::getCacheMaxAge(),
      ForecastCache::MAX_AGE,
    );
  }

}

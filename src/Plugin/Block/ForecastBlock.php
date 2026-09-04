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

    if (
      $forecast === NULL ||
      empty($forecast['days'])
    ) {
      return [
        '#theme' => 'nome_modulo_forecast_block',
        '#location' => NULL,
        '#days' => [],
        '#display' => 'summary',
        '#temperature_unit' => NULL,
        '#error_message' => $this->t(
          'The weather forecast is temporarily unavailable.',
        ),
        '#cache' => [
          'max-age' => 60,
        ],
      ];
    }

    return [
      '#theme' => 'nome_modulo_forecast_block',
      '#location' => (string) $forecast['location'],
      '#days' => array_slice(
        $forecast['days'],
        0,
        1,
      ),
      '#display' => 'summary',
      '#temperature_unit' => (string) $forecast['temperature_unit'],
      '#error_message' => NULL,
    ];
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

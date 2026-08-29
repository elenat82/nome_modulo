<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\nome_modulo\Service\ForecastClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the weather forecast page.
 */
final class ForecastController extends ControllerBase {

  /**
   * Constructs a ForecastController object.
   *
   * @param \Drupal\nome_modulo\Service\ForecastClientInterface $forecastClient
   *   The forecast client.
   */
  public function __construct(
    private readonly ForecastClientInterface $forecastClient,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(ForecastClientInterface::class),
    );
  }

  /**
   * Builds the weather forecast page.
   *
   * @return array
   *   A render array containing the weather forecast page content.
   */
  public function build(): array {
    $forecast = $this->forecastClient->getForecast();

    return [
      '#markup' => $this->t(
        'Current temperature: @temperature °C',
        [
          '@temperature' => $forecast['temperature'],
        ],
      ),
    ];
  }

}

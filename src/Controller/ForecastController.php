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
   * Creates a ForecastController instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @return static
   *   A new ForecastController instance.
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
  public function build(string $display): array {

    return [
      '#theme' => 'nome_modulo_forecast',
      '#forecast' => $this->forecastClient->getForecast(),
      '#display' => $display,
    ];
  }

}

<?php

declare(strict_types=1);

namespace Drupal\nome_modulo_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\nome_modulo_test\Service\FakeForecastClient;

/**
 * Overrides services for Nome Modulo tests.
 */
final class NomeModuloTestServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container): void {
    if ($container->hasDefinition('nome_modulo.forecast_client')) {
      $container
        ->getDefinition('nome_modulo.forecast_client')
        ->setClass(FakeForecastClient::class)
        ->setArguments([]);
    }
  }

}

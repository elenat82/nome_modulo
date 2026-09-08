<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Event;

/**
 * Defines events dispatched by Nome Modulo.
 */
final class NomeModuloEvents {

  /**
   * Dispatched after a Weather alert has been created.
   */
  public const WEATHER_ALERT_CREATED = 'nome_modulo.weather_alert_created';

}

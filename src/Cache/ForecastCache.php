<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Cache;

/**
 * Defines cache metadata for weather forecast data.
 */
final class ForecastCache {

  /**
   * Forecast cache lifetime in seconds.
   */
  public const MAX_AGE = 1800;

  /**
   * Cache tag used for forecast data.
   */
  public const TAG = 'nome_modulo:forecast';

}

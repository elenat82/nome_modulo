<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides the weather forecast page.
 */
final class ForecastController extends ControllerBase {

  /**
   * Builds the weather forecast page.
   *
   * @return array
   *   A render array containing the weather forecast page content.
   */
  public function build(): array {
    return [
      '#markup' => $this->t('Weather forecast will be displayed here.'),
    ];
  }

}

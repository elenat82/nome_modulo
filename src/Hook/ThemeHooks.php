<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Provides theme-related hook implementations.
 */
final class ThemeHooks {

  /**
   * Registers the module's theme implementations.
   *
   * @param array $existing
   *   Existing theme implementations.
   * @param string $type
   *   The type of extension being processed.
   * @param string $theme
   *   The name of the extension being processed.
   * @param string $path
   *   The path to the extension.
   *
   * @return array
   *   The module's theme implementations.
   */
  #[Hook('theme')]
  public function theme(
    array $existing,
    string $type,
    string $theme,
    string $path,
  ): array {
    return [
      'nome_modulo_forecast' => [
        'variables' => [
          'forecast' => NULL,
          'display' => NULL,
        ],
      ],
    ];
  }

  /**
   * Implements hook_preprocess_nome_modulo_forecast().
   */
  #[Hook('preprocess_nome_modulo_forecast')]
  public function preprocessForecast(array &$variables): void {
    $variables['#attached']['library'][] = 'nome_modulo/forecast';
  }

}

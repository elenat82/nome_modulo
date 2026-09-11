<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Extension;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Prevents module uninstall while Weather alert content exists.
 */
final class WeatherAlertUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * Constructs a WeatherAlertUninstallValidator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    TranslationInterface $stringTranslation,
  ) {
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module): array {
    if ($module !== 'nome_modulo') {
      return [];
    }

    $alertIds = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'weather_alert')
      ->range(0, 1)
      ->execute();

    if ($alertIds === []) {
      return [];
    }

    return [
      (string) $this->t(
    'Weather alert content exists. Delete all Weather alert content before uninstalling Nome Modulo',
      ),
    ];
  }

}

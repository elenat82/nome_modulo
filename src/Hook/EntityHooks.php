<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Implements hooks related to entities.
 */
final class EntityHooks {

  /**
   * Adds validation constraints to Weather Alert fields.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface[] $fields
   *   The bundle field definitions.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition.
   * @param string $bundle
   *   The bundle name.
   */
  #[Hook('entity_bundle_field_info_alter')]
  public function entityBundleFieldInfoAlter(
    array &$fields,
    EntityTypeInterface $entityType,
    string $bundle,
  ): void {
    if (
      $entityType->id() !== 'node' ||
      $bundle !== 'weather_alert' ||
      !isset($fields['field_alert_end'])
    ) {
      return;
    }

    $fields['field_alert_end']->addConstraint(
      'WeatherAlertDateRange',
    );
  }

}

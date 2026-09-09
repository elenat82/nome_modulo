<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Entity\EntityInterface;
use Drupal\nome_modulo\Event\NomeModuloEvents;
use Drupal\nome_modulo\Event\WeatherAlertCreatedEvent;
use Drupal\node\NodeInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Implements hooks related to entities.
 */
final class EntityHooks {

  /**
   * Constructs an EntityHooks object.
   *
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(
    private readonly EventDispatcherInterface $eventDispatcher,
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
 * Stores the configured location on newly created Weather alerts.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity being saved.
 */
  #[Hook('entity_presave')]
  public function entityPresave(EntityInterface $entity): void {
    if (
    !$entity instanceof NodeInterface ||
    $entity->bundle() !== 'weather_alert' ||
    !$entity->isNew() ||
    !$entity->hasField('field_alert_location') ||
    !$entity->get('field_alert_location')->isEmpty()
    ) {
      return;
    }

    $location = trim(
    (string) $this->configFactory
      ->get('nome_modulo.settings')
      ->get('location'),
    );

    if ($location === '') {
      return;
    }

    $entity->set(
    'field_alert_location',
    $location,
    );
  }

  /**
   * Dispatches an event when a Weather alert is created.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The newly created entity.
   */
  #[Hook('entity_insert')]
  public function entityInsert(EntityInterface $entity): void {
    if (
    !$entity instanceof NodeInterface ||
    $entity->bundle() !== 'weather_alert'
    ) {
      return;
    }

    $event = new WeatherAlertCreatedEvent($entity);

    $this->eventDispatcher->dispatch(
    $event,
    NomeModuloEvents::WEATHER_ALERT_CREATED,
    );
  }

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

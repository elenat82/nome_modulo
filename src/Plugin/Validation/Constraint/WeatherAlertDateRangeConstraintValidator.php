<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Plugin\Validation\Constraint;

use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the date range of a Weather Alert.
 */
final class WeatherAlertDateRangeConstraintValidator extends ConstraintValidator {

  /**
   * Validates that the end date is later than the start date.
   *
   * @param mixed $value
   *   The value being validated.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The validation constraint.
   */
  public function validate(
    mixed $value,
    Constraint $constraint,
  ): void {
    if (
      !$constraint instanceof WeatherAlertDateRangeConstraint ||
      !$value instanceof FieldItemListInterface ||
      $value->isEmpty()
    ) {
      return;
    }

    $entity = $value->getEntity();

    if (
      !$entity->hasField('field_alert_start') ||
      $entity->get('field_alert_start')->isEmpty()
    ) {
      return;
    }

    $startItem = $entity
      ->get('field_alert_start')
      ->first();

    $endItem = $value->first();

    if (
      $startItem === NULL ||
      $endItem === NULL ||
      empty($startItem->date) ||
      empty($endItem->date)
    ) {
      return;
    }

    if ($endItem->date <= $startItem->date) {
      $this->context
        ->buildViolation($constraint->message)
        ->addViolation();
    }
  }

}

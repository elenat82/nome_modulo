<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Defines the Weather Alert date range validation constraint.
 */
#[Constraint(
  id: 'WeatherAlertDateRange',
  label: new TranslatableMarkup(
    'Weather alert date range',
    [],
    ['context' => 'Validation'],
  ),
  type: FALSE,
)]
final class WeatherAlertDateRangeConstraint extends SymfonyConstraint {

  /**
   * The validation error message.
   */
  public string $message = 'The end date must be later than the start date.';

}

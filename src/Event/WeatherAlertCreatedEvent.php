<?php

declare(strict_types=1);

namespace Drupal\nome_modulo\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\node\NodeInterface;

/**
 * Event dispatched after a Weather alert has been created.
 */
final class WeatherAlertCreatedEvent extends Event {

  /**
   * Constructs a WeatherAlertCreatedEvent object.
   *
   * @param \Drupal\node\NodeInterface $alert
   *   The created Weather alert.
   */
  public function __construct(
    private readonly NodeInterface $alert,
  ) {}

  /**
   * Returns the created Weather alert.
   *
   * @return \Drupal\node\NodeInterface
   *   The created Weather alert.
   */
  public function getAlert(): NodeInterface {
    return $this->alert;
  }

}

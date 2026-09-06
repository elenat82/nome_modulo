<?php

declare(strict_types=1);

namespace Drupal\nome_modulo;

use Drupal\Core\State\StateInterface;

/**
 * Stores environment-specific forecast status information.
 */
final class ForecastStatusStorage {

  /**
   * State key containing the last cron execution timestamp.
   */
  private const LAST_CRON_RUN = 'nome_modulo.last_cron_run';

  /**
   * State key containing the last successful forecast refresh timestamp.
   */
  private const LAST_SUCCESSFUL_REFRESH = 'nome_modulo.last_successful_forecast_refresh';

  /**
   * Constructs a ForecastStatusStorage object.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service used to store environment-specific status values.
   */
  public function __construct(
    private readonly StateInterface $state,
  ) {}

  /**
   * Returns the timestamp of the last cron execution.
   *
   * @return int|null
   *   The timestamp of the last cron execution, or NULL if unavailable.
   */
  public function getLastCronRun(): ?int {
    $value = $this->state->get(
      self::LAST_CRON_RUN,
    );

    return is_int($value) ? $value : NULL;
  }

  /**
   * Stores the timestamp of the last cron execution.
   *
   * @param int $timestamp
   *   The cron execution timestamp.
   */
  public function setLastCronRun(
    int $timestamp,
  ): void {
    $this->state->set(
      self::LAST_CRON_RUN,
      $timestamp,
    );
  }

  /**
   * Returns the timestamp of the last successful forecast refresh.
   *
   * @return int|null
   *   The timestamp of the last successful refresh, or NULL if unavailable.
   */
  public function getLastSuccessfulRefresh(): ?int {
    $value = $this->state->get(
      self::LAST_SUCCESSFUL_REFRESH,
    );

    return is_int($value) ? $value : NULL;
  }

  /**
   * Stores the timestamp of the last successful forecast refresh.
   *
   * @param int $timestamp
   *   The successful forecast refresh timestamp.
   */
  public function setLastSuccessfulRefresh(
    int $timestamp,
  ): void {
    $this->state->set(
      self::LAST_SUCCESSFUL_REFRESH,
      $timestamp,
    );
  }

  /**
   * Removes all state values owned by the forecast status storage.
   */
  public function deleteAll(): void {
    $this->state->deleteMultiple([
      self::LAST_CRON_RUN,
      self::LAST_SUCCESSFUL_REFRESH,
    ]);
  }

}

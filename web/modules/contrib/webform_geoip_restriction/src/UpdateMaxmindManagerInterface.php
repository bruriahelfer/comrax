<?php

declare(strict_types = 1);

namespace Drupal\webform_geoip_restriction;

/**
 * Defines an interface for Update Maxmind Manager classes.
 */
interface UpdateMaxmindManagerInterface {

  /**
   * Update the Maxmind Database.
   */
  public function updMaxmind(): bool|array;

}

<?php

namespace Drupal\geolocation_provider\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Geolocation Provider plugins.
 */
interface GeolocationProviderPluginInterface extends PluginInspectionInterface {

  /**
   * Geolocation a string.
   *
   * @param string $data
   *   The string to geolocalized.
   * @param array $options
   *   Query parameters options.
   * @param bool $to_array
   *   Set to TRUE to get the response as array instead of FeatureCollection.
   *
   * @return \Drupal\geolocation_provider\FeatureCollection|null
   *   A feature collection object or NULL.
   */
  public function geolocation(string $data, array $options = [], bool $to_array = FALSE);

  /**
   * Reverse geolocation coordinates.
   *
   * @param string $latitude
   *   The latitude.
   * @param string $longitude
   *   The longitude.
   * @param bool $to_array
   *   Set to TRUE to get the response as array instead of FeatureCollection.
   *
   * @return \Drupal\geolocation_provider\FeatureCollection|null
   *   A feature collection object or NULL.
   */
  public function reverse(string $latitude, string $longitude, bool $to_array = FALSE);

}

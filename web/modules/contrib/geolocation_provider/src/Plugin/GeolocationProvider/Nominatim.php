<?php

namespace Drupal\geolocation_provider\Plugin\GeolocationProvider;

use Drupal\Core\Url;
use Drupal\geolocation_provider\Plugin\GeolocationProviderPluginBase;

/**
 * Define the nominatim provider.
 *
 * @package Drupal\geolocation_provider\Plugin\GeolocationProvider
 *
 * @GeolocationProvider(
 *   id="nominatim_geolocation_provider",
 *   label=@Translation("Nominatim")
 * )
 */
class Nominatim extends GeolocationProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function geolocation($data, $options = [], $to_array = FALSE) {

    $url = Url::fromUri('https://nominatim.openstreetmap.org/search', [
      'query' => array_merge([
        'q' => $data,
        'format' => 'geojson',
        'polygon' => 1,
      ], $options),
    ]);
    return $this->get($url->toString(), $to_array);
  }

  /**
   * {@inheritdoc}
   */
  public function reverse($latitude, $longitude, $to_array = FALSE) {
    $url = Url::fromUri('https://nominatim.openstreetmap.org/reverse?', [
      'query' => [
        'lat' => $latitude,
        'lon' => $longitude,
      ],
    ]);
    return $this->get($url->toString(), $to_array);
  }

}

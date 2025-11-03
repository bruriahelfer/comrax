<?php

namespace Drupal\geolocation_provider\Plugin\GeolocationProvider;

use Drupal\Core\Url;
use Drupal\geolocation_provider\Plugin\GeolocationProviderPluginBase;

/**
 * Define the bano provider.
 *
 * @package Drupal\geolocation_provider\Plugin\GeolocationProvider
 *
 * @GeolocationProvider(
 *   id="bano_geolocation_provider",
 *   label=@Translation("Bano")
 * )
 */
class Bano extends GeolocationProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function geolocation($data, $options = [], $to_array = FALSE) {
    $url = Url::fromUri('https://api-adresse.data.gouv.fr/search/', [
      'query' => array_merge([
        'q' => $data,
      ], $options),
    ]);
    return $this->get($url->toString(), $to_array);
  }

  /**
   * {@inheritdoc}
   */
  public function reverse($latitude, $longitude, $to_array = FALSE) {
    $url = Url::fromUri('https://api-adresse.data.gouv.fr/reverse/', [
      'query' => [
        'lat' => $latitude,
        'lon' => $longitude,
      ],
    ]);
    return $this->get($url->toString(), $to_array);
  }

}

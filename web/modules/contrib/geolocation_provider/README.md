# Geolocation Provider

Geolocation provider manager. Allow developers to use and define multiple geolocation providers using the Drupal 8 Plugin API.

The module provide a BANO provider by default (Base d'Adresse Nationale Ouverte), a french geolocation provider.
@see : https://geo.api.gouv.fr/adresse for further API's explanations

To create a custom provider, you should define a new GeolocationProvider plugin using the `GeolocationProviderPluginBase` class.

```php
<?php

use Drupal\geolocation_provider\Plugin\GeolocationProviderPluginBase;

/**
 * Sample provider.
 *
 * @GeolocationProvider(
 *   id="sample_geolocation_provider",
 *   label=@Translation("Sample provider")
 * )
 */
class Sample extends GeolocationProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function geolocation($data, $options = []) {
    // If your API doesn't return a FeatureCollection json object, you may not use the get() method.
    // Make your own request using the httpClient property instead.
    return $this->get('your url');
  }

  /**
   * {@inheritdoc}
   */
  public function reverse($latitude, $longitude) {
    return $this->get('your reverse url');
  }

}

```

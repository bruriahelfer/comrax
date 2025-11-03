<?php

namespace Drupal\geolocation_provider\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Geolocation Provider item annotation object.
 *
 * @see \Drupal\geolocation_provider\Plugin\GeolocationProviderPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class GeolocationProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}

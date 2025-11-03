<?php

namespace Drupal\geolocation_provider\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Geolocation Provider plugin manager.
 */
class GeolocationProviderPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new GeolocationProviderPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/GeolocationProvider', $namespaces, $module_handler, 'Drupal\geolocation_provider\Plugin\GeolocationProviderPluginInterface', 'Drupal\geolocation_provider\Annotation\GeolocationProvider');

    $this->alterInfo('geolocation_provider_geolocation_provider_plugin_info');
    $this->setCacheBackend($cache_backend, 'geolocation_provider_geolocation_provider_plugin_plugins');
  }

}

<?php

namespace Drupal\geolocation_provider\Controller;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\geolocation_provider\Plugin\GeolocationProviderPluginManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for the Geolocation providers.
 */
class GeolocationProviderController extends ControllerBase {

  /**
   * The geolocation plugin manager.
   *
   * @var \Drupal\geolocation_provider\Plugin\GeolocationProviderPluginManager
   */
  protected $geolocationManager;

  protected ?LoggerInterface $logger;

  /**
   * Constructs a new GeolocationProviderController object.
   *
   * @param \Drupal\geolocation_provider\Plugin\GeolocationProviderPluginManager $geolocation_manager
   *   The geolocation manager.
   */
  public function __construct(GeolocationProviderPluginManager $geolocation_manager, LoggerInterface $logger = null) {
    $this->geolocationManager = $geolocation_manager;
    $this->logger = $this->getLogger('geolocation_provider');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.geolocation_provider_plugin')
    );
  }

  /**
   * Geolocation callback.
   *
   * @param string $plugin
   *   A plugin id.
   * @param string $search
   *   A string representing the address to geoloc.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response send to the user.
   */
  public function geolocationCallback($plugin, $search) {
    /** @var \Drupal\geolocation_provider\Plugin\GeolocationProviderPluginInterface $instance */
    $instance = NULL;
    $error = '';
    try {
      $instance = $this->geolocationManager->createInstance($plugin);
    }
    catch (PluginException $e) {
      $error = $e->getMessage();
      $this->logger
        ->error('GeolocationProviderController::geolocationCallback error: @error', [
          '@error' => $error,
        ]);
    }
    if (!empty($instance)) {
      return new JsonResponse($instance->geolocation($search, [], TRUE));
    }
    return new JsonResponse($error, 500);
  }

  /**
   * Reverse callback.
   *
   * @param string $plugin
   *   A plugin id.
   * @param float $lat
   *   The latitude used for the reverse search.
   * @param float $lon
   *   The longitude used for the reverse search.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response send to the user.
   */
  public function reverseCallback($plugin, $lat, $lon) {
    /** @var \Drupal\geolocation_provider\Plugin\GeolocationProviderPluginInterface $instance */
    $instance = NULL;
    $error = '';
    try {
      $instance = $this->geolocationManager->createInstance($plugin);
    }
    catch (PluginException $e) {
      $error = $e->getMessage();
      $this->logger
        ->error('GeolocationProviderController::geolocationCallback error: @error', [
          '@error' => $error,
        ]);
    }
    if (!empty($instance)) {
      return new JsonResponse((array) $instance->reverse($lat, $lon, TRUE));
    }
    return new JsonResponse($error, 500);
  }

}

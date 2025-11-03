<?php

namespace Drupal\geolocation_provider\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Base class for Geolocation Provider plugins.
 */
abstract class GeolocationProviderPluginBase extends PluginBase implements GeolocationProviderPluginInterface, ContainerFactoryPluginInterface {

  /**
   * An http client object.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, Client $http_client, Serializer $serializer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->httpClient = $http_client;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
      $container->get('serializer')
    );
  }

  /**
   * Get an http client ready to use for fetching geocode json data.
   *
   * @param string $url
   *   The request url.
   * @param bool $to_array
   *   Set to TRUE to get the response as array instead of FeatureCollection.
   *
   * @return \Drupal\geolocation_provider\FeatureCollection|array|null
   *   The geocode json response object or NULL.
   */
  public function get($url, $to_array = FALSE) {
    if ($to_array) {
      return Json::decode($this->httpClient->get($url)->getBody());
    }
    $response = $this->httpClient->get($url)->getBody();
    return $this->serializer->decode($response, 'geojson');
  }

}

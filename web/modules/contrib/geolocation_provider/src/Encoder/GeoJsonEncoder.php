<?php

namespace Drupal\geolocation_provider\Encoder;

use Drupal\geolocation_provider\FeatureCollection;
use Drupal\serialization\Encoder\JsonEncoder;

/**
 * Overriding JsonEncoder class.
 *
 * @package Drupal\geolocation_provider\Encoder
 */
class GeoJsonEncoder extends JsonEncoder {

  /**
   * The format that this encoder supports.
   *
   * @var string[]
   */
  protected static $formats = ['geojson'];

  /**
   * {@inheritdoc}
   */
  public function supportsDecoding($format, array $context = []): bool {
    return in_array($format, static::$formats);
  }

  /**
   * {@inheritdoc}
   */
  public function decode($data, $format, array $context = []): mixed {
    $json = parent::decode($data, $format, $context);
    if (isset($json['type']) && $json['type'] == 'FeatureCollection') {
      return new FeatureCollection($json['features']);
    }
    return NULL;
  }

}

<?php

namespace Drupal\geolocation_provider;

/**
 * Class Feature.
 *
 * @package Drupal\geolocation_provider
 */
class Feature {

  public $score;

  public $y;

  public $x;

  public $importance;

  public $housenumber;

  public $postcode;

  public $context;

  public $id;

  public $street;

  public $city;

  public $label;

  public $type;

  public $citycode;

  public $name;

  public $geometry;

  /**
   * Feature constructor.
   *
   * @param array $data
   *   Json decoded data.
   */
  public function __construct($data) {
    $properties = $data['properties'] ?? [];
    $this->geometry = $data['geometry'] ?? NULL;
    $this->score = $properties['score'] ?? -1;
    $this->y = $properties['y'] ?? 0;
    $this->x = $properties['x'] ?? 0;
    $this->importance = $properties['importance'] ?? 0;
    $this->housenumber = $properties['housenumber'] ?? '';
    $this->postcode = $properties['postcode'] ?? '';
    $this->context = $properties['context'] ?? '';
    $this->id = $properties['id'] ?? '';
    $this->street = $properties['street'] ?? '';
    $this->city = $properties['city'] ?? '';
    $this->label = $properties['label'] ?? '';
    $this->type = $properties['type'] ?? '';
    $this->citycode = $properties['citycode'] ?? '';
    $this->name = $properties['name'] ?? '';
  }

}

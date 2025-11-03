<?php

namespace Drupal\geolocation_provider;

/**
 * Class that represents the GeoJSON FeatureCollection object.
 *
 * @package Drupal\geolocation_provider
 */
class FeatureCollection implements \Iterator, \Countable {

  /**
   * The current table index.
   *
   * @var int
   */
  private $position;

  /**
   * The collection of geocode json features.
   *
   * @var Feature
   */
  private array|Feature $features;

  /**
   * FeatureCollection constructor.
   *
   * @param array $data
   *   An array of json decoded geocode json feature.
   */
  public function __construct(array $data = []) {
    $this->position = 0;
    $this->features = array_map(function ($f) {
      return new Feature($f);
    }, $data);
  }

  /**
   * {@inheritdoc}
   */
  public function current() {
    return $this->features[$this->position];
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    ++$this->position;
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return $this->position;
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return isset($this->items[$this->position]);
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->position = 0;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return count($this->features);
  }

}

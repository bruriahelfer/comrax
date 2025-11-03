<?php

namespace Drupal\prevnext;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for the main service file.
 *
 * @package Drupal\prevnext
 */
interface PrevNextServiceInterface {

  /**
   * Builds entity previous/next links.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to build links for.
   *
   * @return array
   *   The links render array.
   */
  public function buildEntityLinks(EntityInterface $entity): array;

  /**
   * Retrieves previous and next ids of a given entity, if they exist.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return array
   *   An array of prev/next ids of given entity.
   */
  public function getPreviousNext(EntityInterface $entity);

}

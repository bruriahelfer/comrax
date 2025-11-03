<?php

namespace Drupal\prevnext;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Main service file.
 *
 * @package Drupal\prevnext
 */
class PrevNextService implements PrevNextServiceInterface {

  /**
   * The config factory.
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   */
  private AccountInterface $user;

  /**
   * Previous / Next ids.
   *
   * @var array{prev: ?string, next: ?string}|array{prev: ?int, next: ?int}
   */
  public $prevnext;

  /**
   * Constructs a new PrevNextService instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Defines the interface for a configuration object factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   Defines an account interface which represents the current user.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, AccountInterface $user) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->user = $user;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntityLinks(EntityInterface $entity): array {
    $build = [];

    // Checking if current entity is configured for prevnext or not.
    $config = $this->configFactory->get('prevnext.settings');
    $entity_types = $config->get('prevnext_enabled_entity_types');

    if (empty($entity_types[$entity->getEntityTypeId()])) {
      return $build;
    }

    $entity_bundles = $config->get('prevnext_enabled_entity_bundles');
    if (empty($entity_bundles[$entity->getEntityTypeId()]) || !in_array($entity->bundle(), $entity_bundles[$entity->getEntityTypeId()])) {
      return $build;
    }

    if (!$this->user->hasPermission('view prevnext links') &&
      !$this->user->hasPermission("view {$entity->getEntityTypeId()} prevnext links")
    ) {
      return $build;
    }

    $previous_next = $this->getPreviousNext($entity);
    $cache = [
      'contexts' => [
        'url',
        'user.permissions',
      ],
      'tags' => [
        'config:prevnext.settings',
        "{$entity->getEntityTypeId()}_list",
        "{$entity->getEntityTypeId()}_view",
      ],
    ];

    $items = [
      [
        'key' => 'prev',
        'direction' => 'previous',
        'text' => t('Previous'),
      ],
      [
        'key' => 'next',
        'direction' => 'next',
        'text' => t('Next'),
      ],
    ];

    foreach ($items as $item) {
      if ($previous_next[$item['key']]) {
        $path = '';
        try {
          // Try to build canonical URL for the entity type.
          $path = Url::fromRoute("entity.{$entity->getEntityTypeId()}.canonical", [$entity->getEntityTypeId() => $previous_next[$item['key']]])->toString();
        }
        catch (\Exception $e) {
        }

        if ($path) {
          $build["prevnext_{$item['direction']}"] = [
            '#theme' => 'prevnext',
            '#direction' => $item['direction'],
            '#text' => $item['text'],
            '#id' => $item['key'],
            '#url' => $path,
            '#cache' => $cache,
          ];
        }
      }
    }

    // Once these links will be cached inside the entity rendered output, we
    // will add a custom cache tag to allow invalidation of all these cached
    // info later (for example when a new entity of this type is created).
    $build['#cache']['tags'][] = "prevnext-{$entity->getEntityTypeId()}-{$entity->bundle()}";

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousNext(EntityInterface $entity) {
    $this->prevnext['prev'] = $this->getEntitiesOfType($entity, 'prev');
    $this->prevnext['next'] = $this->getEntitiesOfType($entity, 'next');

    return $this->prevnext;
  }

  /**
   * Retrieves the previous or next ID for the provided entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $order
   *   The order of the query, 'prev' or 'next'.
   *
   * @return string|int|null
   *   The next/previous entity ID after filtering by type, status and language.
   */
  protected function getEntitiesOfType(EntityInterface $entity, $order) {
    $definition = $this->entityTypeManager->getDefinition($entity->getEntityTypeId());
    $query = $this->entityTypeManager->getStorage($entity->getEntityTypeId())->getQuery();

    $query->condition('status', 1);
    $query->range(0, 1);
    $query->accessCheck();
    $query->addTag("prev_next_{$entity->getEntityTypeId()}_type");

    $bundle = $entity->bundle();
    if ($type = $definition->getKey('bundle')) {
      $query->condition($type, $bundle);
      $query->addMetaData($type, $bundle);
    }

    if ($lang = $definition->getKey('langcode')) {
      $langcode = $entity->language()->getId();
      $query->condition($lang, $langcode);
      $query->addMetaData($lang, $langcode);
    }

    if ($id = $definition->getKey('id')) {
      switch ($order) {
        case 'prev':
          $query->condition($id, $entity->id(), '<');
          $query->sort($id, 'DESC');
          break;

        case 'next':
          $query->condition($id, $entity->id(), '>');
          $query->sort($id);
          break;

      }
    }

    $result = NULL;
    if ($results = $query->execute()) {
      $result = reset($results);
    }

    return $result;
  }

}

<?php

namespace Drupal\menu_multilingual\Menu;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;
use Drupal\views\Plugin\Menu\ViewsMenuLink;

/**
 * Class MenuMultilingualLinkTreeModifier.
 *
 * Used to filter out menu items.
 */
class MenuMultilingualLinkTreeModifier implements TrustedCallbackInterface {

  /**
   * Boolean variable for filtering labels.
   *
   * @var bool
   *   Filter labels.
   */
  protected $filterLabels;

  /**
   * Boolean variable for filtering content.
   *
   * @var bool
   *   Filter content.
   */
  protected $filterContent;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   *   The language manager.
   */
  protected $languageManager;

  /**
   * The entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected $entityTypeManager;

  /**
   * The config factory interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   *   The config factory.
   */
  protected $configFactory;

  /**
   * The array field to store db results.
   *
   * @var array
   *   The storages.
   */
  protected $storages;

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['filterLinksInRenderArray'];
  }

  /**
   * MenuMultilingualLinkTreeModifier constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(
    LanguageManagerInterface $languageManager,
    EntityTypeManagerInterface $entityTypeManager,
    ConfigFactoryInterface $configFactory
  ) {
    $this->filterLabels = FALSE;
    $this->filterContent = FALSE;

    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;

    $this->storages = [
      'menu_link_content' => $entityTypeManager->getStorage('menu_link_content'),
    ];
  }

  /**
   * Set filter labels.
   *
   * @param bool $allow_labels
   *   Allow labels.
   */
  public function filterLabels(bool $allow_labels) {
    $this->filterLabels = $allow_labels;
  }

  /**
   * Set filter content.
   *
   * @param bool $allow_content
   *   Allow content.
   */
  public function filterContent(bool $allow_content) {
    $this->filterContent = $allow_content;
  }

  /**
   * Pass menu links from render array of the block to the filter method.
   *
   * @param array $build
   *   The block render-able array.
   *
   * @return array
   *   The modified render-able array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function filterLinksInRenderArray(array $build) {
    $tree =& $build['content']['#items'];
    if (!is_array($tree)) {
      return $build;
    }
    $tree = $this->filtersLinks($tree);
    // Hide block if there are no menu items.
    if (empty($tree)) {
      $build = [
        '#markup' => '',
        '#cache' => $build['#cache'],
      ];
    }
    return $build;
  }

  /**
   * Filter wrapper for either links or menu link tree.
   *
   * @param array $tree
   *   The already built menu tree.
   *
   * @return array
   *   The new menu tree.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function filtersLinks(array $tree) {
    $new_tree = [];
    foreach ($tree as $key => $v) {
      if ($tree[$key]['below']) {
        $tree[$key]['below'] = $this->filtersLinks($tree[$key]['below']);
      }
      $link = $tree[$key]['original_link'];
      if ($this->hasTranslationOrIsDefaultLang($link)) {
        $new_tree[$key] = $tree[$key];
      }
    }
    return $new_tree;
  }

  /**
   * Check link for translation or current language.
   *
   * @param mixed $link
   *   The menu link plugin instance.
   *
   * @return bool
   *   True if link pass a multilingual options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function hasTranslationOrIsDefaultLang($link) {
    $current_lang = $this->languageManager->getCurrentLanguage()->getId();
    $result = FALSE;
    $has_translated_label = FALSE;
    $has_translated_content = FALSE;

    if ($this->filterLabels) {
      $has_translated_label = $this->linkIsTranslated($link, $current_lang);
    }
    if ($this->filterContent) {
      $has_translated_content = $this->linkedEntityHasTranslationsOrIsDefault($link, $current_lang);
    }

    if ($this->filterLabels && $this->filterContent) {
      if ($has_translated_label && $has_translated_content) {
        $result = TRUE;
      }
    }
    else {
      if ($this->filterLabels) {
        $result = $has_translated_label;
      }
      elseif ($this->filterContent) {
        $result = $has_translated_content;
      }
    }

    return $result;
  }

  /**
   * Check link for translations or current language.
   *
   * @param mixed $link
   *   The link that will be checked.
   * @param string $lang
   *   The language id.
   *
   * @return bool
   *   True if link pass a multilingual options.
   */
  private function linkIsTranslated($link, $lang) {
    $result = FALSE;

    $callbacks = [
      'isTranslatedMenuLinkContentMultilingual' => $this->isTranslatedMenuLinkContentMultilingual($link, $lang),
      'isTranslatedViewLink' => $this->isTranslatedViewLink($link, $lang),
    ];

    foreach ($callbacks as $condition_check) {
      if ($condition_check === NULL) {
        continue;
      }
      $result = $condition_check;
      break;
    }

    return $result;
  }

  /**
   * Check menu item link for translations or current language.
   *
   * @param mixed $link
   *   The link that will be checked.
   * @param string $lang
   *   The language id.
   *
   * @return bool
   *   True if link pass a multilingual options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  private function linkedEntityHasTranslationsOrIsDefault($link, $lang) {
    if (empty($link->getRouteName()) || strpos($link->getRouteName(), 'entity.') === FALSE) {
      return TRUE;
    }

    $type = current(array_keys($link->getRouteParameters()));
    $id = $link->getRouteParameters()[$type];
    $result = FALSE;

    if (empty($type) || empty($id)) {
      return $result;
    }

    if (!array_key_exists($type, $this->storages)) {
      $this->storages[$type] = $this->entityTypeManager->getStorage($type);
    }
    $storage = $this->storages[$type];

    $entity = $storage->load($id);

    if ($lang === $entity->get('langcode')) {
      $result = TRUE;
    }
    elseif ($this->entityHasTranslation($entity, $lang)) {
      $result = TRUE;
    }

    $storage->resetCache([$id]);

    return $result;
  }

  /**
   * Helper method to check if entity is translateable.
   *
   * @param \Drupal\menu_multilingual\Plugin\Menu\MenuLinkContentMultilingual|\Drupal\Core\Entity\ContentEntityBase $entity
   *   The base entity object or menu link plugin to get translations on.
   * @param string $lang
   *   The language id.
   *
   * @return bool
   *   Return true when language matches translations languages,
   *   or non translatable.
   */
  private function entityHasTranslation($entity, $lang) {
    // Return false for "Not Specified" language (langcode 'und').
    if ($entity->language()->getId() == 'und') {
      return FALSE;
    }
    // Return true for non-translatable entities and
    // entity with "Not applicable" language (langcode 'zxx').
    elseif (!method_exists($entity, 'isTranslatable') || $entity->language()
      ->getId() === 'zxx') {
      return TRUE;
    }
    $translation_codes = array_keys($entity->getTranslationLanguages());
    return in_array($lang, $translation_codes);
  }

  /**
   * Check if link is ViewsMenuLink & translated.
   *
   * @param mixed $link
   *   The link that will be checked.
   * @param string $lang
   *   The language id.
   *
   * @return bool
   *   True if link is ViewsMenuLink and has translation.
   */
  private function isTranslatedViewLink($link, $lang) {
    $result = FALSE;
    if (!($link instanceof ViewsMenuLink)) {
      return NULL;
    }

    $view_id = sprintf('views.view.%s', $link->getMetaData()['view_id']);
    $original = $this->configFactory->get($view_id)->get('langcode');

    // Make sure that original configuration exists for given view.
    if (!$original || $lang === $original) {
      $result = TRUE;
    }
    // ConfigurableLanguageManager::getLnguageConfigOverride() always
    // returns a new configuration override for the original language.
    else {
      /** @var \Drupal\language\Config\LanguageConfigOverride $config */
      $config = $this->languageManager->getLanguageConfigOverride($lang, $view_id);
      // Configuration override will be marked as a new if one does not
      // exist for current language (thus has no translation).
      $result = $config->isNew() ? FALSE : TRUE;
    }
    return $result;

  }

  /**
   * Check if link is MenuLinkContent & translated.
   *
   * @param mixed $link
   *   The link that will be checked.
   * @param string $lang
   *   The language id.
   *
   * @return bool
   *   True if link is MenuLinkContent and has translation.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  private function isTranslatedMenuLinkContentMultilingual($link, $lang) {
    $result = FALSE;
    if (!($link instanceof MenuLinkContent)) {
      return NULL;
    }
    $storage = $this->storages['menu_link_content'];
    if (!empty($link->getPluginDefinition()['metadata']['entity_id'])) {
      $entity_id = $link->getPluginDefinition()['metadata']['entity_id'];
      $entity = $storage->load($entity_id);
      $langcode_key = $entity->getEntityType()->getKey('langcode');
      if ($lang == $entity->get($langcode_key)->value) {
        $result = TRUE;
      }
      elseif ($this->entityHasTranslation($entity, $lang)) {
        $result = TRUE;
      }
      $storage->resetCache([$entity_id]);
    }
    return $result;
  }

}

<?php

namespace Drupal\menu_multilingual;

use Drupal\block\BlockInterface;

/**
 * Various functions to assist menu_multilingual block.
 */
class Helpers {

  /**
   * Enable menu_multilingual block processing.
   */
  public static function setBlockProcessing(&$build) {
    /** @var \Drupal\block\BlockInterface $block */
    $block = $build['#block'];

    if (static::isMenuBlock($block) && static::hasMenuMultilingualValues($block)) {
      $modifier = \Drupal::service('menu_multilingual.modifier');
      $modifier->filterLabels((bool) $block->getThirdPartySetting('menu_multilingual', 'only_translated_labels'));
      $modifier->filterContent((bool) $block->getThirdPartySetting('menu_multilingual', 'only_translated_content'));
      $build['#pre_render'][] = [$modifier, 'filterLinksInRenderArray'];
    }
  }

  /**
   * Check entity type for translation capabilities.
   */
  public static function checkEntityType($type) {
    /** @var \Drupal\content_translation\ContentTranslationManager $translationManager */
    $translationManager = \Drupal::service('content_translation.manager');
    return $translationManager->isEnabled($type);
  }

  /**
   * Updater for the menu_multilingual block settings.
   */
  public static function languageContentSettingsSubmit() {
    // @todo Add bulk change for block settings.
    // Use power of https://goo.gl/cm37vj
  }

  /**
   * Check that the given block is a menu block.
   */
  public static function isMenuBlock(BlockInterface $block) {
    $plugin_definition = $block->getPlugin()->getPluginDefinition();
    return in_array($plugin_definition['id'], [
      'menu_block',
      'system_menu_block',
    ]);
  }

  /**
   * Check that the given block contains menu_multilingual settings.
   */
  public static function hasMenuMultilingualValues(BlockInterface $block) {
    $settings = $block->getThirdPartySettings('menu_multilingual');
    return !empty($settings['only_translated_labels']) || !empty($settings['only_translated_content']);
  }

}

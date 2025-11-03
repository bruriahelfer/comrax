<?php

/**
 * @file
 * Post update functions for Menu multilingual.
 */

use Drupal\block\BlockInterface;
use Drupal\Core\Config\Entity\ConfigEntityUpdater;

/**
 * Convert menu multilingual settings to third party settings.
 */
function menu_multilingual_post_update_convert_settings_to_third_party_settings(&$sandbox = NULL) {
  if (!\Drupal::moduleHandler()->moduleExists('block')) {
    return;
  }

  \Drupal::classResolver(ConfigEntityUpdater::class)
    ->update($sandbox, 'block', function (BlockInterface $block) {
      $block_settings = $block->get('settings');
      if (isset($block_settings['only_translated_labels']) || isset($block_settings['only_translated_content'])) {
        $block->setThirdPartySetting('menu_multilingual', 'only_translated_labels', (bool) $block_settings['only_translated_labels']);
        $block->setThirdPartySetting('menu_multilingual', 'only_translated_content', (bool) $block_settings['only_translated_content']);
        unset($block_settings['only_translated_labels'], $block_settings['only_translated_content']);
        $block->set('settings', $block_settings);
        return TRUE;
      }
      return FALSE;
    });
}
